<?php

namespace OCA\BigBlueButton\Controller;

use OCA\BigBlueButton\AvatarRepository;
use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\Event\MeetingEndedEvent;
use OCA\BigBlueButton\Event\RecordingReadyEvent;
use OCA\BigBlueButton\Service\RoomService;
use OCP\IConfig;
use OCP\AppFramework\Controller;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IRequest;

class HookController extends Controller {
	/** @var string */
	protected $token;

	/** @var Room|null */
	protected $room;

	/** @var RoomService */
	private $service;

	/** @var AvatarRepository */
	private $avatarRepository;

	/** @var IEventDispatcher */
	private $eventDispatcher;

	/** @var IConfig */
	private $config;

	public function __construct(
		string $appName,
		IRequest $request,
		RoomService $service,
		AvatarRepository $avatarRepository,
		IEventDispatcher $eventDispatcher,
		IConfig $config
	) {
		parent::__construct($appName, $request);

		$this->service = $service;
		$this->avatarRepository = $avatarRepository;
		$this->eventDispatcher = $eventDispatcher;
		$this->config = $config;
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
	 * @return void
	 */
	public function meetingEnded($recordingmarks = false): void {
		$recordingmarks = \boolval($recordingmarks);
		$room = $this->getRoom();

		$this->service->updateRunning($room->getId(), false);

		if ($this->config->getAppValue('bbb', 'avatar.enabled', 'true') === 'true') {
			$this->avatarRepository->clearRoom($room->uid);
		}		

		$this->eventDispatcher->dispatch(MeetingEndedEvent::class, new MeetingEndedEvent($room, $recordingmarks));
	}

	/**
	 * @PublicPage
	 *
	 * @NoCSRFRequired
	 *
	 * @return void
	 */
	public function recordingReady(): void {
		$this->eventDispatcher->dispatch(RecordingReadyEvent::class, new RecordingReadyEvent($this->getRoom()));
	}

	private function getRoom(): ?Room {
		if ($this->room === null) {
			$this->room = $this->service->findByUid($this->token);
		}

		return $this->room;
	}
}
