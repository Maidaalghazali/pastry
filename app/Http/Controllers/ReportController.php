<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemHistory;
use App\Models\WeeklyReport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    // Halaman utama laporan
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $item_id = $request->input('item_id');

        // Get all items untuk dropdown
        $items = Item::orderBy('nama_barang')->get();

        // Generate weekly reports jika belum ada
        $this->generateWeeklyReports($year, $month, $item_id);

        // Get weekly reports
        $query = WeeklyReport::with('item')
            ->where('year', $year)
            ->where('month', $month);

        if ($item_id) {
            $query->where('item_id', $item_id);
        }

        $weeklyReports = $query->orderBy('item_id')
            ->orderBy('week')
            ->get();

        // Group by item
        $reportsByItem = $weeklyReports->groupBy('item_id');

        // Calculate monthly summary
        $monthlySummary = $this->calculateMonthlySummary($weeklyReports);

        return view('reports.index', compact(
            'reportsByItem',
            'monthlySummary',
            'items',
            'year',
            'month',
            'item_id'
        ));
    }

    // Generate weekly reports
    private function generateWeeklyReports($year, $month, $item_id = null)
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Get items
        $items = $item_id
            ? Item::where('id', $item_id)->get()
            : Item::all();

        foreach ($items as $item) {
            $currentDate = $startOfMonth->copy();
            $weekNumber = 1;
            $previousWeekStokAkhir = null;

            while ($currentDate <= $endOfMonth) {
                // Calculate week start (Monday) and end (Sunday)
                $weekStart = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
                $weekEnd = $currentDate->copy()->endOfWeek(Carbon::SUNDAY);

                // Adjust untuk bulan ini saja
                if ($weekStart->month < $month) {
                    $weekStart = $startOfMonth->copy();
                }
                if ($weekEnd->month > $month || $weekEnd > $endOfMonth) {
                    $weekEnd = $endOfMonth->copy();
                }

                // Check if report already exists
                $existingReport = WeeklyReport::where('item_id', $item->id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->where('week', $weekNumber)
                    ->first();

                if (!$existingReport) {
                    // Calculate stok awal
                    if ($previousWeekStokAkhir !== null) {
                        $stokAwal = $previousWeekStokAkhir;
                    } else {
                        // Minggu pertama: ambil stok akhir dari akhir bulan sebelumnya
                        $stokAwal = $this->getStokAwalMingguPertama($item->id, $weekStart);
                    }

                    // Calculate transactions in this week
                    $transactions = ItemHistory::where('item_id', $item->id)
                        ->whereBetween('created_at', [$weekStart, $weekEnd->endOfDay()])
                        ->get();

                    $totalPenambahan = $transactions->where('type', 'penambahan')->sum('jumlah');
                    $totalPengurangan = $transactions->where('type', 'pengurangan')->sum('jumlah');
                    $stokAkhir = $stokAwal + $totalPenambahan - $totalPengurangan;

                    // Create report
                    WeeklyReport::create([
                        'item_id' => $item->id,
                        'year' => $year,
                        'month' => $month,
                        'week' => $weekNumber,
                        'start_date' => $weekStart,
                        'end_date' => $weekEnd,
                        'stok_awal' => $stokAwal,
                        'total_penambahan' => $totalPenambahan,
                        'total_pengurangan' => $totalPengurangan,
                        'stok_akhir' => $stokAkhir,
                    ]);

                    $previousWeekStokAkhir = $stokAkhir;
                } else {
                    $previousWeekStokAkhir = $existingReport->stok_akhir;
                }

                // Move to next week
                $currentDate = $weekEnd->copy()->addDay();
                $weekNumber++;
            }
        }
    }

    // Get stok awal minggu pertama dari bulan sebelumnya
    private function getStokAwalMingguPertama($item_id, $weekStart)
    {
        // Cari report minggu terakhir bulan sebelumnya
        $previousMonth = $weekStart->copy()->subMonth();

        $lastWeekPreviousMonth = WeeklyReport::where('item_id', $item_id)
            ->where('year', $previousMonth->year)
            ->where('month', $previousMonth->month)
            ->orderBy('week', 'desc')
            ->first();

        if ($lastWeekPreviousMonth) {
            return $lastWeekPreviousMonth->stok_akhir;
        }

        // Jika tidak ada, ambil stok_awal dari item
        $item = Item::find($item_id);
        return $item->stok_awal ?? 0;
    }

    // Calculate monthly summary
    private function calculateMonthlySummary($weeklyReports)
    {
        $summary = [];

        foreach ($weeklyReports->groupBy('item_id') as $item_id => $reports) {
            $item = $reports->first()->item;

            $summary[$item_id] = [
                'item' => $item,
                'stok_awal_bulan' => $reports->first()->stok_awal,
                'total_penambahan' => $reports->sum('total_penambahan'),
                'total_pengurangan' => $reports->sum('total_pengurangan'),
                'stok_akhir_bulan' => $reports->last()->stok_akhir,
            ];
        }

        return $summary;
    }

    // Regenerate reports (untuk refresh data)
    public function regenerate(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $item_id = $request->input('item_id');

        // Delete existing reports
        $query = WeeklyReport::where('year', $year)->where('month', $month);

        if ($item_id) {
            $query->where('item_id', $item_id);
        }

        $query->delete();

        // Regenerate
        $this->generateWeeklyReports($year, $month, $item_id);

        return redirect()->route('reports.index', [
            'year' => $year,
            'month' => $month,
            'item_id' => $item_id
        ])->with('success', 'Laporan berhasil di-generate ulang!');
    }
}
