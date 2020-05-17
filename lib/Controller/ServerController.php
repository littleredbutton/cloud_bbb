<?php

namespace OCA\BigBlueButton\Controller;

use OCA\BigBlueButton\BigBlueButton\API;
use OCP\IRequest;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\BigBlueButton\Service\RoomService;

class ServerController extends Controller
{
	/** @var RoomService */
	private $service;

	/** @var API */
	private $server;

	/** @var string */
	private $userId;

	public function __construct(
		$appName,
		IRequest $request,
		RoomService $service,
		API $server,
		$UserId
	) {
		parent::__construct($appName, $request);

		$this->service = $service;
		$this->server = $server;
		$this->userId = $UserId;
	}

	/**
	* @NoAdminRequired
	*/
	public function records(string $roomUid): DataResponse
	{
		$room = $this->service->findByUid($roomUid);

		if ($room === null) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}

		if ($room->userId !== $this->userId) {
			return new DataResponse([], Http::STATUS_FORBIDDEN);
		}

		$recordings = $this->server->getRecordings($room);

		return new DataResponse($recordings);
	}

	/**
	* @NoAdminRequired
	*/
	public function deleteRecord(string $recordId): DataResponse
	{
		$record = $this->server->getRecording($recordId);

		$room = $this->service->findByUid($record['metas']['meetingId']);

		if ($room === null) {
			return new DataResponse(false, Http::STATUS_NOT_FOUND);
		}

		if ($room->userId !== $this->userId) {
			return new DataResponse(false, Http::STATUS_FORBIDDEN);
		}

		$success = $this->server->deleteRecording($recordId);

		return new DataResponse($success);
	}
}
