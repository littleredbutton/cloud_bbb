<?php

namespace OCA\BigBlueButton;

use Closure;
use OCA\BigBlueButton\Service\RoomShareService;
use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\Db\RoomShare;
use OCP\IGroupManager;

class Permission
{

	/** @var IGroupManager */
	private $groupManager;

	/** @var RoomShareService */
	private $roomShareService;

	public function __construct(
		IGroupManager $groupManager,
		RoomShareService $roomShareService
	) {
		$this->groupManager = $groupManager;
		$this->roomShareService = $roomShareService;
	}

	public function isUser(Room $room, string $uid)
	{
		return $this->hasPermission($room, $uid, function (RoomShare $share) {
			return $share->hasUserPermission();
		});
	}

	public function isModerator(Room $room, string $uid)
	{
		return $this->hasPermission($room, $uid, function (RoomShare $share) {
			return $share->hasModeratorPermission();
		});
	}

	public function isAdmin(Room $room, string $uid)
	{
		return $this->hasPermission($room, $uid, function (RoomShare $share) {
			return $share->hasAdminPermission();
		});
	}

	private function hasPermission(Room $room, string $uid, Closure $hasPermission): bool
	{
		if ($uid === null) {
			return false;
		}

		if ($uid === $room->userId) {
			return true;
		}

		$shares = $this->roomShareService->findAll($room->id);

		/** @var RoomShare $share */
		foreach ($shares as $share) {
			if (!$hasPermission($share)) {
				continue;
			}

			if ($share->getShareType() === RoomShare::SHARE_TYPE_USER) {
				if ($share->getShareWith() === $uid) {
					return true;
				}
			} elseif ($share->getShareType() === RoomShare::SHARE_TYPE_GROUP) {
				if ($this->groupManager->isInGroup($uid, $share->getShareWith())) {
					return true;
				}
			}
		}

		return false;
	}
}
