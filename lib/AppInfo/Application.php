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
use \OCA\BigBlueButton\Search\Provider;
use \OCP\AppFramework\App;
use \OCP\AppFramework\QueryException;
use \OCP\AppFramework\Bootstrap\IBootContext;
use \OCP\AppFramework\Bootstrap\IBootstrap;
use \OCP\AppFramework\Bootstrap\IRegistrationContext;
use \OCP\EventDispatcher\IEventDispatcher;
use \OCP\IConfig;
use \OCP\Settings\IManager as ISettingsManager;
use \OCP\User\Events\UserDeletedEvent;
use \OCP\Util;

if ((@include_once __DIR__ . '/../../vendor/autoload.php') === false) {
	throw new \Exception('Cannot include autoload. Did you run install dependencies using composer?');
}

class Application extends App implements IBootstrap {
	public const ID = 'bbb';
	public const ORDER = 80;

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

		Util::addScript('bbb', 'filelist');
	}

	public function boot(IBootContext $context): void {}

	public function register(IRegistrationContext $context): void {
		$context->registerSearchProvider(Provider::class);
	}

	private function registerAsPersonalSetting(): void {
		try {
			/** @var ISettingsManager */
			$settingsManager = $this->getContainer()->query(ISettingsManager::class);
		} catch (QueryException $exception) {
			// Workaround for Nextcloud 19
			$server = $this->getContainer()->getServer();

			if (method_exists($server, 'getSettingsManager')) {
				$settingsManager = $server->getSettingsManager();
			} else {
				return;
			}
		}


		$settingsManager->registerSetting(ISettingsManager::KEY_PERSONAL_SETTINGS, \OCA\BigBlueButton\Settings\Personal::class);
	}

	private function registerAsNavigationEntry(string $name): void {
		$server = $this->getContainer()->getServer();

		$server->getNavigationManager()->add(function () use ($server, $name) {
			return [
				'id' => self::ID,
				'order' => self::ORDER,
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
