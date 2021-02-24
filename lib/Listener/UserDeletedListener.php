<?php

namespace OCA\BigBlueButton\Listener;

use OCA\BigBlueButton\Service\RoomService;
use OCA\BigBlueButton\Service\RoomShareService;
use OCP\Activity\IManager as IActivityManager;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\User\Events\UserDeletedEvent;

class UserDeletedListener implements IEventListener {

	/** @var IActivityManager */
	private $activityManager;

	/** @var RoomService */
	private $roomService;

	/** @var RoomShareService */
	private $shareService;

	public function __construct(
		IActivityManager $activityManager,
		RoomService $roomService,
		RoomShareService $shareService) {
		$this->activityManager = $activityManager;
		$this->roomService = $roomService;
		$this->shareService = $shareService;
	}

	public function handle(Event $event): void {
		if (!($event instanceof UserDeletedEvent)) {
			return;
		}

		$userId = $event->getUser()->getUID();
		$rooms = $this->roomService->findAll($userId, [], []);

		$this->deleteSharesByUserId($userId);

		foreach ($rooms as $room) {
			$this->deleteSharesByRoomId($room->getId());

			$this->roomService->delete($room->getId());
		}
	}

	private function deleteSharesByRoomId(string $roomId): void {
		$shares = $this->shareService->findAll($roomId);

		foreach ($shares as $share) {
			$this->shareService->delete($share->getId());
		}
	}

	private function deleteSharesByUserId(string $userId): void {
		$shares = $this->shareService->findByUserId($userId);

		foreach ($shares as $share) {
			$this->shareService->delete($share->getId());
		}
	}
}
