<?php

namespace OCA\BigBlueButton\BigBlueButton;

use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use BigBlueButton\Parameters\GetRecordingsParameters;
use BigBlueButton\Core\Record;
use BigBlueButton\Parameters\DeleteRecordingsParameters;
use BigBlueButton\Parameters\IsMeetingRunningParameters;
use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\Db\RoomShare;
use OCA\BigBlueButton\Permission;
use OCA\BigBlueButton\Service\RoomShareService;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\IGroupManager;

class API
{
	/** @var IConfig */
	private $config;

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var Permission */
	private $permission;

	/** @var BigBlueButton */
	private $server;

	public function __construct(
		IConfig $config,
		IURLGenerator $urlGenerator,
		Permission $permission
	) {
		$this->config = $config;
		$this->urlGenerator = $urlGenerator;
		$this->permission = $permission;
	}

	private function getServer()
	{
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
	public function createJoinUrl(Room $room, int $creationTime, string $displayname, string $uid = null)
	{
		$password = $this->permission->isModerator($room, $uid) ? $room->moderatorPassword : $room->attendeePassword;

		$joinMeetingParams = new JoinMeetingParameters($room->uid, $displayname, $password);

		$joinMeetingParams->setCreationTime($creationTime);
		$joinMeetingParams->setJoinViaHtml5(true);
		$joinMeetingParams->setRedirect(true);
		$joinMeetingParams->setGuest($uid === null);

		if ($uid) {
			$joinMeetingParams->setUserId($uid);
			// $joinMeetingParams->setAvatarURL();
		}

		return $this->getServer()->getJoinMeetingURL($joinMeetingParams);
	}

	/**
	 * Create meeting room.
	 *
	 * @return int creation time
	 */
	public function createMeeting(Room $room, Presentation $presentation = null)
	{
		$bbb = $this->getServer();
		$meetingParams = $this->buildMeetingParams($room, $presentation);

		try {
			$response = $bbb->createMeeting($meetingParams);
		} catch (\Exception $e) {
			throw new \Exception('Can not process create request: ' . $bbb->getCreateMeetingUrl($meetingParams));
		}

		if (!$response->success()) {
			throw new \Exception('Can not create meeting');
		}

		return $response->getCreationTime();
	}

	private function buildMeetingParams(Room $room, Presentation $presentation = null): CreateMeetingParameters
	{
		$createMeetingParams = new CreateMeetingParameters($room->uid, $room->name);
		$createMeetingParams->setAttendeePassword($room->attendeePassword);
		$createMeetingParams->setModeratorPassword($room->moderatorPassword);
		$createMeetingParams->setRecord($room->record);
		$createMeetingParams->setAllowStartStopRecording($room->record);
		$createMeetingParams->setLogoutUrl($this->urlGenerator->getBaseUrl());

		$invitationUrl = $this->urlGenerator->linkToRouteAbsolute('bbb.join.index', ['token' => $room->uid]);
		$createMeetingParams->setModeratorOnlyMessage('To invite someone to the meeting, send them this link: ' . $invitationUrl);

		if (!empty($room->welcome)) {
			$createMeetingParams->setWelcomeMessage($room->welcome);
		}

		if ($room->maxParticipants > 0) {
			$createMeetingParams->setMaxParticipants($room->maxParticipants);
		}

		if ($presentation !== null && $presentation->isValid()) {
			$createMeetingParams->addPresentation($presentation->getUrl(), null, $presentation->getFilename());
		}

		if ($room->access === Room::ACCESS_WAITING_ROOM) {
			$createMeetingParams->setGuestPolicyAskModerator();
		}

		return $createMeetingParams;
	}

	public function getRecording(string $recordId)
	{
		$recordingParams = new GetRecordingsParameters();
		$recordingParams->setRecordId($recordId);
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

	public function getRecordings(Room $room)
	{
		$recordingParams = new GetRecordingsParameters();
		$recordingParams->setMeetingId($room->uid);
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

	public function deleteRecording(string $recordingId): bool
	{
		$deleteParams = new DeleteRecordingsParameters($recordingId);

		$response = $this->getServer()->deleteRecordings($deleteParams);

		return $response->isDeleted();
	}

	private function recordToArray(Record $record)
	{
		return [
			'id'           => $record->getRecordId(),
			'name'         => $record->getName(),
			'published'    => $record->isPublished(),
			'state'        => $record->getState(),
			'startTime'    => $record->getStartTime(),
			'participants' => $record->getParticipantCount(),
			'type'         => $record->getPlaybackType(),
			'length'       => $record->getPlaybackLength(),
			'url'          => $record->getPlaybackUrl(),
			'metas'        => $record->getMetas(),
		];
	}

	public function check($url, $secret)
	{
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

	public function getVersion($url = null)
	{
		$server = $url === null ? $this->getServer() : new BigBlueButton($url, '');

		return $server->getApiVersion()->getVersion();
	}
}
