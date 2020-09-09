import './waiting.scss';

$(() => {
	let countdown = 30;

	const interval = window.setInterval(() => {
		$('#bbb-waiting-text').text(
			n(
				'bbb',
				'This room is not open yet. We will try it again in %n second. Please wait.',
				'This room is not open yet. We will try it again in %n seconds. Please wait.',
				--countdown
			)
		);

		if (countdown === 0) {
			window.location.reload();
			window.clearInterval(interval);
		}
	}, 1000);
});
