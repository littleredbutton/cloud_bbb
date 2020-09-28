<?php

namespace OCA\BigBlueButton\Service;

use Exception;

use OCA\BigBlueButton\Db\Room;
use OCA\BigBlueButton\Db\RoomMapper;
use OCA\BigBlueButton\Event\RoomCreatedEvent;

use OCA\BigBlueButton\Event\RoomDeletedEvent;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\EventDispatcher\IEventDispatcher;

class RoomService {

	/** @var RoomMapper */
	private $mapper;

	/** @var IEventDispatcher */
	private $eventDispatcher;

	public function __construct(
		RoomMapper $mapper,
		IEventDispatcher $eventDispatcher) {
		$this->mapper = $mapper;
		$this->eventDispatcher = $eventDispatcher;
	}

	public function findAll(string $userId, array $groupIds, array $circleIds): array {
		return $this->mapper->findAll($userId, $groupIds, $circleIds);
	}

	private function handleException(Exception $e): void {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new RoomNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	/**
	 * @throws RoomNotFound
	 */
	public function find($id): Room {
		try {
			return $this->mapper->find($id);

			// in order to be able to plug in different storage backends like files
		// for instance it is a good idea to turn storage related exceptions
		// into service related exceptions so controllers and service users
		// have to deal with only one type of exception
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function findByUid($uid) {
		try {
			return $this->mapper->findByUid($uid);
		} catch (Exception $e) {
			// $this->handleException($e);
			return null;
		}
	}

	public function create($name, $welcome, $maxParticipants, $record, $access, $userId) {
		$room = new Room();

		$room->setUid(\OC::$server->getSecureRandom()->generate(16, \OCP\Security\ISecureRandom::CHAR_HUMAN_READABLE));
		$room->setName($name);
		$room->setWelcome($welcome);
		$room->setMaxParticipants(\max($maxParticipants, 0));
		$room->setAttendeePassword(\OC::$server->getSecureRandom()->generate(32, \OCP\Security\ISecureRandom::CHAR_HUMAN_READABLE));
		$room->setModeratorPassword(\OC::$server->getSecureRandom()->generate(32, \OCP\Security\ISecureRandom::CHAR_HUMAN_READABLE));
		$room->setRecord($record);
		$room->setAccess($access);
		$room->setUserId($userId);

		if ($access === Room::ACCESS_PASSWORD) {
			$room->setPassword($this->humanReadableRandom(8));
		}

		$createdRoom = $this->mapper->insert($room);

		$this->eventDispatcher->dispatch(RoomCreatedEvent::class, new RoomCreatedEvent($createdRoom));

		return $createdRoom;
	}

	public function update($id, $name, $welcome, $maxParticipants, $record, $access, $everyoneIsModerator, $requireModerator) {
		try {
			$room = $this->mapper->find($id);

			if ($room->access !== $access) {
				$room->setPassword($access === Room::ACCESS_PASSWORD ? $this->humanReadableRandom(8) : null);
			}

			$room->setName($name);
			$room->setWelcome($welcome);
			$room->setMaxParticipants(\max($maxParticipants, 0));
			$room->setRecord($record);
			$room->setAccess($access);
			$room->setEveryoneIsModerator($everyoneIsModerator);
			$room->setRequireModerator($requireModerator);

			return $this->mapper->update($room);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete($id) {
		try {
			$room = $this->mapper->find($id);

			$this->mapper->delete($room);

			$this->eventDispatcher->dispatch(RoomDeletedEvent::class, new RoomDeletedEvent($room));

			return $room;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	private function humanReadableRandom($length) {
		return \OC::$server->getSecureRandom()->generate($length, \OCP\Security\ISecureRandom::CHAR_HUMAN_READABLE);
	}
}
