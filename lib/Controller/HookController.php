<?php

namespace OCA\BigBlueButton\Controller;

use OCA\BigBlueButton\Db\Room;
use OCP\IRequest;
use OCP\EventDispatcher\IEventDispatcher;
use OCA\BigBlueButton\Service\RoomService;
use OCA\BigBlueButton\Event\RoomEndedEvent;
use OCA\BigBlueButton\Event\RecordingReadyEvent;
use OCP\AppFramework\Controller;

class HookController extends Controller {
	/** @var string */
	protected $token;

	/** @var Room|null */
	protected $room;

	/** @var RoomService */
	private $service;

	/** @var IEventDispatcher */
	private $eventDispatcher;

	public function __construct(
		string $appName,
		IRequest $request,
		RoomService $service,
		IEventDispatcher $eventDispatcher
	) {
		parent::__construct($appName, $request);

		$this->service = $service;
		$this->eventDispatcher = $eventDispatcher;
	}

	public function setToken(string $token) {
		$this->token = $token;
		$this->room = null;
	}

	public function isValidToken(): bool {
		$room = $this->getRoom();

		return $room !== null;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 */
	public function meetingEnded($recordingmarks = false) {
		$recordingmarks = \boolval($recordingmarks);

		$this->eventDispatcher->dispatch(RoomEndedEvent::class, new RoomEndedEvent($this->getRoom(), $recordingmarks));
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 */
	public function recordingReady() {
		$this->eventDispatcher->dispatch(RecordingReadyEvent::class, new RecordingReadyEvent($this->getRoom()));
	}

	private function getRoom(): ?Room {
		if ($this->room === null) {
			$this->room = $this->service->findByUid($this->token);
		}

		return $this->room;
	}
}
