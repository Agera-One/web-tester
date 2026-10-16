import { test, expect } from '@playwright/test';
import { login } from '../utils/helpers';

test.describe('Modul Master Barang', () => {
  test.beforeEach(async ({ page }) => {
    // Login otomatis pakai helper
    await login(page);
  });

  test('Dapat memuat halaman daftar barang dengan benar @smoke', async ({ page }) => {
    // Sesuaikan '/master-barang' dengan URL rute asli di SeventhSoft
    await page.goto('/master-barang');
    
    // Validasi URL dan pastikan judul halamannya muncul
    await expect(page).toHaveURL(/.*master-barang/);
    // await expect(page.locator('h1')).toContainText('Daftar Barang'); 
  });
});