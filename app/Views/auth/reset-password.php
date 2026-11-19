<form action="<?= url('/reset-password') ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token ?? '') ?>">

    <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" value="<?= e($email ?? '') ?>" required readonly>
    </div>

    <div class="form-group">
        <label class="form-label">New Password</label>
        <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required autofocus>
    </div>

    <div class="form-group">
        <label class="form-label">Confirm New Password</label>
        <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Save New Password</button>
</form>

<div class="text-center text-sm" style="margin-top: 24px;">
    <a href="<?= url('/login') ?>" class="text-muted">&larr; Back to Sign In</a>
</div>
