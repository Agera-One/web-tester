import { Page, expect } from '@playwright/test';

/**
 * Mengotomatisasi proses masuk (login) ke aplikasi dengan penanganan:
 * 1. Kredensial input (email & password)
 * 2. Konflik sesi ganda / Single Session (HTTP 409 - "Logout user di komputer lain")
 * 3. Deteksi galat login dini (misal: "Email tidak terdaftar" atau "Password salah")
 * 
 * @param page Instance Playwright Page
 * @param username Nama pengguna atau email (opsional, fallback ke env TEST_USER atau akun default)
 * @param password Kata sandi (opsional, fallback ke env TEST_PASS atau default)
 */
export async function login(
  page: Page,
  username: string = process.env.TEST_USER || 'bagastest@lt.dev',
  password: string = process.env.TEST_PASS || '123'
): Promise<void> {
  const baseUrl = process.env.BASE_URL || 'https://web-new.seventhsoft.id';

  // 1. Arahkan ke halaman login jika belum berada di domain target
  if (!page.url().startsWith(baseUrl)) {
    await page.goto(baseUrl);
  }

  // 2. Isi kolom kredensial
  const usernameInput = page.locator('#email, input[name="email"], input[name="username"]').first();
  await usernameInput.waitFor({ state: 'visible', timeout: 15000 });
  await usernameInput.fill(username);

  const passwordInput = page.locator('#password, input[name="password"]').first();
  await passwordInput.fill(password);

  // 3. Klik tombol masuk / login
  const submitButton = page.locator('#form-login button[type="submit"], button:has-text("Login"), button:has-text("Masuk")').first();
  
  // Tunggu respons AJAX /login/post untuk mendeteksi respon server seketika
  const [response] = await Promise.all([
    page.waitForResponse(res => res.url().includes('/login/post'), { timeout: 15000 }).catch(() => null),
    submitButton.click(),
  ]);

  if (response) {
    const status = response.status();

    // Penanganan konflik sesi ganda (HTTP 409 - User telah login di komputer lain)
    if (status === 409) {
      const forceLoginCheckbox = page.locator('#forcelogin');
      if (await forceLoginCheckbox.isVisible({ timeout: 5000 }).catch(() => false)) {
        await forceLoginCheckbox.check();

        // Klik tombol Login kembali untuk mematikan sesi sebelumnya (force logout)
        await Promise.all([
          page.waitForResponse(res => res.url().includes('/login/post'), { timeout: 15000 }).catch(() => null),
          submitButton.click(),
        ]);
      }
    }
  }

  // Periksa apakah opsi force login muncul di UI meskipun status bukan 409
  const forceLoginCheckbox = page.locator('#forcelogin');
  if (await forceLoginCheckbox.isVisible({ timeout: 1500 }).catch(() => false)) {
    await forceLoginCheckbox.check();
    await Promise.all([
      page.waitForResponse(res => res.url().includes('/login/post'), { timeout: 15000 }).catch(() => null),
      submitButton.click(),
    ]);
  }

  // Periksa apakah terdapat pesan kesalahan login yang muncul di halaman
  const errorMsg = page.locator('#pesan-error');
  if (await errorMsg.isVisible({ timeout: 2000 }).catch(() => false)) {
    const errorText = (await errorMsg.innerText().catch(() => '')).trim();
    if (errorText && !errorText.toLowerCase().includes('telah login')) {
      throw new Error(`Gagal Login ke Seventhsoft: "${errorText}" (Kredensial yang digunakan: ${username}). Silakan periksa kembali email/username dan password pada data Environment.`);
    }
  }

  // Periksa juga jika ada modal SweetAlert error
  const swalError = page.locator('.swal2-popup.swal2-icon-error');
  if (await swalError.isVisible({ timeout: 1000 }).catch(() => false)) {
    const swalText = (await page.locator('.swal2-html-container, .swal2-title').first().innerText().catch(() => '')).trim();
    throw new Error(`Gagal Login ke Seventhsoft (SweetAlert): "${swalText}" (Kredensial: ${username}).`);
  }

  // 4. Menunggu hingga indikator halaman dasbor termuat penuh
  try {
    await expect(page).toHaveURL(/.*dashboard/, { timeout: 15000 });
  } catch (err) {
    // Jika timeout mencapai dashboard, periksa apakah ada teks error di halaman
    const errorText = (await errorMsg.innerText().catch(() => '')).trim();
    if (errorText) {
      throw new Error(`Gagal Login ke Seventhsoft: "${errorText}" (Kredensial: ${username}). Silakan periksa kembali email/username dan password pada data Environment.`);
    }
    throw err;
  }
  await page.waitForLoadState('domcontentloaded');
}

/**
 * Memanipulasi elemen dropdown Select2.
 * Logika: klik container Select2, ketik teks pada kolom pencarian yang muncul,
 * lalu tekan tombol Enter atau klik opsi hasil pencarian pertama.
 * 
 * @param page Instance Playwright Page
 * @param selector Selector elemen Select2 (bisa berupa ID elemen select asli atau container Select2)
 * @param textToSearch Teks yang ingin dicari dan dipilih
 */
export async function fillSelect2(
  page: Page,
  selector: string,
  textToSearch: string
): Promise<void> {
  const target = page.locator(selector);

  // Deteksi apakah selector merujuk ke tag <select> asli yang disembunyikan oleh Select2
  const isSelectTag = await target.evaluate((el: HTMLElement) => el.tagName.toLowerCase() === 'select').catch(() => false);

  if (isSelectTag) {
    // Klik container Select2 yang berdekatan dengan tag select
    const container = page.locator(`${selector} + .select2, ${selector} ~ span.select2, ${selector} ~ .select2-container`).first();
    if (await container.isVisible().catch(() => false)) {
      await container.click();
    } else {
      await target.click({ force: true });
    }
  } else {
    // Klik container atau elemen pemilihan Select2 langsung
    await target.click();
  }

  // Cari kolom input pencarian pada popup/dropdown Select2 yang aktif
  const searchInput = page.locator(
    '.select2-container--open .select2-search__field, .select2-dropdown .select2-search__field, input.select2-search__field'
  ).first();

  await searchInput.waitFor({ state: 'visible', timeout: 5000 });
  await searchInput.fill(textToSearch);

  // Cari opsi pertama dari hasil pencarian (mengecualikan pesan loading atau no result)
  const firstOption = page.locator(
    '.select2-results__options .select2-results__option:not(.select2-results__message)'
  ).first();

  try {
    // Coba klik opsi pertama jika sudah muncul
    await firstOption.waitFor({ state: 'visible', timeout: 3000 });
    await firstOption.click();
  } catch {
    // Fallback: tekan tombol Enter pada kolom input pencarian
    await searchInput.press('Enter');
  }
}
