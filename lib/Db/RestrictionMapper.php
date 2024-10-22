<?php

namespace OCA\BigBlueButton\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class RestrictionMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'bbb_restrictions', Restriction::class);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): Restriction {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('r.*', 'g.displayname as groupName')
			->from($this->tableName, 'r')
			->leftJoin('r', 'groups', 'g', $qb->expr()->eq('r.group_id', 'g.gid'))
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findByGroupId(string $groupId): Restriction {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('r.*', 'g.displayname as groupName')
			->from($this->tableName, 'r')
			->leftJoin('r', 'groups', 'g', $qb->expr()->eq('r.group_id', 'g.gid'))
			->where($qb->expr()->eq('group_id', $qb->createNamedParameter($groupId)));

		return $this->findEntity($qb);
	}

	/**
	 * @return array<Restriction>
	 */
	public function findByGroupIds(array $groupIds): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('r.*', 'g.displayname as groupName')
			->from($this->tableName, 'r')
			->leftJoin('r', 'groups', 'g', $qb->expr()->eq('r.group_id', 'g.gid'))
			->where($qb->expr()->in('group_id', $qb->createNamedParameter($groupIds, IQueryBuilder::PARAM_STR_ARRAY)));

		/** @var array<Restriction> */
		return $this->findEntities($qb);
	}

	/**
	 * @return array<Restriction>
	 */
	public function findAll(): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('r.*', 'g.displayname as groupName')
			->from($this->tableName, 'r')
			->leftJoin('r', 'groups', 'g', $qb->expr()->eq('r.group_id', 'g.gid'));

		/** @var array<Restriction> */
		return $this->findEntities($qb);
	}
}
