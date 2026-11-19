<div class="flex justify-between items-center" style="margin-bottom: 24px;">
    <div>
        <h2>My Chatbots</h2>
        <p class="text-muted text-sm">Manage, customize, and deploy your conversational assistants.</p>
    </div>
    <?php if ($canCreate): ?>
        <a href="<?= url('/studio/bots/create') ?>" class="btn btn-primary">+ Create Chatbot</a>
    <?php else: ?>
        <a href="<?= url('/studio/billing') ?>" class="btn btn-warning" title="Limit reached for plan">Upgrade for More Bots</a>
    <?php endif; ?>
</div>

<?php if (empty($bots)): ?>
    <div class="card text-center" style="padding: 60px 20px;">
        <div style="font-size: 3rem; margin-bottom: 16px;">🤖</div>
        <h3>No Chatbots Found</h3>
        <p class="text-muted" style="margin-top: 8px; margin-bottom: 24px; max-width: 480px; margin-left: auto; margin-right: auto;">
            You have not created any chatbots yet. Get started in minutes using our guided creation wizard.
        </p>
        <a href="<?= url('/studio/bots/create') ?>" class="btn btn-primary">Create Your First Chatbot</a>
    </div>
<?php else: ?>
    <div class="grid grid-3 gap-6">
        <?php foreach ($bots as $bot): ?>
            <div class="card" style="display: flex; flex-direction: column;">
                <div class="flex justify-between items-start" style="margin-bottom: 16px;">
                    <div class="flex items-center gap-3">
                        <span style="width: 18px; height: 18px; border-radius: 50%; background: <?= e($bot['primary_color'] ?? '#2563eb') ?>; display: inline-block;"></span>
                        <h3 style="font-size: 1.15rem;"><?= e($bot['name']) ?></h3>
                    </div>
                    <span class="badge <?= $bot['status'] === 'published' ? 'badge-success' : 'badge-neutral' ?>">
                        <?= e(ucfirst($bot['status'])) ?>
                    </span>
                </div>

                <p class="text-muted text-sm" style="flex: 1; line-height: 1.5; margin-bottom: 20px;">
                    <?= e($bot['description'] ?: 'No description provided.') ?>
                </p>

                <div style="border-top: 1px solid var(--color-border); padding-top: 14px; margin-bottom: 16px;" class="grid grid-2 text-xs text-muted">
                    <div>
                        <strong><?= (int)$bot['knowledge_count'] ?></strong> Knowledge FAQs
                    </div>
                    <div>
                        <strong><?= (int)$bot['conversations_count'] ?></strong> Total Chats
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <a href="<?= url("/studio/bots/{$bot['id']}/builder") ?>" class="btn btn-primary btn-sm flex-1">Studio Builder</a>
                    <a href="<?= url("/studio/bots/{$bot['id']}/knowledge") ?>" class="btn btn-secondary btn-sm">Knowledge</a>
                    <a href="<?= url("/studio/bots/{$bot['id']}/embed") ?>" class="btn btn-secondary btn-sm">Embed</a>
                    <a href="<?= url("/studio/bots/{$bot['id']}/settings") ?>" class="btn btn-secondary btn-sm" title="Settings">⚙️</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
