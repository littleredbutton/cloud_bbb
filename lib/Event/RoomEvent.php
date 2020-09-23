<?php

namespace OCA\BigBlueButton\Event;

use OCA\BigBlueButton\Db\Room;
use OCP\EventDispatcher\Event;

abstract class RoomEvent extends Event {

	/** @var Room */
	private $room;

	public function __construct(Room $room) {
		$this->room = $room;
	}

	public function getRoom(): Room {
		return $this->room;
	}
}
