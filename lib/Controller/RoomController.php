<?php

namespace OCA\BigBlueButton\Controller;

use OCA\BigBlueButton\CircleHelper;
use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\Permission;
use OCA\BigBlueButton\Service\RoomService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IGroupManager;
use OCP\IRequest;
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

	/** @var CircleHelper */
	private $circleHelper;

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
		CircleHelper $circleHelper,
		$userId
	) {
		parent::__construct($appName, $request);
		$this->service = $service;
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->permission = $permission;
		$this->circleHelper = $circleHelper;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(): DataResponse
	{
		$user = $this->userManager->get($this->userId);
		$groupIds = $this->groupManager->getUserGroupIds($user);
		$circleIds = $this->circleHelper->getCircleIds($this->userId);

		return new DataResponse($this->service->findAll($this->userId, $groupIds, $circleIds));
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(
		string $name,
		string $welcome,
		int $maxParticipants,
		bool $record,
		string $access
	): DataResponse {
		if (!$this->permission->isAllowedToCreateRoom($this->userId)) {
			return new DataResponse([], Http::STATUS_FORBIDDEN);
		}

		$restriction = $this->permission->getRestriction($this->userId);

		if ($restriction->getMaxParticipants() > -1 && ($maxParticipants > $restriction->getMaxParticipants() || $maxParticipants <= 0)) {
			return new DataResponse(['message' => 'Max participants limit exceeded.'], Http::STATUS_BAD_REQUEST);
		}

		if (!$restriction->getAllowRecording() && $record) {
			return new DataResponse(['message' => 'Not allowed to enable recordings.'], Http::STATUS_BAD_REQUEST);
		}

		$disabledRoomTypes = \json_decode($restriction->getRoomTypes());
		if (in_array($access, $disabledRoomTypes) || !in_array($access, Room::ACCESS)) {
			return new DataResponse(['message' => 'Access type not allowed.'], Http::STATUS_BAD_REQUEST);
		}

		return new DataResponse($this->service->create(
			$name,
			$welcome,
			$maxParticipants,
			$record,
			$access,
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
		bool $everyoneIsModerator,
		bool $requireModerator,
		?string $moderatorToken,
		bool $listenOnly,
		bool $mediaCheck,
		bool $cleanLayout,
		bool $joinMuted,
		string $presentationUserId,
		string $presentationPath
	): DataResponse {
		$room = $this->service->find($id);

		if (!$this->permission->isAdmin($room, $this->userId)) {
			return new DataResponse(null, Http::STATUS_FORBIDDEN);
		}

		$restriction = $this->permission->getRestriction($this->userId);

		if ($restriction->getMaxParticipants() > -1 && $maxParticipants !== $room->getMaxParticipants() && ($maxParticipants > $restriction->getMaxParticipants() || $maxParticipants <= 0)) {
			return new DataResponse(['message' => 'Max participants limit exceeded.'], Http::STATUS_BAD_REQUEST);
		}

		if (!$restriction->getAllowRecording() && $record !== $room->getRecord()) {
			return new DataResponse(['message' => 'Not allowed to enable recordings.'], Http::STATUS_BAD_REQUEST);
		}

		$disabledRoomTypes = \json_decode($restriction->getRoomTypes());
		if ((in_array($access, $disabledRoomTypes) && $access !== $room->getAccess()) || !in_array($access, Room::ACCESS)) {
			return new DataResponse(['message' => 'Access type not allowed.'], Http::STATUS_BAD_REQUEST);
		}

		if ($presentationUserId != '' && $presentationUserId != $room->getPresentationUserId()) {
			return new DataResponse(['message' => 'Not allowed to change to another user.'], Http::STATUS_BAD_REQUEST);
		}

		if ($presentationUserId === '') {
			$presentationUserId = $this->userId;
		}

		if ($presentationUserId != $this->userId && $presentationPath != $room->getPresentationPath()) {
			return new DataResponse(['message' => 'Not allowed to choose path of another user.'], Http::STATUS_BAD_REQUEST);
		}

		return $this->handleNotFound(function () use ($id, $name, $welcome, $maxParticipants, $record, $access, $everyoneIsModerator, $requireModerator, $moderatorToken, $listenOnly, $mediaCheck, $cleanLayout, $joinMuted, $presentationUserId, $presentationPath) {
			return $this->service->update($id, $name, $welcome, $maxParticipants, $record, $access, $everyoneIsModerator, $requireModerator, $moderatorToken, $listenOnly, $mediaCheck, $cleanLayout, $joinMuted, $presentationUserId, $presentationPath);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse
	{
		$room = $this->service->find($id);

		if (!$this->permission->isAdmin($room, $this->userId)) {
			return new DataResponse([], Http::STATUS_FORBIDDEN);
		}

		return $this->handleNotFound(function () use ($id) {
			//@TODO delete shares
			return $this->service->delete($id);
		});
	}
}
