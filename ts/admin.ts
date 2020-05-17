import {api} from './Manager/Api';
import './Manager/App.scss';

declare const OCP: any;

$(() => {
	function generateWarningElement(message: string) {
		return $(`<div id="bbb-warning"><span class="icon icon-error-color icon-visible"></span> ${message}</div>`);
	}

	function generateSuccessElement(message: string) {
		return $(`<div id="bbb-success"><span class="icon icon-checkmark-color icon-visible"></span> ${message}</div>`);
	}

	async function checkServer(url: string, secret: string) {
		const result = await api.checkServer(url, secret);

		if (result === 'success') {
			return;
		}

		throw result;
	}

	function checkPasswordConfirmation() {
		return new Promise(resolve => {
			if (OC.PasswordConfirmation && OC.PasswordConfirmation.requiresPasswordConfirmation()) {
				OC.PasswordConfirmation.requirePasswordConfirmation(() => resolve());

				return;
			}

			resolve();
		});
	}

	async function saveSettings(url: string, secret: string) {
		url += url.endsWith('/') ? '' : '/';

		await checkServer(url, secret);
		await checkPasswordConfirmation();

		OCP.AppConfig.setValue('bbb', 'api.url', url);
		OCP.AppConfig.setValue('bbb', 'api.secret', secret);
	}

	$('#bbb-settings form').submit(function (ev) {
		ev.preventDefault();

		$('#bbb-result').empty();

		saveSettings(this['api.url'].value, this['api.secret'].value).then(() => {
			const successElement = generateSuccessElement(t('bbb', 'Settings saved'));

			$('#bbb-result').append(successElement);
		}).catch(err => {
			let message = t('bbb', 'Unexpected error occurred');

			if (err === 'invalid-url') {
				message = t('bbb', 'API url is invalid');
			} else if (err === 'invalid-secret') {
				message = t('bbb', 'API secret is invalid');
			}

			const warningElement = generateWarningElement(message);

			$('#bbb-result').append(warningElement);
		});
	});
});