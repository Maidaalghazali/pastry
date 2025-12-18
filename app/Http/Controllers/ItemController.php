<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * Display a listing of items
     * Sorted: Low stock (stok_akhir <= stok_minimum) first, then by stok_akhir ascending
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $items = Item::query()
            ->when($search, function ($q, $search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('satuan', 'like', "%{$search}%");
            })
            ->select('items.*')
            // Subquery untuk total penambahan
            ->selectSub(function ($q) {
                $q->from('item_histories')
                    ->selectRaw('COALESCE(SUM(jumlah), 0)')
                    ->whereColumn('item_histories.item_id', 'items.id')
                    ->where('type', 'penambahan');
            }, 'total_penambahan')
            // Subquery untuk total pengurangan
            ->selectSub(function ($q) {
                $q->from('item_histories')
                    ->selectRaw('COALESCE(SUM(jumlah), 0)')
                    ->whereColumn('item_histories.item_id', 'items.id')
                    ->where('type', 'pengurangan');
            }, 'total_pengurangan')
            // Sorting: Barang dengan stok <= minimum di atas
            ->orderByRaw('CASE WHEN stok_akhir <= stok_minimum THEN 0 ELSE 1 END')
            ->orderBy('stok_akhir', 'asc') // Lalu urutkan berdasarkan stok (paling sedikit duluan)
            ->orderBy('nama_barang', 'asc') // Terakhir urutkan berdasarkan nama
            ->get();

        return view('items.index', compact('items', 'search'));
    }

    /**
     * Show the form for creating a new item
     */
    public function create()
    {
        return view('items.create');
    }

    /**
     * Store a newly created item
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'stok_awal' => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
        ]);

        Item::create([
            'nama_barang' => $validated['nama_barang'],
            'satuan' => $validated['satuan'],
            'stok_awal' => $validated['stok_awal'],
            'stok_akhir' => $validated['stok_awal'],
            'stok_minimum' => $validated['stok_minimum'],
            'created_by' => auth()->id() ?? 1,
        ]);

        return redirect()->route('items.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified item
     */
    public function edit(Item $item)
    {
        $histories = $item->histories()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('items.edit', compact('item', 'histories'));
    }

    /**
     * Update stock of the specified item (add/subtract)
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'type' => 'required|in:penambahan,pengurangan',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($validated, $item) {
                $stokSebelum = $item->stok_akhir;

                // Calculate new stock
                if ($validated['type'] === 'penambahan') {
                    $stokSesudah = $stokSebelum + $validated['jumlah'];
                } else {
                    $stokSesudah = $stokSebelum - $validated['jumlah'];

                    // Validate: prevent negative stock
                    if ($stokSesudah < 0) {
                        throw new \Exception('Stok tidak mencukupi! Stok saat ini: ' . $stokSebelum);
                    }
                }

                // Create history record
                ItemHistory::create([
                    'item_id' => $item->id,
                    'type' => $validated['type'],
                    'jumlah' => $validated['jumlah'],
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'keterangan' => $validated['keterangan'],
                    'created_by' => auth()->id(),
                ]);

                // Update item stock
                $item->update([
                    'stok_akhir' => $stokSesudah
                ]);

                // Check if stock is low (optional: for notification)
                if ($stokSesudah <= $item->stok_minimum) {
                    \Log::warning('Low stock alert', [
                        'item' => $item->nama_barang,
                        'stok_akhir' => $stokSesudah,
                        'stok_minimum' => $item->stok_minimum
                    ]);
                }
            });

            return redirect()->route('items.edit', $item)
                ->with('success', 'Stok berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified item
     */
    public function destroy(Item $item)
    {
        try {
            $item->delete();

            return redirect()->route('items.index')
                ->with('success', 'Barang berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
        }
    }
}
