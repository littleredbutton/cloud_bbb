<?php

namespace OCA\BigBlueButton\Activity;

use OCA\BigBlueButton\AppInfo\Application;
use OCA\BigBlueButton\Event\MeetingEndedEvent;
use OCA\BigBlueButton\Event\MeetingStartedEvent;
use OCA\BigBlueButton\Event\RecordingReadyEvent;
use OCP\Activity\IManager as IActivityManager;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IUserSession;

class MeetingListener implements IEventListener {

	/** @var IActivityManager */
	private $activityManager;

	/** @var IUserSession */
	private $userSession;

	public function __construct(
		IActivityManager $activityManager,
		IUserSession $userSession) {
		$this->activityManager = $activityManager;
		$this->userSession = $userSession;
	}

	public function handle(Event $event): void {
		if ($event instanceof MeetingStartedEvent) {
			$subject = Provider::MEETING_STARTED;
		} elseif ($event instanceof MeetingEndedEvent) {
			$subject = Provider::MEETING_ENDED;
		} elseif ($event instanceof RecordingReadyEvent) {
			$subject = Provider::RECORDING_READY;
		} else {
			return;
		}

		$room = $event->getRoom();
		$activityEvent = $this->activityManager->generateEvent();

		if (!$this->userSession->isLoggedIn()) {
			$activityEvent->setAuthor($room->getUserId());
		}

		$subjectData = [
			'id' => $room->getId(),
			'name' => $room->getName(),
		];

		if ($event instanceof MeetingEndedEvent) {
			$subjectData['recordingMarks'] = $event->hasRecordingMarks();
		}

		$activityEvent->setApp(Application::ID);
		$activityEvent->setType(Setting::Identifier);
		$activityEvent->setAffectedUser($room->getUserId());
		$activityEvent->setSubject($subject, $subjectData);

		$this->activityManager->publish($activityEvent);
	}
}
