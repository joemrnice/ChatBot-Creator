<div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 32px;">
        <h2>Chatbot Creation Wizard</h2>
        <p class="text-muted text-sm">Design, customize, and configure your assistant in 5 simple steps.</p>
    </div>

    <!-- Template Pre-selection Picker -->
    <?php if (!empty($templates)): ?>
        <div class="card" style="margin-bottom: 28px; background: #eff6ff; border-color: #bfdbfe;">
            <div class="card-subtitle" style="color: #1e40af; font-weight: 700;">PRO TIP: START WITH A TEMPLATE</div>
            <p class="text-sm" style="color: #1e40af; margin-top: 4px; margin-bottom: 14px;">
                Selecting a template pre-populates greetings, personality, suggested questions, and sample knowledge items.
            </p>
            <div class="flex gap-2 flex-wrap">
                <?php foreach ($templates as $tmpl): ?>
                    <a href="<?= url('/studio/bots/create?template=' . $tmpl['id']) ?>" 
                       class="btn <?= ($selectedTemplate && $selectedTemplate['id'] == $tmpl['id']) ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                        <?= e($tmpl['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <form action="<?= url('/studio/bots') ?>" method="POST" class="card">
        <?= csrf_field() ?>
        <?php if ($selectedTemplate): ?>
            <input type="hidden" name="template_id" value="<?= e($selectedTemplate['id']) ?>">
        <?php endif; ?>

        <!-- Step 1: Basic Information -->
        <h3 style="font-size: 1.15rem; border-bottom: 1px solid var(--color-border); padding-bottom: 10px; margin-bottom: 20px;">
            Step 1: Basic Information
        </h3>

        <div class="grid grid-2 gap-4">
            <div class="form-group">
                <label class="form-label">Chatbot Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Sales Concierge" 
                       value="<?= e($selectedTemplate['name'] ?? old('name')) ?>" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category" class="form-control">
                    <option value="business" <?= ($selectedTemplate['category'] ?? '') === 'business' ? 'selected' : '' ?>>Business & Sales</option>
                    <option value="support" <?= ($selectedTemplate['category'] ?? '') === 'support' ? 'selected' : '' ?>>Customer Support</option>
                    <option value="education" <?= ($selectedTemplate['category'] ?? '') === 'education' ? 'selected' : '' ?>>Academic & Tutoring</option>
                    <option value="personal" <?= ($selectedTemplate['category'] ?? '') === 'personal' ? 'selected' : '' ?>>Personal Assistant</option>
                    <option value="real_estate" <?= ($selectedTemplate['category'] ?? '') === 'real_estate' ? 'selected' : '' ?>>Real Estate</option>
                    <option value="ecommerce" <?= ($selectedTemplate['category'] ?? '') === 'ecommerce' ? 'selected' : '' ?>>E-Commerce Store</option>
                    <option value="custom">Custom Purpose</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Description / Purpose</label>
            <input type="text" name="description" class="form-control" placeholder="e.g. Answers product inquiries, pricing questions, and captures qualified leads"
                   value="<?= e($selectedTemplate['description'] ?? old('description')) ?>">
        </div>

        <!-- Step 2: Personality & Tone -->
        <h3 style="font-size: 1.15rem; border-bottom: 1px solid var(--color-border); padding-bottom: 10px; margin-top: 24px; margin-bottom: 20px;">
            Step 2: Personality & Behavior
        </h3>

        <div class="grid grid-3 gap-4">
            <div class="form-group">
                <label class="form-label">Personality</label>
                <select name="personality" class="form-control">
                    <option value="friendly" selected>Friendly & Warm</option>
                    <option value="professional">Professional & Direct</option>
                    <option value="empathetic">Empathetic & Supportive</option>
                    <option value="academic">Academic & Analytical</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Communication Tone</label>
                <select name="tone" class="form-control">
                    <option value="helpful" selected>Helpful & Conversational</option>
                    <option value="concise">Concise & Bulleted</option>
                    <option value="formal">Formal & Polite</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Language</label>
                <select name="language" class="form-control">
                    <option value="en" selected>English</option>
                    <option value="es">Spanish</option>
                    <option value="fr">French</option>
                    <option value="de">German</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Welcome Message *</label>
            <textarea name="welcome_message" class="form-control" rows="2" required><?= e($selectedTemplate['welcome_message'] ?? 'Hello! Welcome to our website. How can I help you today?') ?></textarea>
            <div class="form-help">Displayed to visitors when the chat window opens.</div>
        </div>

        <div class="form-group">
            <label class="form-label">Fallback Message *</label>
            <textarea name="fallback_message" class="form-control" rows="2" required><?= e($selectedTemplate['fallback_message'] ?? 'I do not have enough information to answer that question. Would you like to leave your contact details so our team can follow up?') ?></textarea>
            <div class="form-help">Returned when knowledge base cannot match the query with confidence.</div>
        </div>

        <!-- Step 3: Appearance -->
        <h3 style="font-size: 1.15rem; border-bottom: 1px solid var(--color-border); padding-bottom: 10px; margin-top: 24px; margin-bottom: 20px;">
            Step 3: Appearance & Positioning
        </h3>

        <div class="grid grid-3 gap-4">
            <div class="form-group">
                <label class="form-label">Primary Brand Color</label>
                <input type="color" name="primary_color" class="form-control" style="height: 44px; padding: 4px;" 
                       value="<?= e($selectedTemplate['primary_color'] ?? '#2563eb') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Theme</label>
                <select name="theme" class="form-control">
                    <option value="light" selected>Light Theme</option>
                    <option value="dark">Dark Theme</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Widget Position</label>
                <select name="widget_position" class="form-control">
                    <option value="bottom-right" selected>Bottom Right</option>
                    <option value="bottom-left">Bottom Left</option>
                </select>
            </div>
        </div>

        <!-- Step 4: Suggested Questions -->
        <div class="form-group">
            <label class="form-label">Suggested Question Chips (One per line)</label>
            <?php
            $defaultChips = "What services do you offer?\nHow do I get started?\nCan I contact support?";
            if ($selectedTemplate && !empty($selectedTemplate['suggested_questions'])) {
                $decoded = json_decode($selectedTemplate['suggested_questions'], true);
                if (is_array($decoded)) {
                    $defaultChips = implode("\n", $decoded);
                }
            }
            ?>
            <textarea name="suggested_questions" class="form-control" rows="3"><?= e($defaultChips) ?></textarea>
            <div class="form-help">Interactive chips shown under greeting for instant visitor clicks.</div>
        </div>

        <div class="flex justify-between items-center" style="margin-top: 32px; border-top: 1px solid var(--color-border); padding-top: 20px;">
            <a href="<?= url('/studio/bots') ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary btn-lg">Create Chatbot & Open Studio Builder &rarr;</button>
        </div>
    </form>
</div>
