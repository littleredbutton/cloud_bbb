<?php

namespace OCA\BigBlueButton\Command;

use OCA\BigBlueButton\AvatarRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearAvatarCache extends Command {
	/**
	 * @var AvatarRepository
	 */
	private $avatarRepository;

	public function __construct(
		AvatarRepository $avatarRepository
	) {
		parent::__construct();
		$this->avatarRepository = $avatarRepository;
	}

	protected function configure() {
		$this->setName('bbb:clear-avatar-cache');
		$this->setDescription('Clear all avatars in cache');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$stats = $this->avatarRepository->clearAllRooms();

		$output->writeln("Removed " . $stats["files"] . " avatars in " . $stats["rooms"] . " rooms");

		return 0;
	}
}
