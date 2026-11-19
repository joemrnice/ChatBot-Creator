<?php
$currentUser = auth_user();
$currentPath = \App\Core\Request::capture()->path();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Personal Studio - ChatBot Creator Studio') ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <div class="app-shell">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="<?= url('/studio') ?>" class="logo">
                    <svg viewBox="0 0 24 24"><path d="M12 2a2 2 0 0 1 2 2v2h4a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-1.17l-1.42 2.83A2 2 0 0 1 13.62 22H10.38a2 2 0 0 1-1.79-1.17L7.17 18H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4V4a2 2 0 0 1 2-2zm-3 8a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm6 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/></svg>
                    <span>Studio</span>
                </a>
            </div>

            <nav class="sidebar-menu">
                <div class="sidebar-heading">Workspace</div>
                <a href="<?= url('/studio') ?>" class="sidebar-item <?= $currentPath === '/studio' ? 'active' : '' ?>">
                    <span>📊</span>
                    <span>Overview</span>
                </a>
                <a href="<?= url('/studio/bots') ?>" class="sidebar-item <?= str_starts_with($currentPath, '/studio/bots') ? 'active' : '' ?>">
                    <span>🤖</span>
                    <span>My Chatbots</span>
                </a>
                <a href="<?= url('/studio/bots/create') ?>" class="sidebar-item <?= $currentPath === '/studio/bots/create' ? 'active' : '' ?>">
                    <span>✨</span>
                    <span>Create Chatbot</span>
                </a>
                <a href="<?= url('/studio/templates') ?>" class="sidebar-item <?= $currentPath === '/studio/templates' ? 'active' : '' ?>">
                    <span>📚</span>
                    <span>Templates</span>
                </a>

                <div class="sidebar-heading">Account & Billing</div>
                <a href="<?= url('/studio/usage') ?>" class="sidebar-item <?= $currentPath === '/studio/usage' ? 'active' : '' ?>">
                    <span>📈</span>
                    <span>Usage & Quotas</span>
                </a>
                <a href="<?= url('/studio/billing') ?>" class="sidebar-item <?= str_starts_with($currentPath, '/studio/billing') ? 'active' : '' ?>">
                    <span>💳</span>
                    <span>Billing & Plans</span>
                </a>
                <a href="<?= url('/studio/profile') ?>" class="sidebar-item <?= $currentPath === '/studio/profile' ? 'active' : '' ?>">
                    <span>👤</span>
                    <span>Profile</span>
                </a>
                <a href="<?= url('/studio/security') ?>" class="sidebar-item <?= $currentPath === '/studio/security' ? 'active' : '' ?>">
                    <span>🔒</span>
                    <span>Security</span>
                </a>
                <a href="<?= url('/studio/notifications') ?>" class="sidebar-item <?= $currentPath === '/studio/notifications' ? 'active' : '' ?>">
                    <span>🔔</span>
                    <span>Notifications</span>
                </a>

                <?php if (is_admin()): ?>
                    <div class="sidebar-heading">Administration</div>
                    <a href="<?= url('/admin') ?>" class="sidebar-item" style="color: #ea580c;">
                        <span>⚙️</span>
                        <span>Admin Data Center</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-sm"><?= e($currentUser['name'] ?? 'User') ?></div>
                        <div class="text-xs text-muted"><?= e($currentUser['plan_name'] ?? 'Free') ?> Plan</div>
                    </div>
                    <form action="<?= url('/logout') ?>" method="POST" style="margin: 0;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-secondary btn-sm" title="Log Out">🚪</button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Body -->
        <div class="app-main">
            <header class="app-topbar">
                <div class="flex items-center gap-3">
                    <button type="button" id="sidebar-toggle" class="btn btn-secondary btn-sm" style="display: none;">☰</button>
                    <h3 style="font-size: 1.125rem; font-weight: 700;"><?= e($title ?? 'Personal Studio') ?></h3>
                </div>
                <div class="flex items-center gap-3">
                    <span class="badge badge-primary"><?= e($currentUser['plan_name'] ?? 'Free') ?></span>
                    <a href="<?= url('/studio/bots/create') ?>" class="btn btn-primary btn-sm">+ New Bot</a>
                </div>
            </header>

            <main class="app-content">
                <?php include dirname(__DIR__) . '/components/flash.php'; ?>
                <?= $pageContent ?? '' ?>
            </main>
        </div>
    </div>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
