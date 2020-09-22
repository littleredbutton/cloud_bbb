<?php

namespace OCA\BigBlueButton\Event;

use OCA\BigBlueButton\Db\Room;

class MeetingEndedEvent extends RoomEvent {
	private $recordingMarks = false;

	public function __construct(Room $room, bool $recordingMarks) {
		parent::__construct($room);

		$this->recordingMarks = $recordingMarks;
	}

	public function hasRecordingMarks(): bool {
		return $this->recordingMarks;
	}
}
