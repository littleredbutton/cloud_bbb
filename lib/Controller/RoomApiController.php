<?php

namespace OCA\BigBlueButton\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;

use OCA\BigBlueButton\Service\RoomService;

class RoomApiController extends ApiController
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
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function index(): DataResponse
	{
		return new DataResponse($this->service->findAll($this->userId));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse
	{
		return $this->handleNotFound(function () use ($id) {
			return $this->service->find($id, $this->userId);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
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
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function update(
		int $id,
		string $name,
		string $welcome,
		int $maxParticipants,
		bool $record,
		string $access
	): DataResponse {
		return $this->handleNotFound(function () use ($id, $name, $welcome, $maxParticipants, $record, $access) {
			return $this->service->update($id, $name, $welcome, $maxParticipants, $record, $access, $this->userId);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse
	{
		return $this->handleNotFound(function () use ($id) {
			return $this->service->delete($id, $this->userId);
		});
	}
}
