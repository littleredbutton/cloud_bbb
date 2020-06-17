<?php
namespace OCA\BigBlueButton\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class Room extends Entity implements JsonSerializable
{
	const ACCESS_PUBLIC = 'public';
	const ACCESS_PASSWORD = 'password';
	const ACCESS_WAITING_ROOM = 'waiting_room';
	const ACCESS_INTERNAL = 'internal';
	const ACCESS_INTERNAL_RESTRICTED = 'internal_restricted';

	public $uid;
	public $name;
	public $attendeePassword;
	public $moderatorPassword;
	public $welcome;
	public $maxParticipants;
	public $record;
	public $userId;
	public $access;
	public $password;
	public $everyoneIsModerator;

	public function __construct()
	{
		$this->addType('maxParticipants', 'integer');
		$this->addType('record', 'boolean');
		$this->addType('everyoneIsModerator', 'boolean');
	}

	public function jsonSerialize(): array
	{
		return [
			'id'                  => $this->id,
			'uid'                 => $this->uid,
			'userId'              => $this->userId,
			'name'                => $this->name,
			'welcome'             => $this->welcome,
			'maxParticipants'     => (int) $this->maxParticipants,
			'record'              => boolval($this->record),
			'access'              => $this->access,
			'password'            => $this->password,
			'everyoneIsModerator' => boolval($this->everyoneIsModerator),
		];
	}
}
