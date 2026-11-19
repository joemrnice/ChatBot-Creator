<!-- Hero Section -->
<section style="padding: 64px 0 80px; text-align: center;">
    <div class="container" style="max-width: 900px;">
        <span class="badge badge-primary" style="margin-bottom: 20px; padding: 6px 14px;">Next-Gen No-Code AI Platform</span>
        <h1 style="font-size: 3.25rem; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 24px; line-height: 1.15;">
            Create Powerful Chatbots.<br>
            <span style="color: var(--color-primary);">Customize Without Coding.</span><br>
            Deploy Everywhere.
        </h1>
        <p class="text-muted" style="font-size: 1.25rem; line-height: 1.6; margin-bottom: 36px; max-width: 720px; margin-left: auto; margin-right: auto;">
            The all-in-one multi-tenant SaaS studio for entrepreneurs, businesses, tutors, and agencies. Upload FAQs in JSON or CSV, customize appearance, and embed responsive widgets in seconds.
        </p>
        <div class="flex justify-center items-center gap-4 flex-wrap">
            <a href="<?= url('/register') ?>" class="btn btn-primary btn-lg">Start Free — No Card Needed</a>
            <a href="<?= url('/how-it-works') ?>" class="btn btn-secondary btn-lg">Explore How It Works</a>
        </div>
    </div>
</section>

<!-- Live Interactive Preview Showcase -->
<section style="padding: 20px 0 80px;">
    <div class="container" style="max-width: 1000px;">
        <div class="card" style="padding: 0; overflow: hidden; box-shadow: var(--shadow-xl); border: 2px solid var(--color-border);">
            <div style="background: #0f172a; padding: 14px 20px;" class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span style="width: 12px; height: 12px; border-radius: 50%; background: #ef4444;"></span>
                    <span style="width: 12px; height: 12px; border-radius: 50%; background: #f59e0b;"></span>
                    <span style="width: 12px; height: 12px; border-radius: 50%; background: #10b981;"></span>
                </div>
                <div class="text-xs" style="color: #94a3b8; font-family: monospace;">ChatBot Studio - Split Preview Engine</div>
                <div></div>
            </div>
            <div style="padding: 40px; background: #ffffff;">
                <div class="grid grid-2 gap-8 items-center">
                    <div>
                        <span class="badge badge-success" style="margin-bottom: 12px;">Live Demonstration</span>
                        <h2>Instant No-Code Customization</h2>
                        <p class="text-muted" style="margin-top: 12px; line-height: 1.6;">
                            Our visual studio gives you complete control over tone, greetings, fallback policies, branding colors, widget geometry, and custom CSS.
                        </p>
                        <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 12px;">
                            <div class="flex items-center gap-3">
                                <span style="color: #10b981; font-weight: bold;">✓</span>
                                <span>Multi-Format Ingestion: JSON, CSV, or Form entry</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span style="color: #10b981; font-weight: bold;">✓</span>
                                <span>Shadow DOM CSS Isolation & Mobile-Friendly</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span style="color: #10b981; font-weight: bold;">✓</span>
                                <span>Downloadable Standalone ZIP Packages</span>
                            </div>
                        </div>
                    </div>

                    <!-- Mini Mockup Box -->
                    <div style="background: #f8fafc; border: 1px solid var(--color-border); border-radius: 16px; padding: 20px; box-shadow: var(--shadow-md);">
                        <div style="background: var(--color-primary); color: #fff; padding: 14px 18px; border-radius: 12px 12px 0 0;" class="flex items-center justify-between">
                            <div>
                                <div style="font-weight: 700;">Concierge Assistant</div>
                                <div style="font-size: 11px; opacity: 0.85;">Replies in seconds</div>
                            </div>
                            <span>💬</span>
                        </div>
                        <div style="background: #ffffff; padding: 18px; min-height: 160px; display: flex; flex-direction: column; gap: 10px; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
                            <div style="background: #f1f5f9; padding: 10px 14px; border-radius: 12px; font-size: 13px; max-width: 85%;">
                                Hello! How can I assist you with our services today?
                            </div>
                            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                <span style="background: #eff6ff; color: #2563eb; border: 1px solid #2563eb; padding: 4px 10px; border-radius: 12px; font-size: 11px;">What are your services?</span>
                                <span style="background: #eff6ff; color: #2563eb; border: 1px solid #2563eb; padding: 4px 10px; border-radius: 12px; font-size: 11px;">View pricing</span>
                            </div>
                        </div>
                        <div style="background: #f8fafc; padding: 12px; border: 1px solid #e2e8f0; border-radius: 0 0 12px 12px;" class="flex gap-2">
                            <input type="text" placeholder="Type a message..." class="form-control" style="font-size: 12px; padding: 6px 12px;" readonly value="How do I get started?">
                            <button class="btn btn-primary btn-sm" style="padding: 6px 12px;">Send</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3-Step Creation Process -->
