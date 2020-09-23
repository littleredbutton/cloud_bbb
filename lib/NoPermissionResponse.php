<?php

namespace OCA\BigBlueButton;

use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\Response;
use OCP\Template;

class NoPermissionResponse extends Response {
	public function __construct() {
		parent::__construct();

		$this->setContentSecurityPolicy(new ContentSecurityPolicy());
		$this->setStatus(403);
	}

	public function render() {
		$template = new Template('core', '403', 'guest');
		return $template->fetchPage();
	}
}
