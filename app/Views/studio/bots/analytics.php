<div class="flex justify-between items-center" style="margin-bottom: 24px;">
    <div>
        <h2>Analytics & Performance: <?= e($bot['name']) ?></h2>
        <p class="text-muted text-sm">Real-time statistics derived from visitor interactions.</p>
    </div>
    <a href="<?= url("/studio/bots/{$bot['id']}/conversations") ?>" class="btn btn-secondary btn-sm">Inspect Conversations &rarr;</a>
</div>

<div class="grid grid-4 gap-6" style="margin-bottom: 32px;">
    <div class="card">
        <div class="card-subtitle">Total Conversations</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;">
            <?= number_format($totalConversations) ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 6px;">Unique visitor sessions</div>
    </div>

    <div class="card">
        <div class="card-subtitle">Total Messages</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;">
            <?= number_format($totalMessages) ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 6px;">Exchanged across all chats</div>
    </div>

    <div class="card">
        <div class="card-subtitle">Satisfaction Score</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px; color: var(--color-success);">
            <?= e($feedbackScore) ?>%
        </div>
        <div class="text-xs text-muted" style="margin-top: 6px;">Positive visitor feedback</div>
    </div>

    <div class="card">
        <div class="card-subtitle">Feedback Ratio</div>
        <div style="font-size: 1.25rem; font-weight: 700; margin-top: 10px;">
            👍 <?= e($positiveFeedback) ?> / 👎 <?= e($negativeFeedback) ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 6px;">Upvotes vs downvotes</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Conversation Insights & Health</h3>
    </div>
    <p class="text-muted text-sm" style="line-height: 1.6;">
        Your assistant is actively answering incoming inquiries. To improve satisfaction and reduce fallback responses, review recent conversations to see what visitors are asking, and add missing questions directly to your 
        <a href="<?= url("/studio/bots/{$bot['id']}/knowledge") ?>" style="font-weight: 600;">Knowledge Base</a>.
    </p>
</div>
