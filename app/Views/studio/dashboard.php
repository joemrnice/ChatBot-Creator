<!-- Quota Alert Banner if usage approaches limit -->
<?php if ($messageUsagePct >= 80): ?>
    <div class="alert alert-warning">
        <span>⚠️</span>
        <div class="flex-1">
            <strong>Monthly Message Quota Warning:</strong> You have consumed <?= e($messageUsagePct) ?>% of your plan's message allowance (<?= e($usage['monthly_messages']) ?> / <?= e($maxMessages) ?>).
            <a href="<?= url('/studio/billing') ?>" style="margin-left: 8px; font-weight: 700; text-decoration: underline;">Upgrade to Pro &rarr;</a>
        </div>
    </div>
<?php endif; ?>

<!-- Real Database-Derived KPI Metrics -->
<div class="grid grid-4 gap-6" style="margin-bottom: 32px;">
    <div class="card">
        <div class="card-subtitle">Active Chatbots</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;" class="flex items-center justify-between">
            <span><?= e($usage['active_chatbots']) ?></span>
            <span class="text-xs text-muted" style="font-weight: normal;"><?= e($remainingBots) ?> slot(s) left</span>
        </div>
        <div class="text-xs text-muted" style="margin-top: 8px;">
            Limit: <?= e($maxBots) ?> bots on <?= e($plan['name']) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-subtitle">Monthly Messages</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;" class="flex items-center justify-between">
            <span><?= number_format($usage['monthly_messages']) ?></span>
            <span class="text-xs text-muted" style="font-weight: normal;"><?= e($messageUsagePct) ?>% used</span>
        </div>
        <div style="background: #e2e8f0; height: 6px; border-radius: 3px; margin-top: 8px; overflow: hidden;">
            <div style="background: var(--color-primary); height: 100%; width: <?= e($messageUsagePct) ?>%;"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-subtitle">Monthly Conversations</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;">
            <?= number_format($usage['monthly_conversations']) ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 8px;">
            Unique visitor sessions
        </div>
    </div>

    <div class="card">
        <div class="card-subtitle">Knowledge Entries</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;">
            <?= number_format($usage['total_knowledge_entries']) ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 8px;">
            FAQs and reference documents
        </div>
    </div>
</div>

<div class="grid grid-2 gap-8 items-start">
    <!-- Chatbots List -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">My Chatbots</h3>
                <div class="card-subtitle">Manage and customize your assistants</div>
            </div>
            <a href="<?= url('/studio/bots/create') ?>" class="btn btn-primary btn-sm">+ New Bot</a>
        </div>

        <?php if (empty($chatbots)): ?>
            <div class="text-center" style="padding: 40px 20px;">
                <div style="font-size: 2.5rem; margin-bottom: 12px;">🤖</div>
                <h4>No Chatbots Created Yet</h4>
                <p class="text-muted text-sm" style="margin-top: 6px; margin-bottom: 16px;">Create your first assistant using pre-built templates or from scratch.</p>
                <a href="<?= url('/studio/bots/create') ?>" class="btn btn-primary btn-sm">Launch Creation Wizard</a>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($chatbots as $b): ?>
                    <div style="padding: 14px; border: 1px solid var(--color-border); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: space-between;" class="flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <span style="width: 14px; height: 14px; border-radius: 50%; background: <?= e($b['primary_color'] ?? '#2563eb') ?>; display: inline-block;"></span>
                            <div>
                                <a href="<?= url("/studio/bots/{$b['id']}/builder") ?>" style="font-weight: 700; color: var(--color-secondary);">
                                    <?= e($b['name']) ?>
                                </a>
                                <div class="text-xs text-muted">
                                    /b/<?= e($b['slug']) ?> &bull; <?= (int)$b['knowledge_count'] ?> FAQs &bull; <?= e(ucfirst($b['status'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="<?= url("/studio/bots/{$b['id']}/builder") ?>" class="btn btn-secondary btn-sm" title="Edit Builder">Builder</a>
                            <a href="<?= url("/studio/bots/{$b['id']}/embed") ?>" class="btn btn-secondary btn-sm" title="Embed Code">Embed</a>
                            <a href="<?= url("/b/{$b['slug']}") ?>" target="_blank" class="btn btn-secondary btn-sm" title="Test Live">Test &rarr;</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top: 16px; text-align: right;">
                <a href="<?= url('/studio/bots') ?>" class="text-sm font-semibold">View all bots &rarr;</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Conversations -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Recent Visitor Chats</h3>
                <div class="card-subtitle">Latest incoming conversations</div>
            </div>
        </div>

        <?php if (empty($recentConversations)): ?>
            <div class="text-center" style="padding: 40px 20px;">
                <div style="font-size: 2.5rem; margin-bottom: 12px;">💬</div>
                <h4>No Visitor Chats Yet</h4>
                <p class="text-muted text-sm" style="margin-top: 6px;">Once you embed your chatbot or share your link, incoming visitor chats will appear here.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($recentConversations as $c): ?>
                    <div style="padding: 12px; border: 1px solid var(--color-border); border-radius: var(--radius-md);" class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold">
                                <?= e($c['visitor_name'] ?? 'Visitor ' . substr($c['session_id'], 0, 8)) ?>
                            </div>
                            <div class="text-xs text-muted">
                                Bot: <strong><?= e($c['bot_name']) ?></strong> &bull; <?= e($c['messages_count']) ?> messages
                            </div>
                        </div>
                        <div class="text-xs text-muted">
                            <?= date('M j, g:i a', strtotime($c['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
