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
 * @method bool getAllowLogoutURL()
 * @method void setRoomId(string $id)
 * @method void setMaxRooms(int $number)
 * @method void setMaxParticipants(int $number)
 * @method void setAllowRecording(bool $allow)
 * @method void setAllowLogoutURL(bool $allow)
 */
class Restriction extends Entity implements JsonSerializable {
	public const ALL_ID = '';

	protected $groupId;
	protected $maxRooms = -1;
	protected $roomTypes = '[]';
	protected $maxParticipants = -1;
	protected $allowRecording = true;
	protected $allowLogoutURL = true;

	public function __construct() {
		$this->addType('maxRooms', 'integer');
		$this->addType('maxParticipants', 'integer');
		$this->addType('allowRecording', 'boolean');
		$this->addType('allowLogoutURL', 'boolean');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'groupId' => $this->groupId,
			'maxRooms' => (int) $this->maxRooms,
			'roomTypes' => \json_decode($this->roomTypes),
			'maxParticipants' => (int) $this->maxParticipants,
			'allowRecording' => boolval($this->allowRecording),
			'allowLogoutURL' => boolval($this->allowLogoutURL),
		];
	}
}
