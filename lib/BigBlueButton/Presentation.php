<?php

namespace OCA\BigBlueButton\BigBlueButton;

class Presentation {
	private $url;

	private $filename;

	public function __construct(string $url, string $filename) {
		$this->url = $url;
		$this->filename = preg_replace('/[^\x20-\x7E]+/','#', $filename);
	}

	public function getUrl(): string {
		return $this->url;
	}

	public function getFilename(): string {
		return $this->filename;
	}

	public function isValid(): bool {
		return !empty($this->url) && !empty($this->filename);
	}
}
