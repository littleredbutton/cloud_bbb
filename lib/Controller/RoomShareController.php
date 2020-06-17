<?php

namespace OCA\BigBlueButton\Controller;

use OCA\BigBlueButton\Db\RoomShare;
use OCA\BigBlueButton\Service\RoomService;
use OCA\BigBlueButton\Service\RoomShareNotFound;
use OCP\IRequest;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\BigBlueButton\Service\RoomShareService;
use OCP\IUserManager;

class RoomShareController extends Controller
{
	/** @var RoomShareService */
	private $service;

	/** @var string */
	private $userId;

	/** @var IUserManager */
	private $userManager;

	/** @var RoomService */
	private $roomService;

	use Errors;

	public function __construct(
		$appName,
		IRequest $request,
		RoomShareService $service,
		IUserManager $userManager,
		RoomService $roomService,
		$userId
	) {
		parent::__construct($appName, $request);
		$this->service = $service;
		$this->userManager = $userManager;
		$this->roomService = $roomService;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(): DataResponse
	{
		$roomId = $this->request->getParam('id');

		if ($roomId === null) {
			return new DataResponse([], Http::STATUS_BAD_REQUEST);
		}

		if (!$this->isUserAllowed($roomId)) {
			return new DataResponse([], Http::STATUS_FORBIDDEN);
		}

		$roomShares = $this->service->findAll($roomId);

		/** @var RoomShare $roomShare */
		foreach ($roomShares as $roomShare) {
			$shareWithUser = $this->userManager->get($roomShare->getShareWith());

			if ($shareWithUser !== null) {
				$roomShare->setShareWithDisplayName($shareWithUser->getDisplayName());
			}
		}

		return new DataResponse($roomShares);
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(
		int $roomId,
		int $shareType,
		string $shareWith,
		int $permission
	): DataResponse {
		if (!$this->isUserAllowed($roomId)) {
			return new DataResponse(null, Http::STATUS_FORBIDDEN);
		}

		return new DataResponse($this->service->create(
			$roomId,
			$shareType,
			$shareWith,
			$permission
		));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(
		int $id,
		int $roomId,
		int $shareType,
		string $shareWith,
		int $permission
	): DataResponse {
		if (!$this->isUserAllowed($roomId)) {
			return new DataResponse(null, Http::STATUS_FORBIDDEN);
		}

		return $this->handleNotFound(function () use (
			$id,
			$roomId,
			$shareType,
			$shareWith,
			$permission) {
			return $this->service->update(
				$id,
				$roomId,
				$shareType,
				$shareWith,
				$permission
			);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse
	{
		return $this->handleNotFound(function () use ($id) {
			$roomShare = $this->service->find($id);

			if (!$this->isUserAllowed($roomShare->getRoomId())) {
				return new DataResponse(null, Http::STATUS_FORBIDDEN);
			}

			return $this->service->delete($id);
		});
	}

	private function isUserAllowed(int $roomId): bool
	{
		try {
			$room = $this->roomService->find($roomId);

			return $room->getUserId() === $this->userId;
		} catch (RoomShareNotFound $e) {
			return false;
		}
	}
}
