<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemHistory;
use App\Models\WeeklyReport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * =========================
     * MONTHLY REPORT (REKAP)
     * =========================
     */
    public function monthly(Request $request)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        // generate data (AMAN)
        $this->generateWeeklyReports($year, $month);

        $weeklyReports = WeeklyReport::with('item')
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('item_id')
            ->orderBy('week')
            ->get();

        $monthlySummary = $this->calculateMonthlySummary($weeklyReports);

        // ✅ SORTING: Low stock first, then ascending by stok_akhir_bulan
        usort($monthlySummary, function ($a, $b) {
            // Prioritas 1: Barang dengan stok <= minimum di atas
            $aIsLow = $a['item']->stok_akhir <= $a['item']->stok_minimum ? 0 : 1;
            $bIsLow = $b['item']->stok_akhir <= $b['item']->stok_minimum ? 0 : 1;

            if ($aIsLow !== $bIsLow) {
                return $aIsLow <=> $bIsLow;
            }

            // Prioritas 2: Urutkan berdasarkan stok_akhir_bulan (ascending)
            if ($a['stok_akhir_bulan'] !== $b['stok_akhir_bulan']) {
                return $a['stok_akhir_bulan'] <=> $b['stok_akhir_bulan'];
            }

            // Prioritas 3: Urutkan berdasarkan nama_barang (A-Z)
            return strcasecmp($a['item']->nama_barang, $b['item']->nama_barang);
        });

        return view('reports.monthly', compact(
            'monthlySummary',
            'year',
            'month'
        ));
    }

    /**
     * =========================
     * WEEKLY REPORT (DETAIL)
     * =========================
     */
    public function weekly(Request $request, Item $item)
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $this->generateWeeklyReports($year, $month, $item->id);

        $weeklyReports = WeeklyReport::where('item_id', $item->id)
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('week')
            ->get();

        $weeklyDetails = [];

        foreach ($weeklyReports as $report) {
            $transactions = ItemHistory::where('item_id', $item->id)
                ->whereBetween('created_at', [
                    $report->start_date->startOfDay(),
                    $report->end_date->endOfDay()
                ])
                ->orderBy('created_at')
                ->get();

            $weeklyDetails[] = [
                'report' => $report,
                'transactions' => $transactions
            ];
        }

        return view('reports.weekly', compact(
            'item',
            'weeklyDetails',
            'year',
            'month'
        ));
    }

    /**
     * =========================
     * ENGINE WEEKLY REPORT
     * =========================
     */
    private function generateWeeklyReports(int $year, int $month, ?int $item_id = null): void
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $items = $item_id
            ? Item::where('id', $item_id)->get()
            : Item::all();

        foreach ($items as $item) {

            $currentDate = $startOfMonth->copy();
            $weekNumber  = 1;
            $prevStok    = null;

            while ($currentDate <= $endOfMonth) {

                // ✅ WEEK START & END (CARBON AMAN)
                $weekStart = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
                if ($weekStart->lt($startOfMonth)) {
                    $weekStart = $startOfMonth->copy();
                }

                $weekEnd = $currentDate->copy()->endOfWeek(Carbon::SUNDAY);
                if ($weekEnd->gt($endOfMonth)) {
                    $weekEnd = $endOfMonth->copy();
                }

                $exists = WeeklyReport::where([
                    'item_id' => $item->id,
                    'year'    => $year,
                    'month'   => $month,
                    'week'    => $weekNumber,
                ])->exists();

                if (!$exists) {

                    // ✅ STOK AWAL
                    if ($prevStok !== null) {
                        $stokAwal = $prevStok;
                    } else {
                        $stokAwal = $this->getStokAwalMingguPertama($item->id, $weekStart);
                    }

                    // ✅ TRANSAKSI MINGGUAN
                    $trx = ItemHistory::where('item_id', $item->id)
                        ->whereBetween('created_at', [
                            $weekStart->copy()->startOfDay(),
                            $weekEnd->copy()->endOfDay()
                        ])
                        ->get();

                    $in  = $trx->where('type', 'penambahan')->sum('jumlah');
                    $out = $trx->where('type', 'pengurangan')->sum('jumlah');

                    $stokAkhir = $stokAwal + $in - $out;

                    WeeklyReport::create([
                        'item_id'           => $item->id,
                        'year'              => $year,
                        'month'             => $month,
                        'week'              => $weekNumber,
                        'start_date'        => $weekStart,
                        'end_date'          => $weekEnd,
                        'stok_awal'         => $stokAwal,
                        'total_penambahan'  => $in,
                        'total_pengurangan' => $out,
                        'stok_akhir'        => $stokAkhir,
                    ]);

                    $prevStok = $stokAkhir;
                } else {
                    $prevStok = WeeklyReport::where([
                        'item_id' => $item->id,
                        'year'    => $year,
                        'month'   => $month,
                        'week'    => $weekNumber,
                    ])->value('stok_akhir');
                }

                $currentDate = $weekEnd->copy()->addDay();
                $weekNumber++;
            }
        }
    }

    /**
     * Legacy method - redirect to monthly
     */
    public function index(Request $request)
    {
        return $this->monthly($request);
    }

    /**
     * Get stok awal untuk minggu pertama
     */
    private function getStokAwalMingguPertama(int $item_id, Carbon $weekStart): int
    {
        $previousMonth = $weekStart->copy()->subMonth();

        $lastWeekPreviousMonth = WeeklyReport::where('item_id', $item_id)
            ->where('year', $previousMonth->year)
            ->where('month', $previousMonth->month)
            ->orderByDesc('week')
            ->first();

        return $lastWeekPreviousMonth?->stok_akhir
            ?? Item::find($item_id)?->stok_awal
            ?? 0;
    }

    /**
     * Calculate monthly summary with proper stok_awal_bulan
     */
    private function calculateMonthlySummary($weeklyReports): array
    {
        $summary = [];

        foreach ($weeklyReports->groupBy('item_id') as $item_id => $reports) {
            $firstReport = $reports->first();

            // ✅ Ambil stok akhir dari bulan sebelumnya
            $previousMonth = Carbon::create($firstReport->year, $firstReport->month, 1)->subMonth();

            $lastWeekPreviousMonth = WeeklyReport::where('item_id', $item_id)
                ->where('year', $previousMonth->year)
                ->where('month', $previousMonth->month)
                ->orderByDesc('week')
                ->first();

            // Jika ada data bulan sebelumnya, ambil stok_akhir nya
            // Jika tidak ada (bulan pertama), ambil dari items.stok_awal
            $stokAwalBulan = $lastWeekPreviousMonth?->stok_akhir
                ?? Item::find($item_id)?->stok_awal
                ?? 0;

            $summary[] = [ // ✅ Ubah dari array associative ke indexed array
                'item' => $firstReport->item,
                'item_id' => $item_id, // ✅ Tambahkan ini untuk link ke weekly
                'stok_awal_bulan' => $stokAwalBulan,
                'total_penambahan' => $reports->sum('total_penambahan'),
                'total_pengurangan' => $reports->sum('total_pengurangan'),
                'stok_akhir_bulan' => $reports->last()->stok_akhir,
            ];
        }

        return $summary;
    }

    /**
     * Regenerate reports
     */
    public function regenerate(Request $request)
    {
        $year  = $request->integer('year');
        $month = $request->integer('month');

        $deleted = WeeklyReport::where('year', $year)
            ->where('month', $month)
            ->delete();

        $this->generateWeeklyReports($year, $month);

        return redirect()->route('reports.monthly', [
            'year' => $year,
            'month' => $month
        ])->with('success', "Laporan berhasil di-generate ulang! ({$deleted} data lama dihapus)");
    }
}
