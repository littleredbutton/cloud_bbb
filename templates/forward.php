<?php
	/** @var $_ array */
	/** @var $l \OCP\IL10N */
	style('core', 'guest');
	?>

	<div class="update bbb guest-box">
		<h2><?php p($_['room']) ?></h2>
		<p><?php p($l->t('You will be forwarded to the room in the next few seconds.')); ?><br />
			<br />
			<a href="<?php print_unescaped($_['url']); ?>"><?php p($l->t('Let\'s go!')); ?></a></p>
	</div>
