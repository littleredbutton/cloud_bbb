<?php

namespace OCA\BigBlueButton\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IRequest;

class PageController extends Controller {
	/** @var IConfig */
	private $config;

	public function __construct(string $appName, IRequest $request, IConfig $config) {
		parent::__construct($appName, $request);

		$this->config = $config;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		return new TemplateResponse($this->appName, 'manager', [
			'shortener' => $this->config->getAppValue('bbb', 'app.shortener', ''),
		]);
	}
}
