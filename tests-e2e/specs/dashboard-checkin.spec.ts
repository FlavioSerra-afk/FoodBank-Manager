import { test, expect } from '@playwright/test';
import { getHarnessUrl } from './helpers';

test.describe('Staff dashboard manual entry', () => {
  test('handles check-in statuses via mocked responses', async ({ page }) => {
    await page.route('**/wp-json/fbm/v1/checkin', async (route) => {
      const request = route.request();
      let payload: Record<string, unknown> | null = null;
      try {
        payload = request.postDataJSON();
      } catch (error) {
        payload = null;
      }

      const reference = (payload?.manual_code || payload?.code || '') as string;
      const isOverride = payload?.override === true;

      let responseBody: Record<string, unknown> = {
        status: 'success',
        message: 'Collection recorded.',
      };

      if (isOverride) {
        responseBody = {
          status: 'success',
          message: 'Override recorded successfully.',
        };
      } else {
        switch (reference) {
          case 'FBM-SUCCESS':
            responseBody = {
              status: 'success',
              message: 'Collection recorded.',
            };
            break;
          case 'FBM-DUP':
            responseBody = {
              status: 'already',
              message: 'Member already collected today.',
            };
            break;
          case 'FBM-RECENT':
            responseBody = {
              status: 'recent_warning',
              message: 'Only managers can continue with a justified override.',
              window_notice: 'Collections run on Thursday 11:00–14:30.',
            };
            break;
          case 'FBM-REVOKED':
            responseBody = {
              status: 'revoked',
              message: 'This code has been revoked.',
            };
            break;
          case 'FBM-INVALID':
            responseBody = {
              status: 'invalid',
              message: 'Enter a valid collection code.',
            };
            break;
          case 'FBM-THROTTLED':
            responseBody = {
              status: 'throttled',
              message: 'Please wait a moment before trying again.',
            };
            break;
          default:
            responseBody = {
              status: 'success',
              message: 'Collection recorded.',
            };
            break;
        }
      }

      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(responseBody),
      });
    });

    await page.goto(getHarnessUrl('dashboard.html'));

    const statusBanner = page.locator('[data-fbm-status]');
    await expect(statusBanner).toHaveText(/(Ready to record collections\.|Camera scanning is not supported on this device\.)/);

    const referenceInput = page.locator('[data-fbm-reference]');
    const submitButton = page.locator('[data-fbm-checkin="manual"]');

    await referenceInput.fill('FBM-SUCCESS');
    await submitButton.click();
    await expect(statusBanner).toContainText('Collection recorded.');
    await expect(referenceInput).toHaveValue('');
    await expect(page.locator('[data-fbm-today-success]')).toHaveText('1');

    await referenceInput.fill('FBM-DUP');
    await submitButton.click();
    await expect(statusBanner).toContainText('Member already collected today.');
    await expect(page.locator('[data-fbm-today-duplicate]')).toHaveText('1');

    await referenceInput.fill('FBM-RECENT');
    await submitButton.click();
    const overridePanel = page.locator('[data-fbm-override]');
    await expect(overridePanel).toBeVisible();

    await expect(statusBanner).toHaveText(
      /(Only managers can continue with a justified override\.|Unable to record collection\. Please try again\.)/
    );

    await expect(page.locator('[data-fbm-override-message]')).toContainText(
      'Only managers can continue by recording an override'
    );

    await page.fill('[data-fbm-override-note]', 'Override approved in e2e');
    await page.click('[data-fbm-confirm-override]');
    await expect(statusBanner).toContainText('Override recorded successfully.');
    await expect(overridePanel).toBeHidden();
    await expect(page.locator('[data-fbm-today-override]')).toHaveText('1');

    await referenceInput.fill('FBM-REVOKED');
    await submitButton.click();
    await expect(statusBanner).toContainText('This code has been revoked.');

    await referenceInput.fill('FBM-INVALID');
    await submitButton.click();
    await expect(statusBanner).toContainText('Enter a valid collection code.');

    await referenceInput.fill('FBM-THROTTLED');
    await submitButton.click();
    await expect(statusBanner).toContainText('Please wait a moment before trying again.');
  });
});
