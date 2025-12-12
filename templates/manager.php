<?php
\OCP\Util::addScript('bbb', 'bbb-manager');
?>

<div id="bbb-app">
    <div id="bbb-root" data-shortener="<?php p($_['shortener']); ?>"></div>

    <?php if (!empty($_['warning'])): ?>
        <div id="bbb-warning">
            <span class="icon icon-error-color icon-visible"></span> <?php p($_['warning']); ?>
        </div>
    <?php endif; ?>
</div>
