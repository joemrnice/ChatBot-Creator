<div style="margin-bottom: 28px;">
    <h2>Platform Analytics & Conversation Trends</h2>
    <p class="text-muted text-sm">Deep analytics into platform conversations, user satisfaction ratings, and popular chatbot categories.</p>
</div>

<div class="grid grid-3 gap-6" style="margin-bottom: 32px;">
    <div class="card">
        <div class="card-subtitle">Conversations</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;"><?= e($totalConversations) ?></div>
        <div class="text-xs text-muted" style="margin-top: 4px;"><?= e($totalMessages) ?> messages exchanged</div>
    </div>

    <div class="card">
        <div class="card-subtitle">Visitor Feedback Rating</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px; color: var(--color-success);"><?= e($feedbackScore) ?>%</div>
        <div class="text-xs text-muted" style="margin-top: 4px;">👍 <?= e($positiveFeedback) ?> positive &bull; 👎 <?= e($negativeFeedback) ?> negative</div>
    </div>

    <div class="card">
        <div class="card-subtitle">Categories Tracked</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;"><?= count($categories) ?></div>
        <div class="text-xs text-muted" style="margin-top: 4px;">Diverse industry & personal use cases</div>
    </div>
</div>

<div class="grid grid-2 gap-8" style="margin-bottom: 32px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Chatbots by Category</h3>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th style="text-align: right;">Total Bots</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="2" class="text-muted text-center">No categories recorded.</td></tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td class="font-semibold"><?= e(ucfirst($cat['category'])) ?></td>
                            <td style="text-align: right;"><span class="badge badge-primary"><?= e($cat['count']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Conversations</h3>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Chatbot</th>
                    <th>Messages</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentConversations)): ?>
                    <tr><td colspan="4" class="text-muted text-center">No conversations yet.</td></tr>
                <?php else: ?>
                    <?php foreach (array_slice($recentConversations, 0, 8) as $conv): ?>
                        <tr>
                            <td class="font-semibold"><?= e($conv['bot_name']) ?></td>
                            <td><?= e($conv['messages_count']) ?></td>
                            <td><span class="badge badge-success"><?= e(ucfirst($conv['status'])) ?></span></td>
                            <td class="text-muted text-xs"><?= date('M j, H:i', strtotime($conv['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
