<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TestController extends TestCatalogController
{
    /**
     * Menampilkan formulir untuk membuat skenario Playwright baru.
     */
    public function create()
    {
        return view('tests.create');
    }

    /**
     * Menyimpan berkas skenario pengujian baru ke folder tests-e2e/specs
     * dan menyinkronkan data katalog ke pangkalan data.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_berkas' => 'required|string|max:255',
            'isi_skrip' => 'required|string',
        ], [
            'nama_berkas.required' => 'Nama berkas skenario pengujian wajib diisi.',
            'isi_skrip.required' => 'Kode skrip Playwright tidak boleh kosong.',
        ]);

        $fileName = trim($request->nama_berkas);

        // Pastikan nama berkas berakhiran .spec.ts
        if (!str_ends_with(strtolower($fileName), '.spec.ts') && !str_ends_with(strtolower($fileName), '.ts')) {
            $fileName .= '.spec.ts';
        }

        // Sanitasi nama berkas untuk mencegah directory traversal
        $fileName = basename($fileName);

        $dirPath = base_path('tests-e2e/specs');
        if (!File::isDirectory($dirPath)) {
            File::makeDirectory($dirPath, 0755, true);
        }

        $filePath = $dirPath . DIRECTORY_SEPARATOR . $fileName;

        // Simpan isi skrip ke dalam berkas baru
        File::put($filePath, $request->isi_skrip);

        // Jalankan proses sinkronisasi katalog agar data baru terbaca
        $syncResult = $this->performSync();

        $message = "Skenario pengujian '{$fileName}' berhasil dibuat";
        if ($syncResult['success']) {
            $message .= " dan disinkronkan ke katalog pengujian.";
        } else {
            $message .= ", namun sinkronisasi katalog mencatat: " . $syncResult['message'];
        }

        return redirect()->route('tests.index')->with($syncResult['success'] ? 'success' : 'warning', $message);
    }
}
