<section style="padding: 60px 0;">
    <div class="container">
        <div class="text-center" style="max-width: 640px; margin: 0 auto 48px;">
            <span class="badge badge-primary" style="margin-bottom: 8px;">Subscription Tiers</span>
            <h1>Simple, Predictable Pricing</h1>
            <p class="text-muted text-lg" style="margin-top: 12px; line-height: 1.6;">
                Choose the plan that best fits your volume. Upgrade, downgrade, or cancel anytime from your personal studio.
            </p>
        </div>

        <div class="grid grid-4 gap-6 items-stretch">
            <?php foreach ($plans as $p): ?>
                <?php $isPro = ($p['slug'] === 'pro'); ?>
                <div class="card pricing-card <?= $isPro ? 'featured' : '' ?>" style="display: flex; flex-direction: column;">
                    <?php if ($isPro): ?>
                        <div class="pricing-badge">MOST POPULAR — BEST VALUE</div>
                    <?php endif; ?>

                    <div style="margin-bottom: 16px;">
                        <h3><?= e($p['name']) ?></h3>
                        <p class="text-muted text-xs" style="margin-top: 4px; min-height: 36px;"><?= e($p['tagline']) ?></p>
                    </div>

                    <div style="margin-bottom: 24px;">
                        <span style="font-size: 2.25rem; font-weight: 800; color: var(--color-secondary);">
                            $<?= number_format((float)$p['price_monthly'], 0) ?>
                        </span>
                        <span class="text-muted text-sm">/ month</span>
                        <?php if ((float)$p['price_yearly'] > 0): ?>
                            <div class="text-xs text-muted" style="margin-top: 4px;">or $<?= number_format((float)$p['price_yearly'], 0) ?> billed annually (save 16%)</div>
                        <?php endif; ?>
                    </div>

                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px; margin-bottom: 28px; flex: 1;" class="text-sm">
                        <?php if (!empty($p['features'])): ?>
                            <?php foreach ($p['features'] as $f): ?>
                                <li class="flex items-center gap-2">
                                    <span class="check-icon-pill <?= !$f['is_included'] ? 'disabled' : '' ?>">
                                        <?= $f['is_included'] ? '✓' : '—' ?>
                                    </span>
                                    <span style="<?= !$f['is_included'] ? 'color: #94a3b8; text-decoration: line-through;' : '' ?>">
                                        <?= e($f['feature_name']) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="flex items-center gap-2"><span class="check-icon-pill">✓</span> <?= (int)$p['max_chatbots'] ?> Chatbot(s)</li>
                            <li class="flex items-center gap-2"><span class="check-icon-pill">✓</span> <?= number_format((int)$p['monthly_messages']) ?> Messages/mo</li>
                            <li class="flex items-center gap-2"><span class="check-icon-pill">✓</span> CDN Embed & Downloads</li>
                        <?php endif; ?>
                    </ul>

                    <div>
                        <?php if (auth_user()): ?>
                            <a href="<?= url('/studio/billing') ?>" class="btn <?= $isPro ? 'btn-primary' : 'btn-secondary' ?> btn-block">
                                <?= $isPro ? 'Upgrade to Pro' : 'Choose ' . e($p['name']) ?>
                            </a>
                        <?php else: ?>
                            <a href="<?= url('/register') ?>" class="btn <?= $isPro ? 'btn-primary' : 'btn-secondary' ?> btn-block">
                                <?= $p['price_monthly'] == 0 ? 'Get Started Free' : 'Get Started with ' . e($p['name']) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
