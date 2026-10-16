import { test, expect } from '@playwright/test';
import { login } from '../utils/helpers';

test('berhasil masuk ke dashboard web-new @smoke', async ({ page }) => {
  // 1. Eksekusi login dengan penanganan kredensial dinamis & konflik sesi (HTTP 409 / Force Logout)
  await login(page);

  // 2. Validasi keberhasilan masuk ke halaman dashboard
  await expect(page).toHaveURL(/.*dashboard/);
  await expect(page).toHaveTitle(/.*Seventhsoft.*/i);
});
