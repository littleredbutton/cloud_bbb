<?php

namespace OCA\BigBlueButton;

use OCP\Template;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\ContentSecurityPolicy;

class NoPermissionResponse extends Response {
	public function __construct() {
		parent::__construct();

		$this->setContentSecurityPolicy(new ContentSecurityPolicy());
		$this->setStatus(404);
	}

	public function render() {
		$template = new Template('core', '403', 'guest');
		return $template->fetchPage();
	}
}
