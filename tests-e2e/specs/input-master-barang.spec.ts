import { test, expect } from '@playwright/test';
import { login } from '../utils/helpers';

test.describe('Modul Master Barang - Input Data', () => {
  const baseUrl = process.env.BASE_URL || 'https://web-new.seventhsoft.id';
  const masterItemUrl = `${baseUrl}/master/item`;

  test.beforeEach(async ({ page }) => {
    // 1. Login menggunakan kredensial standar
    await login(page);

    // 2. Arahkan ke URL menu Master Item (Barang)
    await page.goto(masterItemUrl);
    await page.waitForLoadState('domcontentloaded');
  });

  test('Dapat memuat halaman master barang dan membuka formulir tambah barang @master @smoke', async ({ page }) => {
    // 1. Verifikasi berada pada halaman daftar item
    await expect(page).toHaveURL(/.*\/master\/item/);

    // 2. Klik tombol tambah / create (+ di pojok kanan atas)
    const createBtn = page.locator('a[href*="/master/item/create"], button#btn-create, .btn:has-text("Tambah")').first();
    await createBtn.waitFor({ state: 'visible', timeout: 10000 });
    await createBtn.click();

    // 3. Pastikan berhasil diarahkan ke formulir tambah barang baru
    await expect(page).toHaveURL(/.*\/master\/item\/create/, { timeout: 10000 });
    await page.waitForLoadState('domcontentloaded');

    // 4. Verifikasi elemen input utama dan tombol simpan terlihat
    const kodeInput = page.locator('#item_code, input[name="ITEM_CODE"]').first();
    const namaInput = page.locator('#item_name, input[name="ITEM_NAME"]').first();
    const saveButton = page.locator('button#btn-save, button[title="Save"]').first();

    await expect(kodeInput).toBeVisible({ timeout: 10000 });
    await expect(namaInput).toBeVisible({ timeout: 10000 });
    await expect(saveButton).toBeVisible({ timeout: 10000 });
  });

  test('Berhasil menambah dan menyimpan barang baru @master @critical', async ({ page }) => {
    // 1. Buka formulir pembuatan barang baru
    const createBtn = page.locator('a[href*="/master/item/create"], button#btn-create, .btn:has-text("Tambah")').first();
    await createBtn.waitFor({ state: 'visible', timeout: 10000 });
    await createBtn.click();

    await expect(page).toHaveURL(/.*\/master\/item\/create/, { timeout: 10000 });
    await page.waitForLoadState('domcontentloaded');

    // 2. Buat data unik kode & nama barang agar tidak bentrok dengan data yang sudah ada
    const uniqueSuffix = Date.now().toString().slice(-5);
    const itemCode = `BRG-${uniqueSuffix}`;
    const itemName = `Barang Uji E2E ${uniqueSuffix}`;

    // 3. Isi kolom Kode Barang dan Nama Barang
    const kodeInput = page.locator('#item_code, input[name="ITEM_CODE"]').first();
    const namaInput = page.locator('#item_name, input[name="ITEM_NAME"]').first();
    const saveButton = page.locator('button#btn-save, button[title="Save"]').first();

    await kodeInput.fill(itemCode);
    await namaInput.fill(itemName);

    // 4. Klik tombol Simpan
    await saveButton.click();

    // 5. Validasi keberhasilan penyimpanan data:
    // Seventhsoft secara otomatis mengarahkan ke halaman detail view item baru (/master/item/view/<id>)
    await expect(page).toHaveURL(/.*\/master\/item\/(view\/\d+|item)/, { timeout: 15000 });
  });
});