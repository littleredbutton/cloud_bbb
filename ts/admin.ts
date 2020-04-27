declare const OCP: any;

$(() => {
	$('#bbb-settings form').submit(function (ev) {
		ev.preventDefault();

		OCP.AppConfig.setValue('bbb', 'api.url', this['api.url'].value);
		OCP.AppConfig.setValue('bbb', 'api.secret', this['api.secret'].value);
	});
});