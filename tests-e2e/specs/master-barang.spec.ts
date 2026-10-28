import { test, expect } from '@playwright/test';
import { login, waitForLoading } from '../utils/helpers';

test.describe('Modul Master Barang', () => {
  const baseUrl = process.env.BASE_URL || 'https://web-new.seventhsoft.id';
  const masterItemUrl = `${baseUrl}/master/item`;

  test.beforeEach(async ({ page }) => {
    // 1. Login menggunakan helper
    await login(page);

    // 2. Arahkan ke URL menu Master Barang / Item
    await page.goto(masterItemUrl);
    await page.waitForLoadState('domcontentloaded');
    await waitForLoading(page, 5000);
  });

  test('Dapat memuat halaman daftar barang dengan benar @master @smoke', async ({ page }) => {
    // Verifikasi judul halaman
    await expect(page).toHaveTitle(/.*Seventhsoft.*/i);

    // Verifikasi berada pada URL master item
    await expect(page).toHaveURL(/.*\/master\/item/);

    // Verifikasi tabel data barang termuat di halaman
    const dataTable = page.locator('table:visible, .dataTables_wrapper:visible, #table-item').first();
    await expect(dataTable).toBeVisible({ timeout: 10000 });
  });
});