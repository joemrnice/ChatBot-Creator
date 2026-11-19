<section style="padding: 60px 0;">
    <div class="container" style="max-width: 800px;">
        <h1>Privacy Policy</h1>
        <p class="text-muted text-sm" style="margin-top: 8px;">Last updated: <?= date('F j, Y') ?></p>

        <div class="card" style="margin-top: 24px; padding: 32px; line-height: 1.7;">
            <h4>1. Information We Collect</h4>
            <p class="text-muted text-sm" style="margin-top: 8px;">
                We collect your name, email address, password hash, and billing reference identifiers when you create an account or subscribe to our SaaS platform. For visitor chatbot interactions, we record session IDs, IP addresses for security and rate limiting, and chat message transcripts.
            </p>

            <h4 style="margin-top: 24px;">2. Multi-Tenant Data Isolation</h4>
            <p class="text-muted text-sm" style="margin-top: 8px;">
                Each subscriber workspace is isolated at the database level. Your knowledge entries, conversations, and custom instructions are accessible exclusively to your account.
            </p>

            <h4 style="margin-top: 24px;">3. Payment Processing</h4>
            <p class="text-muted text-sm" style="margin-top: 8px;">
                All payment transactions are handled through the official PayPal REST API. We never store credit card numbers, CVVs, or bank details on our servers.
            </p>

            <h4 style="margin-top: 24px;">4. Security Controls</h4>
            <p class="text-muted text-sm" style="margin-top: 8px;">
                We utilize industry-standard password hashing (Argon2id/Bcrypt), HTTPS transport security, HTTP-only SameSite cookies, CSRF tokens, and sanitized SQL parameterized statements.
            </p>
        </div>
    </div>
</section>
