@extends('layouts.public')

@section('content')

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Laporan Mingguan & Bulanan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filter -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap gap-4">
                    <!-- Filter Tahun -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                        <select name="year"
                            class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            @for ($y = now()->year; $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <!-- Filter Bulan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                        <select name="month"
                            class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            @foreach ([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $m => $name)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Barang -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Barang</label>
                        <select name="item_id"
                            class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="">Semua Barang</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}" {{ $item_id == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_barang }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tombol -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Tampilkan
                        </button>
                        <a href="{{ route('reports.index') }}"
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Reset
                        </a>
                    </div>
                </form>

                <!-- Tombol Regenerate -->
                <div class="mt-4 pt-4 border-t">
                    <form method="POST" action="{{ route('reports.regenerate') }}"
                        onsubmit="return confirm('Regenerate laporan? Data lama akan dihapus.')">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="item_id" value="{{ $item_id }}">
                        <button type="submit"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-bold py-1 px-3 rounded">
                            🔄 Regenerate Laporan
                        </button>
                        <span class="text-xs text-gray-500 ml-2">Gunakan jika ada perubahan data transaksi</span>
                    </form>
                </div>
            </div>

            <!-- Summary Bulanan -->
            @if (!empty($monthlySummary))
                <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">📊 Ringkasan Bulanan -
                        {{ ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$month] }}
                        {{ $year }}</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stok Awal
                                        Bulan</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total
                                        Penambahan</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total
                                        Pengurangan</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stok Akhir
                                        Bulan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($monthlySummary as $summary)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                            {{ $summary['item']->nama_barang }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $summary['item']->satuan }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900">
                                            {{ $summary['stok_awal_bulan'] }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-green-600 font-semibold">
                                            +{{ $summary['total_penambahan'] }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-red-600 font-semibold">
                                            -{{ $summary['total_pengurangan'] }}</td>
                                        <td class="px-6 py-4 text-sm text-right font-bold text-blue-600">
                                            {{ $summary['stok_akhir_bulan'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Laporan Mingguan per Item -->
            @forelse($reportsByItem as $item_id => $reports)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">
                        📦 {{ $reports->first()->item->nama_barang }} ({{ $reports->first()->item->satuan }})
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Minggu</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stok Awal
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Penambahan
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pengurangan
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stok Akhir
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($reports as $report)
                                    <tr class="{{ $loop->last ? 'bg-blue-50 font-semibold' : '' }}">
                                        <td class="px-4 py-3 text-sm">Minggu {{ $report->week }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            {{ $report->start_date->format('d M') }} -
                                            {{ $report->end_date->format('d M Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right">{{ $report->stok_awal }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-green-600">
                                            +{{ $report->total_penambahan }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-red-600">
                                            -{{ $report->total_pengurangan }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-bold">{{ $report->stok_akhir }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                    Tidak ada data laporan untuk periode ini
                </div>
            @endforelse
        </div>
    </div>
@endsection
