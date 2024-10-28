<?php

namespace OCA\BigBlueButton;

use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\Response;
use OCP\Template;

/**
 * @template-extends Response<int, array<string, mixed>>
 *
 * (NC < 28)
 * @psalm-suppress TooManyTemplateParams
 */
class NotFoundResponse extends Response {
	public function __construct() {
		parent::__construct();

		$this->setContentSecurityPolicy(new ContentSecurityPolicy());
		$this->setStatus(404);
	}

	public function render() {
		$template = new Template('bbb', '404', 'guest');
		return $template->fetchPage();
	}
}
