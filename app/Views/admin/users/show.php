<div class="flex justify-between items-center" style="margin-bottom: 24px;">
    <div>
        <h2>User Details: <?= e($user['name']) ?></h2>
        <p class="text-muted text-sm">UUID: <?= e($user['uuid']) ?> &bull; Email: <?= e($user['email']) ?></p>
    </div>
    <a href="<?= url('/admin/users') ?>" class="btn btn-secondary btn-sm">&larr; Back to Users</a>
</div>

<div class="grid grid-2 gap-8 items-start" style="margin-bottom: 32px;">
    <!-- Edit User Account Form -->
    <form action="<?= url("/admin/users/{$user['id']}/update") ?>" method="POST" class="card">
        <?= csrf_field() ?>

        <div class="card-header">
            <h3 class="card-title">Account State & Plan</h3>
        </div>

        <div class="form-group">
            <label class="form-label">Account Status</label>
            <select name="status" class="form-control">
                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active (Full access)</option>
                <option value="suspended" <?= $user['status'] === 'suspended' ? 'selected' : '' ?>>Suspended (Block login & bots)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Subscription Plan</label>
            <select name="plan_id" class="form-control">
                <?php foreach ($plans as $p): ?>
                    <option value="<?= e($p['id']) ?>" <?= $user['plan_id'] == $p['id'] ? 'selected' : '' ?>>
                        <?= e($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update User Account</button>
    </form>

    <!-- Subscription & Meta Overview -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Subscription & Session Info</h3>
        </div>

        <table class="table">
            <tr>
                <td class="text-muted">Current Plan</td>
                <td><strong><?= e($user['plan_name'] ?: 'Free') ?></strong></td>
            </tr>
            <tr>
                <td class="text-muted">Subscription Status</td>
                <td><?= e($subscription['status'] ?? 'None / Free Tier') ?></td>
            </tr>
            <tr>
                <td class="text-muted">PayPal Sub ID</td>
                <td class="text-xs" style="font-family: monospace;"><?= e($subscription['paypal_subscription_id'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <td class="text-muted">Last Login</td>
                <td class="text-xs text-muted"><?= $user['last_login_at'] ? date('M j, Y g:i a', strtotime($user['last_login_at'])) : 'Never' ?></td>
            </tr>
        </table>
    </div>
</div>

<!-- Chatbots Owned by User -->
<div class="card" style="margin-bottom: 32px;">
    <div class="card-header">
        <h3 class="card-title">Chatbots Owned (<?= count($bots) ?>)</h3>
    </div>

    <?php if (empty($bots)): ?>
        <p class="text-muted text-sm">This user has not created any chatbots.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Bot Name</th>
                        <th>Slug</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bots as $b): ?>
                        <tr>
                            <td><strong><?= e($b['name']) ?></strong></td>
                            <td class="text-xs text-muted">/b/<?= e($b['slug']) ?></td>
                            <td><?= e(ucfirst($b['category'])) ?></td>
                            <td>
                                <span class="badge <?= $b['status'] === 'published' ? 'badge-success' : 'badge-neutral' ?>">
                                    <?= e(ucfirst($b['status'])) ?>
                                </span>
                            </td>
                            <td class="text-xs text-muted"><?= date('M j, Y', strtotime($b['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
