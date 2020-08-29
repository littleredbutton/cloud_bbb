import './waiting.scss';

$(() => {
	let countdown = 30;

	const interval = window.setInterval(() => {
		$('#bbb-waiting-text').text(t('bbb', 'This room is not open yet. We will try it again in {sec} seconds. Please wait.', {sec: (--countdown).toString()}));

		if (countdown === 0) {
			window.location.reload();
			window.clearInterval(interval);
		}
	}, 1000);
});
