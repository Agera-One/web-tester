import { test, expect } from '@playwright/test';
import { login } from '../utils/helpers';

test.describe('Modul Jurnal Umum', () => {
  const baseUrl = process.env.BASE_URL || 'https://web-new.seventhsoft.id';
  // Di Seventhsoft rute menu Jurnal Umum (Transaksi Jurnal) adalah /gl/jurnal
  const jurnalUmumUrl = `${baseUrl}/gl/jurnal`;

  test.beforeEach(async ({ page }) => {
    // 1. Login menggunakan kredensial standar
    await login(page);

    // 2. Arahkan ke URL menu Jurnal Umum
    await page.goto(jurnalUmumUrl);
    await page.waitForLoadState('domcontentloaded');
  });

  test('Dapat memuat halaman daftar jurnal umum dengan benar', async ({ page }) => {
    // Verifikasi judul halaman
    await expect(page).toHaveTitle(/.*Seventhsoft.*/i);

    // Verifikasi teks header / navigasi Jurnal terlihat
    const headerTitle = page.locator('nav, .content-header, .header, body').getByText(/Jurnal|GL|General Ledger/i).first();
    await expect(headerTitle).toBeVisible({ timeout: 10000 });

    // Verifikasi tabel data jurnal termuat di halaman
    const dataTable = page.locator('table:visible, .dataTables_wrapper:visible').first();
    await expect(dataTable).toBeVisible({ timeout: 10000 });
  });

  test('Dapat membuka form tambah jurnal umum baru', async ({ page }) => {
    // 1. Klik tombol tambah / baru (+ di pojok kanan atas dengan rute create)
    const tambahBtn = page.locator('a[href*="/gl/jurnal/create"]').first();
    await tambahBtn.waitFor({ state: 'visible', timeout: 10000 });
    await tambahBtn.click();

    // 2. Pastikan diarahkan ke formulir tambah jurnal umum
    await expect(page).toHaveURL(/.*\/gl\/jurnal\/create/, { timeout: 10000 });
    await page.waitForLoadState('domcontentloaded');

    // 3. Pastikan tombol simpan terlihat pada form
    const saveButton = page.locator('button#btn-save, button[title="Save"]').first();
    await expect(saveButton).toBeVisible({ timeout: 10000 });

    // 4. Pastikan elemen form terlihat di layar (Tgl, No Bukti/Jurnal, Keterangan)
    const dateElement = page.locator('form, .card, .content-body').getByText(/^Tgl/i).first();
    await expect(dateElement).toBeVisible({ timeout: 10000 });

    const noJurnalInput = page.locator('input[placeholder*="OTOMATIS"], input[value*="OTOMATIS"]').first();
    await expect(noJurnalInput).toBeVisible({ timeout: 10000 });

    const keteranganElement = page.locator('form, .card, .content-body').getByText(/Keterangan/i).first();
    await expect(keteranganElement).toBeVisible({ timeout: 10000 });
  });
});
