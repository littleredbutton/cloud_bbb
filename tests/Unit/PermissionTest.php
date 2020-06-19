<?php
namespace OCA\BigBlueButton\Tests;

use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\Db\RoomShare;
use OCA\BigBlueButton\Permission;
use OCA\BigBlueButton\Service\RoomShareService;
use OCP\IGroupManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase
{
	/** @var Permission */
	private $permission;

	/** @var IGroupManager|MockObject */
	private $groupManager;

	/** @var RoomShareService|MockObject */
	private $roomShareService;

	public function setUp(): void
	{
		parent::setUp();

		/** @var IGroupManager|MockObject */
		$this->groupManager = $this->createMock(IGroupManager::class);

		/** @var RoomShareService|MockObject */
		$this->roomShareService = $this->createMock(RoomShareService::class);

		$this->permission = new Permission(
			$this->groupManager,
			$this->roomShareService
		);
	}

	public function testIsUser()
	{
		$room = $this->createRoom(1, 'foo');

		$this->roomShareService
			->expects($this->exactly(4))
			->method('findAll')
			->will($this->returnValueMap([
				[1, [
					$this->createRoomShare(RoomShare::SHARE_TYPE_USER, 'user', RoomShare::PERMISSION_ADMIN),
					$this->createRoomShare(RoomShare::SHARE_TYPE_GROUP, 'group', RoomShare::PERMISSION_MODERATOR),
				]],
				[2, []],
			]));

		$this->groupManager
			->method('isInGroup')
			->will($this->returnValueMap([
				['bar', 'group', false],
				['group_user', 'group', true],
			]));

		$this->assertFalse($this->permission->isUser($room, null), 'Test guest user');
		$this->assertFalse($this->permission->isUser($room, 'bar'), 'Test no matching share');
		$this->assertFalse($this->permission->isUser($this->createRoom(2, 'foo'), 'bar'), 'Test empty shares');

		$this->assertTrue($this->permission->isUser($room, 'foo'), 'Test room owner');
		$this->assertTrue($this->permission->isUser($room, 'user'));
		$this->assertTrue($this->permission->isUser($room, 'group_user'));
	}

	private function createRoom(int $id, string $userId): Room
	{
		$room = new Room();

		$room->setId($id);
		$room->setUserId($userId);

		return $room;
	}

	private function createRoomShare(int $type, string $with, int $permission): RoomShare
	{
		$share = new RoomShare();

		$share->setShareType($type);
		$share->setShareWith($with);
		$share->setPermission($permission);

		return $share;
	}
}
