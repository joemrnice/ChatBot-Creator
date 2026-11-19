<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'ChatBot Creator Studio') ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="icon" href="<?= asset('images/favicon.ico') ?>" type="image/x-icon">
</head>
<body>
    <header class="public-header">
        <div class="container public-nav">
            <a href="<?= url('/') ?>" class="logo">
                <svg viewBox="0 0 24 24"><path d="M12 2a2 2 0 0 1 2 2v2h4a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-1.17l-1.42 2.83A2 2 0 0 1 13.62 22H10.38a2 2 0 0 1-1.79-1.17L7.17 18H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4V4a2 2 0 0 1 2-2zm-3 8a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm6 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/></svg>
                <span>ChatBot Creator</span>
            </a>
            <div class="flex items-center gap-4">
                <?php if (auth_user()): ?>
                    <a href="<?= url('/studio') ?>" class="btn btn-primary btn-sm">Open Studio</a>
                <?php else: ?>
                    <a href="<?= url('/login') ?>" class="btn btn-secondary btn-sm">Sign In</a>
                    <a href="<?= url('/register') ?>" class="btn btn-primary btn-sm">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container" style="padding-top: 40px; padding-bottom: 60px;">
        <?php include dirname(__DIR__) . '/components/flash.php'; ?>
        <?= $pageContent ?? '' ?>
    </main>

    <footer class="public-footer">
        <div class="container text-center text-sm">
            <p>&copy; <?= date('Y') ?> ChatBot Creator Studio. All rights reserved.</p>
        </div>
    </footer>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
