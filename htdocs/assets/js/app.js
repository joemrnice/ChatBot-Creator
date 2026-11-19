/**
 * ChatBot Creator Studio - Master Application Scripts
 * Vanilla ES6+ - Zero Frameworks
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Toast / Alert Dismissal
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.4s';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });

    // 2. Copy to Clipboard Utility
    document.querySelectorAll('[data-copy]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const text = btn.getAttribute('data-copy');
            if (!text) return;

            try {
                await navigator.clipboard.writeText(text);
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<span>✓ Copied!</span>';
                btn.style.borderColor = '#10b981';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.style.borderColor = '';
                }, 2000);
            } catch (err) {
                console.error('Clipboard copy failed:', err);
            }
        });
    });

    // 3. Destructive Action Confirmation
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            const message = el.getAttribute('data-confirm') || 'Are you sure you want to perform this action?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // 4. Mobile Sidebar Drawer Toggle
    const sidebarToggle = document.querySelector('#sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    // 5. Studio Builder Real-Time Live Preview Sync
    const previewForm = document.querySelector('#bot-builder-form');
    if (previewForm) {
        const titleInput = previewForm.querySelector('[name="header_title"]');
        const subtitleInput = previewForm.querySelector('[name="header_subtitle"]');
        const welcomeInput = previewForm.querySelector('[name="welcome_message"]');
        const colorInput = previewForm.querySelector('[name="primary_color"]');
        const radiusInput = previewForm.querySelector('[name="widget_border_radius"]');

        const liveHeader = document.querySelector('#preview-header-title');
        const liveSubtitle = document.querySelector('#preview-header-subtitle');
        const liveWelcome = document.querySelector('#preview-welcome-message');
        const liveHeaderBox = document.querySelector('#preview-header-box');
        const liveWindow = document.querySelector('#preview-window');

        const syncPreview = () => {
            if (titleInput && liveHeader) liveHeader.textContent = titleInput.value || 'Chat Support';
            if (subtitleInput && liveSubtitle) liveSubtitle.textContent = subtitleInput.value || 'Online';
            if (welcomeInput && liveWelcome) liveWelcome.textContent = welcomeInput.value || 'Hello! How can I assist you today?';
            if (colorInput && liveHeaderBox) liveHeaderBox.style.backgroundColor = colorInput.value;
            if (radiusInput && liveWindow) liveWindow.style.borderRadius = `${radiusInput.value}px`;
        };

        previewForm.addEventListener('input', syncPreview);
        syncPreview();
    }
});
