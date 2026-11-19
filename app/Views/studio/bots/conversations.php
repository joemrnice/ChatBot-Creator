<div class="flex justify-between items-center" style="margin-bottom: 24px;">
    <div>
        <h2>Conversations Log: <?= e($bot['name']) ?></h2>
        <p class="text-muted text-sm">Review full transcripts and inquiries from visitors.</p>
    </div>
    <a href="<?= url("/studio/bots/{$bot['id']}/export/conversations") ?>" class="btn btn-secondary btn-sm">Export Transcripts CSV</a>
</div>

<div class="card">
    <?php if (empty($conversations)): ?>
        <div class="text-center" style="padding: 40px 20px;">
            <div style="font-size: 2.5rem; margin-bottom: 12px;">💬</div>
            <h4>No Visitor Conversations Logged Yet</h4>
            <p class="text-muted text-sm" style="margin-top: 6px;">Once visitors interact with this bot, their session histories and questions will appear here.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Visitor</th>
                        <th>Session ID</th>
                        <th>Messages</th>
                        <th>Status</th>
                        <th>Started</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($conversations as $conv): ?>
                        <tr>
                            <td>
                                <strong><?= e($conv['visitor_name'] ?: 'Anonymous Visitor') ?></strong>
                                <?php if (!empty($conv['visitor_email'])): ?>
                                    <div class="text-xs text-muted"><?= e($conv['visitor_email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-xs text-muted" style="font-family: monospace;">
                                <?= e(substr($conv['session_id'], 0, 16)) ?>...
                            </td>
                            <td><?= (int)$conv['messages_count'] ?></td>
                            <td>
                                <span class="badge <?= $conv['status'] === 'active' ? 'badge-success' : 'badge-neutral' ?>">
                                    <?= e(ucfirst($conv['status'])) ?>
                                </span>
                            </td>
                            <td class="text-xs text-muted">
                                <?= date('M j, Y g:i a', strtotime($conv['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
