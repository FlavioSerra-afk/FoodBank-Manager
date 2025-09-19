import { test, expect } from '@playwright/test';
import { getOption, loginAsAdmin } from './helpers';

const DIAGNOSTICS_PAGE = '/wp-admin/admin.php?page=fbm-diagnostics';

test.describe('Diagnostics tooling', () => {
  test('probes tokens and enforces resend throttling', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto(DIAGNOSTICS_PAGE);
    await page.waitForLoadState('networkidle');

    const token = getOption('fbm_e2e_token_active');
    expect(token).not.toEqual('');

    await page.fill('#fbm-token-probe-payload', token);
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle' }),
      page.click('button:has-text("Probe token")'),
    ]);

    const output = page.locator('.fbm-token-probe__output code');
    await expect(output).toBeVisible();
    const payload = await output.textContent();
    expect(payload).not.toBeNull();

    const parsed = JSON.parse(payload ?? '{}');
    expect(Object.keys(parsed).sort()).toEqual(['hmac_match', 'revoked', 'version']);
    expect(parsed.hmac_match).toBe(true);
    expect(parsed.revoked).toBe(false);
    expect(parsed.version).toBeDefined();

    const failureRow = page.locator('table.widefat tr').filter({ hasText: 'FBM-E2EFAIL' });
    await expect(failureRow).toBeVisible();
    await expect(failureRow.locator('a:has-text("Resend")')).toHaveCount(0);
    await expect(failureRow.locator('span.description')).toContainText('Available after');
  });
});
