import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers';

const REPORTS_PAGE = '/wp-admin/admin.php?page=fbm-reports';

test.describe('Reports and exports', () => {
  test('shows cache hints and streams CSV with expected headers', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto(REPORTS_PAGE);
    await page.waitForLoadState('networkidle');

    const cacheBanner = page.locator('.fbm-cache-status');
    await expect(cacheBanner).toContainText('Fresh results generated moments ago.');

    const lastSeven = page.locator('form.fbm-quick-range button[value="last7"]');
    await lastSeven.click();
    await page.waitForLoadState('networkidle');
    await expect(cacheBanner).toContainText('Fresh results generated moments ago.');

    await lastSeven.click();
    await page.waitForLoadState('networkidle');
    await expect(cacheBanner).toContainText('Using cached results');

    const lastThirty = page.locator('form.fbm-quick-range button[value="last30"]');
    await lastThirty.click();
    await page.waitForLoadState('networkidle');
    await expect(cacheBanner).toContainText('Fresh results generated moments ago.');

    const exportRoute = '**/admin.php?page=fbm-reports**';
    const exportHandled = new Promise<void>((resolve) => {
      let resolved = false;
      page.route(exportRoute, async (route) => {
        const requestUrl = route.request().url();
        if (!requestUrl.includes('fbm_report_action=export')) {
          await route.continue();
          return;
        }

        const response = await route.fetch();
        const headers = response.headers();
        expect(headers['content-type']).toContain('text/csv');
        expect(headers['content-disposition']).toContain('attachment; filename="attendance-');
        await route.fulfill({ response });
        if (!resolved) {
          resolved = true;
          resolve();
        }
      });
    });

    await page.click('.fbm-export-form button[type="submit"]');
    await exportHandled;
    await page.unroute(exportRoute);
  });
});
