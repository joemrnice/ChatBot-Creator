<div style="max-width: 800px; margin: 0 auto;">
    <div class="flex justify-between items-center" style="margin-bottom: 24px;">
        <div>
            <h2>Appearance Settings: <?= e($bot['name']) ?></h2>
            <p class="text-muted text-sm">Customize visual styling, themes, colors, and branding.</p>
        </div>
        <a href="<?= url("/studio/bots/{$bot['id']}/builder") ?>" class="btn btn-secondary btn-sm">Split Builder &rarr;</a>
    </div>

    <form action="<?= url("/studio/bots/{$bot['id']}/appearance") ?>" method="POST" class="card">
        <?= csrf_field() ?>

        <div class="grid grid-2 gap-4">
            <div class="form-group">
                <label class="form-label">Header Title</label>
                <input type="text" name="header_title" class="form-control" value="<?= e($bot['header_title'] ?: $bot['name']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Header Subtitle</label>
                <input type="text" name="header_subtitle" class="form-control" value="<?= e($bot['header_subtitle'] ?: 'Replies instantly') ?>">
            </div>
        </div>

        <div class="grid grid-3 gap-4">
            <div class="form-group">
                <label class="form-label">Primary Color</label>
                <input type="color" name="primary_color" class="form-control" style="height: 44px; padding: 4px;" value="<?= e($bot['primary_color'] ?: '#2563eb') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Theme</label>
                <select name="theme" class="form-control">
                    <option value="light" <?= ($bot['theme'] ?? '') === 'light' ? 'selected' : '' ?>>Light</option>
                    <option value="dark" <?= ($bot['theme'] ?? '') === 'dark' ? 'selected' : '' ?>>Dark</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Border Radius (px)</label>
                <input type="number" name="widget_border_radius" class="form-control" min="4" max="32" value="<?= e($bot['widget_border_radius'] ?: 16) ?>">
            </div>
        </div>

        <div class="grid grid-2 gap-4">
            <div class="form-group">
                <label class="form-label">Position on Host Page</label>
                <select name="widget_position" class="form-control">
                    <option value="bottom-right" <?= ($bot['widget_position'] ?? '') === 'bottom-right' ? 'selected' : '' ?>>Bottom Right</option>
                    <option value="bottom-left" <?= ($bot['widget_position'] ?? '') === 'bottom-left' ? 'selected' : '' ?>>Bottom Left</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">White-labeling (Watermark)</label>
                <?php if (!empty($plan['allow_custom_branding'])): ?>
                    <select name="hide_watermark" class="form-control">
                        <option value="0" <?= empty($bot['hide_watermark']) ? 'selected' : '' ?>>Show "Powered by ChatBot Studio"</option>
                        <option value="1" <?= !empty($bot['hide_watermark']) ? 'selected' : '' ?>>Hide Branding Watermark</option>
                    </select>
                <?php else: ?>
                    <div class="text-xs text-muted" style="margin-top: 8px;">
                        Removing watermark requires Standard or Pro plan. <a href="<?= url('/studio/billing') ?>">Upgrade</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Custom CSS (Scoped to Widget)</label>
            <textarea name="custom_css" class="form-control" rows="4" placeholder=".cbc-launcher { box-shadow: 0 4px 12px rgba(0,0,0,0.2); }"><?= e($bot['custom_css'] ?? '') ?></textarea>
            <div class="form-help">Styles will be evaluated within the isolated widget Shadow DOM.</div>
        </div>

        <button type="submit" class="btn btn-primary">Save Appearance</button>
    </form>
</div>
