<?php

namespace OCA\BigBlueButton\Service;

use Exception;

use OCA\BigBlueButton\Db\Restriction;
use OCA\BigBlueButton\Db\RestrictionMapper;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IGroupManager;

class RestrictionService {
	/** @var RestrictionMapper */
	private $mapper;

	/** @var IGroupManager */
	private $groupManager;

	public function __construct(RestrictionMapper $mapper, IGroupManager $groupManager) {
		$this->mapper = $mapper;
		$this->groupManager = $groupManager;
	}

	public function findAll(): array {
		return $this->mapper->findAll();
	}

	public function existsByGroupId(string $groupId): bool {
		try {
			$this->mapper->findByGroupId($groupId);

			return true;
		} catch (DoesNotExistException $e) {
			return false;
		}
	}

	public function findByGroupIds(array $groupIds): Restriction {
		$restrictions = $this->mapper->findByGroupIds($groupIds);
		try {
			$restriction = $this->mapper->findByGroupId(Restriction::ALL_ID);
		} catch (DoesNotExistException $e) {
			$restriction = new Restriction();
		}

		$roomTypes = \json_decode($restriction->getRoomTypes());

		foreach ($restrictions as $r) {
			if ($restriction->getMaxRooms() > -1 && ($r->getMaxRooms() === -1 || $restriction->getMaxRooms() < $r->getMaxRooms())) {
				$restriction->setMaxRooms($r->getMaxRooms());
			}

			$rRoomTypes = \json_decode($r->getRoomTypes());
			$roomTypes = array_intersect($roomTypes, $rRoomTypes);

			if ($restriction->getMaxParticipants() > -1 && ($r->getMaxParticipants() === -1 || $restriction->getMaxParticipants() < $r->getMaxParticipants())) {
				$restriction->setMaxParticipants($r->getMaxParticipants());
			}

			if (!$restriction->getAllowRecording() && $r->getAllowRecording()) {
				$restriction->setAllowRecording($r->getAllowRecording());
			}
		}

		$restriction->setId(0);
		$restriction->setGroupId('__cumulative');
		$restriction->setRoomTypes(\json_encode(\array_values($roomTypes)));

		return $restriction;
	}

	public function find(int $id): Restriction {
		try {
			return $this->mapper->find($id);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(string $groupId): Restriction {
		$restriction = new Restriction();

		$restriction->setGroupId($groupId);
		$group = $this->groupManager->get($groupId);
		if ($group) {
			$restriction->setGroupName($group->getDisplayName());
		}

		return $this->mapper->insert($restriction);
	}

	public function update(int $id, string $groupId, int $maxRooms, array $roomTypes, int $maxParticipants, bool $allowRecording): Restriction {
		try {
			$restriction = $this->mapper->find($id);

			$restriction->setGroupId($groupId);
			$restriction->setMaxRooms(\max($maxRooms, -1));
			$restriction->setRoomTypes(\json_encode($roomTypes));
			$restriction->setMaxParticipants(\max($maxParticipants, -1));
			$restriction->setAllowRecording($allowRecording);

			return $this->mapper->update($restriction);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id): Restriction {
		try {
			$restriction = $this->mapper->find($id);
			$this->mapper->delete($restriction);

			return $restriction;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	private function handleException(Exception $e): void {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new RestrictionNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}
}
