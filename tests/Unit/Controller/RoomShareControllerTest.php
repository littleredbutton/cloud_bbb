<?php

namespace OCA\BigBlueButton\Tests\Controller;

use OCA\BigBlueButton\CircleHelper;
use OCA\BigBlueButton\Controller\RoomShareController;
use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\Db\RoomShare;
use OCA\BigBlueButton\Service\RoomService;
use OCA\BigBlueButton\Service\RoomShareService;
use OCP\AppFramework\Http;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserManager;
use PHPUnit\Framework\TestCase;

class RoomShareControllerTest extends TestCase {
	private $request;
	private $service;
	private $roomService;
	private $circleHelper;
	private $userManager;
	private $groupManager;
	private $controller;

	private $userId = 'user_foo';

	public function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->service = $this->createMock(RoomShareService::class);
		$this->userManager = $this->createMock(IUserManager::class);
		$this->groupManager = $this->createMock(IGroupManager::class);
		$this->roomService = $this->createMock(RoomService::class);
		$this->circleHelper = $this->createMock(CircleHelper::class);

		$this->controller = new RoomShareController(
			'bbb',
			$this->request,
			$this->service,
			$this->userManager,
			$this->groupManager,
			$this->roomService,
			$this->circleHelper,
			$this->userId
		);
	}

	public function testIndexWithoutRoomId() {
		$response = $this->controller->index();

		$this->assertEquals(Http::STATUS_BAD_REQUEST, $response->getStatus());
	}

	public function testIndexWithoutPermission() {
		$this->request
			->expects($this->once())
			->method('getParam')
			->with('id')
			->willReturn(1234);

		$room = new Room();
		$room->setUserId('user_bar');

		$this->roomService
			->expects($this->once())
			->method('find')
			->with(1234)
			->willReturn($room);

		$response = $this->controller->index();

		$this->assertEquals(Http::STATUS_FORBIDDEN, $response->getStatus());
	}

	public function testIndexWithoutShares() {
		$roomId = 1234;
		$this->request
			->expects($this->once())
			->method('getParam')
			->with('id')
			->willReturn($roomId);

		$room = new Room();
		$room->setUserId($this->userId);

		$this->roomService
			->expects($this->once())
			->method('find')
			->willReturn($room);

		$this->service
			->expects($this->once())
			->method('findAll')
			->with($roomId)
			->willReturn([]);

		$response = $this->controller->index();

		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
		$this->assertEquals([], $response->getData());
	}

	public function testIndexWithShares() {
		$roomId = 1234;
		$this->request
			->expects($this->once())
			->method('getParam')
			->with('id')
			->willReturn($roomId);

		$room = new Room();
		$room->setUserId($this->userId);

		$this->roomService
			->expects($this->once())
			->method('find')
			->willReturn($room);

		$this->service
			->expects($this->once())
			->method('findAll')
			->with($roomId)
			->willReturn([
				new RoomShare()
			]);

		$response = $this->controller->index();

		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
		$this->assertCount(1, $response->getData());
	}
}
