<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sign In - ChatBot Creator Studio') ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            padding: 40px;
            box-shadow: var(--shadow-lg);
        }
        .oauth-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0;
            color: var(--color-text-subtle);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .oauth-divider::before, .oauth-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--color-border);
        }
        .oauth-divider::before { margin-right: 12px; }
        .oauth-divider::after { margin-left: 12px; }
        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 10px 16px;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--color-text);
            background: #ffffff;
            text-decoration: none;
            transition: all var(--transition-fast);
        }
        .social-btn:hover {
            background: #f8fafc;
            border-color: var(--color-border-dark);
            color: var(--color-secondary);
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="text-center" style="margin-bottom: 28px;">
                <a href="<?= url('/') ?>" class="logo justify-center" style="margin-bottom: 12px;">
                    <svg viewBox="0 0 24 24"><path d="M12 2a2 2 0 0 1 2 2v2h4a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-1.17l-1.42 2.83A2 2 0 0 1 13.62 22H10.38a2 2 0 0 1-1.79-1.17L7.17 18H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4V4a2 2 0 0 1 2-2zm-3 8a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm6 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/></svg>
                    <span>ChatBot Creator</span>
                </a>
                <h3 style="font-size: 1.5rem; font-weight: 700;"><?= e($title ?? 'Account Access') ?></h3>
            </div>

            <?php include dirname(__DIR__) . '/components/flash.php'; ?>

            <?= $pageContent ?? '' ?>
        </div>
    </div>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
