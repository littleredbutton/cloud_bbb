<?php

namespace OCA\BigBlueButton\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\BigBlueButton\Service\RoomService;

class RoomController extends Controller
{
	/** @var RoomService */
	private $service;

	/** @var string */
	private $userId;

	use Errors;

	public function __construct(
		$appName,
		IRequest $request,
		RoomService $service,
		$userId
	) {
		parent::__construct($appName, $request);
		$this->service = $service;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(): DataResponse
	{
		return new DataResponse($this->service->findAll($this->userId));
	}

	/**
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse
	{
		return $this->handleNotFound(function () use ($id) {
			return $this->service->find($id, $this->userId);
		});
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
		bool $record
	): DataResponse {
		return $this->handleNotFound(function () use ($id, $name, $welcome, $maxParticipants, $record) {
			return $this->service->update($id, $name, $welcome, $maxParticipants, $record, $this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse
	{
		return $this->handleNotFound(function () use ($id) {
			return $this->service->delete($id, $this->userId);
		});
	}
}
