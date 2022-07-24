<?php

namespace OCA\BigBlueButton\Tests\Integration\Service;

use OC;
use OCA\BigBlueButton\Db\RestrictionMapper;
use OCA\BigBlueButton\Service\RestrictionService;
use OCP\IDBConnection;
use PHPUnit\Framework\TestCase;

class RestrictionServiceTest extends TestCase {
	/** @var IDBConnection */
	private $db;

	/** @var RestrictionService */
	private $service;

	/** @var string */
	private $groupId;

	public function setUp(): void {
		parent::setUp();
		$this->db = OC::$server->getDatabaseConnection();
		$mapper = new RestrictionMapper($this->db);
		$this->service = new RestrictionService($mapper);

		$this->groupId = $this->getRandomString();
	}

	public function testCreate() {
		$restriction = $this->service->create($this->groupId);

		$this->assertEquals($this->groupId, $restriction->getGroupId());

		$this->service->delete($restriction->getId());
	}

	/**
	 * @depends testCreate
	 */
	public function testExistsByGroupId() {
		$restriction = $this->service->create($this->groupId);

		$this->assertTrue($this->service->existsByGroupId($this->groupId));

		$this->assertFalse($this->service->existsByGroupId($this->getRandomString()));

		$this->service->delete($restriction->getId());

		$this->assertFalse($this->service->existsByGroupId($this->groupId));
	}

	/**
	 * @depends testCreate
	 */
	public function testUpdate() {
		$restriction = $this->service->create($this->groupId);
		$updatedRestriction = $this->service->update(
			$restriction->getId(),
			$this->groupId,
			10,
			['public'],
			15,
			false
		);

		$this->assertEquals(10, $updatedRestriction->getMaxRooms());
		$this->assertEquals(15, $updatedRestriction->getMaxParticipants());
		$this->assertEquals(false, $updatedRestriction->getAllowRecording());
		$this->assertEqauls(false, $updatedRestriction->getAllowLogoutURL());

		$this->service->delete($restriction->getId());
	}


	private function getRandomString(): string {
		return \OC::$server->getSecureRandom()->generate(18, \OCP\Security\ISecureRandom::CHAR_HUMAN_READABLE);
	}
}
