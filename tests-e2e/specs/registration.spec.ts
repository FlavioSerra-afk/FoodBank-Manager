import { test, expect } from '@playwright/test';

test.describe('Public registration form', () => {
  test('rejects incomplete submissions', async ({ page }) => {
    await page.goto('/registration/');

    const form = page.locator('.fbm-registration-form__form');
    await expect(form).toBeVisible();

    await page.click('button:has-text("Submit registration")');

    await expect(page.locator('.fbm-registration-form__notice--error')).toContainText(
      'Please correct the errors below and try again.'
    );
    await expect(page.locator('.fbm-registration-form__errors')).toContainText('First name is required.');
    await expect(page.locator('.fbm-registration-form__errors')).toContainText('Last initial must be a single letter.');
    await expect(page.locator('.fbm-registration-form__errors')).toContainText('A valid email address is required.');
  });

  test('accepts a valid submission', async ({ page }) => {
    await page.goto('/registration/');

    await page.fill('input[name="fbm_first_name"]', 'Jordan');
    await page.fill('input[name="fbm_last_initial"]', 'Q');
    await page.fill('input[name="fbm_email"]', 'jordan.e2e@example.com');
    await page.fill('input[name="fbm_household_size"]', '3');

    await page.click('button:has-text("Submit registration")');

    const successNotice = page.locator('.fbm-registration-form__notice--success');
    await expect(successNotice).toContainText('Thank you for registering. We have emailed your check-in QR code.');
  });
});
