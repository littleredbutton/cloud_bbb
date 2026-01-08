<?php

namespace OCA\BigBlueButton\Controller;

use OCA\BigBlueButton\TemplateProvider;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class PageController extends Controller {
	/** @var TemplateProvider */
	private $templateProvider;

	public function __construct(string $appName, IRequest $request, TemplateProvider $templateProvider) {
		parent::__construct($appName, $request);

		$this->templateProvider = $templateProvider;
	}

	/**
	 * @return TemplateResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(): TemplateResponse {
		return $this->templateProvider->getManager();
	}
}
