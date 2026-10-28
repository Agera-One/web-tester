import { test, expect } from '@playwright/test';
import { login } from '../utils/helpers';

test.describe('Modul Purchase Order', () => {
  const baseUrl = process.env.BASE_URL || 'https://web-new.seventhsoft.id';
  // Di Seventhsoft rute menu Purchase Order adalah /purchasing/order
  const purchaseOrderUrl = `${baseUrl}/purchasing/order`;

  test.beforeEach(async ({ page }) => {
    // 1. Login menggunakan kredensial standar
    await login(page);

    // 2. Arahkan ke URL menu Purchase Order (Pesanan Pembelian)
    await page.goto(purchaseOrderUrl);
    await page.waitForLoadState('domcontentloaded');
  });

  test('Dapat memuat halaman daftar pesanan pembelian dengan benar @purchasing @smoke', async ({ page }) => {
    // Verifikasi judul halaman
    await expect(page).toHaveTitle(/.*Seventhsoft.*/i);

    // Verifikasi teks header / navigasi Pembelian atau Order terlihat
    const headerTitle = page.locator('nav, .content-header, .header, body').getByText(/Pembelian|Order/i).first();
    await expect(headerTitle).toBeVisible({ timeout: 10000 });

    // Verifikasi tabel data termuat di halaman
    const dataTable = page.locator('table:visible, .dataTables_wrapper:visible').first();
    await expect(dataTable).toBeVisible({ timeout: 10000 });
  });

  test('Dapat membuka form tambah pesanan pembelian baru @purchasing @critical', async ({ page }) => {
    // 1. Klik tombol tambah / baru (+ di pojok kanan atas dengan rute create)
    const tambahBtn = page.locator('a[href*="/purchasing/order/create"]').first();
    await tambahBtn.waitFor({ state: 'visible', timeout: 10000 });
    await tambahBtn.click();

    // 2. Pastikan diarahkan ke formulir tambah pesanan pembelian
    await expect(page).toHaveURL(/.*\/purchasing\/order\/create/, { timeout: 10000 });
    await page.waitForLoadState('domcontentloaded');

    // 3. Pastikan tombol simpan terlihat pada form
    const saveButton = page.locator('button#btn-save, button[title="Save"]').first();
    await expect(saveButton).toBeVisible({ timeout: 10000 });

    // 4. Pastikan elemen form terlihat di layar (Supplier, Tgl, No PO otomatis)
    const supplierElement = page.locator('form, .card, .content-body').getByText(/Supplier/i).first();
    await expect(supplierElement).toBeVisible({ timeout: 10000 });

    const dateElement = page.locator('form, .card, .content-body').getByText(/^Tgl/i).first();
    await expect(dateElement).toBeVisible({ timeout: 10000 });

    const noPoInput = page.locator('input[placeholder*="OTOMATIS"], input[value*="OTOMATIS"]').first();
    await expect(noPoInput).toBeVisible({ timeout: 10000 });
  });
});
