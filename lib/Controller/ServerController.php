<?php

namespace OCA\BigBlueButton\Controller;

use OCA\BigBlueButton\BigBlueButton\API;
use OCA\BigBlueButton\Permission;
use OCP\IRequest;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\BigBlueButton\Service\RoomService;

class ServerController extends Controller {
	/** @var RoomService */
	private $service;

	/** @var API */
	private $server;

	/** @var Permission */
	private $permission;

	/** @var string */
	private $userId;

	public function __construct(
		$appName,
		IRequest $request,
		RoomService $service,
		API $server,
		Permission $permission,
		$UserId
	) {
		parent::__construct($appName, $request);

		$this->service = $service;
		$this->server = $server;
		$this->permission = $permission;
		$this->userId = $UserId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function records(string $roomUid): DataResponse {
		$room = $this->service->findByUid($roomUid);

		if ($room === null) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}

		if (!$this->permission->isAdmin($room, $this->userId)) {
			return new DataResponse([], Http::STATUS_FORBIDDEN);
		}

		$recordings = $this->server->getRecordings($room);

		return new DataResponse($recordings);
	}

	/**
	 * @NoAdminRequired
	 */
	public function deleteRecord(string $recordId): DataResponse {
		$record = $this->server->getRecording($recordId);

		$room = $this->service->findByUid($record['meetingId']);

		if ($room === null) {
			return new DataResponse(false, Http::STATUS_NOT_FOUND);
		}

		if (!$this->permission->isAdmin($room, $this->userId)) {
			return new DataResponse(false, Http::STATUS_FORBIDDEN);
		}

		$success = $this->server->deleteRecording($recordId);

		return new DataResponse($success);
	}

	public function check(?string $url, ?string $secret) {
		if ($url === null || empty($url) || $secret === null || empty($secret)) {
			return new DataResponse(false);
		}

		return new DataResponse($this->server->check($url, $secret));
	}

	public function version(?string $url) {
		if ($url === null || empty($url)) {
			return new DataResponse(false, Http::STATUS_NOT_FOUND);
		}

		try {
			$version = $this->server->getVersion($url);
		} catch (\Exception $e) {
			return new DataResponse(false, Http::STATUS_NOT_FOUND);
		}

		return new DataResponse($version);
	}
}
