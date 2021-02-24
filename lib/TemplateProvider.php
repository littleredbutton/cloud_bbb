<?php

namespace OCA\BigBlueButton;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;

class TemplateProvider {
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
	public function getManager(): TemplateResponse {
		$warning = '';

		if (empty($this->config->getAppValue('bbb', 'api.url')) || empty($this->config->getAppValue('bbb', 'api.secret'))) {
			$warning = $this->l->t('API URL or secret not configured. Please contact your administrator.');
		}

		return new TemplateResponse('bbb', 'manager', [
			'warning' => $warning,
			'shortener' => $this->config->getAppValue('bbb', 'app.shortener', ''),
		]);
	}
}
