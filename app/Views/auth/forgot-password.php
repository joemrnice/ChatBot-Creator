<form action="<?= url('/forgot-password') ?>" method="POST">
    <?= csrf_field() ?>

    <p class="text-muted text-sm" style="margin-bottom: 20px;">
        Enter your registered email address and we will generate a secure link to reset your password.
    </p>

    <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="name@company.com" value="<?= e(old('email')) ?>" required autofocus>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>

    <?php if ($demoLink = flash('demo_reset_link')): ?>
        <div class="alert alert-info" style="margin-top: 20px; font-size: 12px; word-break: break-all;">
            <strong>Dev/Demo Mode Link:</strong><br>
            <a href="<?= e($demoLink) ?>"><?= e($demoLink) ?></a>
        </div>
    <?php endif; ?>
</form>

<div class="text-center text-sm" style="margin-top: 24px;">
    <a href="<?= url('/login') ?>" class="text-muted">&larr; Back to Sign In</a>
</div>
