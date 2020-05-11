<?php
	/** @var $_ array */
	/** @var $l \OCP\IL10N */
	style('core', 'guest');
	style('core', 'publicshareauth');
?>
<form method="get" action="?">
	<fieldset class="warning">
		<h2><?php p($_['room']) ?></h2>
		<?php if (!isset($_['wrongdisplayname']) || !$_['wrongdisplayname']): ?>
			<p><?php p($l->t('Please enter your name!')); ?></p>
		<?php endif; ?>
		<?php if (isset($_['wrongdisplayname']) && $_['wrongdisplayname']): ?>
			<div class="warning"><?php p($l->t('The name must be at least 3 characters long.')); ?></div>
		<?php endif; ?>
		<p>
			<label for="password" class="infield"><?php p($l->t('displayname')); ?></label>
			<input type="displayname" name="displayname" id="password"
				placeholder="<?php p($l->t('displayname')); ?>" value=""
				required minlength="3" autofocus />
			<input type="submit" id="displayname-submit"
				class="svg icon-confirm input-button-inline" value="" />
		</p>
	</fieldset>
</form>
