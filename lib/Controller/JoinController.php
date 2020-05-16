<?php
namespace OCA\BigBlueButton\Controller;

use OCA\BigBlueButton\BigBlueButton\API;
use OCA\BigBlueButton\BigBlueButton\Presentation;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\PublicShareController;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUserSession;
use OCP\IConfig;
use OCP\Files\NotFoundException;
use OCA\BigBlueButton\Service\RoomService;
use OCP\AppFramework\Http\TemplateResponse;

class JoinController extends PublicShareController
{
	/** @var RoomService */
	private $service;

	/** @var IUserSession */
	private $userSession;

	/** @var IConfig */
	private $config;

	/** @var API */
	private $api;

	public function __construct(
		string $appName,
		IRequest $request,
		ISession $session,
		RoomService $service,
		IUserSession $userSession,
		IConfig $config,
		API $api
	) {
		parent::__construct($appName, $request, $session);

		$this->service = $service;
		$this->userSession = $userSession;
		$this->config = $config;
		$this->api = $api;
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
	public function index($displayname, $u = '', $filename = '')
	{
		$room = $this->service->findByUid($this->getToken());

		if ($room === null) {
			throw new NotFoundException();
		}

		$userId = null;
		$presentation = null;

		if ($this->userSession->isLoggedIn()) {
			$user = $this->userSession->getUser();
			$displayname = $user->getDisplayName();
			$userId = $user->getUID();

			if ($userId === $room->userId) {
				$presentation = new Presentation($u, $filename);
			}
		} elseif (empty($displayname) || strlen($displayname) < 3) {
			$response = new TemplateResponse($this->appName, 'publicdisplayname', [
				'room'             => $room->name,
				'wrongdisplayname' => !empty($displayname) && strlen($displayname) < 3
			], 'guest');

			$this->addFormActionDomain($response);

			return $response;
		}

		$creationDate = $this->api->createMeeting($room, $presentation);
		$joinUrl = $this->api->createJoinUrl($room, $creationDate, $displayname, $userId);

		return new RedirectResponse($joinUrl);
	}

	private function addFormActionDomain($response)
	{
		$apiUrl = $this->config->getAppValue($this->appName, 'api.url');
		$parsedApiUrl = parse_url($apiUrl);

		if ($parsedApiUrl === false) {
			throw new \Exception('No valid api url provided');
		}

		$response->getContentSecurityPolicy()->addAllowedFormActionDomain(($parsedApiUrl['scheme'] ?: 'https') . '://' . $parsedApiUrl['host']);
	}
}
