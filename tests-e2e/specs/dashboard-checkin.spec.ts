import { test, expect } from '@playwright/test';
import { getOption, loginAsAdmin } from './helpers';

const STAFF_PAGE = '/staff-dashboard/';

test.describe('Staff dashboard manual entry', () => {
  test('manual code submission handles duplicates and overrides', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto(STAFF_PAGE);

    const scannerModule = page.locator('[data-fbm-scanner-module]');
    await expect(scannerModule).toBeVisible();

    const manualInput = page.locator('input[name="code"]');

    await manualInput.fill('FBM-E2E123');
    await page.click('button:has-text("Record manual collection")');
    await page.waitForLoadState('networkidle');
    await expect(page.locator('.fbm-staff-dashboard__manual-status')).toContainText('Collection recorded.');
    await expect(manualInput).toHaveValue('');

    await manualInput.fill('FBM-E2E123');
    await page.click('button:has-text("Record manual collection")');
    await page.waitForLoadState('networkidle');
    await expect(page.locator('.fbm-staff-dashboard__manual-status')).toContainText('Member already collected today.');

    const overrideReference = getOption('fbm_e2e_override_reference');
    await manualInput.fill(overrideReference);
    await page.click('button:has-text("Record manual collection")');
    await page.waitForLoadState('networkidle');
    await expect(page.locator('.fbm-staff-dashboard__manual-status')).toContainText('Only managers can continue with a justified override.');
    const overrideForm = page.locator('.fbm-staff-dashboard__manual-override');
    await expect(overrideForm).toBeVisible();

    await page.fill('textarea[name="override_note"]', 'Override approved in e2e');
    await page.click('.fbm-staff-dashboard__manual-override button:has-text("Confirm override")');
    await page.waitForLoadState('networkidle');
    await expect(page.locator('.fbm-staff-dashboard__manual-status')).toContainText('Collection recorded.');
    await expect(overrideForm).not.toBeVisible();
  });
});
