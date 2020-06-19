<?php

namespace OCA\BigBlueButton\AppInfo;

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
	}
}
