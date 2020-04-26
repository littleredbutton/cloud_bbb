<?php
namespace OCA\BigBlueButton\Controller;

use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\PublicShareController;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUserSession;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCA\BigBlueButton\Service\RoomService;
use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use OCP\AppFramework\Http\TemplateResponse;

class JoinController extends PublicShareController
{
	/** @var RoomService */
	private $service;

	/** @var IUserSession */
	private $userSession;

	/** @var IConfig */
	private $config;

	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct(
		string $appName,
		IRequest $request,
		ISession $session,
		RoomService $service,
		IUserSession $userSession,
		IConfig $config,
		IURLGenerator $urlGenerator
	) {
		parent::__construct($appName, $request, $session);

		$this->service = $service;
		$this->userSession = $userSession;
		$this->config = $config;
		$this->urlGenerator = $urlGenerator;
	}

	protected function getPasswordHash(): string
	{
		return '';
	}

	/**
	* Validate the token of this share. If the token is invalid this controller
	* will return a 404.
	*/
	public function isValidToken(): bool
	{
		$room = $this->service->findByUid($this->getToken());

		return $room !== null;
	}

	/**
	 * Allows you to specify if this share is password protected
	 */
	protected function isPasswordProtected(): bool
	{
		return false;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 */
	public function index($displayname, $u, $filename)
	{
		$room = $this->service->findByUid($this->getToken());

		if ($room === null) {
			return 'Room not found';
		}

		$uid = null;
		$url = null;

		if ($this->userSession->isLoggedIn()) {
			$user = $this->userSession->getUser();
			$displayname = $user->getDisplayName();
			$uid = $user->getUID();
			$url = $u;
		} elseif (empty($displayname) || strlen($displayname) < 3) {
			$apiUrl = $this->config->getAppValue($this->appName, 'api.url');
			$response = new TemplateResponse($this->appName, 'publicdisplayname', [
				'wrongdisplayname' => !empty($displayname) && strlen($displayname) < 3
			], 'guest');

			$parsedApiUrl = parse_url($apiUrl);

			if ($parsedApiUrl === false) {
				throw new \Exception('No valid api url provided');
			}

			$response->getContentSecurityPolicy()->addAllowedFormActionDomain(($parsedApiUrl['scheme'] ?: 'https') . '://' . $parsedApiUrl['host']);

			return $response;
		}

		return $this->processPublicJoin($room, $displayname, $uid, $url, $filename);
	}

	private function processPublicJoin($room, $displayname, $uid, $presentation, $filename)
	{
		$apiUrl = $this->config->getAppValue($this->appName, 'api.url');
		$secret = $this->config->getAppValue($this->appName, 'api.secret');

		$bbb = new BigBlueButton($apiUrl, $secret);

		$createMeetingParams = new CreateMeetingParameters($room->uid, $room->name);
		$createMeetingParams->setAttendeePassword($room->attendeePassword);
		$createMeetingParams->setModeratorPassword($room->moderatorPassword);
		$createMeetingParams->setRecord($room->record);
		$createMeetingParams->setLogoutUrl($this->urlGenerator->getBaseUrl());

		$invitationUrl = $this->urlGenerator->getAbsoluteURL($this->request->getPathInfo());
		$createMeetingParams->setModeratorOnlyMessage('To invite someone to the meeting, send them this link: ' . $invitationUrl);

		if (!empty($room->welcome)) {
			$createMeetingParams->setWelcomeMessage($room->welcome);
		}

		if ($room->maxParticipants > 0) {
			$createMeetingParams->setMaxParticipants($room->maxParticipants);
		}

		if ($presentation) {
			$createMeetingParams->addPresentation($presentation, null, $filename);
		}

		try {
			$response = $bbb->createMeeting($createMeetingParams);
		} catch (\Exception $e) {
			throw $e;
			throw new \Exception('Can not process create request');
		}

		if ($response->failed()) {
			throw new \Exception('Can not create meeting');
		}

		$password = $uid === $room->userId ? $room->moderatorPassword : $room->attendeePassword;


		$joinMeetingParams = new JoinMeetingParameters($room->uid, $displayname, $password);

		$joinMeetingParams->setCreationTime($response->getCreationTime());
		$joinMeetingParams->setJoinViaHtml5(true);

		if ($uid) {
			$joinMeetingParams->setUserId($uid);
			// $joinMeetingParams->setAvatarURL();
		}

		$joinMeetingParams->setRedirect(true);
		$joinUrl = $bbb->getJoinMeetingURL($joinMeetingParams);

		return new RedirectResponse($joinUrl);
	}
}
