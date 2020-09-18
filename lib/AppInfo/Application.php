<?php

namespace OCA\BigBlueButton\AppInfo;

use \OCP\IConfig;
use \OCP\Settings\IManager as ISettingsManager;
use \OCP\AppFramework\App;
use \OCP\EventDispatcher\IEventDispatcher;
use \OCA\BigBlueButton\Middleware\JoinMiddleware;
use \OCA\BigBlueButton\Event\RoomCreatedEvent;
use \OCA\BigBlueButton\Event\RoomDeletedEvent;
use \OCA\BigBlueButton\Activity\RoomListener;
use \OCA\BigBlueButton\Event\RoomShareCreatedEvent;
use \OCA\BigBlueButton\Event\RoomShareDeletedEvent;
use \OCA\BigBlueButton\Activity\RoomShareListener;

if ((@include_once __DIR__ . '/../../vendor/autoload.php') === false) {
	throw new \Exception('Cannot include autoload. Did you run install dependencies using composer?');
}

class Application extends App {
	public const ID = 'bbb';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::ID, $urlParams);

		$container = $this->getContainer();

		/* @var IEventDispatcher $eventDispatcher */
		$dispatcher = $container->query(IEventDispatcher::class);
		$dispatcher->addServiceListener(RoomCreatedEvent::class, RoomListener::class);
		$dispatcher->addServiceListener(RoomDeletedEvent::class, RoomListener::class);

		$dispatcher->addServiceListener(RoomShareCreatedEvent::class, RoomShareListener::class);
		$dispatcher->addServiceListener(RoomShareDeletedEvent::class, RoomShareListener::class);

		$container->registerMiddleWare(JoinMiddleware::class);

		$config = $container->query(IConfig::class);

		if ($config->getAppValue(self::ID, 'app.navigation') === 'true') {
			$this->registerAsNavigationEntry();
		} else {
			$this->registerAsPersonalSetting();
		}
	}

	private function registerAsPersonalSetting() {
		$settingsManager = $this->getContainer()->getServer()->getSettingsManager();

		$settingsManager->registerSetting(ISettingsManager::KEY_PERSONAL_SETTINGS, \OCA\BigBlueButton\Settings\Personal::class);
	}

	private function registerAsNavigationEntry() {
		$server = $this->getContainer()->getServer();

		$server->getNavigationManager()->add(function () use ($server) {
			return [
				'id' => self::ID,
				'order' => 80,
				'href' => $server->getURLGenerator()->linkToRoute('bbb.page.index'),
				'icon' => $server->getURLGenerator()->imagePath('bbb', 'app.svg'),
				'name' => 'BigBlueButton',
			];
		});
	}
}
