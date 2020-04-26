<?php
/** @var $l \OCP\IL10N */
/** @var $_ array */

script('bigbluebutton', 'admin');
?>

<div id="bigbluebutton-settings" class="section">
        <h2>BigBlueButton</h2>

        <form>
            <input type="url" name="api.url" value="<?php p($_['api.url']); ?>" placeholder="API url" pattern="https://.*" />
            <input type="password" name="api.secret" value="<?php p($_['api.secret']); ?>" placeholder="API secret" />
            <input type="submit" value="<?php p($l->t('Save')); ?>" />
        </form>
</div>