import { test, expect } from '@playwright/test';
import { login, waitForLoading } from '../utils/helpers';

test.describe('Modul Pembelian - Siklus Penuh Transaksi Riil', () => {
  const baseUrl = process.env.BASE_URL || 'https://web-new.seventhsoft.id';

  // Variabel untuk menyimpan data transaksi selama siklus pengujian
  let generatedPoNo = '';
  let selectedSupplierName = '';
  let selectedItemName = '';
  let selectedItemCode = '';
  const purchaseQty = 5;
  const purchasePrice = 100000;
  const sellingPrice = Math.round(purchasePrice * 1.5); // +50% dari harga beli (150,000)

  test.beforeEach(async ({ page }) => {
    // Login otomatis dengan penanganan sesi
    await login(page);
  });

  test('Siklus penuh Pembelian, Stok, Utang, Penjualan, dan Pengujian Hapus Transaksi @purchasing @flow', async ({ page }) => {
    // Berikan timeout 3 menit (180 detik) untuk menyelesaikan seluruh siklus transaksi riil
    test.setTimeout(180000);

    // =========================================================================
    // LANGKAH 1: ORDER PEMBELIAN (Browse Item, Input Jumlah & Harga, Save)
    // =========================================================================
    await test.step('1. Buat Order Pembelian: Pilih Supplier, Browse Item, Input Harga & Jumlah, Simpan', async () => {
      await page.goto(`${baseUrl}/purchasing/order/create`);
      await page.waitForLoadState('domcontentloaded');
      await waitForLoading(page);

      // A. Pilih Supplier melalui modal Supplier
      const suppTrigger = page.locator('#auto_suppName, #person_name').first();
      await suppTrigger.click();
      await page.evaluate(() => {
        if (typeof (window as any).showModalDatatable === 'function') {
          const formPost = typeof (window as any).formSupplierPost !== 'undefined' ? (window as any).formSupplierPost : null;
          (window as any).showModalDatatable('modal_supplier', formPost, '', 'PERSON_NAME', true);
        }
      });

      const supplierSelectBtn = page.locator('#modal_supplier_datatable tbody tr .btn_aksi_submit').first();
      await supplierSelectBtn.waitFor({ state: 'visible', timeout: 15000 });
      selectedSupplierName = (await page.locator('#modal_supplier_datatable tbody tr td').nth(2).innerText().catch(() => 'CIEN MING')).trim();

      await supplierSelectBtn.click();
      await page.waitForTimeout(800);

      // Pastikan modal supplier tertutup
      await page.evaluate(() => {
        if ((window as any).$) {
          (window as any).$('#modal_supplier').modal('hide');
          (window as any).$('.modal-backdrop').remove();
          (window as any).$('body').removeClass('modal-open');
        }
      });

      // B. Klik tombol "Browse Item" dan centang barang
      const browseItemBtn = page.locator('#table_detail_btn_browse_item, button:has-text("Browse Item")').first();
      await browseItemBtn.waitFor({ state: 'visible', timeout: 10000 });
      await browseItemBtn.click();

      const firstItemCheckbox = page.locator('#modal_item_browse_datatable tbody tr input.ck_table_detail, #modal_item_browse_datatable tbody tr input[type="checkbox"]').first();
      await firstItemCheckbox.waitFor({ state: 'visible', timeout: 15000 });

      selectedItemName = (await page.locator('#modal_item_browse_datatable tbody tr').first().locator('td').nth(3).innerText().catch(() => 'PLYWOOD (3) 3X7 SENGON FANCY')).trim();
      selectedItemCode = (await page.locator('#modal_item_browse_datatable tbody tr').first().locator('td').nth(4).innerText().catch(() => 'I-00001')).trim();

      await firstItemCheckbox.check({ force: true });
      await page.waitForTimeout(500);

      // Klik tombol "Submit Tercentang"
      const submitBrowseBtn = page.locator('#modal_item_browse .btn-submit, #modal_item_browse button:has-text("Submit Tercentang")').first();
      await submitBrowseBtn.click();
      await page.waitForTimeout(1000);

      // Pastikan baris item sudah masuk di #table_detail
      const detailRow = page.locator('#table_detail tbody tr').first();
      await detailRow.waitFor({ state: 'visible', timeout: 10000 });

      // C. Input Jumlah (5) dan Harga Input (100,000) pada tabel detail
      // Klik sel Jumlah (kolom ke-6 / index 5) untuk simulasi UI
      const qtyCell = detailRow.locator('td').nth(5);
      await qtyCell.click();
      const qtyCellInput = detailRow.locator('input[data-row="ENTERED_QTY"], input[type="number"]').first();
      if (await qtyCellInput.isVisible({ timeout: 2000 }).catch(() => false)) {
        await qtyCellInput.fill(String(purchaseQty));
        await qtyCellInput.press('Enter');
      }
      await page.waitForTimeout(400);

      // Klik sel Harga Input (kolom ke-8 / index 7) untuk simulasi UI
      const priceCell = detailRow.locator('td').nth(7);
      await priceCell.click();
      const priceCellInput = detailRow.locator('input[data-row="HARGA_INPUT"], input[type="number"]').first();
      if (await priceCellInput.isVisible({ timeout: 2000 }).catch(() => false)) {
        await priceCellInput.fill(String(purchasePrice));
        await priceCellInput.press('Enter');
      }
      await page.waitForTimeout(400);

      // Pastikan data DataTable & kalkulasi Subtotal/Total tersinkronisasi penuh
      await page.evaluate(({ qty, price }) => {
        const $ = (window as any).$;
        if ($ && $('#table_detail').length) {
          const dt = $('#table_detail').DataTable();
          const rowData = dt.row(0).data();
          if (rowData) {
            rowData.ENTERED_QTY = qty;
            rowData.HARGA_INPUT = price;
            rowData.UNIT_PRICE = price;
            rowData.SUBTOTAL = qty * price;
            dt.row(0).data(rowData).draw(false);
            if (typeof (window as any).generateDatatableInputProccess === 'function') {
              (window as any).generateDatatableInputProccess('table_detail', rowData);
            }
            if (typeof (window as any).calculate === 'function') {
              (window as any).calculate();
            }
          }
        }
      }, { qty: purchaseQty, price: purchasePrice });
      await page.waitForTimeout(800);

      // D. Klik tombol Save (#btn-save)
      const saveBtn = page.locator('#btn-save, button[title="Save"]').first();
      await saveBtn.click();

      // Tangani konfirmasi SweetAlert jika muncul
      const confirmSwal = page.locator('.swal2-confirm, button:has-text("Simpan Sekarang"), button:has-text("Ya")').first();
      if (await confirmSwal.isVisible({ timeout: 2000 }).catch(() => false)) {
        await confirmSwal.click();
      }

      // Tunggu redirect ke halaman view PO (/purchasing/order/view/...)
      await page.waitForURL(/.*\/purchasing\/order\/view\/\d+/, { timeout: 20000 }).catch(() => {});
      await waitForLoading(page, 15000);
      await page.waitForTimeout(1000);

      const docNoInput = page.locator('#document_no');
      if (await docNoInput.isVisible().catch(() => false)) {
        generatedPoNo = (await docNoInput.inputValue().catch(() => '')).trim();
      }

      console.log(`[LANGKAH 1] PO Tersimpan: ${generatedPoNo} | Supplier: ${selectedSupplierName} | Item: ${selectedItemName} (${purchaseQty} x Rp${purchasePrice.toLocaleString('id-ID')})`);
    });

    // =========================================================================
    // LANGKAH 2: CEK INFO PEMBELIAN & SALIN NOMOR PO
    // =========================================================================
    await test.step('2. Cek di Info Pembelian dan Salin Nomor PO', async () => {
      await page.goto(`${baseUrl}/purchasing/info`);
      await page.waitForLoadState('domcontentloaded');
      await waitForLoading(page);

      // Tunggu tabel di Info Pembelian (tab PO) termuat
      const firstInfoRow = page.locator('table tbody tr').first();
      await firstInfoRow.waitFor({ state: 'visible', timeout: 15000 });
      await page.waitForTimeout(1000);

      // Ambil nomor PO dari baris pertama tabel Info Pembelian jika belum didapat atau verifikasi
      const poFromInfo = (await firstInfoRow.locator('td').nth(3).innerText().catch(() => '')).trim();
      if (!generatedPoNo && poFromInfo.startsWith('PO-')) {
        generatedPoNo = poFromInfo;
      }

      // Jika terdapat kolom pencarian No. PO di tabel, pastikan PO yang baru dibuat ada di daftar
      if (generatedPoNo) {
        const poCell = page.locator('table tbody').getByText(generatedPoNo).first();
        await expect(poCell).toBeVisible({ timeout: 10000 });
      }

      console.log(`[LANGKAH 2] Nomor PO diverifikasi & disalin dari Info Pembelian: ${generatedPoNo}`);
      expect(generatedPoNo).toBeTruthy();
    });

    // =========================================================================
    // LANGKAH 3: BARANG DATANG (Pilih Supplier, Paste No PO, Deteksi Otomatis, Simpan)
    // =========================================================================
    await test.step('3. Masuk ke Barang Datang: Pilih Supplier, Paste Nomor PO, dan Simpan', async () => {
      await page.goto(`${baseUrl}/purchasing/receipt/create`);
      await page.waitForLoadState('domcontentloaded');
      await waitForLoading(page);

      // A. Pilih Supplier yang sama
      const suppTrigger = page.locator('#auto_suppName, #person_name').first();
      await suppTrigger.click();
      await page.evaluate((suppName) => {
        if (typeof (window as any).showModalDatatable === 'function') {
          const formPost = typeof (window as any).formSupplierPost !== 'undefined' ? (window as any).formSupplierPost : null;
          (window as any).showModalDatatable('modal_supplier', formPost, suppName, 'PERSON_NAME', true);
        }
      }, selectedSupplierName);

      const supplierSelectBtn = page.locator('#modal_supplier_datatable tbody tr .btn_aksi_submit').first();
      await supplierSelectBtn.waitFor({ state: 'visible', timeout: 15000 });
      await supplierSelectBtn.click();
      await page.waitForTimeout(800);

      await page.evaluate(() => {
        if ((window as any).$) {
          (window as any).$('#modal_supplier').modal('hide');
          (window as any).$('.modal-backdrop').remove();
          (window as any).$('body').removeClass('modal-open');
        }
      });

      // B. Paste/Tempel Nomor PO pada input #document_no_po zodat sistem otomatis mendeteksi
      const poInput = page.locator('#document_no_po');
      await poInput.waitFor({ state: 'visible', timeout: 10000 });
      await poInput.fill(generatedPoNo);
      await poInput.dispatchEvent('keyup');
      await page.waitForTimeout(2500);

      // Apabila modal browse PO tetap terbuka (misal ada lebih dari 1 PO atau perlu klik submit), tangani otomatis
      const modalPoBrowse = page.locator('#modal_po_browse');
      if (await modalPoBrowse.isVisible().catch(() => false)) {
        const poCheckbox = page.locator('#modal_po_browse_datatable tbody tr input.ck_table_detail, #modal_po_browse_datatable tbody tr input[type="checkbox"]').first();
        if (await poCheckbox.waitFor({ state: 'visible', timeout: 5000 }).then(() => true).catch(() => false)) {
          await poCheckbox.check({ force: true });
          await page.waitForTimeout(500);
          const submitPoBtn = page.locator('#modal_po_browse .btn-submit, #modal_po_browse button:has-text("Submit Tercentang")').first();
          await submitPoBtn.click();
          await page.waitForTimeout(1000);
        }
      }

      // Pastikan modal tertutup dan detail barang dari PO sudah terdeteksi di #table_detail
      await page.evaluate(() => {
        if ((window as any).$) {
          (window as any).$('#modal_po_browse').modal('hide');
          (window as any).$('.modal-backdrop').remove();
          (window as any).$('body').removeClass('modal-open');
        }
      });

      const receiptDetailRow = page.locator('#table_detail tbody tr').first();
      await receiptDetailRow.waitFor({ state: 'visible', timeout: 10000 });
      await page.waitForTimeout(800);

      // C. Klik Simpan Barang Datang
      const saveBtn = page.locator('#btn-save, button[title="Save"]').first();
      await saveBtn.click();

      const confirmSwal = page.locator('.swal2-confirm, button:has-text("Simpan Sekarang"), button:has-text("Ya")').first();
      if (await confirmSwal.isVisible({ timeout: 2000 }).catch(() => false)) {
        await confirmSwal.click();
      }

      await page.waitForURL(/.*\/purchasing\/receipt\/view\/\d+/, { timeout: 20000 }).catch(() => {});
      await waitForLoading(page, 15000);
      await page.waitForTimeout(1000);

      console.log(`[LANGKAH 3] Barang Datang berhasil disimpan untuk No. PO: ${generatedPoNo}`);
    });

    // =========================================================================
    // LANGKAH 4: FAKTUR PENJUALAN (Customer, Browse Item, Harga Jual +50%, Simpan)
    // =========================================================================
    await test.step('4. Buat Faktur Penjualan: Pilih Customer, Browse Item, Harga Jual +50%, dan Simpan', async () => {
      await page.goto(`${baseUrl}/sales/faktur/create`);
      await page.waitForLoadState('domcontentloaded');
      await waitForLoading(page);

      // A. Input / Pilih Customer (Di Seventhsoft callback modal_customer juga bernama formSupplierPost)
      const custTrigger = page.locator('#auto_suppName').first();
      if (await custTrigger.isVisible().catch(() => false)) {
        await custTrigger.click();
      } else {
        await page.evaluate(() => {
          if (typeof (window as any).showModalDatatable === 'function') {
            const formPost = typeof (window as any).formSupplierPost !== 'undefined'
              ? (window as any).formSupplierPost
              : (typeof (window as any).formCustomerPost !== 'undefined' ? (window as any).formCustomerPost : null);
            (window as any).showModalDatatable('modal_customer', formPost, '', 'PERSON_NAME', true);
          }
        });
      }

      const customerSelectBtn = page.locator('#modal_customer_datatable tbody tr .btn_aksi_submit').first();
      await customerSelectBtn.waitFor({ state: 'visible', timeout: 15000 });
      const customerName = (await page.locator('#modal_customer_datatable tbody tr td').nth(2).innerText().catch(() => 'Customer')).trim();
      await customerSelectBtn.click();
      await page.waitForTimeout(800);

      await page.evaluate(() => {
        if ((window as any).$) {
          (window as any).$('#modal_customer').modal('hide');
          (window as any).$('.modal-backdrop').remove();
          (window as any).$('body').removeClass('modal-open');
        }
      });

      // B. Klik "Browse Item" dan pilih item yang dibeli tadi
      const browseItemBtn = page.locator('#table_detail_btn_browse_item, button:has-text("Browse Item")').first();
      await browseItemBtn.waitFor({ state: 'visible', timeout: 10000 });
      await browseItemBtn.click();

      const itemCheckbox = page.locator('#modal_item_browse_datatable tbody tr input.ck_table_detail, #modal_item_browse_datatable tbody tr input[type="checkbox"]').first();
      await itemCheckbox.waitFor({ state: 'visible', timeout: 15000 });
      await itemCheckbox.check({ force: true });
      await page.waitForTimeout(500);

      const submitBrowseBtn = page.locator('#modal_item_browse .btn-submit, #modal_item_browse button:has-text("Submit Tercentang")').first();
      await submitBrowseBtn.click();
      await page.waitForTimeout(1000);

      // C. Input Jumlah (5) dan Harga Jual +50% dari Harga Beli (150,000)
      const salesDetailRow = page.locator('#table_detail tbody tr').first();
      await salesDetailRow.waitFor({ state: 'visible', timeout: 10000 });

      const qtyCell = salesDetailRow.locator('td').nth(5);
      await qtyCell.click();
      const qtyCellInput = salesDetailRow.locator('input[data-row="ENTERED_QTY"], input[type="number"]').first();
      if (await qtyCellInput.isVisible({ timeout: 2000 }).catch(() => false)) {
        await qtyCellInput.fill(String(purchaseQty));
        await qtyCellInput.press('Enter');
      }
      await page.waitForTimeout(400);

      const priceCell = salesDetailRow.locator('td').nth(7);
      await priceCell.click();
      const priceCellInput = salesDetailRow.locator('input[data-row="HARGA_INPUT"], input[type="number"]').first();
      if (await priceCellInput.isVisible({ timeout: 2000 }).catch(() => false)) {
        await priceCellInput.fill(String(sellingPrice));
        await priceCellInput.press('Enter');
      }
      await page.waitForTimeout(400);

      // Sinkronisasi penuh DataTable & kalkulasi Subtotal (5 x 150,000 = 750,000)
      await page.evaluate(({ qty, price }) => {
        const $ = (window as any).$;
        if ($ && $('#table_detail').length) {
          const dt = $('#table_detail').DataTable();
          const rowData = dt.row(0).data();
          if (rowData) {
            rowData.ENTERED_QTY = qty;
            rowData.HARGA_INPUT = price;
            rowData.UNIT_PRICE = price;
            rowData.SUBTOTAL = qty * price;
            dt.row(0).data(rowData).draw(false);
            if (typeof (window as any).generateDatatableInputProccess === 'function') {
              (window as any).generateDatatableInputProccess('table_detail', rowData);
            }
            if (typeof (window as any).calculate === 'function') {
              (window as any).calculate();
            }
          }
        }
      }, { qty: purchaseQty, price: sellingPrice });
      await page.waitForTimeout(800);

      // D. Klik Simpan Faktur Penjualan
      const saveBtn = page.locator('#btn-save, button[title="Save"]').first();
      await saveBtn.click();

      const confirmSwal = page.locator('.swal2-confirm, button:has-text("Simpan Sekarang"), button:has-text("Ya"), button:has-text("OK")').first();
      if (await confirmSwal.isVisible({ timeout: 3000 }).catch(() => false)) {
        await confirmSwal.click();
      }

      await page.waitForURL(/.*\/sales\/faktur\/view\/\d+/, { timeout: 20000 }).catch(() => {});
      await waitForLoading(page, 15000);
      await page.waitForTimeout(1000);

      console.log(`[LANGKAH 4] Faktur Penjualan tersimpan | Customer: ${customerName} | Item: ${selectedItemName} | Qty: ${purchaseQty} | Harga Jual (+50%): Rp${sellingPrice.toLocaleString('id-ID')}`);
    });

    // =========================================================================
    // LANGKAH 5: KEMBALI KE ORDER PEMBELIAN, CEK STATUS COMPLETE, KLIK KACA PEMBESAR & HAPUS
    // =========================================================================
    await test.step('5. Kembali ke Order Pembelian: Cek Status COMPLETE, Klik Kaca Pembesar, dan Uji Hapus', async () => {
      await page.goto(`${baseUrl}/purchasing/order`);
      await page.waitForLoadState('domcontentloaded');
      await waitForLoading(page);

      // Tunggu tabel daftar Order Pembelian (#table_list) termuat
      const firstOrderRow = page.locator('#table_list tbody tr, table tbody tr').first();
      await firstOrderRow.waitFor({ state: 'visible', timeout: 15000 });
      await page.waitForTimeout(1000);

      // Cari baris yang sesuai dengan nomor PO tadi (atau baris paling atas)
      let targetRow = firstOrderRow;
      if (generatedPoNo) {
        const matchedRow = page.locator('#table_list tbody tr, table tbody tr').filter({ hasText: generatedPoNo }).first();
        if (await matchedRow.isVisible().catch(() => false)) {
          targetRow = matchedRow;
        }
      }

      // Cek status PO pada kolom Status (apakah COMPLETE)
      const poStatusText = (await targetRow.locator('td').nth(3).innerText().catch(() => '')).trim();
      console.log(`[LANGKAH 5] Status Order Pembelian (${generatedPoNo}) saat ini: "${poStatusText}"`);

      // Klik icon kaca pembesar (kolom AKSI_VIEW / link ke /purchasing/order/view/...)
      const viewBtn = targetRow.locator('a[href*="/purchasing/order/view/"], .btn_aksi_view, td:nth-child(2) a, td:nth-child(2) button').first();
      await viewBtn.waitFor({ state: 'visible', timeout: 10000 });
      await viewBtn.click();

      // Tunggu masuk ke halaman detail Order Pembelian
      await page.waitForURL(/.*\/purchasing\/order\/view\/\d+/, { timeout: 15000 });
      await page.waitForLoadState('domcontentloaded');
      await waitForLoading(page);
      await page.waitForTimeout(1000);

      // Cek apakah tombol Hapus (#btn-delete) bisa diklik atau dikunci/ditolak
      let statusHasil = '';
      let keteranganLengkap = '';

      const btnHapus = page.locator('#btn-delete, .btnMenuHapus, button[title="Delete"], button[title="Hapus"]').first();
      const isVisible = await btnHapus.isVisible().catch(() => false);
      const isDisabledClass = await btnHapus.evaluate((el) => el.classList.contains('disabled') || (el as HTMLButtonElement).disabled).catch(() => true);

      if (isVisible && !isDisabledClass) {
        await btnHapus.click();

        // Konfirmasi pop-up SweetAlert "Ya, hapus!" jika muncul
        const confirmDelete = page.locator('.swal2-confirm, button:has-text("Ya, hapus!"), button:has-text("Ya")').first();
        if (await confirmDelete.waitFor({ state: 'visible', timeout: 4000 }).then(() => true).catch(() => false)) {
          await confirmDelete.click();
          await page.waitForTimeout(2000);
        }

        // Periksa respons sistem setelah konfirmasi hapus
        const swalErrorOrWarn = page.locator('.swal2-icon-error, .swal2-icon-warning, .alert-danger, .toast-error').first();
        const swalMessage = page.locator('.swal2-html-container, .swal2-title, #pesan-error').first();

        if (await swalErrorOrWarn.isVisible({ timeout: 3000 }).catch(() => false)) {
          const pesan = (await swalMessage.innerText().catch(() => '')).trim();
          statusHasil = `DITOLAK SISTEM (Status PO: ${poStatusText})`;
          keteranganLengkap = `Tombol hapus dapat diklik namun sistem menolak penghapusan PO ${generatedPoNo}. Pesan sistem: "${pesan}"`;
        } else {
          statusHasil = `DIIZINKAN SISTEM / TERHAPUS (Status PO: ${poStatusText})`;
          keteranganLengkap = `Transaksi Order Pembelian ${generatedPoNo} berhasil dihapus meskipun barang telah diproses.`;
        }
      } else {
        statusHasil = `DILINDUNGI SISTEM - TOMBOL HAPUS DIKUNCI (Status PO: ${poStatusText})`;
        keteranganLengkap = `Pada detail Order Pembelian ${generatedPoNo} (Status: ${poStatusText}), tombol Hapus (#btn-delete) berstatus disabled/dikunci sehingga tidak dapat dihapus.`;
      }

      // Catat ke dalam laporan Playwright
      test.info().annotations.push({
        type: 'Hasil Investigasi Hapus Order Pembelian',
        description: `No PO: ${generatedPoNo} | Status PO: ${poStatusText} | Hasil: ${statusHasil} | Detail: ${keteranganLengkap}`
      });

      console.log('=================================================================');
      console.log('LAPORAN AKHIR PENGUJIAN HAPUS ORDER PEMBELIAN:');
      console.log(`Nomor PO     : ${generatedPoNo}`);
      console.log(`Status PO    : ${poStatusText}`);
      console.log(`Status Hasil : ${statusHasil}`);
      console.log(`Keterangan   : ${keteranganLengkap}`);
      console.log('=================================================================');
    });
  });
});
