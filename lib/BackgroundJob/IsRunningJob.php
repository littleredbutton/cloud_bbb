<?php

namespace OCA\BigBlueButton\BackgroundJob;

use OCA\BigBlueButton\BigBlueButton\API;
use OCA\BigBlueButton\Service\RoomNotFound;
use OCA\BigBlueButton\Service\RoomService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\IJobList;
use OCP\BackgroundJob\TimedJob;

class IsRunningJob extends TimedJob {
	/** @var IJobList */
	private $jobList;

	/** @var RoomService */
	private $service;

	/** @var API */
	private $api;

	public function __construct(ITimeFactory $time, IJobList $jobList, RoomService $service, API $api) {
		parent::__construct($time);

		$this->jobList = $jobList;
		$this->service = $service;
		$this->api = $api;

		$this->setInterval(15 * 60);
	}

	protected function run($argument) {
		try {
			$room = $this->service->find($argument['id']);
		} catch (RoomNotFound $e) {
			$this->jobList->remove($this, $argument);
			return;
		}

		if (!$room->running) {
			$this->jobList->remove($this, $argument);
			return;
		}

		$isRunning = $this->api->isRunning($room);

		if (!$isRunning) {
			$this->service->updateRunning($room->id, $isRunning);

			$this->jobList->remove($this, $argument);
		}
	}
}
