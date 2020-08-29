<?php
	/** @var $_ array */
	/** @var $l \OCP\IL10N */
	style('core', 'guest');
	script('bbb', 'waiting');
?>

<div class="update bbb">
	<h2><?php p($_['room']); ?></h2>
	<h3><?php p($l->t('Hello %s', $_['name'])); ?></h3>

	<p id="bbb-waiting-text"></p>
</div>
