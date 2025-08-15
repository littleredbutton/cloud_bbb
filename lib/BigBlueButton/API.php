<?php

namespace OCA\BigBlueButton\BigBlueButton;

use BigBlueButton\BigBlueButton;
use BigBlueButton\Core\Record;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\DeleteRecordingsParameters;
use BigBlueButton\Parameters\GetRecordingsParameters;
use BigBlueButton\Parameters\InsertDocumentParameters;
use BigBlueButton\Parameters\IsMeetingRunningParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use BigBlueButton\Parameters\PublishRecordingsParameters;
use OCA\BigBlueButton\AppInfo\Application;
use OCA\BigBlueButton\AvatarRepository;
use OCA\BigBlueButton\Crypto;
use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\Event\MeetingStartedEvent;
use OCA\BigBlueButton\UrlHelper;
use OCP\App\IAppManager;
use OCP\Defaults;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;

class API {
	/** @var IConfig */
	private $config;

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var BigBlueButton|null */
	private $server;

	/** @var Crypto */
	private $crypto;

	/** @var IEventDispatcher */
	private $eventDispatcher;

	/** @var IL10N */
	private $l10n;

	/** @var UrlHelper */
	private $urlHelper;

	/** @var Defaults */
	private $defaults;

	/** @var IAppManager */
	private $appManager;

	/** @var AvatarRepository */
	private $avatarRepository;

	/** @var IRequest */
	private $request;

	public function __construct(
		IConfig $config,
		IURLGenerator $urlGenerator,
		Crypto $crypto,
		IEventDispatcher $eventDispatcher,
		IL10N $l10n,
		UrlHelper $urlHelper,
		Defaults $defaults,
		IAppManager $appManager,
		AvatarRepository $avatarRepository,
		IRequest $request
	) {
		$this->config = $config;
		$this->urlGenerator = $urlGenerator;
		$this->crypto = $crypto;
		$this->eventDispatcher = $eventDispatcher;
		$this->l10n = $l10n;
		$this->urlHelper = $urlHelper;
		$this->defaults = $defaults;
		$this->appManager = $appManager;
		$this->avatarRepository = $avatarRepository;
		$this->request = $request;
	}

	private function getServer(): BigBlueButton {
		if (!$this->server) {
			$apiUrl = $this->config->getAppValue('bbb', 'api.url');
			$secret = $this->config->getAppValue('bbb', 'api.secret');

			$this->server = new BigBlueButton($apiUrl, $secret);
		}

		return $this->server;
	}

	/**
	 * Create join url.
	 *
	 * @return string join url
	 */
	public function createJoinUrl(Room $room, float $creationTime, string $displayname, bool $isModerator, ?string $uid = null) {
		$password = $isModerator ? $room->moderatorPassword : $room->attendeePassword;

		$joinMeetingParams = new JoinMeetingParameters($room->uid, $displayname, $password);

		// ensure that float is not converted to a string in scientific notation
		$joinMeetingParams->setCreateTime(sprintf("%.0f", $creationTime));
		$joinMeetingParams->setJoinViaHtml5(true);
		$joinMeetingParams->setRedirect(true);

		// set the guest parameter for everyone but moderators to send all users to the waiting room if setting is selected
		$joinMeetingParams->setGuest((($room->access === Room::ACCESS_WAITING_ROOM_ALL) && !$isModerator) || $uid === null);

		$joinMeetingParams->addUserData('bbb_listen_only_mode', $room->getListenOnly());

		$joinMeetingParams->addUserData('bbb_skip_check_audio_on_first_join', !$room->getMediaCheck()); // 2.3
		$joinMeetingParams->addUserData('bbb_skip_video_preview_on_first_join', !$room->getMediaCheck()); // 2.3

		if ($room->getCleanLayout()) {
			$joinMeetingParams->addUserData('bbb_auto_swap_layout', true);
			$joinMeetingParams->addUserData('bbb_show_participants_on_login', false);
			$joinMeetingParams->addUserData('bbb_show_public_chat_on_login', false);
		}

		if ($this->config->getAppValue('bbb', 'join.theme') === 'true') {
			$primaryColor = $this->defaults->getColorPrimary();
			$textColor = $this->defaults->getTextColorPrimary();

			$joinMeetingParams->addUserData('bbb_custom_style', ":root{--nc-primary-color:$primaryColor;--nc-primary-text-color:$textColor;--nc-bg-color:#444;--color-primary:var(--nc-primary-color);--btn-primary-color:var(--nc-primary-text-color);--color-text:#222;--loader-bg:var(--nc-bg-color);--user-list-bg:#fff;--user-list-text:#222;--list-item-bg-hover:#f5f5f5;--item-focus-border:var(--nc-primary-color);--color-off-white:#fff;--color-gray-dark:var(--nc-bg-color);}body{background-color:var(--nc-bg-color);}.overlay--1aTlbi{background-color:var(--nc-bg-color);}.userlistPad--o5KDX{border-right: 1px solid #ededed;}.scrollStyle--Ckr4w{background: transparent;}.item--yl1AH:hover, .item--yl1AH:focus{color:--nc-primary-text-color;}#message-input:focus{box-shadow:0 0 0 1px var(--nc-primary-color);border-color:--nc-primary-color;}.active--Z1SuO2X{border-radius:5px;}");
		}

		if ($uid) {
			$joinMeetingParams->setUserID($uid);

			if ($this->config->getAppValue('bbb', 'avatar.enabled', 'true') === 'true') {
				$avatarUrl = $this->avatarRepository->getAvatarUrl($room, $uid);
				$joinMeetingParams->setAvatarURL($avatarUrl);
			}
		}

		return $this->getServer()->getJoinMeetingURL($joinMeetingParams);
	}

