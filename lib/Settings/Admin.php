<?php

namespace OCA\BigBlueButton\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Settings\ISettings;

class Admin implements ISettings {

	/** @var IConfig */
	private $config;

	/**
	 * Admin constructor.
	 *
	 * @param IConfig $config
	 */
	public function __construct(IConfig $config) {
		$this->config = $config;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm() {
		$parameters = [
			'api.url'    => $this->config->getAppValue('bbb', 'api.url'),
			'api.secret' => $this->config->getAppValue('bbb', 'api.secret'),
		];

		return new TemplateResponse('bbb', 'admin', $parameters);
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection() {
		return 'additional';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 * the admin section. The forms are arranged in ascending order of the
	 * priority values. It is required to return a value between 0 and 100.
	 */
	public function getPriority() {
		return 50;
	}
}
