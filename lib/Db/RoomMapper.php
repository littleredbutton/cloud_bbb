<?php

namespace OCA\BigBlueButton\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class RoomMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'bbb_rooms', Room::class);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): Room {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->tableName)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		/** @var Room */
		return $this->findEntity($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByUid(string $uid): Room {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->tableName)
			->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid)));

		/** @var Room */
		return $this->findEntity($qb);
	}

	/**
	 * @return array<Room>
	 */
	public function findAll(string $userId, array $groupIds): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('r.*')
			->from($this->tableName, 'r')
			->leftJoin('r', 'bbb_room_shares', 's', $qb->expr()->eq('r.id', 's.room_id'))
			->where(
				$qb->expr()->orX(
					$qb->expr()->eq('r.user_id', $qb->createNamedParameter($userId)),
					$qb->expr()->andX(
						$qb->expr()->eq('s.permission', $qb->createNamedParameter(RoomShare::PERMISSION_ADMIN, IQueryBuilder::PARAM_INT)),
						$qb->expr()->eq('s.share_type', $qb->createNamedParameter(RoomShare::SHARE_TYPE_USER, IQueryBuilder::PARAM_INT)),
						$qb->expr()->eq('s.share_with', $qb->createNamedParameter($userId))
					),
					$qb->expr()->andX(
						$qb->expr()->eq('s.permission', $qb->createNamedParameter(RoomShare::PERMISSION_ADMIN, IQueryBuilder::PARAM_INT)),
						$qb->expr()->eq('s.share_type', $qb->createNamedParameter(RoomShare::SHARE_TYPE_GROUP, IQueryBuilder::PARAM_INT)),
						$qb->expr()->in('s.share_with', $qb->createNamedParameter($groupIds, IQueryBuilder::PARAM_STR_ARRAY))
					)
				)
			)
			->groupBy('r.id');

		/** @var array<Room> */
		return $this->findEntities($qb);
	}
}
