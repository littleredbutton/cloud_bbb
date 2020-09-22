<?php

namespace OCA\BigBlueButton\Activity;

use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Activity\IManager as IActivityManager;
use OCP\IGroupManager;
use OCA\BigBlueButton\Event\RoomShareCreatedEvent;
use OCA\BigBlueButton\Event\RoomShareDeletedEvent;
use OCA\BigBlueButton\AppInfo\Application;
use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\Db\RoomShare;
use OCA\BigBlueButton\Service\RoomService;

class RoomShareListener implements IEventListener {

	/** @var IActivityManager */
	private $activityManager;

	/** @var RoomService */
	private $roomService;

	/** @var IGroupManager */
	private $groupManager;

	public function __construct(
		IActivityManager $activityManager,
		RoomService $roomService,
		IGroupManager $groupManager) {
		$this->activityManager = $activityManager;
		$this->roomService = $roomService;
		$this->groupManager = $groupManager;
	}

	public function handle(Event $event): void {
		if ($event instanceof RoomShareCreatedEvent) {
			$subject = Provider::SHARE_CREATED;
		} elseif ($event instanceof RoomShareDeletedEvent) {
			$subject = Provider::SHARE_DELETED;
		} else {
			return;
		}

		$share = $event->getRoomShare();
		$shareType = $share->getShareType();
		$room = $this->roomService->find($share->getRoomId());

		if ($shareType === RoomShare::SHARE_TYPE_USER) {
			$this->shareWithUser($subject, $room, $share);
		} elseif ($shareType === RoomShare::SHARE_TYPE_GROUP) {
			$this->shareWithGroup($subject, $room, $share);
		}
	}

	private function shareWithUser(string $subject, Room $room, RoomShare $share) {
		$this->createActivityEvent($subject, $room->getUserId(), $room, $share);
		$this->createActivityEvent($subject, $share->getShareWith(), $room, $share);
	}

	private function shareWithGroup(string $subject, Room $room, RoomShare $share) {
		$this->createActivityEvent($subject, $room->getUserId(), $room, $share);

		$group = $this->groupManager->get($share->getShareWith());

		if ($group === null) {
			return;
		}

		foreach ($group->getUsers() as $user) {
			$this->createActivityEvent($subject, $user->getUID(), $room, $share);
		}
	}

	private function createActivityEvent(string $subject, string $affectedUser, Room $room, RoomShare $roomShare) {
		$activityEvent = $this->activityManager->generateEvent();

		$activityEvent->setApp(Application::ID);
		$activityEvent->setType(Setting::Identifier);
		$activityEvent->setAffectedUser($affectedUser);
		$activityEvent->setSubject($subject, [
			'id' => $room->getId(),
			'name' => $room->getName(),
			'shareType' => $roomShare->getShareType(),
			'shareWith' => $roomShare->getShareWith(),
			'permission' => $roomShare->getPermission(),
		]);

		$this->activityManager->publish($activityEvent);
	}
}
