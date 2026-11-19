<div class="flex justify-between items-center" style="margin-bottom: 24px;">
    <div>
        <h2>Pre-Built Chatbot Templates</h2>
        <p class="text-muted text-sm">Jumpstart your conversational assistant with industry-tailored starter kits.</p>
    </div>
    <a href="<?= url('/studio/bots/create') ?>" class="btn btn-secondary btn-sm">Build from Scratch &rarr;</a>
</div>

<div class="grid grid-3 gap-6">
    <?php foreach ($templates as $tmpl): ?>
        <div class="card" style="display: flex; flex-direction: column;">
            <div class="flex justify-between items-start" style="margin-bottom: 12px;">
                <span class="badge badge-primary"><?= e(ucfirst($tmpl['category'])) ?></span>
                <?php if ($tmpl['is_featured']): ?>
                    <span class="badge badge-success">Featured</span>
                <?php endif; ?>
            </div>

            <h3 style="font-size: 1.2rem;"><?= e($tmpl['name']) ?></h3>
            <p class="text-muted text-sm" style="margin-top: 6px; line-height: 1.5; flex: 1;">
                <?= e($tmpl['description']) ?>
            </p>

            <div style="background: #f8fafc; border: 1px solid var(--color-border); border-radius: 8px; padding: 12px; margin: 16px 0; font-size: 12px; color: var(--color-text-muted);">
                <strong>Greeting:</strong> <?= e($tmpl['welcome_message']) ?>
            </div>

            <a href="<?= url('/studio/bots/create?template=' . $tmpl['id']) ?>" class="btn btn-primary btn-block btn-sm">
                Use This Template &rarr;
            </a>
        </div>
    <?php endforeach; ?>
</div>
