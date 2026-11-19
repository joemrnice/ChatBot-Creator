<div class="flex justify-between items-center" style="margin-bottom: 24px;">
    <div>
        <div class="flex items-center gap-3">
            <h2><?= e($bot['name']) ?></h2>
            <span class="badge <?= $bot['status'] === 'published' ? 'badge-success' : 'badge-neutral' ?>">
                <?= e(ucfirst($bot['status'])) ?>
            </span>
        </div>
        <p class="text-muted text-sm">Category: <?= e(ucfirst($bot['category'])) ?> &bull; Public Link: <a href="<?= url("/b/{$bot['slug']}") ?>" target="_blank"><?= url("/b/{$bot['slug']}") ?></a></p>
    </div>
    <div class="flex gap-2">
        <a href="<?= url("/studio/bots/{$bot['id']}/builder") ?>" class="btn btn-primary">Studio Builder</a>
        <a href="<?= url("/studio/bots/{$bot['id']}/embed") ?>" class="btn btn-secondary">Embed Code</a>
        <a href="<?= url("/b/{$bot['slug']}") ?>" target="_blank" class="btn btn-secondary">Open Live &rarr;</a>
    </div>
</div>

<div class="grid grid-3 gap-6" style="margin-bottom: 32px;">
    <div class="card">
        <div class="card-subtitle">Knowledge Entries</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;">
            <?= e($knowledgeCount) ?>
        </div>
        <div style="margin-top: 8px;">
            <a href="<?= url("/studio/bots/{$bot['id']}/knowledge") ?>" class="text-xs font-semibold">Manage FAQs &rarr;</a>
        </div>
    </div>

    <div class="card">
        <div class="card-subtitle">Total Conversations</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;">
            <?= e($conversationsCount) ?>
        </div>
        <div style="margin-top: 8px;">
            <a href="<?= url("/studio/bots/{$bot['id']}/conversations") ?>" class="text-xs font-semibold">View Transcripts &rarr;</a>
        </div>
    </div>

    <div class="card">
        <div class="card-subtitle">Messages Exchanged</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;">
            <?= e($messagesCount) ?>
        </div>
        <div style="margin-top: 8px;">
            <a href="<?= url("/studio/bots/{$bot['id']}/analytics") ?>" class="text-xs font-semibold">View Analytics &rarr;</a>
        </div>
    </div>
</div>

<div class="grid grid-2 gap-8">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Assistant Overview</h3>
            <a href="<?= url("/studio/bots/{$bot['id']}/builder") ?>" class="btn btn-secondary btn-sm">Edit</a>
        </div>
        <table class="table">
            <tr>
                <td style="width: 140px;" class="text-muted">Welcome Msg</td>
                <td><?= e($bot['welcome_message']) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Fallback Msg</td>
                <td><?= e($bot['fallback_message']) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Personality</td>
                <td><?= e(ucfirst($bot['personality'])) ?> (<?= e(ucfirst($bot['tone'])) ?>)</td>
            </tr>
            <tr>
                <td class="text-muted">Primary Color</td>
                <td>
                    <span style="display: inline-block; width: 14px; height: 14px; border-radius: 3px; background: <?= e($bot['primary_color']) ?>; vertical-align: middle; margin-right: 6px;"></span>
                    <?= e($bot['primary_color']) ?>
                </td>
            </tr>
            <tr>
                <td class="text-muted">Widget Position</td>
                <td><?= e($bot['widget_position']) ?></td>
            </tr>
        </table>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <a href="<?= url("/studio/bots/{$bot['id']}/import") ?>" class="btn btn-secondary" style="justify-content: flex-start;">
                <span>📥</span>
                <span>Import Knowledge via CSV / JSON</span>
            </a>
            <a href="<?= url("/studio/bots/{$bot['id']}/export/zip") ?>" class="btn btn-secondary" style="justify-content: flex-start;">
                <span>📦</span>
                <span>Download Integration ZIP Package</span>
            </a>
            <a href="<?= url("/studio/bots/{$bot['id']}/export/json") ?>" class="btn btn-secondary" style="justify-content: flex-start;">
                <span>📄</span>
                <span>Export Configuration JSON</span>
            </a>
            <a href="<?= url("/studio/bots/{$bot['id']}/settings") ?>" class="btn btn-secondary" style="justify-content: flex-start;">
                <span>⚙️</span>
                <span>Chatbot Settings & Publication Status</span>
            </a>
        </div>
    </div>
</div>
