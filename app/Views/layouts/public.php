<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'ChatBot Creator Studio - Create Powerful Chatbots Without Coding') ?></title>
    <meta name="description" content="Create, customize, manage, and deploy AI chatbots for your website, store, or community without writing code.">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <!-- Public Navigation Bar -->
    <header class="public-header">
        <div class="container public-nav">
            <a href="<?= url('/') ?>" class="logo">
                <svg viewBox="0 0 24 24"><path d="M12 2a2 2 0 0 1 2 2v2h4a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-1.17l-1.42 2.83A2 2 0 0 1 13.62 22H10.38a2 2 0 0 1-1.79-1.17L7.17 18H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4V4a2 2 0 0 1 2-2zm-3 8a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm6 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/></svg>
                <span>ChatBot Creator</span>
            </a>

            <nav class="nav-links">
                <a href="<?= url('/features') ?>">Features</a>
                <a href="<?= url('/how-it-works') ?>">How It Works</a>
                <a href="<?= url('/pricing') ?>">Pricing</a>
                <a href="<?= url('/about') ?>">About</a>
                <a href="<?= url('/faq') ?>">FAQ</a>
            </nav>

            <div class="flex items-center gap-3">
                <?php if (auth_user()): ?>
                    <a href="<?= url('/studio') ?>" class="btn btn-primary btn-sm">My Studio</a>
                <?php else: ?>
                    <a href="<?= url('/login') ?>" class="btn btn-secondary btn-sm">Log In</a>
                    <a href="<?= url('/register') ?>" class="btn btn-primary btn-sm">Get Started Free</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container" style="padding-top: 20px;">
            <?php include dirname(__DIR__) . '/components/flash.php'; ?>
        </div>
        <?= $pageContent ?? '' ?>
    </main>

    <!-- Public Footer -->
    <footer class="public-footer">
        <div class="container">
            <div class="grid grid-4 gap-8" style="margin-bottom: 48px;">
                <div>
                    <div class="logo" style="color: #ffffff; margin-bottom: 16px;">
                        <svg viewBox="0 0 24 24"><path d="M12 2a2 2 0 0 1 2 2v2h4a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-1.17l-1.42 2.83A2 2 0 0 1 13.62 22H10.38a2 2 0 0 1-1.79-1.17L7.17 18H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4V4a2 2 0 0 1 2-2zm-3 8a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm6 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/></svg>
                        <span>ChatBot Creator</span>
                    </div>
                    <p class="text-sm" style="line-height: 1.6;">
                        Empowering individuals and businesses to build, customize, and deploy AI chatbots across websites without writing code.
                    </p>
                </div>
                <div>
                    <h5 style="color: #ffffff; margin-bottom: 16px;">Product</h5>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px;" class="text-sm">
                        <li><a href="<?= url('/features') ?>">Features Overview</a></li>
                        <li><a href="<?= url('/how-it-works') ?>">How It Works</a></li>
                        <li><a href="<?= url('/pricing') ?>">Subscription Plans</a></li>
                        <li><a href="<?= url('/register') ?>">Free Tier</a></li>
                    </ul>
                </div>
                <div>
                    <h5 style="color: #ffffff; margin-bottom: 16px;">Company</h5>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px;" class="text-sm">
                        <li><a href="<?= url('/about') ?>">About Us</a></li>
                        <li><a href="<?= url('/contact') ?>">Contact & Support</a></li>
                        <li><a href="<?= url('/faq') ?>">Help & FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h5 style="color: #ffffff; margin-bottom: 16px;">Legal</h5>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px;" class="text-sm">
                        <li><a href="<?= url('/privacy') ?>">Privacy Policy</a></li>
                        <li><a href="<?= url('/terms') ?>">Terms of Service</a></li>
                        <li><a href="<?= url('/contact') ?>">Security & Compliance</a></li>
                    </ul>
                </div>
            </div>

            <div style="border-top: 1px solid #1e293b; padding-top: 24px;" class="flex justify-between items-center text-xs text-muted flex-wrap gap-4">
                <p>&copy; <?= date('Y') ?> ChatBot Creator Studio. All rights reserved. Zero Frameworks. Pure Native PHP 8.3+.</p>
                <p>Designed for Performance, Accessibility, and Multi-Tenant Isolation.</p>
            </div>
        </div>
    </footer>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
