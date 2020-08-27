<?php

namespace OCA\BigBlueButton\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getRoomId()
 * @method int getMaxRooms()
 * @method string getRoomTypes()
 * @method int getMaxParticipants()
 * @method bool getAllowRecording()
 * @method void setRoomId(string $id)
 * @method void setMaxRooms(int $number)
 * @method void setMaxParticipants(int $number)
 * @method void setAllowRecording(bool $allow)
 */
class Restriction extends Entity implements JsonSerializable {
	public const ALL_ID = '';

	protected $groupId;
	protected $maxRooms = -1;
	protected $roomTypes = '[]';
	protected $maxParticipants = -1;
	protected $allowRecording = true;

	public function __construct() {
		$this->addType('max_rooms', 'integer');
		$this->addType('max_participants', 'integer');
		$this->addType('allow_recording', 'boolean');
	}

	public function jsonSerialize(): array {
		return [
			'id'              => $this->id,
			'groupId'         => $this->groupId,
			'maxRooms'        => (int) $this->maxRooms,
			'roomTypes'       => \json_decode($this->roomTypes),
			'maxParticipants' => (int) $this->maxParticipants,
			'allowRecording'  => boolval($this->allowRecording),
		];
	}
}
