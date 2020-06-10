<?php
namespace OCA\BigBlueButton\Tests\Controller;


use PHPUnit\Framework\TestCase;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IURLGenerator;
use OCP\ISession;
use OCP\IUserSession;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUser;
use OCA\BigBlueButton\Service\RoomService;
use OCA\BigBlueButton\Controller\JoinController;
use OCA\BigBlueButton\BigBlueButton\API;
use OCA\BigBlueButton\NotFoundException;
use OCA\BigBlueButton\Db\Room;

class JoinControllerTest extends TestCase
{
	private $request;
	private $service;
	private $userSession;
	private $config;
	private $urlGenerator;
	private $controller;
	private $api;
	private $room;

	public function setUp(): void
	{
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->session = $this->createMock(ISession::class);
		$this->service = $this->createMock(RoomService::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->config = $this->createMock(IConfig::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->api = $this->createMock(API::class);

		$this->controller = new JoinController(
			'bbb',
			$this->request,
			$this->session,
			$this->service,
			$this->urlGenerator,
			$this->userSession,
			$this->config,
			$this->api
		);

		$this->room = new Room();
		$this->room->uid = 'uid_foo';
		$this->room->userId = 'user_foo';
		$this->room->access = Room::ACCESS_PUBLIC;
		$this->room->name = 'name_foo';
		$this->room->password = 'password_foo';
	}

	public function testNonExistingRoom()
	{
		$this->expectException(NotFoundException::class);
		$this->service
			->expects($this->once())
			->method('findByUID')
			->willReturn(null);

		$this->controller->index(null);
	}

	public function testUserIsLoggedIn()
	{
		$this->controller->setToken($this->room->uid);
		$this->service
			->expects($this->once())
			->method('findByUID')
			->willReturn($this->room);

		$this->userSession
			->expects($this->once())
			->method('isLoggedIn')
			->willReturn(true);

		$user = $this->createMock(IUser::class);
		$user->method('getDisplayName')->willReturn('User Bar');
		$user->method('getUID')->willReturn('user_bar');

		$this->userSession
			->expects($this->once())
			->method('getUser')
			->willReturn($user);

		$this->api
			->expects($this->once())
			->method('createMeeting')
			->willReturn(12345);

		$url = 'https://foobar';
		$this->api
			->expects($this->once())
			->method('createJoinUrl')
			->willReturn($url);

		$result = $this->controller->index(null);

		$this->assertInstanceOf(RedirectResponse::class, $result);
		$this->assertEquals($url, $result->getRedirectURL());
	}

	public function testUserNeedsToAuthenticate()
	{
		$this->markTestIncomplete();
	}

	public function testInvalidDisplayname()
	{
		$this->markTestIncomplete();
	}

	public function testPasswordRequired()
	{
		$this->markTestIncomplete();
	}

	public function testFormActionAllowed()
	{
		$this->markTestIncomplete();
	}
}
