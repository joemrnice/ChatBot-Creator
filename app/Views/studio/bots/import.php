<div style="max-width: 900px; margin: 0 auto;">
    <div class="flex justify-between items-center" style="margin-bottom: 24px;">
        <div>
            <h2>Import Knowledge & Configuration</h2>
            <p class="text-muted text-sm">Bulk ingest questions, answers, and settings into <strong><?= e($bot['name']) ?></strong>.</p>
        </div>
        <a href="<?= url("/studio/bots/{$bot['id']}/knowledge") ?>" class="btn btn-secondary btn-sm">&larr; Back to Knowledge Base</a>
    </div>

    <!-- Step 1: Upload File -->
    <?php if (empty($preview)): ?>
        <div class="card" style="margin-bottom: 32px;">
            <div class="card-header">
                <h3 class="card-title">Step 1: Upload JSON or CSV File</h3>
                <div class="flex gap-2">
                    <a href="<?= url("/studio/bots/{$bot['id']}/import/sample-csv") ?>" class="btn btn-secondary btn-sm">Sample CSV</a>
                    <a href="<?= url("/studio/bots/{$bot['id']}/import/sample-json") ?>" class="btn btn-secondary btn-sm">Sample JSON</a>
                </div>
            </div>

            <form action="<?= url("/studio/bots/{$bot['id']}/import/upload") ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div style="border: 2px dashed var(--color-border); border-radius: var(--radius-lg); padding: 40px; text-align: center; background: #f8fafc; margin-bottom: 24px;">
                    <div style="font-size: 3rem; margin-bottom: 12px;">📁</div>
                    <h4>Select a JSON or CSV file from your computer</h4>
                    <p class="text-muted text-sm" style="margin-top: 6px; margin-bottom: 16px;">
                        Supported extensions: <code>.json</code> (structured configuration) and <code>.csv</code> (tabular Q&A)
                    </p>
                    <input type="file" name="import_file" accept=".json,.csv" required style="display: block; margin: 0 auto;">
                </div>

                <div class="flex justify-between items-center">
                    <div class="text-xs text-muted">
                        Max file size: 10MB &bull; Validated with strict schema checking before saving.
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">Upload & Generate Preview &rarr;</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Step 2: Interactive Preview & Mode Selection -->
        <div class="card" style="margin-bottom: 32px; border: 2px solid var(--color-primary);">
            <div class="card-header">
                <div>
                    <span class="badge badge-primary">Step 2: Review Preview & Confirm</span>
                    <h3 class="card-title" style="margin-top: 6px;">Parsed <?= e(strtoupper($preview['file_type'])) ?>: <?= e($preview['file_name']) ?></h3>
                </div>
                <a href="<?= url("/studio/bots/{$bot['id']}/import") ?>" class="btn btn-secondary btn-sm">Upload Different File</a>
            </div>

            <div class="grid grid-3 gap-4" style="margin-bottom: 24px;">
                <div style="background: #f8fafc; padding: 14px; border-radius: 8px; text-align: center;">
                    <div class="text-xs text-muted">Total Records</div>
                    <div style="font-size: 1.5rem; font-weight: 800;"><?= e($preview['total_count']) ?></div>
                </div>
                <div style="background: #ecfdf5; padding: 14px; border-radius: 8px; text-align: center;">
                    <div class="text-xs" style="color: #065f46;">Valid Ready to Import</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #065f46;"><?= e($preview['valid_count']) ?></div>
                </div>
                <div style="background: #fef2f2; padding: 14px; border-radius: 8px; text-align: center;">
                    <div class="text-xs" style="color: #991b1b;">Invalid / Skipped</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #991b1b;"><?= e($preview['invalid_count']) ?></div>
                </div>
            </div>

            <!-- Errors Report if any -->
            <?php if (!empty($preview['errors'])): ?>
                <div class="alert alert-warning" style="margin-bottom: 20px;">
                    <div>
                        <strong>Formatting Warnings in Uploaded File:</strong>
                        <ul style="padding-left: 20px; margin-top: 6px; font-size: 12px;">
                            <?php foreach (array_slice($preview['errors'], 0, 5) as $err): ?>
                                <li>Row <?= e($err['row']) ?>: <?= e($err['error']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Preview Records Table -->
            <h4 style="margin-bottom: 12px;">Sample Records Preview (First 10)</h4>
            <div class="table-responsive" style="max-height: 280px; overflow-y: auto; margin-bottom: 24px; border: 1px solid var(--color-border); border-radius: 8px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Question / Title</th>
                            <th>Answer / Content Preview</th>
                            <th>Category</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preview['preview_records'] as $r): ?>
                            <tr>
                                <td><span class="badge badge-neutral"><?= e(strtoupper($r['type'])) ?></span></td>
                                <td style="font-weight: 600;"><?= e($r['question'] ?: $r['title']) ?></td>
                                <td class="text-muted text-xs"><?= e(substr($r['answer'] ?: $r['content'], 0, 100)) ?>...</td>
                                <td><?= e($r['category'] ?: 'General') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Commit Form -->
            <form action="<?= url("/studio/bots/{$bot['id']}/import/confirm") ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="job_id" value="<?= e($preview['job_id']) ?>">

                <div class="form-group">
                    <label class="form-label">Select Import Mode</label>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label class="flex items-center gap-2" style="cursor: pointer;">
                            <input type="radio" name="mode" value="add" checked>
                            <span class="text-sm"><strong>Add:</strong> Append new records without modifying existing knowledge entries</span>
                        </label>
                        <label class="flex items-center gap-2" style="cursor: pointer;">
                            <input type="radio" name="mode" value="merge">
                            <span class="text-sm"><strong>Merge:</strong> Update existing FAQs that match by question, and insert any new ones</span>
                        </label>
                        <label class="flex items-center gap-2" style="cursor: pointer;">
                            <input type="radio" name="mode" value="replace">
                            <span class="text-sm" style="color: #dc2626;"><strong>Replace:</strong> Delete all current knowledge base items and replace entirely with this file</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-between items-center" style="margin-top: 24px; border-top: 1px solid var(--color-border); padding-top: 16px;">
                    <a href="<?= url("/studio/bots/{$bot['id']}/import") ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg">Commit Import to Chatbot Knowledge Base &rarr;</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Accepted Formats Documentation Card -->
    <div class="card">
        <h3 class="card-title">Accepted File Specifications</h3>
        <div class="grid grid-2 gap-6" style="margin-top: 16px;">
            <div>
                <h4>CSV Column Headers</h4>
                <p class="text-muted text-xs" style="margin-top: 4px; line-height: 1.6;">
                    Accepted header combinations:
                </p>
                <pre style="background: #f1f5f9; padding: 12px; border-radius: 6px; font-size: 11px; margin-top: 8px;">question,answer,category,tags
"What are your hours?","Mon-Fri 9am-5pm","General","hours,time"</pre>
            </div>
            <div>
                <h4>JSON Configuration Structure</h4>
                <p class="text-muted text-xs" style="margin-top: 4px; line-height: 1.6;">
                    Accepts structured schema with <code>bot</code>, <code>appearance</code>, and <code>knowledge</code> array:
                </p>
                <pre style="background: #f1f5f9; padding: 12px; border-radius: 6px; font-size: 11px; margin-top: 8px;">{
  "schema_version": "1.0",
  "knowledge": [
    {"type": "faq", "question": "...", "answer": "..."}
  ]
}</pre>
            </div>
        </div>
    </div>
</div>
