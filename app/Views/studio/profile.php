<div style="max-width: 680px; margin: 0 auto;">
    <div style="margin-bottom: 28px;">
        <h2>Account Profile</h2>
        <p class="text-muted text-sm">Update your personal account credentials and email address.</p>
    </div>

    <form action="<?= url('/studio/profile') ?>" method="POST" class="card">
        <?= csrf_field() ?>

        <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Account Member Since</label>
            <input type="text" class="form-control" value="<?= date('F j, Y', strtotime($user['created_at'])) ?>" readonly style="background: #f8fafc;">
        </div>

        <button type="submit" class="btn btn-primary">Save Profile</button>
    </form>
</div>
