import { test, expect } from '@playwright/test';

test('berhasil masuk ke dashboard web-new @smoke', async ({ page }) => {
  // 1. Arahkan ke halaman login (Ganti URL dengan rute lokal web-new Anda)
  await page.goto('https://web-new.seventhsoft.id/');

  // 2. Isi formulir kredensial (Sesuaikan atribut name dengan elemen di web-new)
  await page.fill('input[name="email"]', 'bagastest@lt.dev');
  await page.fill('input[name="password"]', '123');
  
  // 3. Klik tombol masuk (Sesuaikan teks atau pemilih tombol)
  await page.click('button:has-text("Login")');

  // 4. Validasi keberhasilan masuk ke halaman dashboard
  await expect(page).toHaveURL(/.*dashboard/);
});
