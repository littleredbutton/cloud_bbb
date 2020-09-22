<?php

namespace OCA\BigBlueButton\Activity;

use OCP\Activity\ISetting;

class Setting implements ISetting {
	public const Identifier = 'bbb';

	public function getIdentifier() {
		return self::Identifier;
	}

	public function getName() {
		return 'BigBlueButton';
	}

	public function getPriority() {
		return 70;
	}

	public function canChangeStream() {
		return true;
	}

	public function isDefaultEnabledStream() {
		return true;
	}

	public function canChangeMail() {
		return true;
	}

	public function isDefaultEnabledMail() {
		return false;
	}
}
