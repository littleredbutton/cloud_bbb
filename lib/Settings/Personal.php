<?php

namespace OCA\BigBlueButton\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class Personal implements ISettings
{
	/**
	 * @return TemplateResponse
	 */
	public function getForm()
	{
		return new TemplateResponse('bbb', 'manager');
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection()
	{
		return 'bbb';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 * the admin section. The forms are arranged in ascending order of the
	 * priority values. It is required to return a value between 0 and 100.
	 *
	 * E.g.: 70
	 */
	public function getPriority()
	{
		return 50;
	}
}
