<section style="padding: 60px 0;">
    <div class="container" style="max-width: 860px;">
        <div class="text-center" style="margin-bottom: 48px;">
            <span class="badge badge-primary" style="margin-bottom: 8px;">Architecture & Workflow</span>
            <h1>How ChatBot Creator Studio Works</h1>
            <p class="text-muted text-lg" style="margin-top: 12px; line-height: 1.6;">
                A look under the hood of our framework-free, highly performant PHP 8.3+ multi-tenant SaaS architecture.
            </p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 32px;">
            <div class="card" style="display: flex; gap: 24px; align-items: flex-start;">
                <div style="background: var(--color-primary-light); color: var(--color-primary); font-size: 1.5rem; font-weight: 800; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    1
                </div>
                <div>
                    <h3>Chatbot Definition & Multi-Tenant Isolation</h3>
                    <p class="text-muted text-sm" style="margin-top: 8px; line-height: 1.6;">
                        Each user operates in an isolated workspace with strict database row-level ownership. When you create a chatbot, the system assigns a unique UUID, slug, and cryptographic public token.
                    </p>
                </div>
            </div>

            <div class="card" style="display: flex; gap: 24px; align-items: flex-start;">
                <div style="background: var(--color-primary-light); color: var(--color-primary); font-size: 1.5rem; font-weight: 800; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    2
                </div>
                <div>
                    <h3>Hybrid Knowledge Matching Engine</h3>
                    <p class="text-muted text-sm" style="margin-top: 8px; line-height: 1.6;">
                        When a visitor sends a query, our engine evaluates MySQL full-text indices and tokenized keyword scores. If a high-confidence match is found, the verified answer is returned immediately. When external AI is enabled, the matching docs are injected into an OpenAI-compatible context window.
                    </p>
                </div>
            </div>

            <div class="card" style="display: flex; gap: 24px; align-items: flex-start;">
                <div style="background: var(--color-primary-light); color: var(--color-primary); font-size: 1.5rem; font-weight: 800; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    3
                </div>
                <div>
                    <h3>Encapsulated CDN Delivery</h3>
                    <p class="text-muted text-sm" style="margin-top: 8px; line-height: 1.6;">
                        Our embeddable widget uses modern browser Shadow DOM encapsulation. This prevents host website CSS rules from distorting chatbot appearance and guarantees zero styling collisions.
                    </p>
                </div>
            </div>

            <div class="card" style="display: flex; gap: 24px; align-items: flex-start;">
                <div style="background: var(--color-primary-light); color: var(--color-primary); font-size: 1.5rem; font-weight: 800; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    4
                </div>
                <div>
                    <h3>Cryptographically Verified Subscriptions</h3>
                    <p class="text-muted text-sm" style="margin-top: 8px; line-height: 1.6;">
                        SaaS entitlements are never trusted from client-side requests. Upgrades are authorized server-side using the official PayPal REST Subscriptions API with asynchronous webhook event verification and idempotency safeguards.
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center" style="margin-top: 48px;">
            <a href="<?= url('/register') ?>" class="btn btn-primary btn-lg">Create Your First Chatbot Now</a>
        </div>
    </div>
</section>
