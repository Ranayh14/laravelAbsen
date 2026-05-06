<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\DailyReport;
use App\Models\MonthlyReport;
use App\Models\User;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
        
        // Load legacy core logic for global helpers if not already loaded
        $corePath = resource_path('views/pages/core.php');
        if (file_exists($corePath)) {
            require_once $corePath;
        }
    }

    /**
     * Export Monthly Reports
     */
    public function exportMonthly(Request $request)
    {
        // Simple auth check for now as we transition
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            return abort(403, 'Forbidden');
        }

        $year = $request->query('year');
        $month = $request->query('month');
        $startup = $request->query('startup');
        $term = $request->query('term');

        $query = MonthlyReport::with('user');

        if ($year) {
            $query->where('year', $year);
        }
        if ($month) {
            $query->where('month', $month);
        }
        if ($startup) {
            $query->whereHas('user', function ($q) use ($startup) {
                $q->where('startup', $startup);
            });
        }
        if ($term) {
            $query->whereHas('user', function ($q) use ($term) {
                $q->where('nama', 'like', "%$term%")
                  ->orWhere('nim', 'like', "%$term%");
            });
        }

        $reports = $query->join('users', 'users.id', '=', 'monthly_reports.user_id')
                         ->orderBy('users.nama', 'asc')
                         ->orderBy('monthly_reports.year', 'desc')
                         ->orderBy('monthly_reports.month', 'desc')
                         ->select('monthly_reports.*') // Avoid column collisions
                         ->get();

        if ($reports->isEmpty()) {
            return redirect()->back()->with('error', 'Data laporan bulanan tidak ditemukan.');
        }

        $sheets = [];
        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        foreach ($reports as $r) {
            $user = $r->user;
            if (!$user) continue;

            $uName = $user->nama;
            if (!isset($sheets[$uName])) {
                $sheets[$uName] = [
                    'title' => 'LAPORAN BULANAN MAGANG - ' . strtoupper($uName),
                    'rows' => []
                ];
                // Employee Info
                $sheets[$uName]['rows'][] = ['NAMA:', $uName, '_style' => 'sInfoLabel'];
                $sheets[$uName]['rows'][] = ['NIM:', $user->nim, '_style' => 'sInfoLabel'];
                $sheets[$uName]['rows'][] = ['STARTUP:', $user->startup, '_style' => 'sInfoLabel'];
                $sheets[$uName]['rows'][] = []; 
            }
            
            $monthName = $months[$r->month] ?? $r->month;
            
            // Month Header
            $sheets[$uName]['rows'][] = ['PERIODE LAPORAN:', strtoupper($monthName) . ' ' . $r->year, '', '', '_style' => 'sSubHeader'];
            $sheets[$uName]['rows'][] = ['Status:', strtoupper($r->status), '', '', '_style' => 'sInfoLabel'];
            $sheets[$uName]['rows'][] = [];

            // Summary
            $sheets[$uName]['rows'][] = ['RINGKASAN PEKERJAAN:', '', '', '', '_style' => 'sSubHeader'];
            $sheets[$uName]['rows'][] = [$r->summary];
            $sheets[$uName]['rows'][] = [];

            // Achievements
            $sheets[$uName]['rows'][] = ['PENCAPAIAN DAN HASIL KERJA:', '', '', '', '_style' => 'sSubHeader'];
            $sheets[$uName]['rows'][] = ['No', 'Pencapaian', 'Detail', '', '_style' => 'sSubHeader'];
            $achievements = json_decode($r->achievements, true);
            if (is_array($achievements) && !empty($achievements)) {
                $no = 1;
                foreach ($achievements as $ach) {
                    $sheets[$uName]['rows'][] = [$no++, $ach['achievement'] ?? '', $ach['detail'] ?? ''];
                }
            } else {
                $sheets[$uName]['rows'][] = ['-', 'Tidak ada data pencapaian', '-', ''];
            }
            $sheets[$uName]['rows'][] = [];

            // Obstacles
            $sheets[$uName]['rows'][] = ['KENDALA:', '', '', '', '_style' => 'sSubHeader'];
            $sheets[$uName]['rows'][] = ['No', 'Kendala', 'Solusi', 'Catatan', '_style' => 'sSubHeader'];
            $obstacles = json_decode($r->obstacles, true);
            if (is_array($obstacles) && !empty($obstacles)) {
                $no = 1;
                foreach ($obstacles as $obs) {
                    $sheets[$uName]['rows'][] = [$no++, $obs['obstacle'] ?? '', $obs['solution'] ?? '', $obs['note'] ?? ''];
                }
            } else {
                $sheets[$uName]['rows'][] = ['-', 'Tidak ada data kendala', '-', '-'];
            }
            
            $sheets[$uName]['rows'][] = [];
            $sheets[$uName]['rows'][] = ['================================================================================', '', '', '']; 
            $sheets[$uName]['rows'][] = []; 
        }

        return $this->exportService->exportToExcelXML('export_bulanan_' . date('Y-m-d'), $sheets);
    }

    /**
     * Export KPI Assessment
     */
    public function exportKpi(Request $request)
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            return abort(403, 'Forbidden');
        }

        // We still need the legacy PDO for getAllKPIData for now
        // core.php provides getPdo()
        $pdo = getPdo();
        
        $filterType = $request->query('filter_type', 'period');
        $periodStart = null;
        $periodEnd = null;
        
        if ($filterType === 'monthly') {
            $year = (int)$request->query('year', date('Y'));
            $month = (int)$request->query('month', date('n'));
            $periodStart = sprintf('%04d-%02d-01', $year, $month);
            $periodEnd = date('Y-m-t', strtotime($periodStart));
        }
        
        $data = getAllKPIData($pdo, $periodStart, $periodEnd);
        if (!$data) return redirect()->back()->with('error', 'No data found');
        
        $rows = [];
        $no = 1;
        foreach ($data['kpi_data'] as $row) {
            $rows[] = [
                $no++,
                $row['nama'],
                $row['total_working_days'],
                $row['actual_working_days'],
                $row['ontime_count'],
                $row['wfa_count'] ?? 0,
                $row['late_count'],
                $row['total_late_minutes'],
                $row['izin_sakit_count'],
                $row['alpha_count'],
                $row['overtime_count'],
                $row['missing_daily_reports_count'] ?? 0,
                $row['kpi_score'],
                $row['status']
            ];
        }

        $sheets = [
            'KPI Summary' => [
                'title' => 'Penilaian KPI Absen - ' . ($filterType === 'monthly' ? "Bulan $month Tahun $year" : "Seluruh Periode"),
                'header' => ['No', 'Nama', 'Hari Kerja (T)', 'Hari Kerja (A)', 'Ontime', 'WFA', 'Terlambat', 'Menit Terlambat', 'Izin/Sakit', 'Alpha', 'Overtime', 'Laporan Kosong', 'Score', 'Status'],
                'rows' => $rows
            ]
        ];

        return $this->exportService->exportToExcelXML('export_kpi_' . date('Y-m-d'), $sheets);
    }

    /**
     * Export Daily Attendance
     */
    public function exportDaily(Request $request)
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            return abort(403, 'Forbidden');
        }

        $filterType = $request->query('filter_type', 'period');
        $format = $request->query('format', $request->query('export_format', 'combined'));
        $year = (int)$request->query('year', date('Y'));
        
        $monthsArr = [];
        if ($request->has('months') && !empty($request->query('months'))) {
            $monthsArr = array_map('intval', explode(',', $request->query('months')));
        } elseif ($request->has('export_months') && is_array($request->query('export_months'))) {
            $monthsArr = array_map('intval', $request->query('export_months'));
        }

        $query = DB::table('users as u')
            ->leftJoin('attendance as a', 'u.id', '=', 'a.user_id')
            ->leftJoin('daily_reports as dr', function($join) {
                $join->on('u.id', '=', 'dr.user_id')
                     ->on(DB::raw('DATE(a.jam_masuk_iso)'), '=', 'dr.report_date');
            })
            ->where('u.role', 'pegawai')
            ->select(
                'u.nama', 'u.nim', 'u.startup',
                DB::raw('DATE(a.jam_masuk_iso) as tanggal'),
                'a.jam_masuk', 'a.status as status_masuk',
                'a.jam_pulang', 'a.ket',
                'dr.content as laporan'
            );

        if ($filterType === 'monthly' && !empty($monthsArr)) {
            $query->whereIn(DB::raw('MONTH(a.jam_masuk_iso)'), $monthsArr)
                  ->where(DB::raw('YEAR(a.jam_masuk_iso)'), $year);
        }

        $rows = $query->orderBy('a.jam_masuk_iso', 'desc')
                      ->orderBy('u.nama', 'asc')
                      ->get();

        if ($rows->isEmpty()) {
            return redirect()->back()->with('error', 'Data tidak ditemukan untuk periode/kategori yang dipilih.');
        }

        $m_names = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $sheets = [];
        $header = ['Tanggal', 'NIM', 'Nama', 'Startup', 'Jam Masuk', 'Status', 'Jam Pulang', 'Keterangan', 'Laporan Harian'];

        if ($format === 'per_employee') {
            foreach ($rows as $r) {
                if (!$r->tanggal) continue;
                if (!isset($sheets[$r->nama])) {
                    $sheets[$r->nama] = [
                        'title' => "Laporan Presensi - " . $r->nama,
                        'header' => $header,
                        'rows' => []
                    ];
                }
                $sheets[$r->nama]['rows'][] = [
                    $r->tanggal, $r->nim, $r->nama, $r->startup,
                    $r->jam_masuk, $r->status_masuk, $r->jam_pulang,
                    $r->ket, $r->laporan
                ];
            }
        } else {
            $selectedMonthNames = array_map(function($m) use ($m_names) { return $m_names[$m] ?? $m; }, $monthsArr);
            $sheets['Combined'] = [
                'title' => 'Data Presensi Lengkap - ' . ($filterType === 'monthly' ? "Bulan " . implode(', ', $selectedMonthNames) . " Tahun $year" : "Seluruh Periode"),
                'header' => $header,
                'rows' => []
            ];
            foreach ($rows as $r) {
                if (!$r->tanggal) continue;
                $sheets['Combined']['rows'][] = [
                    $r->tanggal, $r->nim, $r->nama, $r->startup,
                    $r->jam_masuk, $r->status_masuk, $r->jam_pulang,
                    $r->ket, $r->laporan
                ];
            }
        }

        return $this->exportService->exportToExcelXML('export_presensi_' . date('Y-m-d'), $sheets);
    }
}
