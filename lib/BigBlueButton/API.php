<?php

namespace OCA\BigBlueButton\BigBlueButton;

use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use OCA\BigBlueButton\Db\Room;
use OCP\IConfig;
use OCP\IURLGenerator;

class API
{
	/** @var IConfig */
	private $config;

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var BigBlueButton */
	private $server;

	public function __construct(
		IConfig $config,
		IURLGenerator $urlGenerator
	) {
		$this->config = $config;
		$this->urlGenerator = $urlGenerator;
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
		$password = $uid === $room->userId ? $room->moderatorPassword : $room->attendeePassword;

		$joinMeetingParams = new JoinMeetingParameters($room->uid, $displayname, $password);

		$joinMeetingParams->setCreationTime($creationTime);
		$joinMeetingParams->setJoinViaHtml5(true);
		$joinMeetingParams->setRedirect(true);

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

		try {
			$response = $bbb->createMeeting($this->buildMeetingParams($room, $presentation));
		} catch (\Exception $e) {
			throw $e;
			throw new \Exception('Can not process create request');
		}

		if (!$response->success()) {
			throw new \Exception('Can not create meeting');
		}

		return $response->getCreationTime();
	}

	private function buildMeetingParams(Room $room, Presentation $presentation = null)
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

		return $createMeetingParams;
	}
}
