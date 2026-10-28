import { test, expect } from '@playwright/test';
import { login } from '../utils/helpers';

test.describe('Modul Pembayaran', () => {
  const baseUrl = process.env.BASE_URL || 'https://web-new.seventhsoft.id';
  // Di Seventhsoft rute menu Pembayaran (Bukti Kas Keluar) adalah /finance/bkk
  const pembayaranUrl = `${baseUrl}/finance/bkk`;

  test.beforeEach(async ({ page }) => {
    // 1. Login menggunakan kredensial standar
    await login(page);

    // 2. Arahkan ke URL menu Pembayaran (Bukti Kas Keluar)
    await page.goto(pembayaranUrl);
    await page.waitForLoadState('domcontentloaded');
  });

  test('Dapat memuat halaman daftar pembayaran dengan benar @finance @smoke', async ({ page }) => {
    // Verifikasi judul halaman
    await expect(page).toHaveTitle(/.*Seventhsoft.*/i);

    // Verifikasi teks header / navigasi Kas atau Keluar terlihat
    const headerTitle = page.locator('nav, .content-header, .header, body').getByText(/Kas|Bank|Keluar|BKK/i).first();
    await expect(headerTitle).toBeVisible({ timeout: 10000 });

    // Verifikasi tabel data pembayaran termuat di halaman
    const dataTable = page.locator('table:visible, .dataTables_wrapper:visible').first();
    await expect(dataTable).toBeVisible({ timeout: 10000 });
  });

  test('Dapat membuka form tambah pembayaran baru @finance @critical', async ({ page }) => {
    // 1. Klik tombol tambah / baru (+ di pojok kanan atas dengan rute create)
    const tambahBtn = page.locator('a[href*="/finance/bkk/create"]').first();
    await tambahBtn.waitFor({ state: 'visible', timeout: 10000 });
    await tambahBtn.click();

    // 2. Pastikan diarahkan ke formulir tambah pembayaran
    await expect(page).toHaveURL(/.*\/finance\/bkk\/create/, { timeout: 10000 });
    await page.waitForLoadState('domcontentloaded');

    // 3. Pastikan tombol simpan terlihat pada form
    const saveButton = page.locator('button#btn-save, button[title="Save"]').first();
    await expect(saveButton).toBeVisible({ timeout: 10000 });

    // 4. Pastikan elemen form terlihat di layar (Tgl, No Bukti otomatis, Kas/Bank)
    const dateElement = page.locator('form, .card, .content-body').getByText(/^Tgl/i).first();
    await expect(dateElement).toBeVisible({ timeout: 10000 });

    const noBuktiInput = page.locator('input[placeholder*="OTOMATIS"], input[value*="OTOMATIS"]').first();
    await expect(noBuktiInput).toBeVisible({ timeout: 10000 });

    const kasBankElement = page.locator('form, .card, .content-body').getByText(/Kas|Bank|Akun/i).first();
    await expect(kasBankElement).toBeVisible({ timeout: 10000 });
  });
});
