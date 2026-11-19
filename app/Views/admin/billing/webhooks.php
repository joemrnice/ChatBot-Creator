<div class="flex justify-between items-center" style="margin-bottom: 24px;">
    <div>
        <h2>PayPal Webhook Events</h2>
        <p class="text-muted text-sm">Idempotently received and processed subscription life-cycle notifications.</p>
    </div>
    <a href="<?= url('/admin/payments') ?>" class="btn btn-secondary btn-sm">&larr; Back to Payments</a>
</div>

<div class="card">
    <?php if (empty($events)): ?>
        <p class="text-muted text-sm">No webhook events received yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Event ID</th>
                        <th>Event Type</th>
                        <th>Summary</th>
                        <th>Status</th>
                        <th>Received At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $ev): ?>
                        <tr>
                            <td class="text-xs" style="font-family: monospace;"><?= e($ev['event_id']) ?></td>
                            <td><strong><?= e($ev['event_type']) ?></strong></td>
                            <td class="text-xs text-muted"><?= e($ev['summary']) ?></td>
                            <td>
                                <span class="badge <?= $ev['status'] === 'processed' ? 'badge-success' : 'badge-neutral' ?>">
                                    <?= e(ucfirst($ev['status'])) ?>
                                </span>
                            </td>
                            <td class="text-xs text-muted"><?= date('M j, Y g:i:s a', strtotime($ev['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
