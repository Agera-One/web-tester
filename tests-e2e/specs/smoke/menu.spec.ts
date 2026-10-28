import { test, expect } from '@playwright/test';
import { login, waitForLoading } from '../../utils/helpers';

test.describe('Modul Smoke Test - Navigasi Menu ERP', () => {
  const baseUrl = process.env.BASE_URL || 'https://web-new.seventhsoft.id';

  test.beforeEach(async ({ page }) => {
    // Login otomatis menggunakan helper
    await login(page);
  });

  const menus = [
    { name: 'Dashboard', path: '/dashboard' },
    { name: 'Sales - Faktur Penjualan', path: '/sales/faktur' },
    { name: 'Sales - Pesanan Penjualan', path: '/sales/order' },
    { name: 'Purchasing - Pesanan Pembelian', path: '/purchasing/order' },
    { name: 'Purchasing - Penerimaan Barang', path: '/purchasing/receipt' },
    { name: 'Inventory - Master Item / Barang', path: '/master/item' },
    { name: 'General Ledger - Transaksi Jurnal', path: '/gl/jurnal' },
    { name: 'Finance - Kas Keluar (BKK)', path: '/finance/bkk' },
    { name: 'Finance - Kas Masuk (BKM)', path: '/finance/bkm' },
    { name: 'Master - Pelanggan', path: '/master/customer' },
    { name: 'Master - Supplier', path: '/master/supplier' },
  ];

  for (const menu of menus) {
    test(`Membuka menu ${menu.name} tanpa error @smoke @menu`, async ({ page }) => {
      // 1. Arahkan ke URL menu
      const response = await page.goto(`${baseUrl}${menu.path}`, { waitUntil: 'domcontentloaded' });

      // 2. Pastikan HTTP status sukses (bukan 500 atau crash)
      if (response) {
        expect(response.status(), `Status HTTP untuk ${menu.path} tidak boleh 500/502/503`).toBeLessThan(500);
      }

      // 3. Tunggu animasi/overlay loading selesai jika ada
      await waitForLoading(page, 5000);

      // 4. Verifikasi judul halaman mengandung Seventhsoft
      await expect(page).toHaveTitle(/.*Seventhsoft.*/i);

      // 5. Verifikasi tidak ada pesan error internal server (Laravel Whoops, Exception, Fatal error)
      const errorContent = page.locator('text="Whoops! There was an error.", text="500 | Server Error", text="SQLSTATE", text="Fatal error"');
      await expect(errorContent).toHaveCount(0);

      // 6. Verifikasi struktur layout halaman termuat
      const mainContent = page.locator('body, .content-wrapper, .main-content, #app').first();
      await expect(mainContent).toBeVisible();
    });
  }
});
