<?php
/** @var $l \OCP\IL10N */
/** @var $_ array */

script('bbb', 'admin');
?>

<div id="bbb-settings" class="section">
        <h2>BigBlueButton</h2>

        <p><?php p($l->t('Get your API URL and secret by executing "sudo bbb-conf --secret" on your BigBlueButton server.')); ?></p>

        <form>
            <input type="url" name="api.url" value="<?php p($_['api.url']); ?>" placeholder="<?php p($l->t('API URL')); ?>" pattern="https://.*" required />
            <input type="password" name="api.secret" value="<?php p($_['api.secret']); ?>" placeholder="<?php p($l->t('API secret')); ?>" autocomplete="new-password" required />
            <input type="submit" value="<?php p($l->t('Save')); ?>" />

            <div id="bbb-result"></div>
        </form>

        <p>
            <input type="checkbox" name="app.navigation" id="bbb-app-navigation" class="checkbox" value="1" <?php p($_['app.navigation']); ?>>
            <label for="bbb-app-navigation"><?php p($l->t('Show room manager in app navigation instead of settings page.')); ?></label>
        </p>
</div>
