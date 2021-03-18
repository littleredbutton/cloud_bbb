import { api } from './Common/Api';
import './Manager/App.scss';

declare const OCP: any;

$(() => {
	function generateWarningElement(message: string) {
		return $(`<div id="bbb-warning"><span class="icon icon-error-color icon-visible"></span> ${message}</div>`);
	}

	function generateSuccessElement(message: string) {
		return $(`<div id="bbb-success"><span class="icon icon-checkmark icon-visible"></span> ${message}</div>`);
	}

	async function checkServer(url: string, secret: string) {
		const result = await api.checkServer(url, secret);

		if (result === 'success') {
			return;
		}

		throw result;
	}

	function checkPasswordConfirmation() {
		return new Promise<void>(resolve => {
			if (OC.PasswordConfirmation && OC.PasswordConfirmation.requiresPasswordConfirmation()) {
				OC.PasswordConfirmation.requirePasswordConfirmation(() => resolve());

				return;
			}

			resolve();
		});
	}

	async function saveApiSettings(url: string, secret: string) {
		url += url.endsWith('/') ? '' : '/';

		await checkServer(url, secret);
		await checkPasswordConfirmation();

		OCP.AppConfig.setValue('bbb', 'api.url', url);
		OCP.AppConfig.setValue('bbb', 'api.secret', secret);
	}

	$('#bbb-api').on('submit', function (ev) {
		ev.preventDefault();

		const resultElement = $(this).find('.bbb-result').empty();

		saveApiSettings(this['api.url'].value, this['api.secret'].value).then(() => {
			const successElement = generateSuccessElement(t('bbb', 'Settings saved'));

			setTimeout(() => {
				resultElement.empty();
			}, 3000);

			resultElement.append(successElement);
		}).catch(err => {
			let message = t('bbb', 'Unexpected error occurred');

			if (err === 'invalid-url') {
				message = t('bbb', 'API URL is invalid');
			} else if (err === 'invalid-secret') {
				message = t('bbb', 'API secret is invalid');
			}

			const warningElement = generateWarningElement(message);

			resultElement.append(warningElement);
		});
	});

	function generateExampleShortener(shortener: string) {
		return shortener.replace(/</g, '&lt;').replace(/\{user\}/g, `<strong>${OC.currentUser}</strong>`).replace(/\{token\}/g, '<strong>your_room_id</strong>');
	}

	async function saveShortSettings(shortener: string) {
		await checkPasswordConfirmation();

		if (shortener && shortener.indexOf('https://') !== 0) {
			throw 'https';
		}

		if (shortener && shortener.indexOf('{token}') < 0) {
			throw 'token';
		}

		OCP.AppConfig.setValue('bbb', 'app.shortener', shortener);
	}

	$('#bbb-shortener').on('submit', function (ev) {
		ev.preventDefault();

		const resultElement = $(this).find('.bbb-result').empty();

		saveShortSettings(this['app.shortener'].value).then(() => {
			const successElement = generateSuccessElement(t('bbb', 'Settings saved'));

			setTimeout(() => {
				resultElement.empty();
			}, 3000);

			resultElement.append(successElement);
		}).catch(err => {
			let message = t('bbb', 'Unexpected error occurred');

			if (err === 'https') {
				message = t('bbb', 'URL has to start with HTTPS');
			} else if (err === 'token') {
				message = t('bbb', 'URL has to contain the {token} placeholder');
			}

			const warningElement = generateWarningElement(message);

			console.warn('Could not save app settings', err);

			resultElement.append(warningElement);
		});
	});

	async function saveAppSettings(name: string) {
		await checkPasswordConfirmation();

		console.log(`DAMN THIS ${name}`); //TODO REMOVE
		OCP.AppConfig.setValue('bbb', 'app.navigation.name', name);
	}

	$('#bbb-nav-name').on('submit', function (ev) {
		ev.preventDefault();

		const resultElement = $(this).find('.bbb-result').empty();

		saveAppSettings(this['app.navigation.name'].value).then(() => {
			const successElement = generateSuccessElement(t('bbb', 'Settings saved'));

			setTimeout(() => {
				resultElement.empty();
			}, 3000);

			resultElement.append(successElement);
		}).catch(err => {
			let message = t('bbb', 'Unexpected error occurred');

			const warningElement = generateWarningElement(message);

			console.warn('Could not save app settings', err);

			resultElement.append(warningElement);
		});
	});


	$<HTMLInputElement>('#bbb-shortener [name="app.shortener"]').on('keyup', (ev) => {
		ev.preventDefault();

		const { value } = ev.target;

		if (!value || value.indexOf('https://') !== 0 || value.indexOf('{token}') < 0) {
			$('#bbb-shortener-example').text(t('bbb', 'URL has to start with https:// and contain {token}. Additionally the {user} placeholder can be used.'));

			return;
		}

		const target = window.location.origin + OC.generateUrl('apps/bbb/b/$1');
		const url = (new URL(value));
		const rewritePath = '^' + url.pathname.replace(/^\//, '').replace(/%7Buser%7D/g, '.+').replace(/%7Btoken%7D/g, '(.+)');

		$('#bbb-shortener-example').html(`<p>${generateExampleShortener(value)}</p>
		<details>
		<summary>${t('bbb', 'Example configuration for Apache and Nginx')}</summary>
		<pre>#Apache with mod_rewrite
ServerName    ${url.hostname}
RewriteEngine on
RewriteRule   "${rewritePath}"  "${target}"  [R=307,L]

#Nginx config
server_name ${url.hostname};
rewrite ${rewritePath} ${target} last;
return 307;</pre></details>
		`);
	});
	$('#bbb-shortener [name="app.shortener"]').trigger('keyup');

	$<HTMLInputElement>('#bbb-settings [name="app.navigation"]').on('change', (ev) => {
		ev.preventDefault();

		console.log('checkbox changed to', ev.target.checked);

		OCP.AppConfig.setValue('bbb', 'app.navigation', ev.target.checked);
	});
});