<?php

namespace OCA\BigBlueButton\Controller;

use OCA\BigBlueButton\Db\Restriction;
use OCA\BigBlueButton\Service\RestrictionService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IGroupManager;
use OCP\IRequest;

use OCP\IUserManager;

class RestrictionController extends Controller {
	/** @var RestrictionService */
	private $service;

	/** @var string */
	private $userId;

	/** @var IUserManager */
	private $userManager;

	/** @var IGroupManager */
	private $groupManager;

	use Errors;

	public function __construct(
		$appName,
		IRequest $request,
		RestrictionService $service,
		IUserManager $userManager,
		IGroupManager $groupManager,
		$userId
	) {
		parent::__construct($appName, $request);
		$this->service = $service;
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function user(): DataResponse {
		$user = $this->userManager->get($this->userId);
		$groupIds = $this->groupManager->getUserGroupIds($user);

		return new DataResponse($this->service->findByGroupIds($groupIds));
	}

	public function index(): DataResponse {
		$restrictions = $this->service->findAll();

		if (!$this->service->existsByGroupId(Restriction::ALL_ID)) {
			$defaultRestriction = new Restriction();
			$defaultRestriction->setGroupId('');

			$restrictions[] = $defaultRestriction;
		}

		return new DataResponse($restrictions);
	}

	public function create(
		string $groupId
	): DataResponse {
		if ($this->service->existsByGroupId($groupId)) {
			return new DataResponse([], Http::STATUS_CONFLICT);
		}

		return new DataResponse($this->service->create(
			$groupId
		));
	}

	public function update(
		int $id,
		string $groupId,
		int $maxRooms,
		array $roomTypes,
		int $maxParticipants,
		bool $allowRecording
	): DataResponse {
		return $this->handleNotFound(function () use (
			$id,
			$groupId,
			$maxRooms,
			$roomTypes,
			$maxParticipants,
			$allowRecording) {
			return $this->service->update(
				$id,
				$groupId,
				$maxRooms,
				$roomTypes,
				$maxParticipants,
				$allowRecording
			);
		});
	}

	public function destroy(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			$roomShare = $this->service->find($id);

			return $this->service->delete($id);
		});
	}
}
