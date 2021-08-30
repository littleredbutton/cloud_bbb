<?php
/** @var $l \OCP\IL10N */
/** @var $_ array */

script('bbb', 'admin');
script('bbb', 'restrictions');
?>

<div id="bbb-settings" class="section">
        <h2>BigBlueButton</h2>

        <p><?php p($l->t('Get your API URL and secret by executing "sudo bbb-conf --secret" on your BigBlueButton server.')); ?></p>

        <form id="bbb-api">
            <input type="url" name="api.url" value="<?php p($_['api.url']); ?>" placeholder="<?php p($l->t('API URL')); ?>" pattern="https://.*" required />
            <input type="password" name="api.secret" value="<?php p($_['api.secret']); ?>" placeholder="<?php p($l->t('API secret')); ?>" autocomplete="new-password" required />
            <input type="submit" value="<?php p($l->t('Save')); ?>" />

            <div class="bbb-result"></div>
        </form>

        <p>
            <input type="checkbox" name="app.navigation" id="bbb-app-navigation" class="checkbox bbb-setting" value="1" <?php p($_['app.navigation']); ?> />
            <label for="bbb-app-navigation"><?php p($l->t('Show room manager in app navigation instead of settings page.')); ?></label>
        </p>

        <p>
            <input type="checkbox" name="join.theme" id="bbb-join-theme" class="checkbox bbb-setting" value="1" <?php p($_['join.theme']); ?> />
            <label for="bbb-join-theme"><?php p($l->t('Use Nextcloud theme in BigBlueButton.')); ?></label>
        </p>

        <h3><?php p($l->t('Default Room Settings')); ?></h3>
        <p><?php p($l->t('Below you can change some default values, which are used to create a new room.')); ?></p>

        <p>
            <input type="checkbox" name="join.mediaCheck" id="bbb-join-mediaCheck" class="checkbox bbb-setting" value="1" <?php p($_['join.mediaCheck']); ?> />
            <label for="bbb-join-mediaCheck"><?php p($l->t('Perform media check before usage')); ?></label>
        </p>

        <h3><?php p($l->t('Community')); ?></h3>
        <p><?php p($l->t('Are you enjoying this app? Give something back to the open source community.')); ?> <a href="https://github.com/sualko/cloud_bbb/blob/master/.github/contributing.md" target="_blank" rel="noopener noreferrer" class="button"><span class="heart"></span> <?php p($l->t('Checkout the contributor guide')); ?></a></p>

        <h3><?php p($l->t('URL Shortener')); ?></h3>
        <p><?php p($l->t('If you like to use shorter urls, you can enter a forwarding proxy below.')); ?></p>

        <form id="bbb-shortener">
            <input type="url" name="app.shortener" value="<?php p($_['app.shortener']); ?>" placeholder="<?php p($l->t('URL shortener')); ?>" pattern="https://.*" />
            <input type="submit" value="<?php p($l->t('Save')); ?>" />

            <div id="bbb-shortener-example"></div>

            <div class="bbb-result"></div>
        </form>

        <h3><?php p($l->t('Restrictions')); ?></h3>
        <div id="bbb-restrictions">
        </div>
</div>
