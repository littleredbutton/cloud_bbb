<?php

namespace OCA\BigBlueButton\Controller;

use OCA\BigBlueButton\CircleHelper;
use OCA\BigBlueButton\Db\RoomShare;
use OCA\BigBlueButton\Service\RoomService;
use OCA\BigBlueButton\Service\RoomShareNotFound;
use OCA\BigBlueButton\Service\RoomShareService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserManager;

class RoomShareController extends Controller {
	/** @var RoomShareService */
	private $service;

	/** @var string */
	private $userId;

	/** @var IUserManager */
	private $userManager;

	/** @var IGroupManager */
	private $groupManager;

	/** @var RoomService */
	private $roomService;

	/** @var CircleHelper */
	private $circleHelper;

	use Errors;

	public function __construct(
		$appName,
		IRequest $request,
		RoomShareService $service,
		IUserManager $userManager,
		IGroupManager $groupManager,
		RoomService $roomService,
		CircleHelper $circleHelper,
		$userId
	) {
		parent::__construct($appName, $request);
		$this->service = $service;
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->roomService = $roomService;
		$this->circleHelper = $circleHelper;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(): DataResponse {
		$roomId = $this->request->getParam('id');

		if ($roomId === null) {
			return new DataResponse([], Http::STATUS_BAD_REQUEST);
		}

		if (!$this->isUserAllowed($roomId)) {
			return new DataResponse([], Http::STATUS_FORBIDDEN);
		}

		$roomShares = $this->service->findAll($roomId);
		$shares = [];

		$circleAPI = $this->circleHelper->getCircleAPI();

		/** @var RoomShare $roomShare */
		foreach ($roomShares as $roomShare) {
			if ($roomShare->getShareType() === RoomShare::SHARE_TYPE_USER) {
				$shareWithUser = $this->userManager->get($roomShare->getShareWith());

				if ($shareWithUser === null) {
					continue;
				}

				$roomShare->setShareWithDisplayName($shareWithUser->getDisplayName());
			} elseif ($roomShare->getShareType() === RoomShare::SHARE_TYPE_CIRCLE) {
				if ($circleAPI === false) {
					continue;
				}

				$circle = $circleAPI->detailsCircle($roomShare->getShareWith());

				if ($circle === null) {
					continue;
				}

				$roomShare->setShareWithDisplayName($circle->getName());
			} elseif ($roomShare->getShareType() === RoomShare::SHARE_TYPE_GROUP) {
				$shareWithGroup = $this->groupManager->get($roomShare->getShareWith());

				if ($shareWithGroup === null) {
					continue;
				}

				$roomShare->setShareWithDisplayName($shareWithGroup->getDisplayName());
			}

			$shares[] = $roomShare;
		}

		return new DataResponse($shares);
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
			return new DataResponse([], Http::STATUS_FORBIDDEN);
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
			return new DataResponse([], Http::STATUS_FORBIDDEN);
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
	public function destroy(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			$roomShare = $this->service->find($id);

			if (!$this->isUserAllowed($roomShare->getRoomId())) {
				return new DataResponse([], Http::STATUS_FORBIDDEN);
			}

			return $this->service->delete($id);
		});
	}

	private function isUserAllowed(int $roomId): bool {
		try {
			$room = $this->roomService->find($roomId);

			return $room->getUserId() === $this->userId;
		} catch (RoomShareNotFound $e) {
			return false;
		}
	}
}
