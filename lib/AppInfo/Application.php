<?php

namespace OCA\BigBlueButton\AppInfo;

use \OCA\BigBlueButton\Activity\MeetingListener;
use \OCA\BigBlueButton\Activity\RoomListener;
use \OCA\BigBlueButton\Activity\RoomShareListener;
use \OCA\BigBlueButton\Event\MeetingEndedEvent;
use \OCA\BigBlueButton\Event\MeetingStartedEvent;
use \OCA\BigBlueButton\Event\RecordingReadyEvent;
use \OCA\BigBlueButton\Event\RoomCreatedEvent;
use \OCA\BigBlueButton\Event\RoomDeletedEvent;
use \OCA\BigBlueButton\Event\RoomShareCreatedEvent;
use \OCA\BigBlueButton\Event\RoomShareDeletedEvent;
use \OCA\BigBlueButton\Listener\UserDeletedListener;
use \OCA\BigBlueButton\Middleware\HookMiddleware;
use \OCA\BigBlueButton\Middleware\JoinMiddleware;
use \OCP\AppFramework\App;
use \OCP\EventDispatcher\IEventDispatcher;
use \OCP\IConfig;
use \OCP\Settings\IManager as ISettingsManager;
use \OCP\User\Events\UserDeletedEvent;

if ((@include_once __DIR__ . '/../../vendor/autoload.php') === false) {
	throw new \Exception('Cannot include autoload. Did you run install dependencies using composer?');
}

class Application extends App {
	public const ID = 'bbb';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::ID, $urlParams);

		$container = $this->getContainer();

		/* @var IEventDispatcher $dispatcher */
		$dispatcher = $container->query(IEventDispatcher::class);
		$this->registerServiceListener($dispatcher);

		$container->registerMiddleWare(JoinMiddleware::class);
		$container->registerMiddleWare(HookMiddleware::class);

		$config = $container->query(IConfig::class);

		if ($config->getAppValue(self::ID, 'app.navigation') === 'true') {
			$name = $config->getAppValue(self::ID, 'app.navigation.name', 'BBB');

			$this->registerAsNavigationEntry($name);
		} else {
			$this->registerAsPersonalSetting();
		}
	}

	private function registerAsPersonalSetting(): void {
		/** @var ISettingsManager */
		$settingsManager = $this->getContainer()->query(ISettingsManager::class);

		$settingsManager->registerSetting(ISettingsManager::KEY_PERSONAL_SETTINGS, \OCA\BigBlueButton\Settings\Personal::class);
	}

	private function registerAsNavigationEntry(string $name): void {
		$server = $this->getContainer()->getServer();

		$server->getNavigationManager()->add(function () use ($server, $name) {
			return [
				'id' => self::ID,
				'order' => 80,
				'href' => $server->getURLGenerator()->linkToRoute('bbb.page.index'),
				'icon' => $server->getURLGenerator()->imagePath('bbb', 'app.svg'),
				'name' => $name,
			];
		});
	}

	private function registerServiceListener(IEventDispatcher $dispatcher): void {
		$dispatcher->addServiceListener(RoomCreatedEvent::class, RoomListener::class);
		$dispatcher->addServiceListener(RoomDeletedEvent::class, RoomListener::class);

		$dispatcher->addServiceListener(RoomShareCreatedEvent::class, RoomShareListener::class);
		$dispatcher->addServiceListener(RoomShareDeletedEvent::class, RoomShareListener::class);

		$dispatcher->addServiceListener(MeetingStartedEvent::class, MeetingListener::class);
		$dispatcher->addServiceListener(MeetingEndedEvent::class, MeetingListener::class);
		$dispatcher->addServiceListener(RecordingReadyEvent::class, MeetingListener::class);

		$dispatcher->addServiceListener(UserDeletedEvent::class, UserDeletedListener::class);
	}
}
