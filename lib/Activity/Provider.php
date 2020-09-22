<?php

namespace OCA\BigBlueButton\Activity;

use OCA\BigBlueButton\AppInfo\Application;
use OCA\BigBlueButton\Db\RoomShare;
use OCP\Activity\IProvider;
use OCP\Activity\IEvent;
use OCP\Activity\IManager;
use OCP\IL10N;
use OCP\IUserManager;
use OCP\IGroupManager;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use OCP\IUser;

class Provider implements IProvider {

	/** @var string */
	public const ROOM_CREATED = 'room_created';

	/** @var string */
	public const ROOM_DELETED = 'room_deleted';

	/** @var string */
	public const SHARE_CREATED = 'share_created';

	/** @var string */
	public const SHARE_DELETED = 'share_deleted';

	/** @var string */
	public const MEETING_STARTED = 'meeting_started';

	/** @var string */
	public const MEETING_ENDED = 'meeting_ended';

	/** @var string */
	public const RECORDING_READY = 'recording_ready';

	/** @var IL10N */
	protected $l;

	/** @var IManager */
	protected $activityManager;

	/** @var IUserManager */
	protected $userManager;

	/** @var IGroupManager */
	protected $groupManager;

	/** @var IURLGenerator */
	protected $url;

	/** @var IFactory */
	protected $languageFactory;

	public function __construct(
		IManager $manager,
		IUserManager $userManager,
		IGroupManager $groupManager,
		IURLGenerator $url,
		IFactory $languageFactory
	) {
		$this->activityManager = $manager;
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
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
		} elseif ($subject === self::SHARE_CREATED) {
			$this->parseShareCreated($event);
		} elseif ($subject === self::SHARE_DELETED) {
			$this->parseShareDeleted($event);
		} elseif ($subject === self::MEETING_STARTED) {
			$this->parseMeetingStarted($event);
		} elseif ($subject === self::MEETING_ENDED) {
			$this->parseMeetingEnded($event);
		} elseif ($subject === self::RECORDING_READY) {
			$this->parseRecordingReady($event);
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

	private function parseShareCreated(IEvent $event) {
		$params = $event->getSubjectParameters();

		if ($this->activityManager->getCurrentUserId() === $event->getAuthor()) {
			$subject = $this->l->t('You shared the room %s with {shareWith}.', [$params['name']]);
		} else {
			$subject = $this->l->t('{user} shared the room %s with you.', [$params['name']]);
		}

		$this->setSubjects($event, $subject, [
			'user' => $this->getUser($event->getAuthor()),
			'shareWith' => $params['shareType'] === RoomShare::SHARE_TYPE_USER ? $this->getUser($params['shareWith']) : $this->getGroup($params['shareWith']),
		]);

		$this->setIcon($event, 'share-created');
	}

	private function parseShareDeleted(IEvent $event) {
		$params = $event->getSubjectParameters();

		if ($this->activityManager->getCurrentUserId() === $event->getAuthor()) {
			$subject = $this->l->t('You unshared the room %s with {shareWith}.', [$params['name']]);
		} else {
			$subject = $this->l->t('{user} unshared the room %s with you.', [$params['name']]);
		}

		$this->setSubjects($event, $subject, [
			'user' => $this->getUser($event->getAuthor()),
			'shareWith' => $params['shareType'] === RoomShare::SHARE_TYPE_USER ? $this->getUser($params['shareWith']) : $this->getGroup($params['shareWith']),
		]);

		$this->setIcon($event, 'share-deleted');
	}

	private function parseMeetingStarted(IEvent $event) {
		$params = $event->getSubjectParameters();

		if ($this->activityManager->getCurrentUserId() === $event->getAuthor()) {
			$subject = $this->l->t('You started a meeting in the "%s" room.', [$params['name']]);
		} else {
			$subject = $this->l->t('{user} started a meeting in the "%s" room.', [$params['name']]);
		}

		$this->setSubjects($event, $subject, [
			'user' => $this->getUser($event->getAuthor()),
		]);

		$this->setIcon($event, 'meeting-started');
	}

	private function parseMeetingEnded(IEvent $event) {
		$params = $event->getSubjectParameters();

		$event->setParsedSubject($this->l->t('The meeting in room "%s" has ended.', [$params['name']]));

		$this->setIcon($event, 'meeting-ended');
	}

	private function parseRecordingReady(IEvent $event) {
		$params = $event->getSubjectParameters();

		$event->setParsedSubject($this->l->t('Recording for room "%s" is ready.', [$params['name']]));

		$this->setIcon($event, 'recording-ready');
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

	protected function getGroup($uid) {
		$group = $this->groupManager->get($uid);

		if ($group !== null) {
			return [
				'type' => 'user-group',
				'id' => $group->getGID(),
				'name' => $group->getDisplayName(),
			];
		}

		return [
			'type' => 'user-group',
			'id' => $uid,
			'name' => $uid,
		];
	}
}
