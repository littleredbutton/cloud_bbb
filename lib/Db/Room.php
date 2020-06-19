<?php

namespace OCA\BigBlueButton\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getUid()
 * @method string getName()
 * @method string getAttendeePassword()
 * @method string getModeratorPassword()
 * @method string getWelcome()
 * @method int getMaxParticipants()
 * @method bool getRecord()
 * @method string getUserId()
 * @method string getAccess()
 * @method string getPassword()
 * @method bool getEveryoneIsModerator()
 * @method void setUid(string $uid)
 * @method void setName(string $name)
 * @method void setAttendeePassword(string $pw)
 * @method void setModeratorPassword(string $pw)
 * @method void setWelcome(string $welcome)
 * @method void setMaxParticipants(int $max)
 * @method void setRecord(bool $record)
 * @method void setUserId(string $userId)
 * @method void setAccess(string $access)
 * @method void setPassword(string $pw)
 * @method void setEveryoneIsModerator(bool $everyone)
 */
class Room extends Entity implements JsonSerializable {
	public const ACCESS_PUBLIC = 'public';
	public const ACCESS_PASSWORD = 'password';
	public const ACCESS_WAITING_ROOM = 'waiting_room';
	public const ACCESS_INTERNAL = 'internal';
	public const ACCESS_INTERNAL_RESTRICTED = 'internal_restricted';

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

	public function __construct() {
		$this->addType('maxParticipants', 'integer');
		$this->addType('record', 'boolean');
		$this->addType('everyoneIsModerator', 'boolean');
	}

	public function jsonSerialize(): array {
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
