<?php

namespace OCA\BigBlueButton\Event;

use OCP\EventDispatcher\Event;
use OCA\BigBlueButton\Db\RoomShare;

abstract class RoomShareEvent extends Event {

	/** @var RoomShare */
	private $roomShare;

	public function __construct(RoomShare $roomShare) {
		$this->roomId = $roomShare;
	}

	public function getRoomShare(): RoomShare {
		return $this->roomShare;
	}
}
