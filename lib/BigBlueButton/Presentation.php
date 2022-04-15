<?php

namespace OCA\BigBlueButton\BigBlueButton;

use OCP\Files\Storage\IStorage;

class Presentation {
	private $url;

	private $filename;

	private $path;

	/** @var IStorage */
	private $storage;

	public function __construct(string $path, IStorage $storage) {
		$this->storage = $storage;
		$this->path = preg_replace('/^\//', '', $path);
		$this->filename = preg_replace('/[^\x20-\x7E]+/','#', $path);
	}

	public function generateUrl(): string {
		return $this->storage->getDirectDownload($this->path);
	}

	public function getUrl(): string {
		return $this->url;
	}

	public function getFilename(): string {
		return $this->filename;
	}

	public function isValid(): bool {
		return !empty($this->filename);
	}
}
