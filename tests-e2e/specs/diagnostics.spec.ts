import { test, expect } from '@playwright/test';
import { getHarnessUrl } from './helpers';

test.describe('Diagnostics tooling', () => {
  test('probes tokens and surfaces resend throttling', async ({ page }) => {
    await page.route('**/admin-ajax.php', async (route) => {
      const request = route.request();
      const body = request.postData() ?? '';
      const params = new URLSearchParams(body);
      const action = params.get('action');

      if (action === 'fbm_token_probe') {
        const payload = params.get('fbm_token_probe_payload') ?? '';
        const result = payload.startsWith('FBM1')
          ? { version: '1', hmac_match: true, revoked: false }
          : { version: null, hmac_match: false, revoked: false };

        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({
            success: true,
            data: { result },
          }),
        });
        return;
      }

      if (action === 'fbm_mail_resend') {
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({
            success: false,
            data: {
              code: 'rate-limited',
              message: 'Resend is temporarily rate-limited. Please try again later.',
              hint: 'Available after 30 minutes.',
            },
          }),
        });
        return;
      }

      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ success: true }),
      });
    });

    await page.goto(getHarnessUrl('diagnostics.html'));

    await page.fill('#fbm-token-probe-payload', 'FBM1:E2E-TOKEN');
    await page.click('button:has-text("Probe token")');

    const outputCode = page.locator('.fbm-token-probe__output code');
    await expect(outputCode).toContainText('"version": "1"');

    const payloadText = await outputCode.textContent();
    expect(payloadText).not.toBeNull();
    const parsed = JSON.parse(payloadText ?? '{}');
    expect(Object.keys(parsed).sort()).toEqual(['hmac_match', 'revoked', 'version']);
    expect(parsed.version).toBe('1');
    expect(parsed.hmac_match).toBe(true);
    expect(parsed.revoked).toBe(false);

    const resendButton = page.locator('[data-fbm-diagnostics-resend]');
    await resendButton.click();

    const notice = page.locator('[data-fbm-diagnostics-notice]');
    await expect(notice).toBeVisible();
    await expect(notice).toContainText('Resend is temporarily rate-limited. Please try again later.');

    const hint = page.locator('[data-fbm-diagnostics-resend-hint]');
    await expect(hint).toBeVisible();
    await expect(hint).toContainText('Available after 30 minutes.');
    await expect(resendButton).toBeEnabled();
  });
});
