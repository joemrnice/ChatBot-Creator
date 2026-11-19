<div class="flex justify-between items-center" style="margin-bottom: 24px;">
    <div>
        <h2>Customer Support Tickets</h2>
        <p class="text-muted text-sm">Review incoming user tickets, prioritize issues, and send responses.</p>
    </div>
</div>

<div class="card">
    <?php if (empty($tickets)): ?>
        <div class="text-center" style="padding: 40px 20px;">
            <div style="font-size: 2.5rem; margin-bottom: 12px;">🎫</div>
            <h4>No Support Tickets Currently Open</h4>
            <p class="text-muted text-sm" style="margin-top: 6px;">All customer support inquiries have been resolved.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>User</th>
                        <th>Subject</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $st): ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: 600;"><?= e($st['ticket_number']) ?></td>
                            <td>
                                <div><strong><?= e($st['user_name']) ?></strong></div>
                                <div class="text-xs text-muted"><?= e($st['user_email']) ?></div>
                            </td>
                            <td><?= e($st['subject']) ?></td>
                            <td>
                                <span class="badge <?= $st['priority'] === 'high' || $st['priority'] === 'urgent' ? 'badge-danger' : 'badge-neutral' ?>">
                                    <?= e(ucfirst($st['priority'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $st['status'] === 'open' ? 'badge-warning' : ($st['status'] === 'resolved' ? 'badge-success' : 'badge-neutral') ?>">
                                    <?= e(ucfirst($st['status'])) ?>
                                </span>
                            </td>
                            <td class="text-xs text-muted"><?= date('M j, Y g:i a', strtotime($st['updated_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
