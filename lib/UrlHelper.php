<?php

namespace OCA\BigBlueButton;

use OCA\BigBlueButton\Db\Room;
use OCP\IAppConfig;
use OCP\IURLGenerator;

class UrlHelper {

	public function __construct(
		private IAppConfig $config,
		private IURLGenerator $urlGenerator
	) {
	}

	public function linkToInvitationAbsolute(Room $room): string {
		$url = $this->config->getValueString('bbb', 'app.shortener', '');

		if (empty($url) || strpos($url, 'https://') !== 0 || strpos($url, '{token}') === false) {
			return $this->urlGenerator->linkToRouteAbsolute('bbb.join.index', ['token' => $room->getUid()]);
		}

		$placeholders = [];
		$replacements = [
			'token' => $room->getUid(),
			'user' => $room->getUserId(),
		];


		foreach ($replacements as $placeholder => $parameter) {
			$placeholders[] = '{' . $placeholder . '}';
		}

		return str_replace($placeholders, $replacements, $url);
	}
}
