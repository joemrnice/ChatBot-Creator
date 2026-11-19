<?php
$currentUser = auth_user();
$currentPath = \App\Core\Request::capture()->path();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin Center - ChatBot Creator Studio') ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        .admin-sidebar { background: #0f172a; color: #cbd5e1; border-right: 1px solid #1e293b; }
        .admin-sidebar .sidebar-header { border-bottom: 1px solid #1e293b; }
        .admin-sidebar .sidebar-heading { color: #64748b; }
        .admin-sidebar .sidebar-item { color: #94a3b8; }
        .admin-sidebar .sidebar-item:hover { background: #1e293b; color: #ffffff; }
        .admin-sidebar .sidebar-item.active { background: var(--color-primary); color: #ffffff; }
        .admin-sidebar .sidebar-footer { border-top: 1px solid #1e293b; }
    </style>
</head>
<body>
    <div class="app-shell">
        <!-- Admin Sidebar -->
        <aside class="sidebar admin-sidebar">
            <div class="sidebar-header">
                <a href="<?= url('/admin') ?>" class="logo" style="color: #ffffff;">
                    <svg viewBox="0 0 24 24"><path d="M12 2a2 2 0 0 1 2 2v2h4a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-1.17l-1.42 2.83A2 2 0 0 1 13.62 22H10.38a2 2 0 0 1-1.79-1.17L7.17 18H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4V4a2 2 0 0 1 2-2zm-3 8a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm6 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/></svg>
                    <span>Admin Center</span>
                </a>
            </div>

            <nav class="sidebar-menu">
                <div class="sidebar-heading">Data Center</div>
                <a href="<?= url('/admin') ?>" class="sidebar-item <?= $currentPath === '/admin' ? 'active' : '' ?>">
                    <span>📊</span>
                    <span>Dashboard & MRR</span>
                </a>
                <a href="<?= url('/admin/users') ?>" class="sidebar-item <?= str_starts_with($currentPath, '/admin/users') ? 'active' : '' ?>">
                    <span>👥</span>
                    <span>User Management</span>
                </a>
                <a href="<?= url('/admin/bots') ?>" class="sidebar-item <?= str_starts_with($currentPath, '/admin/bots') ? 'active' : '' ?>">
                    <span>🤖</span>
                    <span>Chatbot Moderation</span>
                </a>
                <a href="<?= url('/admin/usage') ?>" class="sidebar-item <?= $currentPath === '/admin/usage' ? 'active' : '' ?>">
                    <span>📈</span>
                    <span>Platform Usage</span>
                </a>
                <a href="<?= url('/admin/analytics') ?>" class="sidebar-item <?= $currentPath === '/admin/analytics' ? 'active' : '' ?>">
                    <span>📉</span>
                    <span>Analytics</span>
                </a>

                <div class="sidebar-heading">Billing & Revenue</div>
                <a href="<?= url('/admin/plans') ?>" class="sidebar-item <?= str_starts_with($currentPath, '/admin/plans') || $currentPath === '/admin/subscriptions' ? 'active' : '' ?>">
                    <span>📦</span>
                    <span>Plans & Limits</span>
                </a>
                <a href="<?= url('/admin/payments') ?>" class="sidebar-item <?= str_starts_with($currentPath, '/admin/payments') || $currentPath === '/admin/transactions' ? 'active' : '' ?>">
                    <span>💳</span>
                    <span>Payment Transactions</span>
                </a>
                <a href="<?= url('/admin/webhooks') ?>" class="sidebar-item <?= str_starts_with($currentPath, '/admin/webhooks') ? 'active' : '' ?>">
                    <span>⚡</span>
                    <span>PayPal Webhooks</span>
                </a>

                <div class="sidebar-heading">System & Security</div>
                <a href="<?= url('/admin/roles') ?>" class="sidebar-item <?= $currentPath === '/admin/roles' ? 'active' : '' ?>">
                    <span>🔑</span>
                    <span>Roles & Permissions</span>
                </a>
                <a href="<?= url('/admin/settings') ?>" class="sidebar-item <?= str_starts_with($currentPath, '/admin/settings') ? 'active' : '' ?>">
                    <span>⚙️</span>
                    <span>System Settings</span>
                </a>
                <a href="<?= url('/admin/security') ?>" class="sidebar-item <?= $currentPath === '/admin/security' ? 'active' : '' ?>">
                    <span>🔒</span>
                    <span>Security Center</span>
                </a>
                <a href="<?= url('/admin/system') ?>" class="sidebar-item <?= $currentPath === '/admin/system' ? 'active' : '' ?>">
                    <span>🩺</span>
                    <span>System Diagnostics</span>
                </a>
                <a href="<?= url('/admin/audit-logs') ?>" class="sidebar-item <?= str_starts_with($currentPath, '/admin/audit-logs') ? 'active' : '' ?>">
                    <span>🛡️</span>
                    <span>Security Audit Logs</span>
                </a>
                <a href="<?= url('/admin/support') ?>" class="sidebar-item <?= str_starts_with($currentPath, '/admin/support') ? 'active' : '' ?>">
                    <span>🎫</span>
                    <span>Support Tickets</span>
                </a>

                <div class="sidebar-heading">Switch View</div>
                <a href="<?= url('/studio') ?>" class="sidebar-item">
                    <span>↩️</span>
                    <span>Back to User Studio</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-sm" style="color: #ffffff;"><?= e($currentUser['name'] ?? 'Admin') ?></div>
                        <div class="text-xs" style="color: #ea580c;">Administrator</div>
                    </div>
                    <form action="<?= url('/logout') ?>" method="POST" style="margin: 0;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-secondary btn-sm" style="background: #1e293b; color: #fff; border-color: #334155;" title="Log Out">🚪</button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Body -->
        <div class="app-main">
            <header class="app-topbar">
                <div class="flex items-center gap-3">
                    <span class="badge badge-warning">Admin Mode</span>
                    <h3 style="font-size: 1.125rem; font-weight: 700;"><?= e($title ?? 'Admin Center') ?></h3>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= url('/studio') ?>" class="btn btn-secondary btn-sm">User Studio</a>
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
