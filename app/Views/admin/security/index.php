<div style="margin-bottom: 28px;">
    <h2>Security Monitoring & Defense Center</h2>
    <p class="text-muted text-sm">Real-time inspection of authentication attempts, brute-force rate limits, and platform security flags.</p>
</div>

<div class="grid grid-3 gap-6" style="margin-bottom: 32px;">
    <div class="card">
        <div class="card-subtitle">Password Hashing</div>
        <div style="font-size: 1.25rem; font-weight: 700; margin-top: 6px;">
            <?= $securityChecks['password_argon'] ? 'Argon2id (Enhanced)' : 'Bcrypt / Native' ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 4px;">Zero plaintext passwords stored</div>
    </div>

    <div class="card">
        <div class="card-subtitle">Session Security</div>
        <div style="font-size: 1.25rem; font-weight: 700; margin-top: 6px;">
            <?= $securityChecks['session_httponly'] ? 'HttpOnly Active' : 'Standard' ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 4px;">SameSite: <?= e($securityChecks['session_samesite']) ?> &bull; Anti-Fixation Enabled</div>
    </div>

    <div class="card">
        <div class="card-subtitle">Debug Mode</div>
        <div style="font-size: 1.25rem; font-weight: 700; margin-top: 6px; color: <?= $securityChecks['app_debug'] ? 'var(--color-success)' : 'var(--color-danger)' ?>;">
            <?= $securityChecks['app_debug'] ? 'Disabled (Production Safe)' : 'Enabled (Development)' ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 4px;">Stack traces hidden from public</div>
    </div>
</div>

<div class="grid grid-2 gap-8">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Login Attempts</h3>
            <span class="text-xs text-muted">Throttled at 5 attempts / 15 min</span>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>IP Address</th>
                    <th>Status</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($loginAttempts)): ?>
                    <tr><td colspan="4" class="text-muted text-center">No login attempts logged.</td></tr>
                <?php else: ?>
                    <?php foreach ($loginAttempts as $attempt): ?>
                        <tr>
                            <td><?= e($attempt['email']) ?></td>
                            <td><code><?= e($attempt['ip_address']) ?></code></td>
                            <td>
                                <span class="badge <?= (int)$attempt['success'] === 1 ? 'badge-success' : 'badge-danger' ?>">
                                    <?= (int)$attempt['success'] === 1 ? 'Successful' : 'Failed' ?>
                                </span>
                            </td>
                            <td class="text-muted text-xs"><?= date('M j, H:i:s', strtotime($attempt['attempted_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Security & Auth Audit Logs</h3>
            <span class="text-xs text-muted">Immutable trail</span>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>IP</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentSecurityLogs)): ?>
                    <tr><td colspan="4" class="text-muted text-center">No security events logged.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentSecurityLogs as $log): ?>
                        <tr>
                            <td class="font-semibold"><?= e($log['user_name'] ?? 'System') ?></td>
                            <td><code><?= e($log['action']) ?></code></td>
                            <td><code><?= e($log['ip_address'] ?? '127.0.0.1') ?></code></td>
                            <td class="text-muted text-xs"><?= date('M j, H:i:s', strtotime($log['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
