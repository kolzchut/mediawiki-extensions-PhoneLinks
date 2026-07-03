import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright configuration for the PhoneLinks extension.
 *
 * Run:
 *   npm install && npx playwright test
 *
 * These specs are self-contained: they render a fixture built from the
 * extension's real CSS module and assert that the capability media query
 * reveals the correct variant. No running MediaWiki is required, so there are
 * no environment variables to set. The two projects exercise the desktop
 * (mouse/hover) and mobile (touch/no-hover) sides of the query.
 */
export default defineConfig({
  testDir: './e2e',
  testMatch: ['**/*.spec.ts'],
  timeout: 30000,
  expect: { timeout: 7000 },
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,

  reporter: [
    ['html', { outputFolder: 'playwright-report', open: 'never' }],
    ['list'],
  ],

  use: {
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: [
    {
      name: 'phonelinks-desktop',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'phonelinks-mobile',
      use: { ...devices['Pixel 5'] },
    },
  ],
});
