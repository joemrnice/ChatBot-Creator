<div style="margin-bottom: 28px;">
    <h2>Roles & Access Permissions</h2>
    <p class="text-muted text-sm">Role-based access control (RBAC) definitions and assigned administrator privileges.</p>
</div>

<div class="grid grid-2 gap-8" style="margin-bottom: 32px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">System Roles</h3>
            <span class="badge badge-primary"><?= count($roles) ?> Roles</span>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Role Name</th>
                    <th>Identifier</th>
                    <th style="text-align: right;">Assigned Users</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td>
                            <div class="font-semibold"><?= e($role['name']) ?></div>
                            <div class="text-xs text-muted"><?= e($role['description'] ?? '') ?></div>
                        </td>
                        <td><code><?= e($role['slug']) ?></code></td>
                        <td style="text-align: right;">
                            <span class="badge <?= (int)$role['users_count'] > 0 ? 'badge-primary' : 'badge-neutral' ?>">
                                <?= e($role['users_count']) ?> users
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">System Permissions Matrix</h3>
            <span class="badge badge-secondary"><?= count($permissions) ?> Permissions</span>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Permission</th>
                    <th>Category</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($permissions as $perm): ?>
                    <tr>
                        <td class="font-semibold"><code><?= e($perm['slug']) ?></code></td>
                        <td><span class="badge badge-neutral"><?= e(ucfirst($perm['category'] ?? 'General')) ?></span></td>
                        <td class="text-xs text-muted"><?= e($perm['description'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