	/**
	 * Create meeting room.
	 *
	 * @return float|int creation time
	 */
	public function createMeeting(Room $room, Presentation $presentation = null) {
		$bbb = $this->getServer();
		$meetingParams = $this->buildMeetingParams($room, $presentation);

		try {
			$response = $bbb->createMeeting($meetingParams);
		} catch (\Exception $e) {
			throw new \Exception('Can not process create request: ' . $bbb->getCreateMeetingUrl($meetingParams));
		}

		if (!$response->success()) {
			throw new \Exception('Can not create meeting: ' . $response->getMessage());
		}

		if ($response->getMessageKey() !== 'duplicateWarning') {
			$this->eventDispatcher->dispatch(MeetingStartedEvent::class, new MeetingStartedEvent($room));
		}

		return $response->getCreationTime();
	}

	private function buildMeetingParams(Room $room, Presentation $presentation = null): CreateMeetingParameters {
		$createMeetingParams = new CreateMeetingParameters($room->uid, $room->name);
		$createMeetingParams->setAttendeePW($room->attendeePassword);
		$createMeetingParams->setModeratorPW($room->moderatorPassword);
		$createMeetingParams->setRecord($room->record);
		$createMeetingParams->setAllowStartStopRecording($room->record);
		$createMeetingParams->setLogoutURL($this->urlGenerator->getBaseUrl());
		$createMeetingParams->setMuteOnStart($room->getJoinMuted());

		$createMeetingParams->addMeta('bbb-origin-version', $this->appManager->getAppVersion(Application::ID));
		$createMeetingParams->addMeta('bbb-origin', \method_exists($this->defaults, 'getProductName') ? $this->defaults->getProductName() : 'Nextcloud');
		$createMeetingParams->addMeta('bbb-origin-server-name', $this->request->getServerHost());

		$analyticsCallbackUrl = $this->config->getAppValue('bbb', 'api.meta_analytics-callback-url');
		if (!empty($analyticsCallbackUrl)) {
			// For more details: https://github.com/bigbluebutton/bigbluebutton/blob/develop/record-and-playback/core/scripts/post_events/post_events_analytics_callback.rb
			$createMeetingParams->addMeta('analytics-callback-url', $analyticsCallbackUrl);
			$createMeetingParams->setMeetingKeepEvents(true);
		}

		$mac = $this->crypto->calculateHMAC($room->uid);

		$endMeetingUrl = $this->urlGenerator->linkToRouteAbsolute('bbb.hook.meetingEnded', ['token' => $room->uid, 'mac' => $mac]);
		$createMeetingParams->setEndCallbackUrl($endMeetingUrl);

		$recordingReadyUrl = $this->urlGenerator->linkToRouteAbsolute('bbb.hook.recordingReady', ['token' => $room->uid, 'mac' => $mac]);
		$createMeetingParams->setRecordingReadyCallbackUrl($recordingReadyUrl);

		$invitationUrl = $this->urlHelper->linkToInvitationAbsolute($room);
		$createMeetingParams->setModeratorOnlyMessage($this->l10n->t('To invite someone to the meeting, send them this link: %s', [$invitationUrl]));

		if (!empty($room->welcome)) {
			$createMeetingParams->setWelcome($room->welcome);
		}

		if ($room->maxParticipants > 0) {
			$createMeetingParams->setMaxParticipants($room->maxParticipants);
		}

		if ($presentation !== null && $presentation->isValid()) {
			/** @psalm-suppress InvalidArgument */
			$createMeetingParams->addPresentation($presentation->getUrl(), null, $presentation->getFilename());
		}

		if ($room->access === Room::ACCESS_WAITING_ROOM || $room->access === Room::ACCESS_WAITING_ROOM_ALL) {
			$createMeetingParams->setGuestPolicyAskModerator();
		}

		return $createMeetingParams;
	}

