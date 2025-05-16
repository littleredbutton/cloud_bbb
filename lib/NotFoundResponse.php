<?php

namespace OCA\BigBlueButton;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\Response;
use OCP\Template;

/**
 * @template-extends Response<Http::STATUS_*, array<string, mixed>>
 *
 * (NC < 28)
 * @psalm-suppress TooManyTemplateParams
 */
class NotFoundResponse extends Response {
	public function __construct() {
		parent::__construct(Http::STATUS_NOT_FOUND);

		$this->setContentSecurityPolicy(new ContentSecurityPolicy());
	}

	public function render() {
		$template = new Template('bbb', '404', 'guest');
		return $template->fetchPage();
	}
}