<section style="padding: 60px 0; background: #ffffff; border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <div class="text-center" style="max-width: 600px; margin: 0 auto 48px;">
            <span class="badge badge-primary" style="margin-bottom: 8px;">Effortless Setup</span>
            <h2>Three Simple Steps to Launch</h2>
            <p class="text-muted" style="margin-top: 8px;">From idea to deployed customer-facing chatbot in less than 5 minutes.</p>
        </div>

        <div class="grid grid-3 gap-8">
            <div class="card text-center" style="padding: 32px 24px;">
                <div style="font-size: 2.5rem; margin-bottom: 16px;">1️⃣</div>
                <h4>Choose or Build</h4>
                <p class="text-muted text-sm" style="margin-top: 10px; line-height: 1.6;">
                    Select a battle-tested template (Sales, Support, Tutoring, Real Estate) or start from scratch using the step-by-step studio wizard.
                </p>
            </div>
            <div class="card text-center" style="padding: 32px 24px;">
                <div style="font-size: 2.5rem; margin-bottom: 16px;">2️⃣</div>
                <h4>Import Knowledge</h4>
                <p class="text-muted text-sm" style="margin-top: 10px; line-height: 1.6;">
                    Upload existing FAQ documents via CSV or structured JSON. Preview and validate records safely with transactional rollbacks.
                </p>
            </div>
            <div class="card text-center" style="padding: 32px 24px;">
                <div style="font-size: 2.5rem; margin-bottom: 16px;">3️⃣</div>
                <h4>Embed & Deploy</h4>
                <p class="text-muted text-sm" style="margin-top: 10px; line-height: 1.6;">
                    Copy the lightweight CDN script tag or download a self-contained ZIP package to integrate into HTML, WordPress, PHP, or custom portals.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section (Database-Driven, PRO Highlighted) -->
<section id="pricing" style="padding: 80px 0;">
    <div class="container">
        <div class="text-center" style="max-width: 640px; margin: 0 auto 48px;">
            <span class="badge badge-primary" style="margin-bottom: 8px;">Transparent Pricing</span>
            <h2>Subscription Plans Built for Scale</h2>
            <p class="text-muted" style="margin-top: 8px;">Start on our Free tier and scale seamlessly with our verified PayPal recurring subscriptions.</p>
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
                                <?= $isPro ? 'Upgrade to Pro' : 'Select Plan' ?>
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

<!-- FAQs Section -->
<section style="padding: 60px 0 80px; background: #ffffff; border-top: 1px solid var(--color-border);">
    <div class="container" style="max-width: 800px;">
        <div class="text-center" style="margin-bottom: 40px;">
            <h2>Frequently Asked Questions</h2>
            <p class="text-muted" style="margin-top: 8px;">Everything you need to know about ChatBot Creator Studio.</p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            <div class="card" style="padding: 20px;">
                <h4>Do I need coding experience to build chatbots?</h4>
                <p class="text-muted text-sm" style="margin-top: 8px; line-height: 1.6;">
                    No coding is required whatsoever. You can customize the bot's behavior, personality, appearance, and knowledge base entirely using intuitive form fields and file upload tools.
                </p>
            </div>
            <div class="card" style="padding: 20px;">
                <h4>How does the knowledge retrieval engine work?</h4>
                <p class="text-muted text-sm" style="margin-top: 8px; line-height: 1.6;">
                    The platform uses a hybrid knowledge retrieval mechanism combining MySQL full-text searching, keyword token matching, and optional OpenAI-compatible AI provider adapters. If no AI provider is configured, the system provides accurate knowledge-based answers without third-party dependencies.
                </p>
            </div>
            <div class="card" style="padding: 20px;">
                <h4>Can I embed my chatbots on WordPress, Shopify, or custom websites?</h4>
                <p class="text-muted text-sm" style="margin-top: 8px; line-height: 1.6;">
                    Yes! Every chatbot receives a responsive script tag that works on any website. You can also download a self-contained ZIP package with full HTML and PHP examples.
                </p>
            </div>
            <div class="card" style="padding: 20px;">
                <h4>What payment methods are supported?</h4>
                <p class="text-muted text-sm" style="margin-top: 8px; line-height: 1.6;">
                    We integrate with official PayPal recurring subscriptions. All subscriptions are verified server-side with cryptographic webhook signature processing.
                </p>
            </div>
        </div>
    </div>
</section>
