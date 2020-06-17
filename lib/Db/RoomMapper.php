<?php
namespace OCA\BigBlueButton\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class RoomMapper extends QBMapper
{
	public function __construct(IDBConnection $db)
	{
		parent::__construct($db, 'bbb_rooms', Room::class);
	}

	/**
	 * @param int $id
	 * @param string $userId
	 * @return Entity|Room
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): Room
	{
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->tableName)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}

	/**
	 * @param string $uid
	 * @return Entity|Room
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByUid(string $uid): Room
	{
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->tableName)
			->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid)));
		return $this->findEntity($qb);
	}

	/**
	 * @param int $userId
	 * @param array $groupIds
	 * @return array
	 */
	public function findAll(string $userId, array $groupIds): array
	{
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
						$qb->expr()->in('s.share_with', $groupIds)
					)
				)
			)
			->groupBy('r.id');
		return $this->findEntities($qb);
	}
}
