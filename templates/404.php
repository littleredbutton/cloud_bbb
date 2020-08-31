<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
/** @var \OCP\Defaults $theme */
?>
<div class="body-login-container update">
	<div class="icon-big icon-search icon-white"></div>
	<h2><?php p($l->t('Room not found')); ?></h2>
	<p class="infogroup"><?php p($l->t('The room could not be found. Maybe it was deleted?')); ?></p>
	<p><a class="button primary" href="<?php p(\OC::$server->getURLGenerator()->linkTo('', 'index.php')) ?>">
		<?php p($l->t('Back to %s', [$theme->getName()])); ?>
	</a></p>
</div>