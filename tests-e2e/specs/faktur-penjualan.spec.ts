import { test, expect } from '@playwright/test';
import { login } from '../utils/helpers';

test.describe('Modul Faktur Penjualan', () => {
  const baseUrl = process.env.BASE_URL || 'https://web-new.seventhsoft.id';
  // Di Seventhsoft rute menu Faktur Penjualan adalah /sales/faktur
  const fakturPenjualanUrl = `${baseUrl}/sales/faktur`;

  test.beforeEach(async ({ page }) => {
    // 1. Login menggunakan kredensial standar
    await login(page);

    // 2. Arahkan ke URL menu Faktur Penjualan
    await page.goto(fakturPenjualanUrl);
    await page.waitForLoadState('domcontentloaded');
  });

  test('Dapat memuat halaman daftar faktur penjualan dengan benar', async ({ page }) => {
    // Verifikasi judul halaman
    await expect(page).toHaveTitle(/.*Seventhsoft.*/i);

    // Verifikasi teks header / navigasi Penjualan terlihat
    const headerTitle = page.locator('nav, .content-header, .header, body').getByText(/Penjualan/i).first();
    await expect(headerTitle).toBeVisible({ timeout: 10000 });

    // Verifikasi tabel data faktur termuat di halaman (elemen tabel yang tampak)
    const dataTable = page.locator('table:visible, .dataTables_wrapper:visible').first();
    await expect(dataTable).toBeVisible({ timeout: 10000 });
  });

  test('Dapat membuka form tambah faktur penjualan baru', async ({ page }) => {
    // 1. Klik tombol tambah / baru (+ di pojok kanan atas dengan rute create)
    const tambahBtn = page.locator('a[href*="/sales/faktur/create"]').first();
    await tambahBtn.waitFor({ state: 'visible', timeout: 10000 });
    await tambahBtn.click();

    // 2. Pastikan diarahkan ke formulir tambah faktur
    await expect(page).toHaveURL(/.*\/sales\/faktur\/create/, { timeout: 10000 });
    await page.waitForLoadState('domcontentloaded');

    // 3. Pastikan elemen form faktur terlihat di layar:
    // Input/Label Pelanggan (Customer) di dalam form / card
    const customerElement = page.locator('form, .card, .content-body').getByText(/^Customer/i).first();
    await expect(customerElement).toBeVisible({ timeout: 10000 });

    // Input/Label Tanggal (Tgl) yang tampak di dalam form
    const dateElement = page.locator('form, .card, .content-body').getByText(/^Tgl/i).first();
    await expect(dateElement).toBeVisible({ timeout: 10000 });

    // Input Nomor Faktur otomatis yang tampak
    const noFakturInput = page.locator('input[placeholder*="OTOMATIS"], input[value*="OTOMATIS"]').first();
    await expect(noFakturInput).toBeVisible({ timeout: 10000 });
  });
});
