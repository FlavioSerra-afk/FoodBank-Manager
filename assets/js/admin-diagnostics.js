/* Diagnostics admin JS */
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('fbm-diagnostics-copy');
    if (!btn) {
        return;
    }
    btn.addEventListener('click', function () {
        const json = btn.getAttribute('data-report') || '';
        const text = document.getElementById('fbm-diagnostics-report')?.innerText || '';
        const payload = json + '\n\n' + text;
        window.navigator.clipboard.writeText(payload).then(function () {
            btn.textContent = 'Copied';
            setTimeout(function () {
                btn.textContent = 'Copy report';
            }, 2000);
        });
    });
});
