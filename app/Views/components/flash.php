<?php
$successMsg = flash('success');
$errorMsg = flash('error');
$infoMsg = flash('info');
$warningMsg = flash('warning');
$errors = flash('errors');
?>

<?php if ($successMsg): ?>
    <div class="alert alert-success alert-dismissible">
        <span>✓</span>
        <div><?= e($successMsg) ?></div>
    </div>
<?php endif; ?>

<?php if ($errorMsg): ?>
    <div class="alert alert-error alert-dismissible">
        <span>⚠️</span>
        <div><?= e($errorMsg) ?></div>
    </div>
<?php endif; ?>

<?php if ($infoMsg): ?>
    <div class="alert alert-info alert-dismissible">
        <span>ℹ️</span>
        <div><?= e($infoMsg) ?></div>
    </div>
<?php endif; ?>

<?php if ($warningMsg): ?>
    <div class="alert alert-warning alert-dismissible">
        <span>⚠️</span>
        <div><?= e($warningMsg) ?></div>
    </div>
<?php endif; ?>

<?php if (!empty($errors) && is_array($errors)): ?>
    <div class="alert alert-error alert-dismissible">
        <span>⚠️</span>
        <div>
            <ul style="padding-left: 18px; margin: 0;">
                <?php foreach ($errors as $field => $fieldErrors): ?>
                    <?php foreach ($fieldErrors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>
