<div class="flex justify-between items-center" style="margin-bottom: 24px;">
    <div>
        <div class="flex items-center gap-3">
            <h2>Knowledge Base: <?= e($bot['name']) ?></h2>
            <span class="badge badge-primary"><?= e($currentCount) ?> / <?= e($maxKnowledge) ?> Entries</span>
        </div>
        <p class="text-muted text-sm">Teach your chatbot factual answers to common questions and customer inquiries.</p>
    </div>
    <div class="flex gap-2">
        <a href="<?= url("/studio/bots/{$bot['id']}/import") ?>" class="btn btn-primary btn-sm">📥 Import CSV / JSON</a>
        <a href="<?= url("/studio/bots/{$bot['id']}/export/csv") ?>" class="btn btn-secondary btn-sm">Export CSV</a>
    </div>
</div>

<div class="grid grid-2 gap-8 items-start">
    <!-- Form to Add Knowledge -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Knowledge Entry</h3>
            <span class="text-xs text-muted">Manual Entry</span>
        </div>

        <form action="<?= url("/studio/bots/{$bot['id']}/knowledge") ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label">Entry Type</label>
                <select name="type" id="kb-type" class="form-control" onchange="toggleKbFields()">
                    <option value="faq">FAQ (Question & Answer)</option>
                    <option value="document">Reference Document (Title & Content)</option>
                </select>
            </div>

            <!-- FAQ Fields -->
            <div id="faq-fields">
                <div class="form-group">
                    <label class="form-label">Question</label>
                    <input type="text" name="question" class="form-control" placeholder="e.g. What are your opening hours?">
                </div>

                <div class="form-group">
                    <label class="form-label">Answer</label>
                    <textarea name="answer" class="form-control" rows="3" placeholder="e.g. We are open Monday through Friday from 9 AM to 6 PM."></textarea>
                </div>
            </div>

            <!-- Document Fields -->
            <div id="doc-fields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Document Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Return Policy Guide">
                </div>

                <div class="form-group">
                    <label class="form-label">Document Content</label>
                    <textarea name="content" class="form-control" rows="4" placeholder="Full descriptive text content..."></textarea>
                </div>
            </div>

            <div class="grid grid-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control" placeholder="e.g. Sales, Policies">
                </div>

                <div class="form-group">
                    <label class="form-label">Tags (comma-separated)</label>
                    <input type="text" name="tags" class="form-control" placeholder="e.g. hours, schedule, time">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Add to Knowledge Base</button>
        </form>
    </div>

    <!-- Existing Knowledge Items List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Saved Knowledge (<?= count($items) ?>)</h3>
            <span class="text-xs text-muted">Searched on visitor query</span>
        </div>

        <?php if (empty($items)): ?>
            <div class="text-center" style="padding: 40px 20px;">
                <div style="font-size: 2.5rem; margin-bottom: 12px;">📖</div>
                <h4>No Knowledge Entries Yet</h4>
                <p class="text-muted text-sm" style="margin-top: 6px;">Add your first question & answer on the left or use the CSV/JSON importer above.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 14px; max-height: 520px; overflow-y: auto;">
                <?php foreach ($items as $item): ?>
                    <div style="padding: 14px; border: 1px solid var(--color-border); border-radius: var(--radius-md); background: #ffffff;">
                        <div class="flex justify-between items-start" style="margin-bottom: 6px;">
                            <span class="badge badge-primary"><?= e(strtoupper($item['type'])) ?></span>
                            <form action="<?= url("/studio/bots/{$bot['id']}/knowledge/delete") ?>" method="POST" style="margin: 0;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="item_id" value="<?= e($item['id']) ?>">
                                <button type="submit" class="btn btn-secondary btn-sm" style="padding: 2px 8px; font-size: 11px; color: #ef4444;" title="Delete Item" onclick="return confirm('Delete this knowledge item?')">✕</button>
                            </form>
                        </div>

                        <?php if ($item['type'] === 'faq'): ?>
                            <div class="font-semibold text-sm" style="color: var(--color-secondary);">
                                Q: <?= e($item['question']) ?>
                            </div>
                            <div class="text-sm text-muted" style="margin-top: 4px;">
                                A: <?= e($item['answer']) ?>
                            </div>
                        <?php else: ?>
                            <div class="font-semibold text-sm" style="color: var(--color-secondary);">
                                <?= e($item['title']) ?>
                            </div>
                            <div class="text-sm text-muted" style="margin-top: 4px;">
                                <?= e(substr($item['content'], 0, 150)) ?>...
                            </div>
                        <?php endif; ?>

                        <div class="flex justify-between items-center text-xs text-muted" style="margin-top: 10px; border-top: 1px solid #f1f5f9; padding-top: 6px;">
                            <span>Cat: <?= e($item['category'] ?: 'General') ?> &bull; Tags: <?= e($item['tags'] ?: 'none') ?></span>
                            <span>Hits: <?= (int)$item['hits_count'] ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleKbFields() {
    const type = document.getElementById('kb-type').value;
    const faqFields = document.getElementById('faq-fields');
    const docFields = document.getElementById('doc-fields');

    if (type === 'faq') {
        faqFields.style.display = 'block';
        docFields.style.display = 'none';
    } else {
        faqFields.style.display = 'none';
        docFields.style.display = 'block';
    }
}
</script>
