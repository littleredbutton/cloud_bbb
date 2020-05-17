<?php
/** @var $l \OCP\IL10N */
/** @var $_ array */

script('bbb', 'admin');
?>

<div id="bbb-settings" class="section">
        <h2>BigBlueButton</h2>

        <p><?php p($l->t('Get your API url and secret by executing "sudo bbb-conf --secret" on your BigBlueButton server.')); ?></p>

        <form>
            <input type="url" name="api.url" value="<?php p($_['api.url']); ?>" placeholder="<?php p($l->t('API url')); ?>" pattern="https://.*" required />
            <input type="password" name="api.secret" value="<?php p($_['api.secret']); ?>" placeholder="<?php p($l->t('API secret')); ?>" autocomplete="new-password" required />
            <input type="submit" value="<?php p($l->t('Save')); ?>" />

            <div id="bbb-result"></div>
        </form>
</div>