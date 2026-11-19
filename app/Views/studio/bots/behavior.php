<div style="max-width: 800px; margin: 0 auto;">
    <div class="flex justify-between items-center" style="margin-bottom: 24px;">
        <div>
            <h2>Behavior & AI Settings: <?= e($bot['name']) ?></h2>
            <p class="text-muted text-sm">Configure conversation tone, fallback messages, and AI provider integrations.</p>
        </div>
        <a href="<?= url("/studio/bots/{$bot['id']}/builder") ?>" class="btn btn-secondary btn-sm">Split Builder &rarr;</a>
    </div>

    <form action="<?= url("/studio/bots/{$bot['id']}/behavior") ?>" method="POST" class="card">
        <?= csrf_field() ?>

        <div class="grid grid-2 gap-4">
            <div class="form-group">
                <label class="form-label">Personality</label>
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
            <label class="form-label">Welcome Message</label>
            <textarea name="welcome_message" class="form-control" rows="2" required><?= e($bot['welcome_message']) ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Fallback Message</label>
            <textarea name="fallback_message" class="form-control" rows="2" required><?= e($bot['fallback_message']) ?></textarea>
            <div class="form-help">Displayed when no matching knowledge answers are found.</div>
        </div>

        <div class="form-group">
            <label class="form-label">System Instructions / Prompt Directives</label>
            <textarea name="system_instructions" class="form-control" rows="3" placeholder="You are a helpful customer concierge. Keep answers polite and accurate."><?= e($bot['system_instructions'] ?? '') ?></textarea>
        </div>

        <!-- AI Provider Section -->
        <h4 style="margin-top: 24px; margin-bottom: 14px; border-top: 1px solid var(--color-border); padding-top: 18px;">
            AI Engine Configuration
        </h4>

        <div class="grid grid-2 gap-4">
            <div class="form-group">
                <label class="form-label">Response Engine</label>
                <select name="ai_provider" class="form-control">
                    <option value="fallback" <?= ($bot['ai_provider'] ?? '') === 'fallback' ? 'selected' : '' ?>>Built-in Knowledge Engine (No API key needed)</option>
                    <option value="openai" <?= ($bot['ai_provider'] ?? '') === 'openai' ? 'selected' : '' ?>>OpenAI-Compatible Generative AI</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Model (if AI enabled)</label>
                <input type="text" name="ai_model" class="form-control" value="<?= e($bot['ai_model'] ?: 'gpt-4o-mini') ?>" placeholder="gpt-4o-mini, gpt-4o, claude-3-haiku">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Custom API Key (Optional)</label>
            <?php if (!empty($plan['allow_ai_custom_keys'])): ?>
                <input type="password" name="ai_custom_api_key" class="form-control" placeholder="sk-..." autocomplete="new-password">
                <div class="form-help">Stored encrypted via AES-256-CBC. Leave blank to retain current key or use system fallback.</div>
            <?php else: ?>
                <div class="text-xs text-muted" style="padding: 10px; background: #f8fafc; border-radius: 6px;">
                    Custom AI keys require Pro or Enterprise plan. Using built-in high accuracy knowledge search. <a href="<?= url('/studio/billing') ?>">Upgrade</a>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Save Behavior Settings</button>
    </form>
</div>
