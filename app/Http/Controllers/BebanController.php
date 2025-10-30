<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Beban;

class BebanController extends Controller
{
    public function index()
    {
        $bebans = Beban::latest()->get();
        return view('master-data.beban.index', compact('bebans'));
    }

    public function create()
    {
        return view('master-data.beban.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_akun' => 'required|string|max:255',
            'nominal' => 'required|numeric',
            'tanggal' => 'required|date',
        ]);

        Beban::create($request->all());

        return redirect()->route('master-data.beban.index')
            ->with('success', 'Data beban berhasil ditambahkan.');
    }

    public function edit(Beban $beban)
    {
        return view('master-data.beban.edit', compact('beban'));
    }

    public function update(Request $request, Beban $beban)
    {
        $request->validate([
            'nama_akun' => 'required|string|max:255',
            'nominal' => 'required|numeric',
            'tanggal' => 'required|date',
        ]);

        $beban->update($request->all());

        return redirect()->route('master-data.beban.index')
            ->with('success', 'Data beban berhasil diperbarui.');
    }

    public function destroy(Beban $beban)
    {
        $beban->delete();

        return redirect()->route('master-data.beban.index')
            ->with('success', 'Data beban berhasil dihapus.');
    }
}
