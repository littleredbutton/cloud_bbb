<?php

namespace OCA\BigBlueButton\Service;

use Exception;

use OCA\BigBlueButton\Db\RoomShare;
use OCA\BigBlueButton\Db\RoomShareMapper;
use OCA\BigBlueButton\Event\RoomShareCreatedEvent;

use OCA\BigBlueButton\Event\RoomShareDeletedEvent;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\EventDispatcher\IEventDispatcher;

class RoomShareService {

	/** @var RoomShareMapper */
	private $mapper;

	/** @var IEventDispatcher */
	private $eventDispatcher;

	public function __construct(
		RoomShareMapper $mapper,
		IEventDispatcher $eventDispatcher) {
		$this->mapper = $mapper;
		$this->eventDispatcher = $eventDispatcher;
	}

	public function findAll(int $roomId): array {
		return $this->mapper->findAll($roomId);
	}

	public function findByUserId(string $userId): array {
		return $this->mapper->findByUserId($userId);
	}

	private function handleException(Exception $e): void {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new RoomShareNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find(int $id): RoomShare {
		try {
			return $this->mapper->find($id);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(int $roomId, int $shareType, string $shareWith, int $permission): RoomShare {
		try {
			$roomShare = $this->mapper->findByRoomAndEntity($roomId, $shareWith, $shareType);

			return $this->update($roomShare->getId(), $roomId, $shareType, $shareWith, $permission);
		} catch (DoesNotExistException $e) {
			$roomShare = new RoomShare();

			$roomShare->setRoomId($roomId);
			$roomShare->setShareType($shareType);
			$roomShare->setShareWith($shareWith);
			$roomShare->setPermission($permission);

			$createdRoomShare = $this->mapper->insert($roomShare);

			$this->eventDispatcher->dispatch(RoomShareCreatedEvent::class, new RoomShareCreatedEvent($createdRoomShare));

			return $createdRoomShare;
		}
	}

	public function update(int $id, int $roomId, int $shareType, string $shareWith, int $permission): RoomShare {
		try {
			$roomShare = $this->mapper->find($id);

			$roomShare->setRoomId($roomId);
			$roomShare->setShareType($shareType);
			$roomShare->setShareWith($shareWith);
			$roomShare->setPermission($permission);

			return $this->mapper->update($roomShare);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id): RoomShare {
		try {
			$roomShare = $this->mapper->find($id);
			$this->mapper->delete($roomShare);

			$this->eventDispatcher->dispatch(RoomShareDeletedEvent::class, new RoomShareDeletedEvent($roomShare));

			return $roomShare;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
