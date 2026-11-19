/**
 * ChatBot Creator Studio - Embeddable CDN Widget
 * Vanilla ES6+ - Zero Frameworks
 */
(function (window, document) {
    'use strict';

    if (window.ChatBotWidgetLoaded) return;
    window.ChatBotWidgetLoaded = true;

    class ChatBotWidget {
        constructor(config) {
            this.token = config.token;
            this.host = (config.host || '').replace(/\/$/, '');
            this.sessionId = this.getOrCreateSessionId();
            this.isOpen = false;
            this.config = null;
            this.messages = [];
            this.container = null;
            this.shadow = null;

            this.init();
        }

        getOrCreateSessionId() {
            const key = `cbc_sid_${this.token}`;
            let sid = localStorage.getItem(key);
            if (!sid) {
                sid = 'sid_' + Math.random().toString(36).substring(2, 15) + Date.now().toString(36);
                localStorage.setItem(key, sid);
            }
            return sid;
        }

        async init() {
            try {
                // Fetch public config
                const res = await fetch(`${this.host}/embed/${this.token}/config`);
                if (!res.ok) {
                    console.error('ChatBot Widget: Failed to load config.', res.status);
                    return;
                }
                this.config = await res.json();
                this.render();
            } catch (err) {
                console.error('ChatBot Widget Initialization Error:', err);
            }
        }

        render() {
            const hostEl = document.createElement('div');
            hostEl.id = `cbc-widget-${this.token}`;
            document.body.appendChild(hostEl);

            // Use Shadow DOM for style encapsulation
            if (hostEl.attachShadow) {
                this.shadow = hostEl.attachShadow({ mode: 'open' });
            } else {
                this.shadow = hostEl;
            }

            // Load CSS
            const styleLink = document.createElement('link');
            styleLink.rel = 'stylesheet';
            styleLink.href = `${this.host}/public-assets/widget/chatbot-widget.css`;
            this.shadow.appendChild(styleLink);

            // Dynamic styles from bot config
            const customStyles = document.createElement('style');
            const primary = this.config.primary_color || '#2563eb';
            const radius = this.config.widget_border_radius || 16;
            customStyles.textContent = `
                :host, .cbc-widget-root {
                    --cbc-primary: ${primary};
                    --cbc-primary-hover: ${primary};
                    --cbc-radius: ${radius}px;
                }
                ${this.config.custom_css || ''}
            `;
            this.shadow.appendChild(customStyles);

            // Wrapper
            const wrapper = document.createElement('div');
            wrapper.className = 'cbc-widget-root';

            const posClass = this.config.widget_position === 'bottom-left' ? 'cbc-left' : '';

            wrapper.innerHTML = `
                <!-- Launcher Button -->
                <button class="cbc-launcher ${posClass}" aria-label="Open Chat">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
                    </svg>
                </button>

                <!-- Chat Window -->
                <div class="cbc-window ${posClass}">
                    <div class="cbc-header">
                        <div class="cbc-header-info">
                            <h4>${this.escapeHtml(this.config.header_title || this.config.name)}</h4>
                            <p>${this.escapeHtml(this.config.header_subtitle || 'Online')}</p>
                        </div>
                        <button class="cbc-close-btn" aria-label="Close Chat">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>

                    <div class="cbc-messages">
                        <!-- Welcome message -->
                        <div class="cbc-msg cbc-msg-bot">
                            ${this.escapeHtml(this.config.welcome_message || 'Hello! How can I assist you today?')}
                            ${this.renderChips()}
                        </div>
                    </div>

                    <div class="cbc-footer">
                        <form class="cbc-input-box">
                            <input type="text" class="cbc-input" placeholder="Type a message..." autocomplete="off" />
                            <button type="submit" class="cbc-send-btn" aria-label="Send Message">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </form>
                        ${!this.config.hide_watermark ? `
                            <div class="cbc-watermark">
                                Powered by <a href="${this.host}" target="_blank" rel="noopener">ChatBot Creator Studio</a>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;

            this.shadow.appendChild(wrapper);
            this.container = wrapper;

            this.bindEvents();
        }

        renderChips() {
            const chips = this.config.suggested_questions || [];
            if (!chips.length) return '';
            return `
                <div class="cbc-chips">
                    ${chips.map(q => `<button type="button" class="cbc-chip" data-question="${this.escapeHtml(q)}">${this.escapeHtml(q)}</button>`).join('')}
                </div>
            `;
        }

        bindEvents() {
            const launcher = this.container.querySelector('.cbc-launcher');
            const closeBtn = this.container.querySelector('.cbc-close-btn');
            const windowEl = this.container.querySelector('.cbc-window');
            const form = this.container.querySelector('.cbc-input-box');
            const input = this.container.querySelector('.cbc-input');
            const messagesEl = this.container.querySelector('.cbc-messages');

            const toggle = () => {
                this.isOpen = !this.isOpen;
                windowEl.classList.toggle('cbc-open', this.isOpen);
                if (this.isOpen) {
                    input.focus();
                }
            };

            launcher.addEventListener('click', toggle);
            closeBtn.addEventListener('click', toggle);

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const text = input.value.trim();
                if (text) {
                    this.sendMessage(text);
                    input.value = '';
                }
            });

            messagesEl.addEventListener('click', (e) => {
                const chip = e.target.closest('.cbc-chip');
                if (chip) {
                    const q = chip.getAttribute('data-question');
                    if (q) {
                        this.sendMessage(q);
                    }
                }
            });
        }

        async sendMessage(text) {
            const messagesEl = this.container.querySelector('.cbc-messages');

            // Render user bubble
            const userMsg = document.createElement('div');
            userMsg.className = 'cbc-msg cbc-msg-user';
            userMsg.textContent = text;
            messagesEl.appendChild(userMsg);
            messagesEl.scrollTop = messagesEl.scrollHeight;

            // Render typing indicator
            const typing = document.createElement('div');
            typing.className = 'cbc-typing';
            typing.innerHTML = '<span class="cbc-dot"></span><span class="cbc-dot"></span><span class="cbc-dot"></span>';
            messagesEl.appendChild(typing);
            messagesEl.scrollTop = messagesEl.scrollHeight;

            try {
                const res = await fetch(`${this.host}/api/v1/chat/${this.token}/messages`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        session_id: this.sessionId,
                        message: text,
                        referrer: window.location.href,
                    })
                });

                typing.remove();

                const data = await res.json();
                const botReply = data.data?.content || data.message || "I didn't quite catch that.";

                const botMsg = document.createElement('div');
                botMsg.className = 'cbc-msg cbc-msg-bot';
                botMsg.textContent = botReply;
                messagesEl.appendChild(botMsg);
                messagesEl.scrollTop = messagesEl.scrollHeight;
            } catch (err) {
                typing.remove();
                const errMsg = document.createElement('div');
                errMsg.className = 'cbc-msg cbc-msg-bot';
                errMsg.textContent = "Unable to connect to assistant. Please try again shortly.";
                messagesEl.appendChild(errMsg);
                messagesEl.scrollTop = messagesEl.scrollHeight;
            }
        }

        escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    }

    window.ChatBotWidget = {
        init: function (config) {
            return new ChatBotWidget(config);
        }
    };

    // Auto-detect script src to auto-initialize
    const currentScript = document.currentScript;
    if (currentScript && currentScript.src) {
        const matches = currentScript.src.match(/\/embed\/([a-zA-Z0-9_\-]+)\.js/);
        if (matches && matches[1]) {
            const token = matches[1];
            const parsedUrl = new URL(currentScript.src);
            const host = parsedUrl.origin + parsedUrl.pathname.replace(/\/embed\/[^\/]+\.js$/, '');
            window.ChatBotWidget.init({ token, host });
        }
    }
})(window, document);
