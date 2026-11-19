<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($bot['header_title'] ?: $bot['name']) ?> - Live Chat Assistant</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('widget/chatbot-widget.css') ?>">
    <style>
        body {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .standalone-box {
            width: 100%;
            max-width: 480px;
            height: 680px;
            max-height: 90vh;
            background: #ffffff;
            border-radius: <?= e($bot['widget_border_radius'] ?: 16) ?>px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--color-border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .standalone-header {
            background: <?= e($bot['primary_color'] ?: '#2563eb') ?>;
            color: #ffffff;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .standalone-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        .standalone-feed {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: #f8fafc;
        }
        .standalone-footer {
            padding: 14px 18px;
            background: #ffffff;
            border-top: 1px solid var(--color-border);
        }
    </style>
</head>
<body>
    <div class="standalone-box">
        <!-- Header -->
        <div class="standalone-header">
            <div class="standalone-avatar">🤖</div>
            <div style="flex: 1;">
                <h3 style="font-size: 1.15rem; font-weight: 700; color: #ffffff; line-height: 1.2;">
                    <?= e($bot['header_title'] ?: $bot['name']) ?>
                </h3>
                <p style="font-size: 0.8125rem; opacity: 0.9; margin-top: 2px;">
                    <?= e($bot['header_subtitle'] ?: 'Replies instantly') ?>
                </p>
            </div>
            <span class="badge" style="background: rgba(255,255,255,0.25); color: #fff; font-size: 10px;">Live</span>
        </div>

        <!-- Chat Feed -->
        <div id="chat-feed" class="standalone-feed">
            <div class="cbc-msg cbc-msg-bot">
                <div><?= e($bot['welcome_message']) ?></div>
                <?php if (!empty($bot['suggested_questions_array'])): ?>
                    <div class="cbc-chips">
                        <?php foreach ($bot['suggested_questions_array'] as $q): ?>
                            <button type="button" class="cbc-chip" onclick="sendStandalone('<?= e(addslashes($q)) ?>')"><?= e($q) ?></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer Input Form -->
        <div class="standalone-footer">
            <form id="standalone-form" class="cbc-input-box" onsubmit="event.preventDefault(); submitStandalone();">
                <input type="text" id="standalone-input" class="cbc-input" placeholder="Ask a question..." autocomplete="off">
                <button type="submit" class="cbc-send-btn" aria-label="Send">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </form>

            <?php if (empty($bot['hide_watermark'])): ?>
                <div class="cbc-watermark">
                    Powered by <a href="<?= url('/') ?>" target="_blank">ChatBot Creator Studio</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    const BOT_TOKEN = '<?= e($bot['public_token']) ?>';
    const API_ENDPOINT = '<?= url("/api/v1/chat/{$bot['public_token']}/messages") ?>';
    let sessionId = localStorage.getItem('cbc_sid_' + BOT_TOKEN);
    if (!sessionId) {
        sessionId = 'sid_' + Math.random().toString(36).substring(2, 12);
        localStorage.setItem('cbc_sid_' + BOT_TOKEN, sessionId);
    }

    function sendStandalone(text) {
        document.getElementById('standalone-input').value = text;
        submitStandalone();
    }

    async function submitStandalone() {
        const input = document.getElementById('standalone-input');
        const text = input.value.trim();
        if (!text) return;

        input.value = '';
        const feed = document.getElementById('chat-feed');

        // User message
        const uBubble = document.createElement('div');
        uBubble.className = 'cbc-msg cbc-msg-user';
        uBubble.textContent = text;
        feed.appendChild(uBubble);
        feed.scrollTop = feed.scrollHeight;

        // Typing indicator
        const typing = document.createElement('div');
        typing.className = 'cbc-typing';
        typing.innerHTML = '<span class="cbc-dot"></span><span class="cbc-dot"></span><span class="cbc-dot"></span>';
        feed.appendChild(typing);
        feed.scrollTop = feed.scrollHeight;

        try {
            const res = await fetch(API_ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ session_id: sessionId, message: text })
            });
            const data = await res.json();
            typing.remove();

            const bBubble = document.createElement('div');
            bBubble.className = 'cbc-msg cbc-msg-bot';
            bBubble.textContent = data.data?.content || data.message || "I didn't quite catch that.";
            feed.appendChild(bBubble);
            feed.scrollTop = feed.scrollHeight;
        } catch (err) {
            typing.remove();
            const eBubble = document.createElement('div');
            eBubble.className = 'cbc-msg cbc-msg-bot';
            eBubble.textContent = 'Unable to reach assistant. Please try again.';
            feed.appendChild(eBubble);
        }
    }
    </script>
</body>
</html>
