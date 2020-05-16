<?php
/** @var $l \OCP\IL10N */
/** @var $_ array */

script('bbb', 'admin');
?>

<div id="bbb-settings" class="section">
        <h2>BigBlueButton</h2>

        <form>
            <input type="url" name="api.url" value="<?php p($_['api.url']); ?>" placeholder="<?php p($l->t('API url')); ?>" pattern="https://.*" />
            <input type="password" name="api.secret" value="<?php p($_['api.secret']); ?>" placeholder="<?php p($l->t('API secret')); ?>" />
            <input type="submit" value="<?php p($l->t('Save')); ?>" />
        </form>
</div>