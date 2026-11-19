<div class="flex justify-between items-center" style="margin-bottom: 24px;">
    <div>
        <h2>Security Audit Logs</h2>
        <p class="text-muted text-sm">Immutable security audit trails for logins, configuration edits, and administrative overrides.</p>
    </div>
</div>

<div class="card">
    <?php if (empty($logs)): ?>
        <p class="text-muted text-sm">No audit logs recorded yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>User</th>
                        <th>IP Address</th>
                        <th>Details</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <strong><?= e(str_replace('_', ' ', strtoupper($log['action']))) ?></strong>
                            </td>
                            <td>
                                <div><?= e($log['user_name'] ?? 'System/Visitor') ?></div>
                                <?php if (!empty($log['user_email'])): ?>
                                    <div class="text-xs text-muted"><?= e($log['user_email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-xs" style="font-family: monospace;"><?= e($log['ip_address']) ?></td>
                            <td class="text-xs text-muted" style="max-width: 300px; word-break: break-all;">
                                <?= e($log['details'] ?? '—') ?>
                            </td>
                            <td class="text-xs text-muted"><?= date('M j, Y g:i:s a', strtotime($log['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
