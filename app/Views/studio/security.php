<div style="max-width: 760px; margin: 0 auto;">
    <div style="margin-bottom: 28px;">
        <h2>Security Settings</h2>
        <p class="text-muted text-sm">Manage your password, connected social accounts, and review active sessions.</p>
    </div>

    <!-- Change Password -->
    <form action="<?= url('/studio/security/password') ?>" method="POST" class="card" style="margin-bottom: 32px;">
        <?= csrf_field() ?>

        <div class="card-header">
            <h3 class="card-title">Change Password</h3>
        </div>

        <div class="form-group">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
        </div>

        <div class="grid grid-2 gap-4">
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Min 8 characters" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="new_password_confirmation" class="form-control" placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>

    <!-- Social OAuth Connections -->
    <div class="card" style="margin-bottom: 32px;">
        <div class="card-header">
            <h3 class="card-title">Connected Social Logins</h3>
        </div>

        <?php if (empty($oauthAccounts)): ?>
            <p class="text-muted text-sm">No social accounts connected. You currently log in via email and password.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($oauthAccounts as $oa): ?>
                    <div style="padding: 12px 16px; border: 1px solid var(--color-border); border-radius: var(--radius-md);" class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="badge badge-primary"><?= e(strtoupper($oa['provider'])) ?></span>
                            <span class="text-sm font-semibold">Connected on <?= date('M j, Y', strtotime($oa['created_at'])) ?></span>
                        </div>
                        <span class="text-xs text-muted" style="color: #10b981;">Active ✓</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Login Attempts -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Login History</h3>
        </div>

        <?php if (empty($recentLogins)): ?>
            <p class="text-muted text-sm">No login attempts recorded.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>IP Address</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLogins as $rl): ?>
                            <tr>
                                <td class="text-sm"><?= date('M j, Y g:i:s a', strtotime($rl['attempted_at'])) ?></td>
                                <td class="text-sm" style="font-family: monospace;"><?= e($rl['ip_address']) ?></td>
                                <td>
                                    <span class="badge <?= $rl['success'] ? 'badge-success' : 'badge-danger' ?>">
                                        <?= $rl['success'] ? 'Successful' : 'Failed' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
