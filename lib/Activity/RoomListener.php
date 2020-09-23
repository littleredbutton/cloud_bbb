<?php

namespace OCA\BigBlueButton\Activity;

use OCA\BigBlueButton\AppInfo\Application;
use OCA\BigBlueButton\Event\RoomCreatedEvent;
use OCA\BigBlueButton\Event\RoomDeletedEvent;
use OCP\Activity\IManager as IActivityManager;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

class RoomListener implements IEventListener {

	/** @var IActivityManager */
	private $activityManager;

	public function __construct(IActivityManager $activityManager) {
		$this->activityManager = $activityManager;
	}

	public function handle(Event $event): void {
		if ($event instanceof RoomCreatedEvent) {
			$subject = Provider::SHARE_CREATED;
		} elseif ($event instanceof RoomDeletedEvent) {
			$subject = Provider::SHARE_DELETED;
		} else {
			return;
		}

		$room = $event->getRoom();
		$activityEvent = $this->activityManager->generateEvent();

		$activityEvent->setApp(Application::ID);
		$activityEvent->setType(Setting::Identifier);
		$activityEvent->setAffectedUser($room->getUserId());
		$activityEvent->setSubject($subject, [
			'id' => $room->getId(),
			'name' => $room->getName(),
		]);

		$this->activityManager->publish($activityEvent);
	}
}
