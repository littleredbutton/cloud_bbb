<?php

namespace OCA\BigBlueButton\Controller;

use OCA\BigBlueButton\BigBlueButton\API;
use OCA\BigBlueButton\BigBlueButton\Presentation;
use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\NoPermissionException;
use OCA\BigBlueButton\NotFoundException;
use OCA\BigBlueButton\Permission;
use OCA\BigBlueButton\Service\RoomService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;

class JoinController extends Controller {
	/** @var string */
	protected $token;

	/** @var Room|null */
	protected $room;

	/** @var RoomService */
	private $service;

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var IUserSession */
	private $userSession;

	/** @var API */
	private $api;

	/** @var Permission */
	private $permission;

	public function __construct(
		string $appName,
		IRequest $request,
		RoomService $service,
		IURLGenerator $urlGenerator,
		IUserSession $userSession,
		API $api,
		Permission $permission
	) {
		parent::__construct($appName, $request);

		$this->service = $service;
		$this->urlGenerator = $urlGenerator;
		$this->userSession = $userSession;
		$this->api = $api;
		$this->permission = $permission;
	}

	public function setToken(string $token): void {
		$this->token = $token;
		$this->room = null;
	}

	public function isValidToken(): bool {
		$room = $this->getRoom();

		return $room !== null;
	}

	/**
	 * @PublicPage
	 *
	 * @NoCSRFRequired
	 *
	 * @return RedirectResponse|TemplateResponse
	 */
	public function index($displayname, $u = '', $filename = '', $password = '') {
		$room = $this->getRoom();

		if ($room === null) {
			throw new NotFoundException();
		}

		$moderatorToken = $this->request->getParam('moderatorToken');

		if (!empty($moderatorToken) && $moderatorToken !== $room->moderatorToken) {
			throw new NoPermissionException();
		}

		$displayname = trim($displayname);
		$userId = null;
		$presentation = null;

		if ($this->userSession->isLoggedIn()) {
			$user = $this->userSession->getUser();
			$displayname = $user->getDisplayName();
			$userId = $user->getUID();

			if ($room->access === Room::ACCESS_INTERNAL_RESTRICTED && !$this->permission->isUser($room, $userId)) {
				throw new NoPermissionException();
			}

			if ($this->permission->isAdmin($room, $userId)) {
				$presentation = new Presentation($u, $filename);
			}
		} elseif ($room->access === Room::ACCESS_INTERNAL || $room->access === Room::ACCESS_INTERNAL_RESTRICTED) {
			return new RedirectResponse($this->getLoginUrl());
		} elseif (empty($displayname) || strlen($displayname) < 3 || ($room->access === Room::ACCESS_PASSWORD && $password !== $room->password)) {
			$response = new TemplateResponse($this->appName, 'join', [
				'room' => $room->name,
				'wrongdisplayname' => !empty($displayname) && strlen($displayname) < 3,
				'passwordRequired' => $room->access === Room::ACCESS_PASSWORD,
				'wrongPassword' => $password !== $room->password && $password !== '',
				'loginUrl' => $this->getLoginUrl(),
			], 'guest');

			return $response;
		}

		$isModerator = (!empty($moderatorToken) && $moderatorToken === $room->moderatorToken) || $this->permission->isModerator($room, $userId);

		if ($room->requireModerator && !$isModerator && !$this->api->isRunning($room)) {
			return new TemplateResponse($this->appName, 'waiting', [
				'room' => $room->name,
				'name' => $displayname,
			], 'guest');
		}

		$creationDate = $this->api->createMeeting($room, $presentation);
		$joinUrl = $this->api->createJoinUrl($room, $creationDate, $displayname, $isModerator, $userId);

		\OCP\Util::addHeader('meta', ['http-equiv' => 'refresh', 'content' => '3;url='.$joinUrl]);

		return new TemplateResponse($this->appName, 'forward', [
			'room' => $room->name,
			'url' => $joinUrl,
		], 'guest');
	}

	private function getRoom(): ?Room {
		if ($this->room === null) {
			$this->room = $this->service->findByUid($this->token);
		}

		return $this->room;
	}

	private function getLoginUrl(): string {
		return $this->urlGenerator->linkToRoute('core.login.showLoginForm', [
			'redirect_url' => $this->urlGenerator->linkToRoute(
				'bbb.join.index',
				['token' => $this->token]
			),
		]);
	}
}
