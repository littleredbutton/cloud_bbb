<?php

namespace OCA\BigBlueButton\AppInfo;

use \OCP\IConfig;
use \OCP\Settings\IManager as ISettingsManager;
use \OCP\AppFramework\App;
use \OCA\BigBlueButton\Middleware\JoinMiddleware;

if ((@include_once __DIR__ . '/../../vendor/autoload.php') === false) {
	throw new \Exception('Cannot include autoload. Did you run install dependencies using composer?');
}

class Application extends App {
	public function __construct(array $urlParams = []) {
		parent::__construct('bbb', $urlParams);

		$container = $this->getContainer();

		$container->registerMiddleWare(JoinMiddleware::class);

		$config = $container->query(IConfig::class);

		if ($config->getAppValue('bbb', 'app.navigation') === 'true') {
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
				'id' => 'bbb',
				'order' => 80,
				'href' => $server->getURLGenerator()->linkToRoute('bbb.page.index'),
				'icon' => $server->getURLGenerator()->imagePath('bbb', 'app.svg'),
				'name' => 'BigBlueButton',
			];
		});
	}
}
