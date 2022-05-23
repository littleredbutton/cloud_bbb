<?php

namespace OCA\BigBlueButton\BigBlueButton;

use OCA\DAV\Db\Direct;
use OCA\DAV\Db\DirectMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\IRootFolder;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\Storage\IStorage;
use OCP\IURLGenerator;
use OCP\Security\ISecureRandom;

class Presentation
{
	private $url;
	private $userId;

	/** @var File*/
	private $file;

	/** @var Folder */
	private $userFolder;

	/** @var DirectMapper */
	private $mapper;

	/** @var ISecureRandom */
	private $random;

	/** @var ITimeFactory */
	private $timeFactory;

	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct(
		string $path,
		string $userId,
		IRootFolder $iRootFolder,
		DirectMapper $mapper,
		ISecureRandom $random,
		ITimeFactory $timeFactory,
		IURLGenerator $urlGenerator
	) {
		$this->userFolder = $iRootFolder->getUserFolder($userId);
		$this->file = $this->userFolder->get($path);
		$this->mapper = $mapper;
		$this->random = $random;
		$this->timeFactory = $timeFactory;
		$this->userId = $userId;
		$this->urlGenerator = $urlGenerator;
	}

	public function generateUrl()
	{
		$direct = new Direct();
		$direct->setUserId($this->userId);
		$direct->setFileId($this->file->getId());

		$token = $this->random->generate(60, ISecureRandom::CHAR_ALPHANUMERIC);
		$direct->setToken($token);
		$direct->setExpiration($this->timeFactory->getTime() + (60 * 60 * 8));

		$this->mapper->insert($direct);

		$url = $this->urlGenerator->getAbsoluteURL('remote.php/direct/' . $token);

		return $url;
	}

	public function getUrl(): string
	{
		return $this->url;
	}

	public function getFilename(): string
	{
		return $this->file->getName();
	}

	public function isValid(): bool
	{
		return !empty($this->file->getContent());
	}
}
