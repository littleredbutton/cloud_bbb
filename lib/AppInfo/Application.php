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
use \OCP\IConfig;
use \OCP\Settings\IManager as ISettingsManager;
use \OCP\User\Events\UserDeletedEvent;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\INavigationManager;
use OCP\IURLGenerator;
use OCP\Util;

class Application extends App implements IBootstrap {
	public const ID = 'bbb';
	public const ORDER = 80;

	public function __construct(array $urlParams = []) {
		parent::__construct(self::ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		if ((@include_once __DIR__ . '/../../vendor/autoload.php') === false) {
			throw new \Exception('Cannot include autoload. Did you run install dependencies using composer?');
		}

		$context->registerEventListener(RoomCreatedEvent::class, RoomListener::class);
		$context->registerEventListener(RoomDeletedEvent::class, RoomListener::class);

		$context->registerEventListener(RoomShareCreatedEvent::class, RoomShareListener::class);
		$context->registerEventListener(RoomShareDeletedEvent::class, RoomShareListener::class);

		$context->registerEventListener(MeetingStartedEvent::class, MeetingListener::class);
		$context->registerEventListener(MeetingEndedEvent::class, MeetingListener::class);
		$context->registerEventListener(RecordingReadyEvent::class, MeetingListener::class);

		$context->registerEventListener(UserDeletedEvent::class, UserDeletedListener::class);

		$context->registerSearchProvider(Provider::class);

		$context->registerMiddleware(JoinMiddleware::class);
		$context->registerMiddleware(HookMiddleware::class);
	}

	public function boot(IBootContext $context): void {
		$context->injectFn([$this, 'registerAdminPage']);

		Util::addScript('bbb', 'filelist');
	}

	public function registerAdminPage(ISettingsManager $settingsManager, INavigationManager $navigationManager, IURLGenerator $urlGenerator, IConfig $config):void {
		if ($config->getAppValue(self::ID, 'app.navigation') === 'true') {
			$this->registerAsNavigationEntry($navigationManager, $urlGenerator, $config);
		} else {
			$this->registerAsPersonalSetting($settingsManager);
		}
	}

	private function registerAsPersonalSetting(ISettingsManager $settingsManager): void {
		$settingsManager->registerSetting(ISettingsManager::KEY_PERSONAL_SETTINGS, \OCA\BigBlueButton\Settings\Personal::class);
	}

	private function registerAsNavigationEntry(INavigationManager $navigationManager, IURLGenerator $urlGenerator, IConfig $config): void {
		$name = $config->getAppValue(self::ID, 'app.navigation.name', 'BBB');

		$navigationManager->add(function () use ($urlGenerator, $name) {
			return [
				'id' => self::ID,
				'order' => 80,
				'href' => $urlGenerator->linkToRoute('bbb.page.index'),
				'icon' => $urlGenerator->imagePath('bbb', 'app.svg'),
				'name' => $name,
			];
		});
	}
}
