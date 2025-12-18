@extends('layouts.public')

@section('content')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Laporan Mingguan - {{ $item->nama_barang }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Breadcrumb / Back Button -->
            <div class="mb-6">
                <a href="{{ route('reports.monthly', ['year' => $year, 'month' => $month]) }}"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg whitespace-nowrap">
                    Kembali ke Laporan Bulanan
                </a>
            </div>

            <!-- Info Item -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ $item->nama_barang }}</h3>
                        <p class="text-gray-600 mt-1">Satuan: {{ $item->satuan }}</p>
                        <p class="text-sm text-gray-500 mt-1">
                            Periode:
                            {{ ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$month] }}
                            {{ $year }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Stok Akhir Bulan</p>
                        <p class="text-4xl font-bold text-blue-600">
                            {{ end($weeklyDetails)['report']->stok_akhir ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Bulanan -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <h4 class="text-lg font-semibold mb-4">Ringkasan Bulan Ini</h4>
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-gray-600 mb-1">Stok Awal Bulan</p>
                        <p class="text-2xl font-bold text-gray-700">{{ $weeklyDetails[0]['report']->stok_awal ?? 0 }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-gray-600 mb-1">Total Penambahan</p>
                        <p class="text-2xl font-bold text-green-600">
                            +{{ collect($weeklyDetails)->sum('report.total_penambahan') }}
                        </p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-gray-600 mb-1">Total Pengurangan</p>
                        <p class="text-2xl font-bold text-red-600">
                            -{{ collect($weeklyDetails)->sum('report.total_pengurangan') }}
                        </p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-gray-600 mb-1">Stok Akhir Bulan</p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ end($weeklyDetails)['report']->stok_akhir ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detail Per Minggu -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Minggu</th>
                            <th class="px-4 py-2 text-right">Awal</th>
                            <th class="px-4 py-2 text-right">Masuk</th>
                            <th class="px-4 py-2 text-right">Keluar</th>
                            <th class="px-4 py-2 text-right">Akhir</th>
                            <th class="px-4 py-2 text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($weeklyDetails as $i => $detail)
                            <tr class="border-t">
                                <td class="px-4 py-2 font-semibold">
                                    M{{ $detail['report']->week }}
                                    <div class="text-xs text-gray-500">
                                        {{ $detail['report']->start_date->format('d M') }} –
                                        {{ $detail['report']->end_date->format('d M') }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-right">{{ $detail['report']->stok_awal }}</td>
                                <td class="px-4 py-2 text-right text-green-600">
                                    +{{ $detail['report']->total_penambahan }}</td>
                                <td class="px-4 py-2 text-right text-red-600">
                                    -{{ $detail['report']->total_pengurangan }}</td>
                                <td class="px-4 py-2 text-right font-bold text-blue-600">
                                    {{ $detail['report']->stok_akhir }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <button onclick="toggleDetail('detail-{{ $i }}')"
                                        class="text-blue-600 text-xs hover:underline">
                                        Detail
                                    </button>
                                </td>
                            </tr>

                            <!-- Detail (Hidden) -->
                            <tr id="detail-{{ $i }}" class="hidden bg-gray-50">
                                <td colspan="6" class="p-4">
                                    @include('reports.partials.transactions', [
                                        'transactions' => $detail['transactions'],
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Info Footer -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Catatan:</strong> Semua transaksi tercatat dengan detail lengkap seperti yang ditampilkan di
                    fitur edit barang.
                    Data ini mencerminkan semua penambahan dan pengurangan stok yang terjadi pada periode yang dipilih.
                </p>
            </div>
        </div>
    </div>

    <script>
        function toggleDetail(id) {
            const el = document.getElementById(id);
            el.classList.toggle('hidden');
        }
    </script>
@endsection
