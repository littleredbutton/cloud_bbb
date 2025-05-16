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
class NoPermissionResponse extends Response {
	public function __construct() {
		parent::__construct(Http::STATUS_FORBIDDEN);

		$this->setContentSecurityPolicy(new ContentSecurityPolicy());
	}

	public function render() {
		$template = new Template('core', '403', 'guest');
		return $template->fetchPage();
	}
}
