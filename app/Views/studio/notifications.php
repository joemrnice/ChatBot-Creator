<div style="max-width: 800px; margin: 0 auto;">
    <div class="flex justify-between items-center" style="margin-bottom: 24px;">
        <div>
            <h2>Notifications</h2>
            <p class="text-muted text-sm">System updates, subscription alerts, and import completions.</p>
        </div>
    </div>

    <div class="card">
        <?php if (empty($notifications)): ?>
            <div class="text-center" style="padding: 40px 20px;">
                <div style="font-size: 2.5rem; margin-bottom: 12px;">🔔</div>
                <h4>No Notifications Right Now</h4>
                <p class="text-muted text-sm" style="margin-top: 6px;">You are completely up to date.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($notifications as $n): ?>
                    <div style="padding: 14px; border: 1px solid var(--color-border); border-radius: var(--radius-md);" class="flex items-start gap-3">
                        <span style="font-size: 1.25rem;">
                            <?= $n['type'] === 'success' ? '✅' : ($n['type'] === 'warning' ? '⚠️' : 'ℹ️') ?>
                        </span>
                        <div class="flex-1">
                            <div class="font-semibold text-sm"><?= e($n['title']) ?></div>
                            <div class="text-sm text-muted" style="margin-top: 2px;"><?= e($n['message']) ?></div>
                            <div class="text-xs text-muted" style="margin-top: 6px;"><?= date('M j, Y g:i a', strtotime($n['created_at'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
