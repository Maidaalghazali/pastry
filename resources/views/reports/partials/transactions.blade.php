@if ($transactions->count())
    <table class="min-w-full table-fixed text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-2 text-left">Tanggal</th>
                <th class="px-4 py-2 text-center">Tipe</th>
                <th class="px-4 py-2 text-right">Jumlah</th>
                <th class="px-4 py-2 text-right">Stok Akhir</th>
                <th class="px-4 py-2 text-left">User</th>
            </tr>
        </thead>

        <tbody class="divide-y">
            @foreach ($transactions as $trx)
                <tr>
                    <td class="px-4 py-2 text-left">
                        {{ $trx->created_at->format('d/m H:i') }}
                    </td>

                    <td class="px-4 py-2 text-center">
                        <span
                            class="text-xs font-semibold
                {{ $trx->type === 'penambahan' ? 'text-green-600' : 'text-red-600' }}">
                            {{ ucfirst($trx->type) }}
                        </span>
                    </td>

                    <td class="px-4 py-2 text-right font-medium">
                        {{ $trx->type === 'penambahan' ? '+' : '-' }}{{ $trx->jumlah }}
                    </td>

                    <td class="px-4 py-2 text-right font-semibold">
                        {{ $trx->stok_sesudah }}
                    </td>

                    <td class="px-4 py-2 text-left text-gray-600">
                        {{ $trx->user->name ?? '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p class="text-center text-gray-400 text-sm py-4">
        Tidak ada transaksi minggu ini
    </p>
@endif
