<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class EnvironmentController extends Controller
{
    public function index()
    {
        $environments = Environment::latest()->get();
        return view('environments.index', compact('environments'));
    }

    public function create()
    {
        return view('environments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        // Mengenkripsi kata sandi agar aman namun dapat didekripsi oleh skrip otomasi pengujian
        $validated['password'] = Crypt::encryptString($validated['password']);

        Environment::create($validated);

        return redirect()->route('environments.index')->with('success', 'Lingkungan pengujian berhasil ditambahkan.');
    }

    public function show(Environment $environment)
    {
        //
    }

    public function edit(Environment $environment)
    {
        return view('environments.edit', compact('environment'));
    }

    public function update(Request $request, Environment $environment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
        ]);

        // Jika form kata sandi diisi, perbarui dengan enkripsi baru. Jika tidak, pertahankan yang lama.
        if ($request->filled('password')) {
            $validated['password'] = Crypt::encryptString($request->password);
        } else {
            unset($validated['password']);
        }

        $environment->update($validated);

        return redirect()->route('environments.index')->with('success', 'Lingkungan pengujian berhasil diperbarui.');
    }

    public function destroy(Environment $environment)
    {
        $environment->delete();
        return redirect()->route('environments.index')->with('success', 'Lingkungan pengujian berhasil dihapus.');
    }
}