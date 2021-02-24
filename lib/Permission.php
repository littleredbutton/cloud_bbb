<?php

namespace OCA\BigBlueButton;

use Closure;
use OCA\BigBlueButton\Db\Restriction;
use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\Db\RoomShare;
use OCA\BigBlueButton\Service\RestrictionService;
use OCA\BigBlueButton\Service\RoomService;
use OCA\BigBlueButton\Service\RoomShareService;
use OCP\IGroupManager;
use OCP\IUserManager;

class Permission {

	/** @var IUserManager */
	private $userManager;

	/** @var IGroupManager */
	private $groupManager;

	/** @var RoomService */
	private $roomService;

	/** @var RestrictionService */
	private $restrictionService;

	/** @var RoomShareService */
	private $roomShareService;

	/** @var CircleHelper */
	private $circleHelper;

	public function __construct(
		IUserManager $userManager,
		IGroupManager $groupManager,
		RoomService $roomService,
		RestrictionService $restrictionService,
		RoomShareService $roomShareService,
		CircleHelper $circleHelper
	) {
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->roomService = $roomService;
		$this->restrictionService = $restrictionService;
		$this->roomShareService = $roomShareService;
		$this->circleHelper = $circleHelper;
	}

	public function getRestriction(string $uid): Restriction {
		$user = $this->userManager->get($uid);
		$groupIds = $this->groupManager->getUserGroupIds($user);

		return $this->restrictionService->findByGroupIds($groupIds);
	}

	public function isAllowedToCreateRoom(string $uid): bool {
		$numberOfCreatedRooms = count($this->roomService->findAll($uid, [], []));
		$restriction = $this->getRestriction($uid);

		return $restriction->getMaxRooms() < 0 || $restriction->getMaxRooms() > $numberOfCreatedRooms;
	}

	public function isUser(Room $room, ?string $uid): bool {
		return $this->hasPermission($room, $uid, function (RoomShare $share) {
			return $share->hasUserPermission();
		});
	}

	public function isModerator(Room $room, ?string $uid): bool {
		if ($room->everyoneIsModerator) {
			return true;
		}

		return $this->hasPermission($room, $uid, function (RoomShare $share) {
			return $share->hasModeratorPermission();
		});
	}

	public function isAdmin(Room $room, ?string $uid): bool {
		return $this->hasPermission($room, $uid, function (RoomShare $share) {
			return $share->hasAdminPermission();
		});
	}

	private function hasPermission(Room $room, ?string $uid, Closure $hasPermission): bool {
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
			} elseif ($share->getShareType() === RoomShare::SHARE_TYPE_CIRCLE) {
				if ($this->circleHelper->isInCircle($uid, $share->getShareWith())) {
					return true;
				}
			}
		}

		return false;
	}
}
