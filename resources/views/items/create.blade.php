@extends('layouts.public')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800">Tambah Barang Baru</h2>

                    <form action="{{ route('items.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="nama_barang" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Barang <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="nama_barang"
                                   id="nama_barang"
                                   value="{{ old('nama_barang') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                            @error('nama_barang')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="satuan" class="block text-sm font-medium text-gray-700 mb-2">
                                Satuan <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="satuan"
                                   id="satuan"
                                   value="{{ old('satuan') }}"
                                   placeholder="contoh: pcs, box, kg, liter"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                            @error('satuan')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="stok_awal" class="block text-sm font-medium text-gray-700 mb-2">
                                Stok Awal <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   name="stok_awal"
                                   id="stok_awal"
                                   value="{{ old('stok_awal', 0) }}"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                            @error('stok_awal')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="stok_minimum" class="block text-sm font-medium text-gray-700 mb-2">
                                Stok Minimum (Batas Peringatan) <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   name="stok_minimum"
                                   id="stok_minimum"
                                   value="{{ old('stok_minimum', 0) }}"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                            <p class="text-sm text-gray-500 mt-1">
                                ⚠️ Barang akan ditandai merah jika stok mencapai atau di bawah batas ini
                            </p>
                            @error('stok_minimum')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('items.index') }}"
                               class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                                Batal
                            </a>
                            <button type="submit"
                                    class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
