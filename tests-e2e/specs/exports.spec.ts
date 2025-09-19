import { test, expect } from '@playwright/test';
import { getHarnessUrl } from './helpers';

test.describe('Reports and exports', () => {
  test('shows cache hints and streams CSV with expected headers', async ({ page }) => {
    await page.route('**/admin-ajax.php', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ success: true }),
      });
    });

    let exportBody = '';

    await page.route('**/exports/*.csv', async (route) => {
      exportBody = '\uFEFF"Member Reference","Collections"\n"FBM-0001","2"\n';
      await route.fulfill({
        status: 200,
        headers: {
          'Content-Type': 'text/csv; charset=UTF-8',
          'Content-Disposition': 'attachment; filename="attendance-export.csv"',
        },
        body: exportBody,
      });
    });

    await page.goto(getHarnessUrl('reports.html'));

    const cacheMessage = page.locator('[data-fbm-cache-message]');
    await expect(cacheMessage).toHaveText('Fresh results generated moments ago.');

    const lastSeven = page.locator('[data-quick-range="last7"]');
    const lastThirty = page.locator('[data-quick-range="last30"]');

    await lastSeven.click();
    await expect(cacheMessage).toContainText('Using cached results (cached');

    await lastThirty.click();
    await expect(cacheMessage).toHaveText('Fresh results generated moments ago.');

    const refreshButton = page.locator('[data-fbm-cache-refresh]');
    await refreshButton.click();
    await expect(cacheMessage).toHaveText('Fresh results generated moments ago.');

    const exportStatus = page.locator('[data-fbm-export-status]');
    const exportResponsePromise = page.waitForResponse('**/exports/attendance.csv');

    await page.click('.fbm-export-form button[type="submit"]');

    const exportResponse = await exportResponsePromise;
    const headers = exportResponse.headers();
    expect(headers['content-type']).toBe('text/csv; charset=UTF-8');
    expect(headers['content-disposition']).toContain('attendance-export.csv');

    await expect(exportStatus).toBeVisible();
    await expect(exportStatus).toHaveText('Export ready.');

    expect(exportBody.charCodeAt(0)).toBe(0xfeff);
    expect(exportBody).toContain('"Member Reference","Collections"');
  });
});
