<?php
$appUrl = rtrim(url('/'), '/');
$publicToken = $bot['public_token'];
$embedTag = '<script src="' . $appUrl . '/embed/' . $publicToken . '.js" defer></script>';
$standaloneUrl = url("/b/{$bot['slug']}");
?>

<div style="max-width: 900px; margin: 0 auto;">
    <div class="flex justify-between items-center" style="margin-bottom: 24px;">
        <div>
            <h2>Embed & Integration: <?= e($bot['name']) ?></h2>
            <p class="text-muted text-sm">Deploy your assistant to any website, CMS, or standalone landing page.</p>
        </div>
        <a href="<?= url("/b/{$bot['slug']}") ?>" target="_blank" class="btn btn-secondary btn-sm">Preview Public Page &rarr;</a>
    </div>

    <!-- Option A: CDN Embed Snippet -->
    <div class="card" style="margin-bottom: 28px;">
        <div class="card-header">
            <div>
                <span class="badge badge-primary">Option A</span>
                <h3 class="card-title" style="margin-top: 4px;">CDN One-Line Script Embed</h3>
            </div>
            <button class="btn btn-primary btn-sm" data-copy="<?= e($embedTag) ?>">Copy Embed Code</button>
        </div>

        <p class="text-muted text-sm" style="margin-bottom: 14px;">
            Paste this single script tag into the HTML of your website before the closing <code>&lt;/body&gt;</code> tag. Compatible with HTML, WordPress, Shopify, PHP, and all CMS platforms.
        </p>

        <pre style="background: #0f172a; color: #38bdf8; padding: 16px; border-radius: var(--radius-md); font-family: monospace; font-size: 13px; overflow-x: auto;"><?= e($embedTag) ?></pre>
    </div>

    <!-- Option B: Downloadable ZIP Package -->
    <div class="card" style="margin-bottom: 28px;">
        <div class="card-header">
            <div>
                <span class="badge badge-success">Option B</span>
                <h3 class="card-title" style="margin-top: 4px;">Self-Hosted / Offline ZIP Package</h3>
            </div>
            <a href="<?= url("/studio/bots/{$bot['id']}/export/zip") ?>" class="btn btn-secondary btn-sm">Download ZIP Archive</a>
        </div>

        <p class="text-muted text-sm" style="line-height: 1.6;">
            Download a server-generated ZIP package containing <code>widget.js</code>, <code>widget.css</code>, <code>config.json</code>, a detailed <code>README.md</code>, and working <code>html-example.html</code> and <code>php-example.php</code> templates.
        </p>
    </div>

    <!-- Option C: Standalone Shareable Link -->
    <div class="card" style="margin-bottom: 28px;">
        <div class="card-header">
            <div>
                <span class="badge badge-neutral">Option C</span>
                <h3 class="card-title" style="margin-top: 4px;">Direct Vanity Link</h3>
            </div>
            <button class="btn btn-secondary btn-sm" data-copy="<?= e($standaloneUrl) ?>">Copy Link</button>
        </div>

        <p class="text-muted text-sm" style="margin-bottom: 14px;">
            Share this dedicated link with customers, clients, or on social media for direct full-screen conversations:
        </p>

        <input type="text" class="form-control" value="<?= e($standaloneUrl) ?>" readonly>
    </div>

    <!-- Domain Whitelist Security -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Security: Permitted Website Domains</h3>
        </div>

        <p class="text-muted text-sm" style="margin-bottom: 16px;">
            Restrict where this widget can be embedded. If configured, requests from unauthorized host origins are denied by our server-side security layer.
        </p>

        <form action="<?= url("/studio/bots/{$bot['id']}/embed") ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label">Visibility Policy</label>
                <select name="visibility" class="form-control">
                    <option value="public" <?= ($bot['visibility'] ?? '') === 'public' ? 'selected' : '' ?>>Public (Allowed on any website)</option>
                    <option value="domain_restricted" <?= ($bot['visibility'] ?? '') === 'domain_restricted' ? 'selected' : '' ?>>Domain Restricted (Only permitted domains)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Allowed Domains (One per line)</label>
                <?php
                $domainsStr = !empty($bot['allowed_domains']) ? implode("\n", $bot['allowed_domains']) : '';
                ?>
                <textarea name="allowed_domains" class="form-control" rows="3" placeholder="example.com&#10;app.example.com"><?= e($domainsStr) ?></textarea>
                <div class="form-help">Enter domains without http:// or trailing slashes.</div>
            </div>

            <button type="submit" class="btn btn-primary">Save Domain Restrictions</button>
        </form>
    </div>
</div>
