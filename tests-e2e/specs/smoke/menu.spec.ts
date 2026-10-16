import { test, expect } from '@playwright/test';

test.describe('Modul Smoke Test', () => {
  test('halaman utama @smoke', async ({ page }) => {
    // Navigasi ke halaman utama
    await page.goto('https://example.com');
    // Memastikan judul halaman sesuai
    await expect(page).toHaveTitle(/Example Domain/);
  });
});
