<?php

namespace OCA\BigBlueButton;

use OCA\BigBlueButton\AppInfo\Application;
use OCA\BigBlueButton\Db\Room;
use OCP\IAvatarManager;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\Security\ISecureRandom;

class AvatarRepository {
	public const CONF_KEY_PATH = 'avatar.path';
	public const CONF_KEY_URL = 'avatar.url';

	/** @var IAvatarManager */
	private $avatarManager;

	/** @var ISecureRandom */
	private $random;

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var IConfig */
	private $config;

	public function __construct(
		IAvatarManager $avatarManager,
		IURLGenerator $urlGenerator,
		ISecureRandom $random,
		IConfig $config) {
		$this->avatarManager = $avatarManager;
		$this->urlGenerator = $urlGenerator;
		$this->random = $random;
		$this->config = $config;
	}

	public function getAvatarUrl(Room $room, string $userId): string {
		if (!$this->isAvatarCacheConfigured()) {
			return $this->urlGenerator->linkToRouteAbsolute('core.avatar.getAvatar', ['userId' => $userId, 'size' => 32]);
		}

		$roomDirName = $room->uid . '-' . $this->generateUrlSafeRandom(16);
		$roomDirPath = $this->getRootPath() . $roomDirName . DIRECTORY_SEPARATOR;

		if (!file_exists($roomDirPath)) {
			if (!mkdir($roomDirPath, 0755, true)) {
				throw new \RuntimeException('Can not create room directory: ' . $roomDirPath);
			}

			file_put_contents($roomDirPath . 'index.html', '');

			if (!file_exists($this->getRootPath() . 'index.html')) {
				file_put_contents($this->getRootPath() . 'index.html', 'Avatar cache is working.');
			}
		}

		$avatar = $this->avatarManager->getAvatar($userId);
		$file = $avatar->getFile(32);

		$avatarFileName = $this->generateUrlSafeRandom(16) . '-' . $file->getName();
		$avatarPath = $roomDirPath . $avatarFileName;

		if (!file_put_contents($avatarPath, $file->getContent())) {
			throw new \RuntimeException('Could not write avatar file: ' . $avatarPath);
		}

		chmod($avatarPath, 0644);

		return $this->getBaseUrl() . $roomDirName . '/' . $avatarFileName;
	}

	public function clearRoom(string $roomUid): int {
		if (!$this->isAvatarCacheConfigured() || empty($roomUid)) {
			return 0;
		}

		$fileCounter = 0;

		foreach (glob($this->getRootPath() . $roomUid . '-*' . DIRECTORY_SEPARATOR) as $dir) {
			foreach (scandir($dir) as $file) {
				if (in_array($file, ['.', '..'])) {
					continue;
				}

				unlink($dir . $file);

				if ($file !== 'index.html') {
					$fileCounter++;
				}
			}

			rmdir($dir);
		}

		return $fileCounter;
	}

	public function clearAllRooms(): array {
		if (!$this->isAvatarCacheConfigured()) {
			return [
				'rooms' => 0,
				'files' => 0,
			];
		}

		$path = $this->getRootPath();
		$roomCounter = 0;
		$fileCounter = 0;

		foreach (scandir($path) as $dir) {
			if (in_array($dir, ['.', '..']) || $dir === 'index.html') {
				continue;
			}

			$roomUid = \explode("-", $dir)[0];

			$fileCounter += $this->clearRoom($roomUid);

			$roomCounter++;
		}

		return [
			'rooms' => $roomCounter,
			'files' => $fileCounter,
		];
	}

	private function generateUrlSafeRandom(int $length): string {
		// from Nextcloud 23 ISecureRandom::CHAR_ALPHANUMERIC can be used as shortcut
		return $this->random->generate($length, ISecureRandom::CHAR_UPPER . ISecureRandom::CHAR_LOWER . ISecureRandom::CHAR_DIGITS);
	}

	private function isAvatarCacheConfigured(): bool {
		return !empty($this->getRootPath()) && !empty($this->getBaseUrl());
	}

	private function getRootPath(): string {
		$path = $this->config->getAppValue(Application::ID, self::CONF_KEY_PATH);

		if (empty($path)) {
			return '';
		}

		return substr($path, -\strlen($path)) === DIRECTORY_SEPARATOR ? $path : ($path . DIRECTORY_SEPARATOR);
	}

	private function getBaseUrl(): string {
		$url = $this->config->getAppValue(Application::ID, self::CONF_KEY_URL);

		if (empty($url)) {
			return '';
		}

		if (preg_match('/^https?:\/\//', $url) === 0) {
			$url = $this->urlGenerator->getAbsoluteURL($url);
		}

		if (preg_match('/\/$/', $url) === 0) {
			$url .= '/';
		}

		return $url;
	}
}
