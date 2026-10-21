import { test, expect } from '@playwright/test';
import { login } from '../utils/helpers';

test.describe('Modul Master Barang', () => {
  test.beforeEach(async ({ page }) => {
    // Login otomatis pakai helper
    await login(page);
  });

  test('Dapat memuat halaman daftar barang dengan benar @smoke', async ({ page }) => {
    // 1. Ambil URL target dari environment
    const baseUrl = process.env.BASE_URL || 'https://web-new.seventhsoft.id';

    // 2. Lakukan navigasi menggunakan URL yang telah digabung secara utuh
    await page.goto(`${baseUrl}/master-barang`);

    // 3. Validasi keberhasilan pemuatan rute target
    await expect(page).toHaveURL(/.*master-barang/);
  });
});