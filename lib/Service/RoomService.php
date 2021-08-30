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
use OCP\IConfig;

class RoomService {

	/** @var RoomMapper */
	private $mapper;

	/** @var IConfig */
	private $config;

	/** @var IEventDispatcher */
	private $eventDispatcher;

	public function __construct(
		RoomMapper $mapper,
		IConfig $config,
		IEventDispatcher $eventDispatcher) {
		$this->mapper = $mapper;
		$this->config = $config;
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
	public function find(int $id): Room {
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

	public function findByUid(string $uid): ?Room {
		try {
			return $this->mapper->findByUid($uid);
		} catch (Exception $e) {
			// $this->handleException($e);
			return null;
		}
	}

	/**
	 * @return array<Room>
	 */
	public function findByUserId(string $userId): array {
		return $this->mapper->findByUserId($userId);
	}

	public function create(string $name, string $welcome, int $maxParticipants, bool $record, string $access, string $userId): \OCP\AppFramework\Db\Entity {
		$room = new Room();

		$mediaCheck = $this->config->getAppValue('bbb', 'join.mediaCheck') === 'true';

		$room->setUid(\OC::$server->getSecureRandom()->generate(16, \OCP\Security\ISecureRandom::CHAR_HUMAN_READABLE));
		$room->setName($name);
		$room->setWelcome($welcome);
		$room->setMaxParticipants(\max($maxParticipants, 0));
		$room->setAttendeePassword(\OC::$server->getSecureRandom()->generate(32, \OCP\Security\ISecureRandom::CHAR_HUMAN_READABLE));
		$room->setModeratorPassword(\OC::$server->getSecureRandom()->generate(32, \OCP\Security\ISecureRandom::CHAR_HUMAN_READABLE));
		$room->setRecord($record);
		$room->setAccess($access);
		$room->setUserId($userId);
		$room->setListenOnly(true);
		$room->setMediaCheck($mediaCheck);
		$room->setCleanLayout(false);
		$room->setJoinMuted(false);

		if ($access === Room::ACCESS_PASSWORD) {
			$room->setPassword($this->humanReadableRandom(8));
		}

		$createdRoom = $this->mapper->insert($room);

		$this->eventDispatcher->dispatch(RoomCreatedEvent::class, new RoomCreatedEvent($createdRoom));

		return $createdRoom;
	}

	/**
	 * @param null|string $moderatorToken
	 *
	 * @return \OCP\AppFramework\Db\Entity|null
	 */
	public function update(
		int $id,
		string $name,
		string $welcome,
		int $maxParticipants,
		bool $record,
		string $access,
		bool $everyoneIsModerator,
		bool $requireModerator,
		?string $moderatorToken,
		bool $listenOnly,
		bool $mediaCheck,
		bool $cleanLayout,
		bool $joinMuted) {
		try {
			$room = $this->mapper->find($id);

			if ($room->access !== $access) {
				$room->setPassword($access === Room::ACCESS_PASSWORD ? $this->humanReadableRandom(8) : null);
			}

			if ($room->moderatorToken !== $moderatorToken) {
				$room->setModeratorToken(empty($moderatorToken) ? null : $this->humanReadableRandom(16));
			}

			$room->setName($name);
			$room->setWelcome($welcome);
			$room->setMaxParticipants(\max($maxParticipants, 0));
			$room->setRecord($record);
			$room->setAccess($access);
			$room->setEveryoneIsModerator($everyoneIsModerator);
			$room->setRequireModerator($requireModerator);
			$room->setListenOnly($listenOnly);
			$room->setMediaCheck($mediaCheck);
			$room->setCleanLayout($cleanLayout);
			$room->setJoinMuted($joinMuted);

			return $this->mapper->update($room);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * @return Room|null
	 */
	public function delete(int $id) {
		try {
			$room = $this->mapper->find($id);

			$this->mapper->delete($room);

			$this->eventDispatcher->dispatch(RoomDeletedEvent::class, new RoomDeletedEvent($room));

			return $room;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * @param int $length
	 */
	private function humanReadableRandom(int $length) {
		return \OC::$server->getSecureRandom()->generate($length, \OCP\Security\ISecureRandom::CHAR_HUMAN_READABLE);
	}
}
