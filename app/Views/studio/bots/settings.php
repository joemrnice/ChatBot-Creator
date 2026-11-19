<div style="max-width: 800px; margin: 0 auto;">
    <div class="flex justify-between items-center" style="margin-bottom: 24px;">
        <div>
            <h2>Bot Settings: <?= e($bot['name']) ?></h2>
            <p class="text-muted text-sm">General configuration and lifecycle management.</p>
        </div>
        <a href="<?= url("/studio/bots/{$bot['id']}/builder") ?>" class="btn btn-secondary btn-sm">&larr; Back to Builder</a>
    </div>

    <!-- General Settings Form -->
    <form action="<?= url("/studio/bots/{$bot['id']}/settings") ?>" method="POST" class="card" style="margin-bottom: 32px;">
        <?= csrf_field() ?>

        <div class="card-header">
            <h3 class="card-title">General Settings</h3>
        </div>

        <div class="form-group">
            <label class="form-label">Chatbot Name</label>
            <input type="text" name="name" class="form-control" value="<?= e($bot['name']) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Description / Internal Purpose</label>
            <input type="text" name="description" class="form-control" value="<?= e($bot['description'] ?? '') ?>">
        </div>

        <div class="grid grid-2 gap-4">
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category" class="form-control">
                    <option value="business" <?= $bot['category'] === 'business' ? 'selected' : '' ?>>Business & Sales</option>
                    <option value="support" <?= $bot['category'] === 'support' ? 'selected' : '' ?>>Customer Support</option>
                    <option value="education" <?= $bot['category'] === 'education' ? 'selected' : '' ?>>Education & Tutoring</option>
                    <option value="personal" <?= $bot['category'] === 'personal' ? 'selected' : '' ?>>Personal Assistant</option>
                    <option value="real_estate" <?= $bot['category'] === 'real_estate' ? 'selected' : '' ?>>Real Estate</option>
                    <option value="ecommerce" <?= $bot['category'] === 'ecommerce' ? 'selected' : '' ?>>E-Commerce Store</option>
                    <option value="custom" <?= $bot['category'] === 'custom' ? 'selected' : '' ?>>Custom Purpose</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Primary Language</label>
                <select name="language" class="form-control">
                    <option value="en" <?= $bot['language'] === 'en' ? 'selected' : '' ?>>English</option>
                    <option value="es" <?= $bot['language'] === 'es' ? 'selected' : '' ?>>Spanish</option>
                    <option value="fr" <?= $bot['language'] === 'fr' ? 'selected' : '' ?>>French</option>
                    <option value="de" <?= $bot['language'] === 'de' ? 'selected' : '' ?>>German</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Publication Status</label>
            <select name="status" class="form-control">
                <option value="published" <?= $bot['status'] === 'published' ? 'selected' : '' ?>>Published (Live and accessible)</option>
                <option value="draft" <?= $bot['status'] === 'draft' ? 'selected' : '' ?>>Draft (Private / in development)</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>

    <!-- Danger Zone: Delete Bot -->
    <div class="card" style="border-color: #fecaca; background: #fff5f5;">
        <div class="card-header">
            <div>
                <h3 class="card-title" style="color: #dc2626;">Danger Zone</h3>
                <div class="card-subtitle" style="color: #991b1b;">Permanently delete this chatbot and all its conversations.</div>
            </div>
        </div>

        <p class="text-sm" style="color: #7f1d1d; margin-bottom: 20px; line-height: 1.5;">
            Once you delete a chatbot, its knowledge base entries, settings, and visitor conversation histories cannot be recovered.
        </p>

        <form action="<?= url("/studio/bots/{$bot['id']}/delete") ?>" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete <?= e(addslashes($bot['name'])) ?>? This action cannot be undone.');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger">Delete This Chatbot Permanently</button>
        </form>
    </div>
</div>
