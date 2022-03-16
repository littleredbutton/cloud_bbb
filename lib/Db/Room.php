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
 * @method bool getRequireModerator()
 * @method bool getEveryoneIsModerator()
 * @method string getModeratorToken()
 * @method bool getListenOnly()
 * @method bool getMediaCheck()
 * @method bool getCleanLayout()
 * @method bool getJoinMuted()
 * @method string getLogoutURL()
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
 * @method void setRequireModerator(bool $require)
 * @method void setModeratorToken(string $moderatorToken)
 * @method void setListenOnly(bool $listenOnly)
 * @method void setMediaCheck(bool $mediaCheck)
 * @method void setCleanLayout(bool $cleanLayout)
 * @method void setJoinMuted(bool $joinMuted)
 * @method void setLogoutURL(string $logoutURL)
 */
class Room extends Entity implements JsonSerializable {
	public const ACCESS_PUBLIC = 'public';
	public const ACCESS_PASSWORD = 'password';
	public const ACCESS_WAITING_ROOM = 'waiting_room';
	public const ACCESS_WAITING_ROOM_ALL = 'waiting_room_all';
	public const ACCESS_INTERNAL = 'internal';
	public const ACCESS_INTERNAL_RESTRICTED = 'internal_restricted';

	public const ACCESS = [self::ACCESS_PUBLIC, self::ACCESS_PASSWORD, self::ACCESS_WAITING_ROOM, self::ACCESS_WAITING_ROOM_ALL, self::ACCESS_INTERNAL, self::ACCESS_INTERNAL_RESTRICTED];

	public $uid;
	public $name;
	public $attendeePassword;
	public $moderatorPassword;
	public $welcome;
	public $maxParticipants;
	public $record;
	public $userId;
	public $access = self::ACCESS_PUBLIC;
	public $password;
	public $everyoneIsModerator;
	public $requireModerator = false;
	public $shared = false;
	public $moderatorToken;
	public $listenOnly;
	public $mediaCheck;
	public $cleanLayout;
	public $joinMuted;
	public $logoutURL;

	public function __construct() {
		$this->addType('maxParticipants', 'integer');
		$this->addType('record', 'boolean');
		$this->addType('everyoneIsModerator', 'boolean');
		$this->addType('requireModerator', 'boolean');
		$this->addType('shared', 'boolean');
		$this->addType('listenOnly', 'boolean');
		$this->addType('mediaCheck', 'boolean');
		$this->addType('cleanLayout', 'boolean');
		$this->addType('joinMuted', 'boolean');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'uid' => $this->uid,
			'userId' => $this->userId,
			'name' => $this->name,
			'welcome' => $this->welcome,
			'maxParticipants' => (int) $this->maxParticipants,
			'record' => boolval($this->record),
			'access' => $this->access,
			'password' => $this->password,
			'logoutURL' => $this->logoutURL,
			'everyoneIsModerator' => boolval($this->everyoneIsModerator),
			'requireModerator' => boolval($this->requireModerator),
			'shared' => boolval($this->shared),
			'moderatorToken' => $this->moderatorToken,
			'listenOnly' => boolval($this->listenOnly),
			'mediaCheck' => boolval($this->mediaCheck),
			'cleanLayout' => boolval($this->cleanLayout),
			'joinMuted' => boolval($this->joinMuted),
		];
	}
}
