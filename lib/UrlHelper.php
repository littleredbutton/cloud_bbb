<?php

namespace OCA\BigBlueButton;

use OCA\BigBlueButton\Db\Room;
use OCP\IConfig;
use OCP\IURLGenerator;

class UrlHelper {
	/** @var IConfig */
	private $config;

	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct(
		IConfig $config,
		IURLGenerator $urlGenerator
	) {
		$this->config = $config;
		$this->urlGenerator = $urlGenerator;
	}

	public function linkToInvitationAbsolute(Room $room): string {
		$url = $this->config->getAppValue('bbb', 'app.shortener', '');

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
