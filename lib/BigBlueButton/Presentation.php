<?php

namespace OCA\BigBlueButton\BigBlueButton;

use OCP\Files\IRootFolder;
use OCP\Files\File;
use OCP\Files\Storage\IStorage;

class Presentation
{
	private $url;

	/** @var File*/
	private $file;

	/** @var IRootFolder */
	private $userFolder;

	/** @var IStorage */
	private $storage;

	public function __construct(string $path, string $userID, IRootFolder $iRootFolder)
	{
		$userFolder = $iRootFolder->getUserFolder($userID);
		$this->file = $userFolder->get($path);
		$this->storage = $this->file->getStorage();
	}

	public function generateUrl(): string
	{
		$filePath = $this->file->getInternalPath();
		[$url] = $this->storage->getDirectDownload($filePath);
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
