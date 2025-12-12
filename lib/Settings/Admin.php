<?php

namespace OCA\BigBlueButton\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IAppConfig;
use OCP\Settings\ISettings;

class Admin implements ISettings {

	/**
	 * Admin constructor.
	 *
	 * @param IAppConfig $config
	 */
	public function __construct(private IAppConfig $config) {
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm() {
		$parameters = [
			'api.url' => $this->config->getValueString('bbb', 'api.url'),
			'api.secret' => $this->config->getValueString('bbb', 'api.secret'),
			'app.navigation' => $this->config->getValueBool('bbb', 'app.navigation') ? 'checked' : '',
			'join.theme' => $this->config->getValueBool('bbb', 'join.theme') ? 'checked' : '',
			'app.shortener' => $this->config->getValueString('bbb', 'app.shortener'),
			'join.mediaCheck' => $this->config->getValueBool('bbb', 'join.mediaCheck', true) ? 'checked' : '',
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
