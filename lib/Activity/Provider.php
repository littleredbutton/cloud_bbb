<?php

namespace OCA\BigBlueButton\Activity;

use OCA\BigBlueButton\AppInfo\Application;
use OCP\Activity\IProvider;
use OCP\Activity\IEvent;
use OCP\Activity\IManager;
use OCP\IL10N;
use OCP\IUserManager;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use OCP\IUser;

class Provider implements IProvider {

	/** @var string */
	public const ROOM_CREATED = 'room_created';

	/** @var string */
	public const ROOM_DELETED = 'room_deleted';

	/** @var IL10N */
	protected $l;

	/** @var IManager */
	protected $activityManager;

	/** @var IUserManager */
	protected $userManager;

	/** @var IURLGenerator */
	protected $url;

	/** @var IFactory */
	protected $languageFactory;

	public function __construct(
		IManager $manager,
		IUserManager $userManager,
		IURLGenerator $url,
		IFactory $languageFactory
	) {
		$this->activityManager = $manager;
		$this->userManager = $userManager;
		$this->url = $url;
		$this->languageFactory = $languageFactory;
	}

	public function parse($language, IEvent $event, IEvent $previousEvent = null) {
		if ($event->getApp() !== Application::ID) {
			throw new \InvalidArgumentException();
		}

		$this->l = $this->languageFactory->get(Application::ID, $language);

		$subject = $event->getSubject();

		if ($subject === self::ROOM_CREATED) {
			$this->parseRoomCreated($event);
		} elseif ($subject === self::ROOM_DELETED) {
			$this->parseRoomDeleted($event);
		} else {
			$event->setParsedSubject('Unknown subject: ' . $subject);
		}

		return $event;
	}

	private function parseRoomCreated(IEvent $event) {
		$params = $event->getSubjectParameters();

		$event->setParsedSubject($this->l->t('You created the room %s.', [$params['name']]));

		$this->setIcon($event, 'room-created');
	}

	private function parseRoomDeleted(IEvent $event) {
		$params = $event->getSubjectParameters();

		if ($this->activityManager->getCurrentUserId() === $event->getAuthor()) {
			$event->setParsedSubject($this->l->t('You deleted the room %s.', [$params['name']]));
		} else {
			$subject = $this->l->t('{user} deleted the room %s', [$params['name']]);

			$this->setSubjects($event, $subject, [
				'user' => $this->getUser($event->getAuthor()),
			]);
		}

		$this->setIcon($event, 'room-deleted');
	}

	private function setIcon(IEvent $event, string $baseName) {
		if ($this->activityManager->getRequirePNG()) {
			$imagePath = $this->url->imagePath(Application::ID, 'actions/'.$baseName.'.png');
		} else {
			$imagePath = $this->url->imagePath(Application::ID, 'actions/'.$baseName.'.svg');
		}

		$event->setIcon($this->url->getAbsoluteURL($imagePath));
	}

	private function setSubjects(IEvent $event, $subject, array $parameters) {
		$placeholders = $replacements = [];

		foreach ($parameters as $placeholder => $parameter) {
			$placeholders[] = '{' . $placeholder . '}';
			if ($parameter['type'] === 'file') {
				$replacements[] = $parameter['path'];
			} else {
				$replacements[] = $parameter['name'];
			}
		}

		$event->setParsedSubject(str_replace($placeholders, $replacements, $subject))
			->setRichSubject($subject, $parameters);
	}

	protected function getUser($uid) {
		$user = $this->userManager->get($uid);

		if ($user instanceof IUser) {
			return [
				'type' => 'user',
				'id' => $user->getUID(),
				'name' => $user->getDisplayName(),
			];
		}

		return [
			'type' => 'user',
			'id' => $uid,
			'name' => $uid,
		];
	}
}
