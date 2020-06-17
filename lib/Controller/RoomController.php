<?php

namespace OCA\BigBlueButton\Controller;

use OCA\BigBlueButton\Service\RoomService;
use OCA\BigBlueButton\Permission;
use OCP\IRequest;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\IGroupManager;
use OCP\IUserManager;

class RoomController extends Controller
{
	/** @var RoomService */
	private $service;

	/** @var IUserManager */
	private $userManager;

	/** @var IGroupManager */
	private $groupManager;

	/** @var Permission */
	private $permission;

	/** @var string */
	private $userId;

	use Errors;

	public function __construct(
		$appName,
		IRequest $request,
		RoomService $service,
		IUserManager $userManager,
		IGroupManager $groupManager,
		Permission $permission,
		$userId
	) {
		parent::__construct($appName, $request);
		$this->service = $service;
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->permission = $permission;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(): DataResponse
	{
		$user = $this->userManager->get($this->userId);
		$groupIds = $this->groupManager->getUserGroupIds($user);

		return new DataResponse($this->service->findAll($this->userId, $groupIds));
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(
		string $name,
		string $welcome,
		int $maxParticipants,
		bool $record
	): DataResponse {
		return new DataResponse($this->service->create(
			$name,
			$welcome,
			$maxParticipants,
			$record,
			$this->userId
		));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(
		int $id,
		string $name,
		string $welcome,
		int $maxParticipants,
		bool $record,
		string $access,
		bool $everyoneIsModerator
	): DataResponse {
		$room = $this->service->find($id);

		if (!$this->permission->isAdmin($room, $this->userId)) {
			return new DataResponse(null, Http::STATUS_FORBIDDEN);
		}

		return $this->handleNotFound(function () use ($id, $name, $welcome, $maxParticipants, $record, $everyoneIsModerator, $access) {
			return $this->service->update($id, $name, $welcome, $maxParticipants, $record, $access, $everyoneIsModerator);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse
	{
		$room = $this->service->find($id);

		if (!$this->permission->isAdmin($room, $this->userId)) {
			return new DataResponse(null, Http::STATUS_FORBIDDEN);
		}

		return $this->handleNotFound(function () use ($id) {
			//@TODO delete shares
			return $this->service->delete($id);
		});
	}
}