	public function getRecording(string $recordId) {
		$recordingParams = new GetRecordingsParameters();
		$recordingParams->setRecordID($recordId);
		$recordingParams->setState('any');

		$response = $this->getServer()->getRecordings($recordingParams);

		if (!$response->success()) {
			throw new \Exception('Could not process get recording request');
		}

		$records = $response->getRecords();

		if (count($records) === 0) {
			throw new \Exception('Found no record with given id');
		}

		return $this->recordToArray($records[0]);
	}

	public function getRecordings(Room $room): array {
		$recordingParams = new GetRecordingsParameters();
		$recordingParams->setMeetingID($room->uid);
		$recordingParams->setState('processing,processed,published,unpublished');

		$response = $this->getServer()->getRecordings($recordingParams);

		if (!$response->success()) {
			throw new \Exception('Could not process get recordings request');
		}

		$records = $response->getRecords();

		return array_map(function ($record) {
			return $this->recordToArray($record);
		}, $records);
	}

	public function deleteRecording(string $recordingId): bool {
		$deleteParams = new DeleteRecordingsParameters($recordingId);

		$response = $this->getServer()->deleteRecordings($deleteParams);

		return $response->isDeleted();
	}

	public function publishRecording(string $recordingId, bool $published): bool {
		$publishParams = new PublishRecordingsParameters($recordingId, $published);

		$response = $this->getServer()->publishRecordings($publishParams);

		return $response->isPublished();
	}

	/**
	 * @return (array|bool|int|string)[]
	 *
	 * @psalm-return array{id: string, meetingId: string, name: string, published: bool, state: string, startTime: string, participants: int, type: string, length: string, url: string, metas: array}
	 */
	private function recordToArray(Record $record): array {
		return [
			'id' => $record->getRecordId(),
			'meetingId' => $record->getMeetingId(),
			'name' => $record->getName(),
			'published' => $record->isPublished(),
			'state' => $record->getState(),
			'startTime' => $record->getStartTime(),
			'participants' => $record->getParticipantCount(),
			'type' => $record->getPlaybackType(),
			'length' => $record->getPlaybackLength(),
			'url' => $record->getPlaybackUrl(),
			'metas' => $record->getMetas(),
		];
	}

	public function check(string $url, string $secret): string {
		$server = new BigBlueButton($url, $secret);

		$meetingParams = new IsMeetingRunningParameters('foobar');

		try {
			$response = $server->isMeetingRunning($meetingParams);

			if (!$response->success() && !$response->failed()) {
				return 'invalid-url';
			}

			if (!$response->success()) {
				return 'invalid-secret';
			}

			return 'success';
		} catch (\Exception $e) {
			return 'invalid-url';
		}
	}

	/**
	 * @param null|string $url
	 */
	public function getVersion(?string $url = null) {
		$server = $url === null ? $this->getServer() : new BigBlueButton($url, '');

		return $server->getApiVersion()->getVersion();
	}

	public function isRunning(Room $room): bool {
		$isMeetingRunningParams = new IsMeetingRunningParameters($room->getUid());

		$response = $this->getServer()->isMeetingRunning($isMeetingRunningParams);

		return $response->success() && $response->isRunning();
	}

	public function insertDocument(Room $room, string $url, string $filename): bool {
		$insertDocumentParams = new InsertDocumentParameters($room->getUid());

		$insertDocumentParams->addPresentation($url, $filename, null, null);

		$response = $this->getServer()->insertDocument($insertDocumentParams);

		return $response->success();
	}
}
