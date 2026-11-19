<div class="flex justify-between items-center" style="margin-bottom: 24px;">
    <div>
        <h2>User Management</h2>
        <p class="text-muted text-sm">Search, inspect, activate, or suspend platform accounts.</p>
    </div>
</div>

<div class="card" style="margin-bottom: 24px;">
    <form action="<?= url('/admin/users') ?>" method="GET" class="flex gap-4 items-center flex-wrap">
        <input type="text" name="q" class="form-control" style="max-width: 300px;" placeholder="Search name or email..." value="<?= e($search) ?>">
        <select name="status" class="form-control" style="max-width: 180px;">
            <option value="">All Statuses</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="suspended" <?= $status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <?php if ($search || $status): ?>
            <a href="<?= url('/admin/users') ?>" class="btn btn-secondary btn-sm">Reset</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Plan</th>
                    <th>Bots</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <strong><?= e($u['name']) ?></strong>
                            <div class="text-xs text-muted"><?= e($u['email']) ?></div>
                        </td>
                        <td>
                            <span class="badge badge-primary"><?= e($u['plan_name'] ?: 'Free') ?></span>
                        </td>
                        <td style="font-weight: 700;"><?= (int)$u['bots_count'] ?></td>
                        <td>
                            <span class="badge <?= $u['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                <?= e(ucfirst($u['status'])) ?>
                            </span>
                        </td>
                        <td class="text-xs text-muted"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <a href="<?= url("/admin/users/{$u['id']}") ?>" class="btn btn-secondary btn-sm">Inspect &rarr;</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
