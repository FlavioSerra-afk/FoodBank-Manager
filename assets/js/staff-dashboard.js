(function () {
    'use strict';

    const config = window.fbmStaffDashboard || null;
    const dashboard = document.querySelector('[data-fbm-staff-dashboard]');

    if (!config || !dashboard) {
        return;
    }

    const statusEl = dashboard.querySelector('[data-fbm-status]');
    const countEl = dashboard.querySelector('[data-fbm-today-count]');
    const lastEl = dashboard.querySelector('[data-fbm-last-result]');
    const manualForm = dashboard.querySelector('[data-fbm-manual]');
    const scanButton = dashboard.querySelector('[data-fbm-start-scan]');
    const scanError = dashboard.querySelector('[data-fbm-scan-error]');
    const preview = dashboard.querySelector('[data-fbm-preview]');

    let todayCount = parseInt(countEl ? countEl.textContent || '0' : '0', 10);

    const messages = Object.assign(
        {
            ready: 'Ready for the next collection.',
            success: 'Check-in recorded.',
            duplicate: 'Member already collected today.',
            error: 'Unable to record attendance. Please try again.',
        },
        config.messages || {}
    );

    function setStatus(message, tone) {
        if (!statusEl) {
            return;
        }
        statusEl.textContent = message;
        statusEl.dataset.fbmTone = tone || 'info';
    }

    function setLastResult(text) {
        if (lastEl) {
            lastEl.textContent = text;
        }
    }

    async function submitToken(token) {
        if (!config.checkinUrl) {
            setStatus(messages.error, 'error');
            return;
        }

        setStatus(messages.ready, 'loading');

        try {
            const response = await fetch(config.checkinUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': config.nonce || '',
                },
                body: JSON.stringify({ token: token }),
            });

            const payload = await response.json().catch(() => ({}));

            if (response.status === 409) {
                setStatus(messages.duplicate, 'warning');
                setLastResult(payload.member || token);
                return;
            }

            if (response.ok) {
                todayCount += 1;
                if (countEl) {
                    countEl.textContent = String(todayCount);
                }
                setStatus(messages.success, 'success');
                setLastResult(payload.member || token);
                return;
            }
        } catch (error) {
            console.error('fbmStaffDashboard', error);
        }

        setStatus(messages.error, 'error');
    }

    if (manualForm) {
        manualForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const tokenField = manualForm.querySelector('input[name="token"]');
            const token = tokenField ? tokenField.value.trim() : '';

            if (!token) {
                setStatus(messages.error, 'error');
                return;
            }

            submitToken(token);
            if (tokenField) {
                tokenField.value = '';
                tokenField.focus();
            }
        });
    }

    if (scanButton) {
        scanButton.addEventListener('click', function () {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (scanError) {
                    scanError.textContent = messages.error;
                    scanError.hidden = false;
                }
                return;
            }

            if (scanError) {
                scanError.textContent = '';
                scanError.hidden = true;
            }
            if (preview) {
                preview.hidden = false;
            }

            setStatus(messages.ready, 'info');
            // Detailed camera integration will be added in a later pass.
        });
    }

    setStatus(messages.ready, 'info');
})();
