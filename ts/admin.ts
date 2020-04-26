declare const OCP: any;

$(() => {
    $('#bigbluebutton-settings form').submit(function (ev) {
        ev.preventDefault();

        OCP.AppConfig.setValue('bigbluebutton', 'api.url', this['api.url'].value);
        OCP.AppConfig.setValue('bigbluebutton', 'api.secret', this['api.secret'].value);
    })
});