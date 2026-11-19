<div style="margin-bottom: 28px;">
    <h2>Subscription Plans & Quota Management</h2>
    <p class="text-muted text-sm">Configure commercial pricing, feature entitlements, usage limits, and PayPal billing plan mappings.</p>
</div>

<div style="display: flex; flex-direction: column; gap: 32px;">
    <?php foreach ($plans as $plan): ?>
        <form action="<?= url('/admin/plans/update') ?>" method="POST" class="card">
            <?= csrf_field() ?>
            <input type="hidden" name="plan_id" value="<?= e($plan['id']) ?>">

            <div class="card-header">
                <div>
                    <div class="flex items-center gap-3">
                        <h3 class="card-title"><?= e($plan['name']) ?> Plan</h3>
                        <span class="badge badge-primary">Slug: <?= e($plan['slug']) ?></span>
                        <?php if ($plan['is_featured']): ?>
                            <span class="badge badge-warning">Featured (Best Value)</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-subtitle"><?= e($plan['tagline']) ?></div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Save Plan Changes</button>
            </div>

            <!-- Pricing & Plan Info -->
            <div class="grid grid-3 gap-4" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label">Plan Display Name</label>
                    <input type="text" name="name" class="form-control" value="<?= e($plan['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Monthly Price ($ USD)</label>
                    <input type="number" step="0.01" name="price_monthly" class="form-control" value="<?= e($plan['price_monthly']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Yearly Price ($ USD)</label>
                    <input type="number" step="0.01" name="price_yearly" class="form-control" value="<?= e($plan['price_yearly']) ?>" required>
                </div>
            </div>

            <!-- Quotas & Limits -->
            <div class="grid grid-3 gap-4" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label">Max Active Chatbots</label>
                    <input type="number" name="max_chatbots" class="form-control" value="<?= e($plan['max_chatbots']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Monthly Messages Quota</label>
                    <input type="number" name="monthly_messages" class="form-control" value="<?= e($plan['monthly_messages']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Max Knowledge Entries (per bot)</label>
                    <input type="number" name="max_knowledge_entries" class="form-control" value="<?= e($plan['max_knowledge_entries']) ?>" required>
                </div>
            </div>

            <!-- PayPal Plan ID Mappings -->
            <h4 style="font-size: 1rem; margin-top: 10px; margin-bottom: 12px; border-top: 1px solid var(--color-border); padding-top: 14px;">
                PayPal Subscription Plan IDs
            </h4>
            <div class="grid grid-2 gap-4" style="margin-bottom: 20px;">
                <?php
                $ppMonth = '';
                $ppYear = '';
                if (!empty($plan['paypal_mappings'])) {
                    foreach ($plan['paypal_mappings'] as $pm) {
                        if ($pm['billing_interval'] === 'MONTH') $ppMonth = $pm['paypal_plan_id'];
                        if ($pm['billing_interval'] === 'YEAR') $ppYear = $pm['paypal_plan_id'];
                    }
                }
                ?>
                <div class="form-group">
                    <label class="form-label">PayPal Monthly Plan ID (P-XXXXXXXX)</label>
                    <input type="text" name="paypal_plan_month" class="form-control" value="<?= e($ppMonth) ?>" placeholder="e.g. P-5ML4271244454362WXNWU5NQ">
                </div>

                <div class="form-group">
                    <label class="form-label">PayPal Yearly Plan ID (Optional)</label>
                    <input type="text" name="paypal_plan_year" class="form-control" value="<?= e($ppYear) ?>" placeholder="e.g. P-7YR4271244454362WXNWU5NQ">
                </div>
            </div>

            <!-- Feature Toggles -->
            <div class="flex gap-6 items-center flex-wrap" style="padding-top: 12px; border-top: 1px solid #f1f5f9;">
                <label class="flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="allow_custom_branding" value="1" <?= !empty($plan['allow_custom_branding']) ? 'checked' : '' ?>>
                    <span class="text-sm">Allow White-labeling (Hide Watermark)</span>
                </label>

                <label class="flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="allow_ai_custom_keys" value="1" <?= !empty($plan['allow_ai_custom_keys']) ? 'checked' : '' ?>>
                    <span class="text-sm">Allow Custom AI Provider API Keys</span>
                </label>

                <label class="flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="allow_api_access" value="1" <?= !empty($plan['allow_api_access']) ? 'checked' : '' ?>>
                    <span class="text-sm">Allow REST API Access</span>
                </label>

                <label class="flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="is_featured" value="1" <?= !empty($plan['is_featured']) ? 'checked' : '' ?>>
                    <span class="text-sm" style="color: #ca8a04; font-weight: 600;">Highlight as Featured (Best Value)</span>
                </label>
            </div>
        </form>
    <?php endforeach; ?>
</div>
