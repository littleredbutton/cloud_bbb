<?php

namespace OCA\BigBlueButton\Tests\Unit\Service;

use OCA\BigBlueButton\Db\Restriction;


use OCA\BigBlueButton\Db\RestrictionMapper;
use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\Service\RestrictionService;
use PHPUnit\Framework\TestCase;

class RestrictionServiceTest extends TestCase {
	protected $mapper;
	protected $service;

	public function setUp(): void {
		$this->mapper = $this->createMock(RestrictionMapper::class);

		$this->service = new RestrictionService($this->mapper);
	}

	public function testFindByGroupIds() {
		$restriction0 = new Restriction();
		$restriction0->setRoomTypes(\json_encode([Room::ACCESS_INTERNAL]));
		$restriction0->setMaxParticipants(50);
		$restriction0->setAllowRecording(false);

		$restriction1 = new Restriction();
		$restriction1->setRoomTypes(\json_encode([Room::ACCESS_INTERNAL, Room::ACCESS_INTERNAL_RESTRICTED]));
		$restriction1->setMaxRooms(10);
		$restriction1->setMaxParticipants(100);
		$restriction1->setAllowRecording(true);

		$this->mapper
			->expects($this->once())
			->method('findByGroupIds')
			->willReturn([$restriction1]);

		$this->mapper
			->expects($this->once())
			->method('findByGroupId')
			->willReturn($restriction0);

		$result = $this->service->findByGroupIds([]);

		$this->assertEquals([Room::ACCESS_INTERNAL], \json_decode($result->getRoomTypes()));
		$this->assertEquals(-1, $result->getMaxRooms());
		$this->assertEquals(100, $result->getMaxParticipants());
		$this->assertTrue($result->getAllowRecording());
	}
}
