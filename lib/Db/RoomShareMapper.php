<?php

namespace OCA\BigBlueButton\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class RoomShareMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'bbb_room_shares', RoomShare::class);
	}

	/**
	 * @param int $id
	 * @return Entity|RoomShare
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): RoomShare {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('bbb_room_shares')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}

	public function findByRoomAndEntity(int $roomId, string $shareWith, int $shareType): RoomShare {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('bbb_room_shares')
			->where($qb->expr()->eq('room_id', $qb->createNamedParameter($roomId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('share_with', $qb->createNamedParameter($shareWith)))
			->andWhere($qb->expr()->eq('share_type', $qb->createNamedParameter($shareType, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	public function findAll(int $roomId): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('bbb_room_shares')
			->where($qb->expr()->eq('room_id', $qb->createNamedParameter($roomId, IQueryBuilder::PARAM_INT)));
		return $this->findEntities($qb);
	}
}
