<div style="margin-bottom: 32px;">
    <h2>Resource Usage & Quotas</h2>
    <p class="text-muted text-sm">Real-time accounting of your plan quotas for the current billing cycle (<?= e($usage['period']) ?>).</p>
</div>

<div class="grid grid-3 gap-6" style="margin-bottom: 32px;">
    <div class="card">
        <div class="card-subtitle">Active Chatbots</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;">
            <?= e($usage['active_chatbots']) ?> / <?= (int)$plan['max_chatbots'] ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 6px;">Available slots for deployment</div>
    </div>

    <div class="card">
        <div class="card-subtitle">Monthly Messages</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;">
            <?= number_format($usage['monthly_messages']) ?> / <?= number_format((int)$plan['monthly_messages']) ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 6px;">Resets at start of each calendar month</div>
    </div>

    <div class="card">
        <div class="card-subtitle">Knowledge Entries</div>
        <div style="font-size: 2rem; font-weight: 800; margin-top: 6px;">
            <?= number_format($usage['total_knowledge_entries']) ?> / <?= number_format((int)$plan['max_knowledge_entries']) ?>
        </div>
        <div class="text-xs text-muted" style="margin-top: 6px;">Total FAQs across all assistants</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Historical Consumption Ledger</h3>
    </div>

    <?php if (empty($history)): ?>
        <p class="text-muted text-sm">No historical usage periods recorded yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Billing Cycle</th>
                        <th>Metric</th>
                        <th>Units Consumed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td style="font-weight: 600;"><?= e($h['period_year_month']) ?></td>
                            <td><?= e(ucwords(str_replace('_', ' ', $h['metric_key']))) ?></td>
                            <td style="font-weight: 700;"><?= number_format((int)$h['metric_value']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
