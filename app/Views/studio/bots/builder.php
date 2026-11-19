<div class="flex justify-between items-center" style="margin-bottom: 24px;">
    <div>
        <div class="flex items-center gap-3">
            <h2>Studio Builder: <?= e($bot['name']) ?></h2>
            <span class="badge <?= $bot['status'] === 'published' ? 'badge-success' : 'badge-neutral' ?>">
                <?= e(ucfirst($bot['status'])) ?>
            </span>
        </div>
        <p class="text-muted text-sm">Fine-tune appearance and behavior with real-time live preview.</p>
    </div>
    <div class="flex gap-2">
        <a href="<?= url("/studio/bots/{$bot['id']}/embed") ?>" class="btn btn-secondary btn-sm">Embed Code</a>
        <a href="<?= url("/b/{$bot['slug']}") ?>" target="_blank" class="btn btn-secondary btn-sm">Public Page &rarr;</a>
    </div>
</div>

<div class="builder-split">
    <!-- Left Column: Configuration Controls Form -->
    <form id="bot-builder-form" action="<?= url("/studio/bots/{$bot['id']}/builder") ?>" method="POST">
        <?= csrf_field() ?>

        <!-- Publication Status Card -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="flex justify-between items-center">
                <div>
                    <h4 style="font-size: 1.05rem;">Publication Status</h4>
                    <p class="text-muted text-xs" style="margin-top: 2px;">
                        Published bots can respond to visitors on websites and standalone links.
                    </p>
                </div>
                <select name="status" class="form-control" style="width: auto; font-weight: 600;">
                    <option value="published" <?= $bot['status'] === 'published' ? 'selected' : '' ?>>Published (Live)</option>
                    <option value="draft" <?= $bot['status'] === 'draft' ? 'selected' : '' ?>>Draft (Private)</option>
                </select>
            </div>
        </div>

        <!-- Appearance Section -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h3 class="card-title">Widget Appearance</h3>
            </div>

            <div class="grid grid-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Header Title</label>
                    <input type="text" name="header_title" class="form-control" value="<?= e($bot['header_title'] ?: $bot['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Header Subtitle</label>
                    <input type="text" name="header_subtitle" class="form-control" value="<?= e($bot['header_subtitle'] ?: 'Online') ?>">
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
                        <option value="light" <?= ($bot['theme'] ?? '') === 'light' ? 'selected' : '' ?>>Light Theme</option>
                        <option value="dark" <?= ($bot['theme'] ?? '') === 'dark' ? 'selected' : '' ?>>Dark Theme</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Corner Radius (px)</label>
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
        </div>

        <!-- Behavior & Personality Section -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h3 class="card-title">Personality & Conversation Behavior</h3>
            </div>

            <div class="grid grid-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Tone & Persona</label>
                    <select name="personality" class="form-control">
                        <option value="friendly" <?= ($bot['personality'] ?? '') === 'friendly' ? 'selected' : '' ?>>Friendly & Welcoming</option>
                        <option value="professional" <?= ($bot['personality'] ?? '') === 'professional' ? 'selected' : '' ?>>Professional & Direct</option>
                        <option value="empathetic" <?= ($bot['personality'] ?? '') === 'empathetic' ? 'selected' : '' ?>>Empathetic & Supportive</option>
                        <option value="academic" <?= ($bot['personality'] ?? '') === 'academic' ? 'selected' : '' ?>>Academic & Detailed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Response Style</label>
                    <select name="response_style" class="form-control">
                        <option value="concise" <?= ($bot['response_style'] ?? '') === 'concise' ? 'selected' : '' ?>>Concise & Structured</option>
                        <option value="detailed" <?= ($bot['response_style'] ?? '') === 'detailed' ? 'selected' : '' ?>>Detailed & Thorough</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Welcome Greeting Message</label>
                <textarea name="welcome_message" class="form-control" rows="2" required><?= e($bot['welcome_message']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Fallback Message (when knowledge is not found)</label>
                <textarea name="fallback_message" class="form-control" rows="2" required><?= e($bot['fallback_message']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Suggested Question Chips (One per line)</label>
                <?php
                $chipsStr = !empty($bot['suggested_questions_array']) ? implode("\n", $bot['suggested_questions_array']) : '';
                ?>
                <textarea name="suggested_questions" class="form-control" rows="3"><?= e($chipsStr) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Lead / Contact Capture</label>
                <label class="flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="contact_capture_enabled" value="1" <?= !empty($bot['contact_capture_enabled']) ? 'checked' : '' ?>>
                    <span class="text-sm">Prompt visitors for name/email when assistance is needed</span>
                </label>
            </div>
        </div>

        <div style="position: sticky; bottom: 20px; z-index: 30; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); padding: 16px; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); border: 1px solid var(--color-border);" class="flex justify-between items-center">
            <span class="text-sm text-muted">Preview on right updates automatically</span>
            <button type="submit" class="btn btn-primary btn-lg">Save Chatbot Changes</button>
        </div>
    </form>

    <!-- Right Column: Interactive Live Preview -->
    <div class="preview-sticky">
        <div class="card" style="padding: 16px;">
            <div class="flex justify-between items-center" style="margin-bottom: 12px;">
                <span class="badge badge-primary">Interactive Live Preview</span>
                <span class="text-xs text-muted">Test your bot live</span>
            </div>

            <!-- Mock Window Container -->
            <div id="preview-window" style="width: 100%; height: 520px; background: #ffffff; border: 1px solid var(--color-border); border-radius: <?= e($bot['widget_border_radius'] ?: 16) ?>px; box-shadow: var(--shadow-md); display: flex; flex-direction: column; overflow: hidden;">
                <!-- Header -->
                <div id="preview-header-box" style="background: <?= e($bot['primary_color'] ?: '#2563eb') ?>; color: #ffffff; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 id="preview-header-title" style="font-size: 15px; font-weight: 700; color: #ffffff; line-height: 1.2;">
                            <?= e($bot['header_title'] ?: $bot['name']) ?>
                        </h4>
                        <p id="preview-header-subtitle" style="font-size: 11px; opacity: 0.85; margin-top: 2px;">
                            <?= e($bot['header_subtitle'] ?: 'Online') ?>
                        </p>
                    </div>
                    <span>💬</span>
                </div>

                <!-- Messages Feed -->
                <div id="preview-messages" style="flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 10px; background: #f8fafc;">
                    <div class="cbc-msg cbc-msg-bot">
                        <span id="preview-welcome-message"><?= e($bot['welcome_message']) ?></span>
                        <div id="preview-chips-container" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px;">
                            <?php foreach ($bot['suggested_questions_array'] as $q): ?>
                                <button type="button" class="cbc-chip" onclick="sendPreviewQuestion('<?= e(addslashes($q)) ?>')"><?= e($q) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Input Footer -->
                <div style="padding: 10px 14px; background: #ffffff; border-top: 1px solid var(--color-border);">
                    <form id="preview-chat-form" style="display: flex; gap: 6px;" onsubmit="event.preventDefault(); submitPreviewChat();">
                        <input type="text" id="preview-input" class="form-control" style="font-size: 13px; padding: 8px 12px;" placeholder="Ask something..." autocomplete="off">
                        <button type="submit" class="btn btn-primary btn-sm" style="padding: 8px 14px;">Send</button>
                    </form>
                    <?php if (empty($bot['hide_watermark'])): ?>
                        <div style="text-align: center; font-size: 10px; color: #94a3b8; margin-top: 4px;">
                            Powered by ChatBot Creator Studio
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const BOT_PUBLIC_TOKEN = '<?= e($bot['public_token']) ?>';
const API_BASE = '<?= rtrim(url('/'), '/') ?>';

function sendPreviewQuestion(question) {
    document.getElementById('preview-input').value = question;
    submitPreviewChat();
}

async function submitPreviewChat() {
    const input = document.getElementById('preview-input');
    const msg = input.value.trim();
    if (!msg) return;

    input.value = '';
    const messagesEl = document.getElementById('preview-messages');

    // Add user bubble
    const userBubble = document.createElement('div');
    userBubble.className = 'cbc-msg cbc-msg-user';
    userBubble.textContent = msg;
    messagesEl.appendChild(userBubble);
    messagesEl.scrollTop = messagesEl.scrollHeight;

    // Add typing bubble
    const typing = document.createElement('div');
    typing.className = 'cbc-msg cbc-msg-bot';
    typing.textContent = 'Thinking...';
    messagesEl.appendChild(typing);
    messagesEl.scrollTop = messagesEl.scrollHeight;

    try {
        const res = await fetch(`${API_BASE}/api/v1/chat/${BOT_PUBLIC_TOKEN}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ session_id: 'preview_session_1', message: msg })
        });
        const data = await res.json();
        typing.remove();

        const botBubble = document.createElement('div');
        botBubble.className = 'cbc-msg cbc-msg-bot';
        botBubble.textContent = data.data?.content || data.message || 'No response returned.';
        messagesEl.appendChild(botBubble);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    } catch (err) {
        typing.remove();
        const errBubble = document.createElement('div');
        errBubble.className = 'cbc-msg cbc-msg-bot';
        errBubble.textContent = 'Unable to connect to assistant engine.';
        messagesEl.appendChild(errBubble);
    }
}
</script>
