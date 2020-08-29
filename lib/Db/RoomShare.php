<?php

namespace OCA\BigBlueButton\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;
use OCP\Share\IShare;

/**
 * @method int getRoomId()
 * @method int getShareType()
 * @method string getShareWith()
 * @method string|null getShareWithDisplayName()
 * @method int getPermission()
 * @method void setShareWithDisplayName(string $displayName)
 * @method void setRoomId(int $id)
 * @method void setShareType(int $type)
 * @method void setShareWith(string $with)
 * @method void setShareWithDisplayName(string $displayName)
 * @method void setPermission(int $permission)
 */
class RoomShare extends Entity implements JsonSerializable {
	public const PERMISSION_ADMIN = 0;
	public const PERMISSION_MODERATOR = 1;
	public const PERMISSION_USER = 2;

	public const SHARE_TYPE_USER = IShare::TYPE_USER;
	public const SHARE_TYPE_GROUP = IShare::TYPE_GROUP;
	public const SHARE_TYPE_CIRCLE = IShare::TYPE_CIRCLE;

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
