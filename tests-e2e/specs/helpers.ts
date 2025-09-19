import { Page } from '@playwright/test';
import { execSync } from 'child_process';
import path from 'path';

const repoRoot = path.resolve(__dirname, '..', '..');

export async function loginAsAdmin(page: Page): Promise<void> {
  await page.goto('/wp-login.php');

  const adminBar = page.locator('#wpadminbar');
  if (await adminBar.isVisible().catch(() => false)) {
    return;
  }

  await page.fill('#user_login', 'admin');
  await page.fill('#user_pass', 'password');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle' }),
    page.click('#wp-submit'),
  ]);
}

export function getOption(option: string): string {
  const result = execSync(`npx wp-env run tests-cli wp option get ${option}`, {
    cwd: repoRoot,
    encoding: 'utf8',
  });

  return result.trim();
}
