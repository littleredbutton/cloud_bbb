<?php
	/** @var $_ array */
	/** @var $l \OCP\IL10N */
	style('core', 'guest');
	script('bbb', 'join');
?>
<form method="get" action="?">
	<fieldset class="warning bbb">
		<h2><?php p($_['room']) ?></h2>
		<?php if (!isset($_['wrongdisplayname']) || !$_['wrongdisplayname']): ?>
			<p><?php p($l->t('Please enter your name!')); ?></p>
		<?php endif; ?>
		<?php if (isset($_['wrongdisplayname']) && $_['wrongdisplayname']): ?>
			<div class="warning"><?php p($l->t('The name must be at least 3 characters long.')); ?></div>
		<?php endif; ?>
		<?php if (isset($_['wrongPassword']) && $_['wrongPassword']): ?>
			<div class="warning"><?php p($l->t('You have to provide the correct password to join the meeting.')); ?></div>
		<?php endif; ?>
		<div class="bbb-container">
			<label for="displayname" class="infield"><?php p($l->t('Display name')); ?></label>
			<input type="text" name="displayname" id="displayname" class="bbb-input"
				placeholder="<?php p($l->t('Display name')); ?>" value=""
				required minlength="3" autofocus />
			<?php if (isset($_['passwordRequired']) && $_['passwordRequired']): ?>
				<label for="password" class="infield"><?php p($l->t('Password')); ?></label>
				<input type="text" name="password" id="password" class="bbb-input"
					placeholder="<?php p($l->t('Password')); ?>" value=""
					required minlength="8" />
				<button class="primary"><?php p($l->t('Join')); ?>
				<div class="submit-icon icon-confirm-white"></div></button>
			<?php else: ?>
				<input type="submit" id="displayname-submit"
					class="svg icon-confirm input-button-inline" value="" />
			<?php endif; ?>

		</div>
	</fieldset>
</form>
