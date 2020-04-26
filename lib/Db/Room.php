<?php
namespace OCA\BigBlueButton\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class Room extends Entity implements JsonSerializable
{
	public $uid;
	public $name;
	public $attendeePassword;
	public $moderatorPassword;
	public $welcome;
	public $maxParticipants;
	public $record;
	public $userId;

	public function jsonSerialize(): array
	{
		return [
			'id'              => $this->id,
			'uid'             => $this->uid,
			'name'            => $this->name,
			'welcome'         => $this->welcome,
			'maxParticipants' => (int) $this->maxParticipants,
			'record'          => boolval($this->record),
		];
	}
}
