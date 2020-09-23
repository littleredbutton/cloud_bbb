<?php

namespace OCA\BigBlueButton\Settings;

use \OCP\IL10N;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Settings\ISettings;

class Personal implements ISettings {
	/** @var IConfig */
	private $config;

	/** @var IL10N */
	private $l;

	/**
	 * Admin constructor.
	 *
	 * @param IConfig $config
	 */
	public function __construct(IConfig $config, IL10N $l) {
		$this->config = $config;
		$this->l = $l;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm() {
		$warning = '';

		if (empty($this->config->getAppValue('bbb', 'api.url')) || empty($this->config->getAppValue('bbb', 'api.secret'))) {
			$warning = $this->l->t('API URL or secret not configured. Please contact your administrator.');
		}

		return new TemplateResponse('bbb', 'manager', [
			'warning' => $warning,
			'shortener' => $this->config->getAppValue('bbb', 'app.shortener', ''),
		]);
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection() {
		return 'bbb';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 * the admin section. The forms are arranged in ascending order of the
	 * priority values. It is required to return a value between 0 and 100.
	 *
	 * E.g.: 70
	 */
	public function getPriority() {
		return 50;
	}
}
