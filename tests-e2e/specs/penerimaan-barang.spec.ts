import { test, expect } from '@playwright/test';
import { login } from '../utils/helpers';

test.describe('Modul Penerimaan Barang', () => {
  const baseUrl = process.env.BASE_URL || 'https://web-new.seventhsoft.id';
  // Di Seventhsoft rute menu Penerimaan Barang (Barang Datang) adalah /purchasing/receipt
  const penerimaanBarangUrl = `${baseUrl}/purchasing/receipt`;

  test.beforeEach(async ({ page }) => {
    // 1. Login menggunakan kredensial standar
    await login(page);

    // 2. Arahkan ke URL menu Penerimaan Barang
    await page.goto(penerimaanBarangUrl);
    await page.waitForLoadState('domcontentloaded');
  });

  test('Dapat memuat halaman daftar penerimaan barang dengan benar @purchasing @smoke', async ({ page }) => {
    // Verifikasi judul halaman
    await expect(page).toHaveTitle(/.*Seventhsoft.*/i);

    // Verifikasi teks header / navigasi Pembelian atau Barang Datang terlihat
    const headerTitle = page.locator('nav, .content-header, .header, body').getByText(/Pembelian|Barang Datang|Penerimaan/i).first();
    await expect(headerTitle).toBeVisible({ timeout: 10000 });

    // Verifikasi tabel data termuat di halaman
    const dataTable = page.locator('table:visible, .dataTables_wrapper:visible').first();
    await expect(dataTable).toBeVisible({ timeout: 10000 });
  });

  test('Dapat membuka form tambah penerimaan barang baru @purchasing @critical', async ({ page }) => {
    // 1. Klik tombol tambah / baru (+ di pojok kanan atas dengan rute create)
    const tambahBtn = page.locator('a[href*="/purchasing/receipt/create"]').first();
    await tambahBtn.waitFor({ state: 'visible', timeout: 10000 });
    await tambahBtn.click();

    // 2. Pastikan diarahkan ke formulir tambah penerimaan barang
    await expect(page).toHaveURL(/.*\/purchasing\/receipt\/create/, { timeout: 10000 });
    await page.waitForLoadState('domcontentloaded');

    // 3. Pastikan tombol simpan terlihat pada form
    const saveButton = page.locator('button#btn-save, button[title="Save"]').first();
    await expect(saveButton).toBeVisible({ timeout: 10000 });

    // 4. Pastikan elemen form terlihat di layar (Supplier, Tgl, No Bukti otomatis)
    const supplierElement = page.locator('form, .card, .content-body').getByText(/Supplier/i).first();
    await expect(supplierElement).toBeVisible({ timeout: 10000 });

    const dateElement = page.locator('form, .card, .content-body').getByText(/^Tgl/i).first();
    await expect(dateElement).toBeVisible({ timeout: 10000 });

    const noPenerimaanInput = page.locator('input[placeholder*="OTOMATIS"], input[value*="OTOMATIS"]').first();
    await expect(noPenerimaanInput).toBeVisible({ timeout: 10000 });
  });
});
