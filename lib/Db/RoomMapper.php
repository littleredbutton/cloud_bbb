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
		$qb->select('r.*')
			->from($this->tableName, 'r')
			->leftJoin('r', 'bbb_room_shares', 's', $qb->expr()->eq('r.id', 's.room_id'))
			->addSelect($qb->createFunction('count(case when `s`.`permission` = 0 then 1 else null end) as shared'))
			->where($qb->expr()->eq('r.id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->groupBy('r.id');
		;

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
		$qb->select('r.*')
			->from($this->tableName, 'r')
			->leftJoin('r', 'bbb_room_shares', 's', $qb->expr()->eq('r.id', 's.room_id'))
			->addSelect($qb->createFunction('count(case when `s`.`permission` = 0 then 1 else null end) as shared'))
			->where($qb->expr()->eq('r.uid', $qb->createNamedParameter($uid)))
			->groupBy('r.id');
		;

		/** @var Room */
		return $this->findEntity($qb);
	}

	/**
	 * @return array<Room>
	 */
	public function findByUserId(string $userId): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->tableName, 'r')
			->where($qb->expr()->eq('r.user_id', $qb->createNamedParameter($userId)));

		/** @var array<Room> */
		return $this->findEntities($qb);
	}

	/**
	 * @return array<Room>
	 */
	public function findAll(string $userId, array $groupIds, array $circleIds): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('r.*')
			->from($this->tableName, 'r')
			->leftJoin('r', 'bbb_room_shares', 's', $qb->expr()->eq('r.id', 's.room_id'))
			->addSelect($qb->createFunction('count(case when `s`.`permission` = 0 then 1 else null end) as shared'))
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
					),
					$qb->expr()->andX(
						$qb->expr()->eq('s.permission', $qb->createNamedParameter(RoomShare::PERMISSION_ADMIN, IQueryBuilder::PARAM_INT)),
						$qb->expr()->eq('s.share_type', $qb->createNamedParameter(RoomShare::SHARE_TYPE_CIRCLE, IQueryBuilder::PARAM_INT)),
						$qb->expr()->in('s.share_with', $qb->createNamedParameter($circleIds, IQueryBuilder::PARAM_STR_ARRAY))
					)
				)
			)
			->groupBy('r.id');

		/** @var array<Room> */
		return $this->findEntities($qb);
	}
	
	/**
	 * @return array<Room>
	 */
	public function search(string $userId, string $query): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->tableName, 'r')
			->where($qb->expr()->eq('r.user_id', $qb->createNamedParameter($userId)))
			->andwhere($qb->expr()->ILike('name',
				$qb->createNamedParameter('%' . $this->db->escapeLikeParameter($query) . '%', IQueryBuilder::PARAM_STR),
				IQueryBuilder::PARAM_STR));

		/** @var array<Room> */
		return $this->findEntities($qb);
	}
}
