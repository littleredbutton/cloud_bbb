<?php

namespace OCA\BigBlueButton\Tests;

use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\Db\RoomShare;
use OCA\BigBlueButton\Db\Restriction;
use OCA\BigBlueButton\Permission;
use OCA\BigBlueButton\Service\RoomService;
use OCA\BigBlueButton\Service\RoomShareService;
use OCA\BigBlueButton\Service\RestrictionService;
use OCA\BigBlueButton\CircleHelper;
use OCP\IUserManager;
use OCP\IGroupManager;
use OCP\IUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase {
	/** @var Permission */
	private $permission;

	/** @var IUserManager|MockObject */
	private $userManager;

	/** @var IGroupManager|MockObject */
	private $groupManager;

	/** @var RoomService|MockObject */
	private $roomService;

	/** @var RoomShareService|MockObject */
	private $roomShareService;

	/** @var RestrictionService|MockObject */
	private $restrictionService;

	/** @var CircleHelper|MockObject */
	private $circleHelper;

	public function setUp(): void {
		parent::setUp();

		/** @var IUserManager|MockObject */
		$this->userManager = $this->createMock(IUserManager::class);

		/** @var IGroupManager|MockObject */
		$this->groupManager = $this->createMock(IGroupManager::class);

		/** @var RoomService|MockObject */
		$this->roomService = $this->createMock(RoomService::class);

		/** @var RestrictionService|MockObject */
		$this->restrictionService = $this->createMock(RestrictionService::class);

		/** @var RoomShareService|MockObject */
		$this->roomShareService = $this->createMock(RoomShareService::class);

		/** @var CircleHelper|MockObject */
		$this->circleHelper = $this->createMock(CircleHelper::class);

		$this->permission = new Permission(
			$this->userManager,
			$this->groupManager,
			$this->roomService,
			$this->restrictionService,
			$this->roomShareService,
			$this->circleHelper
		);
	}

	public function testIsUserNotAllowed() {
		$this->userManager
			->expects($this->once())
			->method('get')
			->with('foobar')
			->willReturn($this->createMock(IUser::class));

		$this->groupManager
			->expects($this->once())
			->method('getUserGroupIds')
			->willReturn([]);

		$this->roomService
			->expects($this->once())
			->method('findAll')
			->willReturn([
				$this->createRoom(1, 'foo'),
				$this->createRoom(2, 'bar'),
			]);

		$restriction = new Restriction();
		$restriction->setMaxRooms(2);

		$this->restrictionService
			->expects($this->once())
			->method('findByGroupIds')
			->willReturn($restriction);

		$this->assertFalse($this->permission->isAllowedToCreateRoom('foobar'));
	}

	public function testIsUser() {
		$room = $this->createRoom(1, 'foo');

		$this->roomShareService
			->expects($this->exactly(5))
			->method('findAll')
			->will($this->returnValueMap([
				[1, [
					$this->createRoomShare(RoomShare::SHARE_TYPE_USER, 'user', RoomShare::PERMISSION_ADMIN),
					$this->createRoomShare(RoomShare::SHARE_TYPE_GROUP, 'group', RoomShare::PERMISSION_MODERATOR),
					$this->createRoomShare(RoomShare::SHARE_TYPE_CIRCLE, 'circle', RoomShare::PERMISSION_USER),
				]],
				[2, []],
			]));

		$this->groupManager
			->method('isInGroup')
			->will($this->returnValueMap([
				['bar', 'group', false],
				['group_user', 'group', true],
			]));

		$this->circleHelper
			->method('isInCircle')
			->will($this->returnValueMap([
				['bar', 'circle', false],
				['circle_user', 'circle', true],
			]));

		$this->assertFalse($this->permission->isUser($room, null), 'Test guest user');
		$this->assertFalse($this->permission->isUser($room, 'bar'), 'Test no matching share');
		$this->assertFalse($this->permission->isUser($this->createRoom(2, 'foo'), 'bar'), 'Test empty shares');

		$this->assertTrue($this->permission->isUser($room, 'foo'), 'Test room owner');
		$this->assertTrue($this->permission->isUser($room, 'user'));
		$this->assertTrue($this->permission->isUser($room, 'group_user'));
		$this->assertTrue($this->permission->isUser($room, 'circle_user'));
	}

	private function createRoom(int $id, string $userId): Room {
		$room = new Room();

		$room->setId($id);
		$room->setUserId($userId);

		return $room;
	}

	private function createRoomShare(int $type, string $with, int $permission): RoomShare {
		$share = new RoomShare();

		$share->setShareType($type);
		$share->setShareWith($with);
		$share->setPermission($permission);

		return $share;
	}
}
