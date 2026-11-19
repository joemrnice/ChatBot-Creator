<div style="margin-bottom: 32px;">
    <h2>Billing & Subscriptions</h2>
    <p class="text-muted text-sm">Manage your SaaS plan, entitlements, payment history, and invoices.</p>
</div>

<!-- Current Plan Overview Card -->
<div class="card" style="margin-bottom: 36px; background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border-color: #bfdbfe;">
    <div class="flex justify-between items-start flex-wrap gap-4">
        <div>
            <span class="badge badge-primary">Current Active Plan</span>
            <h1 style="font-size: 2.25rem; font-weight: 800; margin-top: 8px; color: var(--color-primary);">
                <?= e($currentPlan['name']) ?> Plan
            </h1>
            <p class="text-muted text-sm" style="margin-top: 4px;">
                Status: <strong style="color: #10b981;"><?= e(ucfirst($activeSubscription['status'] ?? 'Active')) ?></strong>
                <?php if (!empty($activeSubscription['current_period_end'])): ?>
                    &bull; Renews on <?= date('F j, Y', strtotime($activeSubscription['current_period_end'])) ?>
                <?php endif; ?>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <?php if (!empty($activeSubscription) && $currentPlan['slug'] !== 'free'): ?>
                <form action="<?= url('/studio/billing/cancel') ?>" method="POST" onsubmit="return confirm('Are you sure you want to cancel your paid subscription? You will retain access until the end of the billing period.');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-secondary btn-sm" style="color: #dc2626;">Cancel Subscription</button>
                </form>
            <?php endif; ?>
            <a href="#available-plans" class="btn btn-primary btn-sm">Upgrade Plan</a>
        </div>
    </div>

    <div class="grid grid-3 gap-6" style="margin-top: 24px; border-top: 1px solid #dbeafe; padding-top: 16px;">
        <div>
            <div class="text-xs text-muted">Chatbot Allocation</div>
            <div class="font-bold text-lg" style="margin-top: 2px;">
                <?= (int)$currentPlan['max_chatbots'] ?> Chatbots Max
            </div>
        </div>
        <div>
            <div class="text-xs text-muted">Monthly Message Quota</div>
            <div class="font-bold text-lg" style="margin-top: 2px;">
                <?= number_format((int)$currentPlan['monthly_messages']) ?> Messages
            </div>
        </div>
        <div>
            <div class="text-xs text-muted">Branding & AI Key Access</div>
            <div class="font-bold text-lg" style="margin-top: 2px;">
                <?= !empty($currentPlan['allow_custom_branding']) ? 'Custom Branding ✓' : 'Default Watermark' ?>
            </div>
        </div>
    </div>
</div>

<!-- Plan Comparison Grid -->
<div id="available-plans" style="margin-bottom: 48px;">
    <div class="text-center" style="margin-bottom: 32px;">
        <h3>Available Subscription Plans</h3>
        <p class="text-muted text-sm" style="margin-top: 4px;">Upgrade anytime to unlock higher message volumes, OpenAI integration, and custom branding.</p>
    </div>

    <div class="grid grid-4 gap-6 items-stretch">
        <?php foreach ($plans as $p): ?>
            <?php 
                $isCurrent = ($p['id'] == $currentPlan['id']);
                $isPro = ($p['slug'] === 'pro');
            ?>
            <div class="card pricing-card <?= $isPro ? 'featured' : '' ?>" style="display: flex; flex-direction: column;">
                <?php if ($isPro): ?>
                    <div class="pricing-badge">MOST POPULAR — BEST VALUE</div>
                <?php endif; ?>

                <div style="margin-bottom: 12px;">
                    <h3 style="font-size: 1.25rem;"><?= e($p['name']) ?></h3>
                    <p class="text-muted text-xs" style="margin-top: 4px; min-height: 32px;"><?= e($p['tagline']) ?></p>
                </div>

                <div style="margin-bottom: 20px;">
                    <span style="font-size: 2rem; font-weight: 800; color: var(--color-secondary);">
                        $<?= number_format((float)$p['price_monthly'], 0) ?>
                    </span>
                    <span class="text-muted text-xs">/ mo</span>
                </div>

                <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px; flex: 1;" class="text-xs">
                    <?php if (!empty($p['features'])): ?>
                        <?php foreach ($p['features'] as $f): ?>
                            <li class="flex items-center gap-2">
                                <span class="check-icon-pill <?= !$f['is_included'] ? 'disabled' : '' ?>" style="width: 16px; height: 16px; font-size: 10px;">
                                    <?= $f['is_included'] ? '✓' : '—' ?>
                                </span>
                                <span style="<?= !$f['is_included'] ? 'color: #94a3b8; text-decoration: line-through;' : '' ?>">
                                    <?= e($f['feature_name']) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="flex items-center gap-2"><span class="check-icon-pill" style="width: 16px; height: 16px; font-size: 10px;">✓</span> <?= (int)$p['max_chatbots'] ?> Chatbot(s)</li>
                        <li class="flex items-center gap-2"><span class="check-icon-pill" style="width: 16px; height: 16px; font-size: 10px;">✓</span> <?= number_format((int)$p['monthly_messages']) ?> Messages/mo</li>
                    <?php endif; ?>
                </ul>

                <div>
                    <?php if ($isCurrent): ?>
                        <button class="btn btn-secondary btn-block btn-sm" disabled style="opacity: 0.7;">Current Plan</button>
                    <?php else: ?>
                        <form action="<?= url("/studio/billing/subscribe/{$p['slug']}") ?>" method="POST">
                            <?= csrf_field() ?>
                            <?php if (!$isPayPalConfigured): ?>
                                <input type="hidden" name="demo_activate" value="1">
                                <button type="submit" class="btn <?= $isPro ? 'btn-primary' : 'btn-secondary' ?> btn-block btn-sm">
                                    <?= $p['price_monthly'] == 0 ? 'Switch to Free' : 'Select ' . e($p['name']) . ' (Demo)' ?>
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn <?= $isPro ? 'btn-primary' : 'btn-secondary' ?> btn-block btn-sm">
                                    <?= $isPro ? 'Upgrade with PayPal' : 'Select ' . e($p['name']) ?>
                                </button>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Invoices & Transaction History -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Billing History & Invoices</h3>
    </div>

    <?php if (empty($invoices)): ?>
        <p class="text-muted text-sm">No payment invoices logged yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                        <?php $invDate = $inv['billing_date'] ?? $inv['created_at'] ?? date('Y-m-d H:i:s'); ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: 600;"><?= e($inv['invoice_number'] ?? '') ?></td>
                            <td><?= e($inv['description'] ?? '') ?></td>
                            <td class="font-semibold">$<?= number_format((float)($inv['amount'] ?? 0), 2) ?> USD</td>
                            <td><?= date('M j, Y', strtotime($invDate)) ?></td>
                            <td>
                                <span class="badge <?= ($inv['status'] ?? '') === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= e(ucfirst($inv['status'] ?? 'pending')) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
