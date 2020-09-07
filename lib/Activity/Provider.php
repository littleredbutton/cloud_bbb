<?php

namespace OCA\BigBlueButton\Activity;

use OCA\BigBlueButton\AppInfo\Application;
use OCP\Activity\IProvider;
use OCP\Activity\IEvent;
use OCP\Activity\IManager;

class Provider implements IProvider {

	/** @var string */
	public const ROOM_CREATED = 'room_created';

	/** @var string */
	public const ROOM_DELETED = 'room_deleted';

	/** @var IManager */
	protected $activityManager;

	public function __construct(
		IManager $manager
	) {
		$this->activityManager = $manager;
	}

	public function parse($language, IEvent $event, IEvent $previousEvent = null) {
		if ($event->getApp() !== Application::ID) {
			throw new \InvalidArgumentException();
		}

		$subject = $event->getSubject();
		$params = $event->getSubjectParameters();

		if ($subject === self::ROOM_CREATED) {
			$event->setParsedSubject('You created the room ' . $params['name']);
		} elseif ($subject === self::ROOM_DELETED) {
			if ($this->activityManager->getCurrentUserId() === $event->getAffectedUser()) {
				$event->setParsedSubject('You deleted the room ' . $params['name']);
			} else {
				$event->setParsedSubject($event->getAffectedUser() . ' deleted the room ' . $params['name']);
			}
		}

		return $event;
	}
}
