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

    public function edit(Request $request)
    {
        $filename = $request->query('file');
        $path = base_path('tests-e2e/specs/' . $filename);

        if (!File::exists($path)) {
            return redirect()->route('tests.index')->with('error', 'File skenario tidak ditemukan.');
        }

        $content = File::get($path);
        return view('tests.edit', compact('filename', 'content'));
    }

    public function update(Request $request)
    {
        $filename = $request->input('file');
        $path = base_path('tests-e2e/specs/' . $filename);
        
        $content = $request->input('content', $request->input('isi_skrip'));

        // Menimpa isi file lama dengan kode yang baru
        File::put($path, $content);

        // Sinkronisasi katalog agar data kasus uji langsung diperbarui
        $this->performSync();

        return redirect()->route('tests.index')->with('success', 'Skenario berhasil diperbarui!');
    }

    public function destroy(Request $request)
    {
        $filename = $request->input('file');
        $path = base_path('tests-e2e/specs/' . $filename);

        if (File::exists($path)) {
            // Menghapus file fisik dari direktori
            File::delete($path);

            $suite = \App\Models\TestSuite::where('title', $filename)
                ->orWhere('file_path', 'like', '%' . $filename)
                ->first();

            if ($suite) {
                \App\Models\TestCase::where('test_suite_id', $suite->id)->delete();
                $suite->delete();
            }

            return redirect()->route('tests.index')->with('success', 'Skenario berhasil dihapus!');
        }
        return redirect()->route('tests.index')->with('error', 'Skenario gagal dihapus.');
    }
}
