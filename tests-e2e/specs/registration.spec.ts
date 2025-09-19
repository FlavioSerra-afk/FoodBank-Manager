import { test, expect } from '@playwright/test';
import { getHarnessUrl } from './helpers';

test.describe('Public registration form', () => {
  test('rejects incomplete submissions', async ({ page }) => {
    await page.goto(getHarnessUrl('registration.html'));

    const form = page.locator('.fbm-registration-form__form');
    await expect(form).toBeVisible();

    await page.click('button:has-text("Submit registration")');

    const errorNotice = page.locator('[data-fbm-registration-error]');
    await expect(errorNotice).toBeVisible();
    await expect(errorNotice).toContainText('Please correct the errors below and try again.');

    const errorsList = page.locator('[data-fbm-registration-errors]');
    await expect(errorsList).toBeVisible();
    await expect(errorsList).toContainText('First name is required.');
    await expect(errorsList).toContainText('Last initial must be a single letter.');
    await expect(errorsList).toContainText('A valid email address is required.');
  });

  test('accepts a valid submission', async ({ page }) => {
    await page.goto(getHarnessUrl('registration.html'));

    await page.fill('input[name="fbm_first_name"]', 'Jordan');
    await page.fill('input[name="fbm_last_initial"]', 'Q');
    await page.fill('input[name="fbm_email"]', 'jordan.e2e@example.com');
    await page.fill('input[name="fbm_household_size"]', '3');

    await page.click('button:has-text("Submit registration")');

    const successNotice = page.locator('[data-fbm-registration-success]');
    await expect(successNotice).toBeVisible();
    await expect(successNotice).toContainText('Thank you for registering. We have emailed your check-in QR code.');
    await expect(page.locator('.fbm-registration-form__form')).toBeHidden();
  });
});
