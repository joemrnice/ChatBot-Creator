<div style="margin-bottom: 28px;">
    <h2>Platform Usage & Quotas</h2>
    <p class="text-muted text-sm">Monitor overall resource utilization, active bots, message consumption, and tenant usage metrics.</p>
</div>

<div class="grid grid-3 gap-6" style="margin-bottom: 32px;">
    <div class="card">
        <div class="card-subtitle">Total Chatbots</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;"><?= e($totalBots) ?></div>
        <div class="text-xs text-muted" style="margin-top: 4px;"><?= e($activeBots) ?> Published / Active</div>
    </div>

    <div class="card">
        <div class="card-subtitle">Total Messages Exchanged</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;"><?= e($totalMessages) ?></div>
        <div class="text-xs text-muted" style="margin-top: 4px;">Across <?= e($totalConversations) ?> total conversations</div>
    </div>

    <div class="card">
        <div class="card-subtitle">Knowledge Base Items</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;"><?= e($totalKnowledge) ?></div>
        <div class="text-xs text-muted" style="margin-top: 4px;">Indexed across all tenant bots</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Top Users by Platform Consumption</h3>
        <span class="text-xs text-muted">Showing highest message & bot consumers</span>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Plan</th>
                    <th>Created Bots</th>
                    <th>Messages Exchanged</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($topUsers)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted" style="padding: 30px;">No user activity recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($topUsers as $user): ?>
                        <tr>
                            <td class="font-semibold"><?= e($user['name']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td><span class="badge badge-primary"><?= e($user['plan_name'] ?? 'Free') ?></span></td>
                            <td><?= e($user['bots_count']) ?></td>
                            <td><?= e($user['messages_count']) ?></td>
                            <td>
                                <a href="<?= url("/admin/users/{$user['id']}") ?>" class="btn btn-secondary btn-sm">Inspect User</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
