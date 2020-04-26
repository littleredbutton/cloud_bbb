<?php
namespace OCA\BigBlueButton\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\Db\RoomMapper;

class RoomService
{

	/** @var RoomMapper */
	private $mapper;

	public function __construct(RoomMapper $mapper)
	{
		$this->mapper = $mapper;
	}

	public function findAll(string $userId): array
	{
		return $this->mapper->findAll($userId);
	}

	private function handleException(Exception $e): void
	{
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new RoomNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	public function find($id, $userId)
	{
		try {
			return $this->mapper->find($id, $userId);

			// in order to be able to plug in different storage backends like files
		// for instance it is a good idea to turn storage related exceptions
		// into service related exceptions so controllers and service users
		// have to deal with only one type of exception
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function findByUid($uid)
	{
		try {
			return $this->mapper->findByUid($uid);
		} catch (Exception $e) {
			// $this->handleException($e);
			return null;
		}
	}

	public function create($name, $welcome, $maxParticipants, $record, $userId)
	{
		$room = new Room();

		$room->setUid(\OC::$server->getSecureRandom()->generate(16, \OCP\Security\ISecureRandom::CHAR_HUMAN_READABLE));
		$room->setName($name);
		$room->setWelcome($welcome);
		$room->setMaxParticipants($maxParticipants);
		$room->setAttendeePassword(\OC::$server->getSecureRandom()->generate(32, \OCP\Security\ISecureRandom::CHAR_HUMAN_READABLE));
		$room->setModeratorPassword(\OC::$server->getSecureRandom()->generate(32, \OCP\Security\ISecureRandom::CHAR_HUMAN_READABLE));
		$room->setRecord($record);
		$room->setUserId($userId);

		return $this->mapper->insert($room);
	}

	public function update($id, $name, $welcome, $maxParticipants, $record, $userId)
	{
		try {
			$room = $this->mapper->find($id, $userId);

			$room->setName($name);
			$room->setWelcome($welcome);
			$room->setMaxParticipants($maxParticipants);
			$room->setRecord($record);
			$room->setUserId($userId);

			return $this->mapper->update($room);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete($id, $userId)
	{
		try {
			$room = $this->mapper->find($id, $userId);
			$this->mapper->delete($room);
			return $room;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
