<?php
namespace OCA\BigBlueButton\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\BigBlueButton\Db\RoomShare;
use OCA\BigBlueButton\Db\RoomShareMapper;

class RoomShareService
{

	/** @var RoomShareMapper */
	private $mapper;

	public function __construct(RoomShareMapper $mapper)
	{
		$this->mapper = $mapper;
	}

	public function findAll(int $roomId): array
	{
		return $this->mapper->findAll($roomId);
	}

	private function handleException(Exception $e): void
	{
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new RoomShareNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find($id): RoomShare
	{
		try {
			return $this->mapper->find($id);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(int $roomId, int $shareType, string $shareWith, int $permission): RoomShare
	{
		$roomShare = new RoomShare();

		$roomShare->setRoomId($roomId);
		$roomShare->setShareType($shareType);
		$roomShare->setShareWith($shareWith);
		$roomShare->setPermission($permission);

		return $this->mapper->insert($roomShare);
	}

	public function update(int $id, int $roomId, int $shareType, string $shareWith, int $permission): RoomShare
	{
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

	public function delete(int $id): RoomShare
	{
		try {
			$roomShare = $this->mapper->find($id);
			$this->mapper->delete($roomShare);

			return $roomShare;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
