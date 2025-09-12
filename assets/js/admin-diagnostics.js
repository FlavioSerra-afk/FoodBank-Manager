/* Diagnostics admin JS */
document.addEventListener('DOMContentLoaded', function () {
    const copyBtn = document.getElementById('fbm-diagnostics-copy');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            const json = copyBtn.getAttribute('data-report') || '';
            const text = document.getElementById('fbm-diagnostics-report')?.innerText || '';
            const payload = json + '\n\n' + text;
            window.navigator.clipboard.writeText(payload).then(function () {
                copyBtn.textContent = 'Copied';
                setTimeout(function () {
                    copyBtn.textContent = 'Copy report';
                }, 2000);
            });
        });
    }

    const sendBtn = document.getElementById('fbm-mailtest-send');
    if (sendBtn) {
        sendBtn.addEventListener('click', function () {
            const to = document.getElementById('fbm-mailtest-to')?.value || '';
            const nonce = sendBtn.getAttribute('data-nonce') || '';
            const resEl = document.getElementById('fbm-mailtest-result');
            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'fbm_mail_test', to: to, _ajax_nonce: nonce }).toString(),
            }).then(function (r) { return r.json(); }).then(function (data) {
                if (resEl) {
                    resEl.textContent = data.success ? 'Sent' : 'Error';
                }
            }).catch(function () {
                if (resEl) { resEl.textContent = 'Error'; }
            });
        });
    }

    const dlBtn = document.getElementById('fbm-diagnostics-download');
    if (dlBtn) {
        dlBtn.addEventListener('click', function () {
            const json = dlBtn.getAttribute('data-report') || '{}';
            const blob = new Blob([json], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'fbm-system-report.json';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        });
    }
});
