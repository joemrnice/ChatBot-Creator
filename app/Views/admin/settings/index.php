<div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 28px;">
        <h2>System & Integration Settings</h2>
        <p class="text-muted text-sm">Configure third-party payment gateways, social OAuth apps, and platform defaults.</p>
    </div>

    <form action="<?= url('/admin/settings') ?>" method="POST">
        <?= csrf_field() ?>

        <!-- General Platform Settings -->
        <div class="card" style="margin-bottom: 28px;">
            <div class="card-header">
                <h3 class="card-title">General Platform Branding</h3>
            </div>
            <div class="grid grid-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Platform Name</label>
                    <input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? 'ChatBot Creator Studio') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Support / Contact Email</label>
                    <input type="email" name="contact_email" class="form-control" value="<?= e($settings['contact_email'] ?? 'support@chatbot-creator.com') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Platform Tagline</label>
                <input type="text" name="site_tagline" class="form-control" value="<?= e($settings['site_tagline'] ?? 'Create Powerful Chatbots. Customize Without Coding. Deploy Everywhere.') ?>">
            </div>
        </div>

        <!-- PayPal Subscriptions REST API -->
        <div class="card" style="margin-bottom: 28px;">
            <div class="card-header">
                <h3 class="card-title">PayPal Subscriptions REST API</h3>
                <span class="badge badge-primary">Official Integration</span>
            </div>
            <div class="grid grid-2 gap-4">
                <div class="form-group">
                    <label class="form-label">PayPal Mode</label>
                    <select name="paypal_mode" class="form-control">
                        <option value="sandbox" <?= ($settings['paypal_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>Sandbox (Testing)</option>
                        <option value="live" <?= ($settings['paypal_mode'] ?? '') === 'live' ? 'selected' : '' ?>>Live (Production)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Currency</label>
                    <input type="text" name="currency" class="form-control" value="<?= e($settings['currency'] ?? 'USD') ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">PayPal Client ID</label>
                <input type="text" name="paypal_client_id" class="form-control" value="<?= e($settings['paypal_client_id'] ?? env('PAYPAL_CLIENT_ID', '')) ?>" placeholder="Client ID from developer.paypal.com">
            </div>
            <div class="form-group">
                <label class="form-label">PayPal Client Secret</label>
                <input type="password" name="paypal_client_secret" class="form-control" value="<?= e($settings['paypal_client_secret'] ?? env('PAYPAL_CLIENT_SECRET', '')) ?>" placeholder="Client Secret">
            </div>
            <div class="form-group">
                <label class="form-label">PayPal Webhook ID</label>
                <input type="text" name="paypal_webhook_id" class="form-control" value="<?= e($settings['paypal_webhook_id'] ?? env('PAYPAL_WEBHOOK_ID', '')) ?>" placeholder="Webhook ID for signature verification">
                <div class="form-help">Webhook URL to configure in PayPal: <code><?= url('/api/v1/paypal/webhook') ?></code></div>
            </div>
        </div>

        <!-- AI Provider Defaults -->
        <div class="card" style="margin-bottom: 28px;">
            <div class="card-header">
                <h3 class="card-title">AI Provider & Default Model</h3>
            </div>
            <div class="grid grid-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Default Response Engine</label>
                    <select name="default_ai_provider" class="form-control">
                        <option value="fallback" <?= ($settings['default_ai_provider'] ?? 'fallback') === 'fallback' ? 'selected' : '' ?>>Built-in Knowledge Search (Offline/Rules)</option>
                        <option value="openai" <?= ($settings['default_ai_provider'] ?? '') === 'openai' ? 'selected' : '' ?>>OpenAI-Compatible API</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Default Model</label>
                    <input type="text" name="default_model" class="form-control" value="<?= e($settings['default_model'] ?? 'gpt-4o-mini') ?>">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Save System Configuration</button>
    </form>
</div>
