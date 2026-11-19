<div style="margin-bottom: 28px;">
    <h2>Admin Data Center Dashboard</h2>
    <p class="text-muted text-sm">Platform-wide statistics, revenue metrics, and system diagnostics.</p>
</div>

<!-- Financial & Platform High-Level KPIs -->
<div class="grid grid-4 gap-6" style="margin-bottom: 32px;">
    <div class="card" style="border-left: 4px solid #10b981;">
        <div class="card-subtitle">Estimated MRR</div>
        <div style="font-size: 2rem; font-weight: 800; color: #10b981; margin-top: 6px;">
            $<?= number_format($estimatedMrr, 2) ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 4px;">Monthly Recurring Revenue</div>
    </div>

    <div class="card" style="border-left: 4px solid var(--color-primary);">
        <div class="card-subtitle">Total Processed Revenue</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--color-primary); margin-top: 6px;">
            $<?= number_format($totalRevenue, 2) ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 4px;"><?= $successfulPayments ?> completed transaction(s)</div>
    </div>

    <div class="card" style="border-left: 4px solid #8b5cf6;">
        <div class="card-subtitle">Total Registered Users</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;">
            <?= number_format($totalUsers) ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 4px;"><?= $activeUsers ?> active &bull; <?= $suspendedUsers ?> suspended</div>
    </div>

    <div class="card" style="border-left: 4px solid #f59e0b;">
        <div class="card-subtitle">Total Platform Bots</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;">
            <?= number_format($totalBots) ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 4px;"><?= $publishedBots ?> published &bull; <?= $draftBots ?> draft</div>
    </div>
</div>

<!-- Subscribers Breakdown by Plan -->
<div class="card" style="margin-bottom: 32px;">
    <div class="card-header">
        <h3 class="card-title">Subscribers by Plan Tier</h3>
    </div>
    <div class="grid grid-4 gap-4 text-center">
        <div style="background: #f8fafc; padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <div class="badge badge-neutral">Free Tier</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin-top: 8px;"><?= e($freeCount) ?></div>
            <div class="text-xs text-muted" style="margin-top: 4px;">Users on Free tier</div>
        </div>

        <div style="background: #eff6ff; padding: 18px; border-radius: var(--radius-md); border: 1px solid #bfdbfe;">
            <div class="badge badge-primary">Standard</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin-top: 8px; color: var(--color-primary);"><?= e($standardCount) ?></div>
            <div class="text-xs text-muted" style="margin-top: 4px;">$19.00 / month</div>
        </div>

        <div style="background: #fefce8; padding: 18px; border-radius: var(--radius-md); border: 1px solid #fef08a;">
            <div class="badge badge-warning">Pro (Featured)</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin-top: 8px; color: #ca8a04;"><?= e($proCount) ?></div>
            <div class="text-xs text-muted" style="margin-top: 4px;">$49.00 / month</div>
        </div>

        <div style="background: #f5f3ff; padding: 18px; border-radius: var(--radius-md); border: 1px solid #ddd6fe;">
            <div class="badge badge-primary" style="background: #7c3aed; color: #fff;">Enterprise</div>
            <div style="font-size: 1.75rem; font-weight: 800; margin-top: 8px; color: #7c3aed;"><?= e($enterpriseCount) ?></div>
            <div class="text-xs text-muted" style="margin-top: 4px;">$199.00 / month</div>
        </div>
    </div>
</div>

<div class="grid grid-2 gap-8 items-start">
    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Registrations</h3>
            <a href="<?= url('/admin/users') ?>" class="text-xs font-semibold">View all &rarr;</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $u): ?>
                        <tr>
                            <td>
                                <strong><?= e($u['name']) ?></strong>
                                <div class="text-xs text-muted"><?= e($u['email']) ?></div>
                            </td>
                            <td class="text-xs text-muted"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <span class="badge <?= $u['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= e(ucfirst($u['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= url("/admin/users/{$u['id']}") ?>" class="btn btn-secondary btn-sm" style="padding: 2px 8px; font-size: 11px;">Inspect</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Audit Logs -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Live Security Audit Log</h3>
            <a href="<?= url('/admin/audit-logs') ?>" class="text-xs font-semibold">Full Trail &rarr;</a>
        </div>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($recentLogs as $log): ?>
                <div style="padding: 10px 14px; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--color-border);" class="flex items-center justify-between text-xs">
                    <div>
                        <strong style="color: var(--color-secondary);"><?= e(str_replace('_', ' ', strtoupper($log['action']))) ?></strong>
                        <div class="text-muted" style="margin-top: 2px;"><?= e($log['user_name'] ?? 'System/Visitor') ?> (IP: <?= e($log['ip_address']) ?>)</div>
                    </div>
                    <span class="text-muted"><?= date('g:i:s a', strtotime($log['created_at'])) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
