<?php

namespace OCA\BigBlueButton\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class RoomShare extends Entity implements JsonSerializable {
	public const PERMISSION_ADMIN = 0;
	public const PERMISSION_MODERATOR = 1;
	public const PERMISSION_USER = 2;

	public const SHARE_TYPE_USER = 0;
	public const SHARE_TYPE_GROUP = 1;

	protected $roomId;
	protected $shareType;
	protected $shareWith;
	protected $shareWithDisplayName;
	protected $permission;

	public function __construct() {
		$this->addType('roomId', 'integer');
		$this->addType('shareType', 'integer');
		$this->addType('permission', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id'                   => $this->id,
			'roomId'               => $this->roomId,
			'shareType'            => $this->shareType,
			'shareWith'            => $this->shareWith,
			'shareWithDisplayName' => $this->shareWithDisplayName,
			'permission'           => $this->permission,
		];
	}

	public function hasUserPermission(): bool {
		return $this->permission === self::PERMISSION_ADMIN || $this->permission === self::PERMISSION_MODERATOR || $this->permission === self::PERMISSION_USER;
	}

	public function hasModeratorPermission(): bool {
		return $this->permission === self::PERMISSION_ADMIN || $this->permission === self::PERMISSION_MODERATOR;
	}

	public function hasAdminPermission(): bool {
		return $this->permission === self::PERMISSION_ADMIN;
	}
}
