<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $items = Item::with('histories')
            ->when($search, function ($query, $search) {
                return $query->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('satuan', 'like', "%{$search}%");
            })
            ->get();

        return view('items.index', compact('items', 'search'));
    }

    public function create()
    {
        return view('items.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'stok_awal' => 'required|integer|min:0',
        ]);

        Item::create([
            'nama_barang' => $request->nama_barang,
            'satuan' => $request->satuan,
            'stok_awal' => $request->stok_awal,
            'stok_akhir' => $request->stok_awal,
            'created_by' => auth()->id() ?? 1, // Gunakan user ID 1 jika tidak login
        ]);

        return redirect()->route('items.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        // Middleware sudah handle auth & role check
        $histories = $item->histories()->orderBy('created_at', 'desc')->get();
        return view('items.edit', compact('item', 'histories'));
    }

    public function update(Request $request, Item $item)
    {
        // Middleware sudah handle auth & role check
        $request->validate([
            'type' => 'required|in:penambahan,pengurangan',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $item) {
                $stokSebelum = $item->stok_akhir;

                if ($request->type === 'penambahan') {
                    $stokSesudah = $stokSebelum + $request->jumlah;
                } else {
                    $stokSesudah = $stokSebelum - $request->jumlah;

                    if ($stokSesudah < 0) {
                        throw new \Exception('Stok tidak mencukupi!');
                    }
                }

                // Create history
                ItemHistory::create([
                    'item_id' => $item->id,
                    'type' => $request->type,
                    'jumlah' => $request->jumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'keterangan' => $request->keterangan,
                    'created_by' => auth()->id(),
                ]);

                // Update item
                $item->update([
                    'stok_akhir' => $stokSesudah
                ]);
            });

            return redirect()->route('items.edit', $item)
                ->with('success', 'Stok berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')
            ->with('success', 'Barang berhasil dihapus.');
    }
}
