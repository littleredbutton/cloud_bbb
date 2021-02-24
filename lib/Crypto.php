<?php

namespace OCA\BigBlueButton;

use OCP\Security\ICrypto;

class Crypto {
	/** @var ICrypto */
	private $crypto;

	public function __construct(
		ICrypto $crypto
	) {
		$this->crypto = $crypto;
	}

	public function calculateHMAC(string $message): string {
		if ($message === null) {
			throw new \InvalidArgumentException();
		}

		return $this->encodeBase64UrlSafe(\sha1($this->crypto->calculateHMAC($message), true));
	}

	public function verifyHMAC(string $message, string $mac): bool {
		if ($message === null || $mac === null) {
			return false;
		}

		$validMac = $this->encodeBase64UrlSafe(\sha1($this->crypto->calculateHMAC($message), true));

		return $validMac === $mac;
	}

	/**
	 * @return false|string
	 */
	private function encodeBase64UrlSafe(string $data) {
		$b64 = \base64_encode($data);

		if ($b64 === false) {
			return false;
		}

		return \rtrim(\strtr($b64, '+/', '-_'), '=');
	}
}
