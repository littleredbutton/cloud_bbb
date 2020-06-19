<?php
script('bbb', 'manager');
?>

<div id="bbb-root"></div>

<?php if (!empty($_['warning'])): ?>
    <div id="bbb-warning">
        <span class="icon icon-error-color icon-visible"></span> <?php p($_['warning']); ?>
    </div>
<?php endif; ?>