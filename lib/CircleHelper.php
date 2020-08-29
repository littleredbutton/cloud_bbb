<?php

namespace OCA\BigBlueButton;

use OCA\BigBlueButton\AppInfo\Application;
use OCP\App\IAppManager;

class CircleHelper {
	public const LEVEL_NONE = 0;
	public const LEVEL_MEMBER = 1;
	public const LEVEL_MODERATOR = 4;
	public const LEVEL_ADMIN = 8;
	public const LEVEL_OWNER = 9;

	private $api;

	/** @var Application */
	private $app;

	/** @var IAppManager */
	private $appManager;

	private $cache = [];

	public function __construct(
		Application $app,
		IAppManager $appManager
	) {
		$this->app = $app;
		$this->appManager = $appManager;
	}

	public function isInCircle(string $userId, string $circleId): bool {
		return \in_array($circleId, $this->getCircleIds($userId));
	}

	public function getCircleIds(string $userId): array {
		if (!\array_key_exists($userId, $this->cache)) {
			$this->cache[$userId] = [];

			$api = $this->getCircleAPI();

			if ($api !== false) {
				$circles = $api->listCircles(\OCA\Circles\Api\v1\Circles::CIRCLES_ALL, '', \OCA\Circles\Api\v1\Circles::LEVEL_MEMBER);

				foreach ($circles as $circle) {
					$this->cache[$userId][] = $circle->getUniqueId();
				}
			}
		}

		return $this->cache[$userId];
	}

	public function getCircleAPI() {
		if ($this->api === null) {
			if ($this->appManager->isEnabledForUser('circles') && class_exists('\OCA\Circles\Api\v1\Circles')) {
				$container = $this->app->getContainer();
				$this->api = $container->query(\OCA\Circles\Api\v1\Circles::class);
			} else {
				$this->api = false;
			}
		}

		return $this->api;
	}
}
