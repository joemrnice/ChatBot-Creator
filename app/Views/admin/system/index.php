<div style="margin-bottom: 28px;">
    <h2>System Health & Diagnostics</h2>
    <p class="text-muted text-sm">Server environment, PHP 8.3+ compatibility, extension checks, directory permissions, and external integrations.</p>
</div>

<div class="grid grid-3 gap-6" style="margin-bottom: 32px;">
    <div class="card">
        <div class="card-subtitle">PHP Version</div>
        <div style="font-size: 1.5rem; font-weight: 800; margin-top: 6px;"><?= e($phpVersion) ?></div>
        <div class="text-xs text-muted" style="margin-top: 4px;">Memory Usage: <?= e($memoryUsage) ?></div>
    </div>

    <div class="card">
        <div class="card-subtitle">Database Engine</div>
        <div style="font-size: 1.5rem; font-weight: 800; margin-top: 6px;">MySQL / PDO</div>
        <div class="text-xs text-muted" style="margin-top: 4px;"><?= e($dbVersion) ?></div>
    </div>

    <div class="card">
        <div class="card-subtitle">Architecture Mode</div>
        <div style="font-size: 1.5rem; font-weight: 800; margin-top: 6px; color: var(--color-primary);">Native PHP 8.3+</div>
        <div class="text-xs text-muted" style="margin-top: 4px;">Zero Frameworks &bull; High Performance</div>
    </div>
</div>

<div class="grid grid-2 gap-8" style="margin-bottom: 32px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Required PHP Extensions</h3>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Extension</th>
                    <th>Required For</th>
                    <th style="text-align: right;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $extDescriptions = [
                    'pdo' => 'Database connectivity',
                    'pdo_mysql' => 'MySQL storage engine',
                    'curl' => 'PayPal API & external AI providers',
                    'json' => 'Data imports & API serialization',
                    'openssl' => 'AES-256 encryption & token signing',
                    'zip' => 'Downloadable integration packages',
                    'mbstring' => 'UTF-8 multibyte string safety',
                ];
                foreach ($extensions as $ext => $loaded):
                ?>
                    <tr>
                        <td class="font-semibold"><code>ext-<?= e($ext) ?></code></td>
                        <td class="text-xs text-muted"><?= e($extDescriptions[$ext] ?? 'Core system') ?></td>
                        <td style="text-align: right;">
                            <span class="badge <?= $loaded ? 'badge-success' : 'badge-danger' ?>">
                                <?= $loaded ? 'Available' : 'Missing' ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Storage Directory Permissions</h3>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Directory</th>
                    <th>Purpose</th>
                    <th style="text-align: right;">Writable</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $pathPurposes = [
                    'storage/logs' => 'Application & error logs',
                    'storage/cache' => 'Temporary cached queries',
                    'storage/uploads' => 'Import files & user assets',
                    'storage/exports' => 'Generated ZIP bundles',
                    'storage/private' => 'Isolated secure storage',
                ];
                foreach ($storagePaths as $path => $writable):
                ?>
                    <tr>
                        <td class="font-semibold"><code><?= e($path) ?></code></td>
                        <td class="text-xs text-muted"><?= e($pathPurposes[$path] ?? 'Storage') ?></td>
                        <td style="text-align: right;">
                            <span class="badge <?= $writable ? 'badge-success' : 'badge-danger' ?>">
                                <?= $writable ? 'Writable (OK)' : 'Not Writable' ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">External Integrations Configuration</h3>
        <a href="<?= url('/admin/settings') ?>" class="btn btn-secondary btn-sm">Configure API Secrets &rarr;</a>
    </div>
    <div class="grid grid-3 gap-4" style="margin-top: 12px;">
        <div style="border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 16px;">
            <div class="font-semibold">PayPal Subscriptions</div>
            <div class="text-xs text-muted" style="margin-top: 4px;">Mode: <?= e(ucfirst($integrations['paypal']['mode'])) ?></div>
            <div style="margin-top: 8px;">
                <span class="badge <?= $integrations['paypal']['configured'] ? 'badge-success' : 'badge-warning' ?>">
                    <?= $integrations['paypal']['configured'] ? 'Configured' : 'Credentials Missing' ?>
                </span>
            </div>
        </div>

        <div style="border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 16px;">
            <div class="font-semibold">Social Authentication</div>
            <div class="text-xs text-muted" style="margin-top: 4px;">GitHub, Google, Facebook OAuth</div>
            <div style="margin-top: 8px;">
                <span class="badge <?= ($integrations['github']['configured'] || $integrations['google']['configured']) ? 'badge-success' : 'badge-neutral' ?>">
                    <?= ($integrations['github']['configured'] || $integrations['google']['configured']) ? 'Active Providers' : 'Optional / Ready' ?>
                </span>
            </div>
        </div>

        <div style="border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 16px;">
            <div class="font-semibold">AI Conversation Engine</div>
            <div class="text-xs text-muted" style="margin-top: 4px;">Default: <?= e(ucfirst(str_replace('_', ' ', $integrations['ai_provider']['provider']))) ?></div>
            <div style="margin-top: 8px;">
                <span class="badge badge-success">
                    Native FAQ & AI Ready
                </span>
            </div>
        </div>
    </div>
</div>
