<div class="flex justify-between items-center" style="margin-bottom: 24px;">
    <div>
        <h2>Chatbot Moderation</h2>
        <p class="text-muted text-sm">Review, moderate, and suspend chatbots across the entire platform.</p>
    </div>
</div>

<div class="card" style="margin-bottom: 24px;">
    <form action="<?= url('/admin/bots') ?>" method="GET" class="flex gap-4 items-center flex-wrap">
        <input type="text" name="q" class="form-control" style="max-width: 300px;" placeholder="Search bot name or slug..." value="<?= e($search) ?>">
        <select name="category" class="form-control" style="max-width: 180px;">
            <option value="">All Categories</option>
            <option value="business" <?= $category === 'business' ? 'selected' : '' ?>>Business</option>
            <option value="support" <?= $category === 'support' ? 'selected' : '' ?>>Support</option>
            <option value="education" <?= $category === 'education' ? 'selected' : '' ?>>Education</option>
            <option value="personal" <?= $category === 'personal' ? 'selected' : '' ?>>Personal</option>
            <option value="real_estate" <?= $category === 'real_estate' ? 'selected' : '' ?>>Real Estate</option>
            <option value="ecommerce" <?= $category === 'ecommerce' ? 'selected' : '' ?>>E-Commerce</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <?php if ($search || $category): ?>
            <a href="<?= url('/admin/bots') ?>" class="btn btn-secondary btn-sm">Reset</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Chatbot</th>
                    <th>Owner</th>
                    <th>Category</th>
                    <th>FAQs</th>
                    <th>Status</th>
                    <th>Moderation Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bots as $b): ?>
                    <tr>
                        <td>
                            <strong><?= e($b['name']) ?></strong>
                            <div class="text-xs text-muted">/b/<?= e($b['slug']) ?></div>
                        </td>
                        <td>
                            <div class="text-sm"><?= e($b['owner_name']) ?></div>
                            <div class="text-xs text-muted"><?= e($b['owner_email']) ?></div>
                        </td>
                        <td><?= e(ucfirst($b['category'])) ?></td>
                        <td><?= (int)$b['knowledge_count'] ?></td>
                        <td>
                            <span class="badge <?= $b['status'] === 'published' ? 'badge-success' : ($b['status'] === 'suspended' ? 'badge-danger' : 'badge-neutral') ?>">
                                <?= e(ucfirst($b['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <form action="<?= url('/admin/bots/status') ?>" method="POST" class="flex gap-2 items-center" style="margin: 0;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="bot_id" value="<?= e($b['id']) ?>">
                                <?php if ($b['status'] === 'suspended'): ?>
                                    <button type="submit" name="status" value="draft" class="btn btn-secondary btn-sm" style="padding: 3px 8px; font-size: 11px;">Unsuspend</button>
                                <?php else: ?>
                                    <button type="submit" name="status" value="suspended" class="btn btn-danger btn-sm" style="padding: 3px 8px; font-size: 11px;" onclick="return confirm('Suspend this chatbot?')">Suspend</button>
                                <?php endif; ?>
                                <a href="<?= url("/b/{$b['slug']}") ?>" target="_blank" class="btn btn-secondary btn-sm" style="padding: 3px 8px; font-size: 11px;">View &rarr;</a>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
