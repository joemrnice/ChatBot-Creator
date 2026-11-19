<div class="flex justify-between items-center" style="margin-bottom: 24px;">
    <div>
        <h2>Transactions & Payment Ledger</h2>
        <p class="text-muted text-sm">Full audit of processed charges and subscription captures.</p>
    </div>
    <a href="<?= url('/admin/webhooks') ?>" class="btn btn-secondary btn-sm">Inspect PayPal Webhooks &rarr;</a>
</div>

<div class="card">
    <?php if (empty($transactions)): ?>
        <p class="text-muted text-sm">No payment transactions recorded yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Provider</th>
                        <th>Status</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: 600;"><?= e($tx['transaction_id']) ?></td>
                            <td>
                                <div><strong><?= e($tx['user_name']) ?></strong></div>
                                <div class="text-xs text-muted"><?= e($tx['user_email']) ?></div>
                            </td>
                            <td style="font-weight: 700;">$<?= number_format((float)$tx['amount'], 2) ?> <?= e($tx['currency']) ?></td>
                            <td><?= e(ucfirst($tx['payment_provider'])) ?></td>
                            <td>
                                <span class="badge <?= $tx['status'] === 'completed' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= e(ucfirst($tx['status'])) ?>
                                </span>
                            </td>
                            <td class="text-xs text-muted"><?= date('M j, Y g:i a', strtotime($tx['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
