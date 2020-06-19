<?php

namespace OCA\BigBlueButton\Tests\Integration\Db;

use OC;
use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\Db\RoomMapper;
use OCA\BigBlueButton\Db\RoomShare;
use OCA\BigBlueButton\Db\RoomShareMapper;
use PHPUnit\Framework\TestCase;
use OCP\IDBConnection;

class RoomMapperTest extends TestCase {
	/** @var IDBConnection */
	private $db;

	/** @var RoomMapper */
	private $mapper;

	/** @var RoomShareMapper */
	private $shareMapper;

	/** @var string */
	private $userId;

	/** @var string */
	private $uid;

	public function setUp(): void {
		parent::setUp();
		$this->db = OC::$server->getDatabaseConnection();
		$this->mapper = new RoomMapper($this->db);
		$this->shareMapper = new RoomShareMapper($this->db);

		$this->userId = $this->getRandomString();
		$this->uid = $this->getRandomString();
	}

	public function testInsert() {
		$room = $this->insert($this->uid, $this->userId);

		$this->assertEquals($this->uid, $room->getUid());

		$this->mapper->delete($room);
	}

	/**
	 * @depends testInsert
	 */
	public function testFind() {
		$newRoom = $this->insert($this->uid, $this->userId);

		$room = $this->mapper->find($newRoom->getId());

		$this->assertEquals($this->uid, $room->getUid());

		$this->mapper->delete($room);
	}

	/**
	 * @depends testInsert
	 */
	public function testFindByUid() {
		$newRoom = $this->insert($this->uid, $this->userId);

		$room = $this->mapper->findByUid($this->uid);

		$this->assertEquals($newRoom->getId(), $room->getId());

		$this->mapper->delete($room);
	}

	/**
	 * @depends testInsert
	 */
	public function testFindAll() {
		$room = $this->insert($this->uid, $this->userId);

		$foreignRoom1 = $this->insert($this->getRandomString(), $this->getRandomString());
		$foreignRoom2 = $this->insert($this->getRandomString(), $this->getRandomString());
		$foreignRoom3 = $this->insert($this->getRandomString(), $this->getRandomString());

		$this->assertCount(1, $this->mapper->findAll($this->userId, []));

		$shares = [];
		$shares[] = $this->insertShare($foreignRoom1->getId(), RoomShare::SHARE_TYPE_USER, $this->userId);
		$shares[] = $this->insertShare($foreignRoom1->getId(), RoomShare::SHARE_TYPE_GROUP, 'foo bar');
		$shares[] = $this->insertShare($foreignRoom2->getId(), RoomShare::SHARE_TYPE_GROUP, 'foo bar');
		$shares[] = $this->insertShare($foreignRoom3->getId(), RoomShare::SHARE_TYPE_USER, $this->getRandomString());
		$shares[] = $this->insertShare($foreignRoom3->getId(), RoomShare::SHARE_TYPE_USER, $this->userId, RoomShare::PERMISSION_MODERATOR);
		$shares[] = $this->insertShare($foreignRoom3->getId(), RoomShare::SHARE_TYPE_GROUP, 'foo bar', RoomShare::PERMISSION_USER);

		$rooms = $this->mapper->findAll($this->userId, ['foo bar']);
		$this->assertCount(3, $rooms);

		$this->mapper->delete($room);
		$this->mapper->delete($foreignRoom1);
		$this->mapper->delete($foreignRoom2);
		$this->mapper->delete($foreignRoom3);

		foreach ($shares as $share) {
			$this->shareMapper->delete($share);
		}
	}

	private function getRandomString(): string {
		return \OC::$server->getSecureRandom()->generate(18, \OCP\Security\ISecureRandom::CHAR_HUMAN_READABLE);
	}

	private function insert($uid, $userId): Room {
		$room = new Room();
		$room->setUid($uid);
		$room->setName('random name');
		$room->setWelcome('');
		$room->setMaxParticipants(0);
		$room->setAttendeePassword('1');
		$room->setModeratorPassword('2');
		$room->setRecord(false);
		$room->setUserId($userId);

		return $this->mapper->insert($room);
	}

	private function insertShare($id, $type, $with, $permission = RoomShare::PERMISSION_ADMIN): RoomShare {
		$roomShare = new RoomShare();

		$roomShare->setRoomId($id);
		$roomShare->setShareType($type);
		$roomShare->setShareWith($with);
		$roomShare->setPermission($permission);

		return $this->shareMapper->insert($roomShare);
	}
}
