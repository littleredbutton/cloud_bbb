<?php

namespace OCA\BigBlueButton\AppInfo;

use OCA\BigBlueButton\Permission;
use OCP\App\IAppManager;
use OCP\Capabilities\ICapability;
use OCP\IUserSession;

class Capabilities implements ICapability {
	public function __construct(private IUserSession $userSession, private IAppManager $appManager, private Permission $permission) {
	}

	public function getCapabilities(): array {
		$user = $this->userSession->getUser();
		if (!$user) {
			return [];
		}
		$restriction = $this->permission->getRestriction($user->getUID());
		$capabilities = array_filter($restriction->jsonSerialize(), function ($key) {
			return in_array($key, ['maxRooms', 'maxParticipants', 'allowRecording']);
		}, ARRAY_FILTER_USE_KEY);

		return [
			Application::ID => array_merge([
				'appVersion' => $this->appManager->getAppVersion(Application::ID),
				'isAllowedToCreateRoom' => $this->permission->isAllowedToCreateRoom($user->getUID()),
			], $capabilities)
		];
	}
}
