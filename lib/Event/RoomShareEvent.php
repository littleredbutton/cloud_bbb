<?php

namespace OCA\BigBlueButton\Event;

use OCA\BigBlueButton\Db\RoomShare;
use OCP\EventDispatcher\Event;

abstract class RoomShareEvent extends Event {

	/** @var RoomShare */
	private $roomShare;

	public function __construct(RoomShare $roomShare) {
		$this->roomShare = $roomShare;
	}

	public function getRoomShare(): RoomShare {
		return $this->roomShare;
	}
}
