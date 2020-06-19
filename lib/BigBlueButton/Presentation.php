<?php

namespace OCA\BigBlueButton\BigBlueButton;

class Presentation {
	private $url;

	private $filename;

	public function __construct(string $url, string $filename) {
		$this->url = $url;
		$this->filename = $filename;
	}

	public function getUrl() {
		return $this->url;
	}

	public function getFilename() {
		return $this->filename;
	}

	public function isValid() {
		return !empty($this->url) && !empty($this->filename);
	}
}
