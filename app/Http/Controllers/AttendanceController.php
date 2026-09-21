<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSetting;
use App\Models\AuditLog;
use App\Models\CooperativeNotification;
use App\Models\Pekerja;
use App\Services\AttendanceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendance)
    {
    }

    public function workerIndex(Request $request): View
    {
        $staff = $this->worker($request);
        $setting = $this->attendance->setting();
        $record = AttendanceRecord::query()
            ->where('staff_id', $staff->id_pekerja)
            ->whereDate('attendance_date', today())
            ->first();

        if ($record) {
            $original = [
                'late_minutes' => $record->late_minutes,
                'working_minutes' => $record->working_minutes,
                'early_leave_minutes' => $record->early_leave_minutes,
                'status' => $record->status,
            ];

            $this->attendance->applyCheckInStatus($record, $setting);

            if ($record->check_out_time) {
                $this->attendance->applyCheckoutStatus($record, $setting);
            }

            if (
                $original['late_minutes'] !== $record->late_minutes
                || $original['working_minutes'] !== $record->working_minutes
                || $original['early_leave_minutes'] !== $record->early_leave_minutes
                || $original['status'] !== $record->status
            ) {
                $record->save();
            }
        }

        $displayLateMinutes = $this->attendance->liveLateMinutes($record, $setting);
        $displayWorkingMinutes = $this->attendance->liveWorkingMinutes($record);

        return view('attendance.worker.index', [
            'role' => 'staff',
            'staffType' => 'coop_staff',
            'user' => $staff,
            'setting' => $setting,
            'record' => $record,
            'displayLateMinutes' => $displayLateMinutes,
            'displayWorkingMinutes' => $displayWorkingMinutes,
            'todayStatus' => $this->attendance->displayStatus($record, $setting),
            'isWorkingDay' => $this->attendance->isWorkingDay(CarbonImmutable::today(), $setting),
        ]);
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $staff = $this->worker($request);
        $setting = $this->attendance->setting();
        $validated = $this->locationInput($request);
        $today = CarbonImmutable::today();

        if (! $this->attendance->isWorkingDay($today, $setting)) {
            return back()->withErrors(['attendance' => 'Hari ini bukan hari bekerja yang ditetapkan.']);
        }

        if (AttendanceRecord::query()->where('staff_id', $staff->id_pekerja)->whereDate('attendance_date', $today)->exists()) {
            return back()->withErrors(['attendance' => 'Check In untuk hari ini telah direkodkan.']);
        }

        $gps = $this->attendance->verifyLocation((float) $validated['latitude'], (float) $validated['longitude'], $setting);
        if (! $gps['verified']) {
            $this->audit($request, 'check_in_blocked_outside_area', $staff, 'Cubaan Check In ditolak kerana di luar kawasan kehadiran.', [
                'gps_distance_meter' => round((float) ($gps['distance'] ?? 0), 2),
                'gps_verified' => false,
            ]);
            $this->notifyWorker($staff, 'Check In ditolak', 'Akses Check In ditolak kerana anda berada di luar kawasan Politeknik.', route('coop-staff.attendance.index'));

            return back()->withErrors(['attendance' => 'Anda berada di luar kawasan Politeknik. Check In tidak dibenarkan.']);
        }

        $record = new AttendanceRecord([
            'staff_id' => $staff->id_pekerja,
            'attendance_date' => $today->toDateString(),
            'check_in_time' => now(),
            'check_in_latitude' => $validated['latitude'],
            'check_in_longitude' => $validated['longitude'],
            'check_in_gps_verified' => true,
            'location_name' => $setting->location_name,
            'device_information' => substr((string) $request->userAgent(), 0, 1000),
        ]);
        $this->attendance->applyCheckInStatus($record, $setting);
        $record->save();

        $lateMessage = $record->status === 'late' ? ' Anda lewat '.$this->duration($record->late_minutes).'.' : '';
        $this->audit($request, 'check_in', $record, 'Check In direkodkan.'.$lateMessage, ['gps_distance_meter' => round((float) $gps['distance'], 2)]);
        $this->notifyWorker($staff, 'Check In berjaya', 'Kehadiran anda direkodkan pada '.now()->timezone('Asia/Kuala_Lumpur')->format('h:i A').'.'.$lateMessage, route('coop-staff.attendance.index'));

        return back()->with('status', 'Check In berjaya direkodkan.'.$lateMessage);
    }

    public function checkOut(Request $request): RedirectResponse
    {
        $staff = $this->worker($request);
        $setting = $this->attendance->setting();
        $validated = $this->locationInput($request);
        $record = AttendanceRecord::query()
            ->where('staff_id', $staff->id_pekerja)
            ->whereDate('attendance_date', today())
            ->first();

        if (! $record || ! $record->check_in_time) {
            return back()->withErrors(['attendance' => 'Sila Check In dahulu sebelum Check Out.']);
        }

        if ($record->check_out_time) {
            return back()->withErrors(['attendance' => 'Check Out untuk hari ini telah direkodkan.']);
        }

        $gps = $this->attendance->verifyLocation((float) $validated['latitude'], (float) $validated['longitude'], $setting);
        $gpsVerified = (bool) ($gps['verified'] ?? false);

        $record->fill([
            'check_out_time' => now(),
            'check_out_latitude' => $validated['latitude'],
            'check_out_longitude' => $validated['longitude'],
            'check_out_gps_verified' => $gpsVerified,
        ]);
        $this->attendance->applyCheckoutStatus($record, $setting);
        $record->save();

        $earlyMessage = $record->early_leave_minutes > 0 ? ' Rekod keluar awal: '.$this->duration($record->early_leave_minutes).'.' : '';
        $outsideMessage = $gpsVerified ? '' : ' GPS check out berada di luar kawasan Politeknik.';
        $this->audit(
            $request,
            $gpsVerified ? 'check_out' : 'check_out_outside_area',
            $record,
            'Check Out direkodkan.'.$earlyMessage.$outsideMessage,
            [
                'gps_distance_meter' => round((float) ($gps['distance'] ?? 0), 2),
                'gps_verified' => $gpsVerified,
            ]
        );
        $this->notifyWorker(
            $staff,
            'Check Out berjaya',
            'Jumlah masa bekerja hari ini: '.$this->duration($record->working_minutes).'.'.$earlyMessage.$outsideMessage,
            route('coop-staff.attendance.history')
        );

        return back()->with('status', 'Check Out berjaya. Jumlah masa bekerja: '.$this->duration($record->working_minutes).'.'.$outsideMessage);
    }

    public function history(Request $request): View
    {
        $staff = $this->worker($request);
        $this->attendance->finalizeDueDates();
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);
        $status = (string) $request->query('status', '');

        $records = AttendanceRecord::query()
            ->where('staff_id', $staff->id_pekerja)
            ->when($month >= 1 && $month <= 12, fn ($query) => $query->whereMonth('attendance_date', $month))
            ->when($year >= 2020 && $year <= 2100, fn ($query) => $query->whereYear('attendance_date', $year))
            ->when(in_array($status, $this->recordStatuses(), true), fn ($query) => $query->where('status', $status))
            ->latest('attendance_date')
            ->paginate(25)
            ->withQueryString();

        return view('attendance.worker.history', [
            'role' => 'staff', 'staffType' => 'coop_staff', 'user' => $staff,
            'records' => $records,
            'filters' => compact('month', 'year', 'status'),
            'statuses' => $this->statusLabels(),
            'correctionTypes' => $this->correctionTypes(),
            'absenceReasons' => $this->absenceReasons(),
        ]);
    }

    public function corrections(Request $request): View
    {
        $staff = $this->worker($request);

        return view('attendance.worker.corrections', [
            'role' => 'staff', 'staffType' => 'coop_staff', 'user' => $staff,
            'records' => AttendanceRecord::query()->where('staff_id', $staff->id_pekerja)->latest('attendance_date')->limit(60)->get(),
            'corrections' => AttendanceCorrection::query()->with('attendance')->where('staff_id', $staff->id_pekerja)->latest()->paginate(20),
            'correctionTypes' => $this->correctionTypes(),
            'absenceReasons' => $this->absenceReasons(),
        ]);
    }

    public function storeCorrection(Request $request): RedirectResponse
    {
        $staff = $this->worker($request);
        $validated = $request->validate([
            'attendance_id' => ['nullable', 'integer'],
            'correction_date' => ['required', 'date', 'before_or_equal:today'],
            'correction_type' => ['required', 'in:missing_check_in,missing_check_out,wrong_check_in_time,wrong_check_out_time,other'],
            'requested_time' => ['nullable', 'date_format:H:i'],
            'absence_reason' => ['nullable', 'in:'.implode(',', array_keys($this->absenceReasons()))],
            'reason' => ['required', 'string', 'max:1500'],
            'supporting_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $record = null;
        if (! empty($validated['attendance_id'])) {
            $record = AttendanceRecord::query()->where('staff_id', $staff->id_pekerja)->findOrFail($validated['attendance_id']);
            if ($record->attendance_date->toDateString() !== $validated['correction_date']) {
                return back()->withErrors(['attendance_id' => 'Tarikh rekod kehadiran tidak sepadan.'])->withInput();
            }
        }

        $path = $request->hasFile('supporting_document') ? $request->file('supporting_document')->store('attendance-corrections') : null;
        $compiledReason = $this->composeCorrectionReason(
            $validated['reason'],
            $validated['absence_reason'] ?? null
        );

        $correction = AttendanceCorrection::query()->create([
            'attendance_id' => $record?->id,
            'staff_id' => $staff->id_pekerja,
            'correction_date' => $validated['correction_date'],
            'correction_type' => $validated['correction_type'],
            'requested_time' => $validated['requested_time'] ?? null,
            'reason' => $compiledReason,
            'supporting_document' => $path,
            'status' => 'pending',
        ]);

        $this->audit($request, 'correction_requested', $correction, 'Permohonan pembetulan kehadiran dihantar.');
        $this->notifyAdmins('Pembetulan kehadiran baharu', $staff->nama.' menghantar permohonan pembetulan kehadiran.', route('admin.attendance.corrections'));

        return back()->with('status', 'Permohonan pembetulan berjaya dihantar untuk semakan admin.');
    }

    public function downloadCorrectionDocument(Request $request, AttendanceCorrection $correction): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $isAdmin = $request->session()->get('auth_role') === 'admin';
        $isOwner = $request->session()->get('auth_role') === 'staff' && (int) $request->session()->get('auth_id') === (int) $correction->staff_id;
        abort_unless($isAdmin || $isOwner, 403);
        abort_unless($correction->supporting_document && Storage::exists($correction->supporting_document), 404);

        return Storage::download($correction->supporting_document);
    }

    public function adminIndex(Request $request): RedirectResponse
    {
        $this->admin($request);

        return redirect()->route('admin.attendance.live');
    }

    public function dashboard(Request $request): View
    {
        $admin = $this->admin($request);
        $setting = $this->attendance->setting();
        $this->attendance->finalizeDueDates(setting: $setting);
        $today = CarbonImmutable::today();
        $workers = Pekerja::query()->where('staff_type', 'coop_staff')->where('status_aktif', true)->orderBy('nama')->get();
        $records = AttendanceRecord::query()->with('staff')->whereDate('attendance_date', $today)->get()->keyBy('staff_id');
        $recentRecords = AttendanceRecord::query()->with('staff')->latest('attendance_date')->latest()->limit(8)->get();
        $pendingCorrections = Schema::hasTable('attendance_corrections')
            ? AttendanceCorrection::query()->with('staff')->where('status', 'pending')->latest()->limit(6)->get()
            : collect();

        return view('admin.dashboards.kehadiran', [
            'role' => $request->session()->get('auth_role'),
            'user' => $admin,
            'setting' => $setting,
            'summary' => [
                ...$this->todaySummary($workers, $records, $setting),
                'corrections' => Schema::hasTable('attendance_corrections')
                    ? AttendanceCorrection::query()->where('status', 'pending')->count()
                    : 0,
                'early_leave' => AttendanceRecord::query()->whereDate('attendance_date', $today)->where('early_leave_minutes', '>', 0)->count(),
            ],
            'recentRecords' => $recentRecords,
            'pendingCorrections' => $pendingCorrections,
            'charts' => [
                'today' => [
                    ['label' => 'Hadir', 'value' => $this->todaySummary($workers, $records, $setting)['present']],
                    ['label' => 'Lewat', 'value' => $this->todaySummary($workers, $records, $setting)['late']],
                    ['label' => 'Belum Check Out', 'value' => $this->todaySummary($workers, $records, $setting)['not_checked_out']],
                    ['label' => 'Tidak Hadir', 'value' => $this->todaySummary($workers, $records, $setting)['absent']],
                ],
                'issues' => [
                    ['label' => 'Pembetulan', 'value' => Schema::hasTable('attendance_corrections') ? AttendanceCorrection::query()->where('status', 'pending')->count() : 0],
                    ['label' => 'Keluar Awal', 'value' => AttendanceRecord::query()->whereDate('attendance_date', $today)->where('early_leave_minutes', '>', 0)->count()],
                ],
            ],
            'labels' => $this->statusLabels(),
        ]);
    }

    public function live(Request $request): View
    {
        $admin = $this->admin($request);
        $setting = $this->attendance->setting();
        $this->attendance->finalizeDueDates(setting: $setting);
        $today = CarbonImmutable::today();
        $workers = Pekerja::query()->where('staff_type', 'coop_staff')->where('status_aktif', true)->orderBy('nama')->get();
        $records = AttendanceRecord::query()->whereDate('attendance_date', $today)->get()->keyBy('staff_id');

        return view('attendance.admin.live', [
            'role' => $request->session()->get('auth_role'), 'user' => $admin, 'setting' => $setting,
            'workers' => $workers, 'records' => $records,
            'summary' => $this->todaySummary($workers, $records, $setting),
        ]);
    }

    public function records(Request $request): View
    {
        $admin = $this->admin($request);
        $this->attendance->finalizeDueDates();
        $filters = [
            'date' => (string) $request->query('date', ''),
            'month' => (string) $request->query('month', ''),
            'staff_id' => (string) $request->query('staff_id', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $records = $this->filteredRecords($filters)->with('staff')->latest('attendance_date')->latest('check_in_time')->paginate(30)->withQueryString();

        return view('attendance.admin.records', [
            'role' => $request->session()->get('auth_role'), 'user' => $admin, 'records' => $records,
            'workers' => $this->workerList(), 'filters' => $filters, 'statuses' => $this->statusLabels(),
        ]);
    }

    public function show(Request $request, AttendanceRecord $record): View
    {
        $admin = $this->admin($request);
        abort_unless($record->staff && $record->staff->staff_type === 'coop_staff', 404);

        return view('attendance.admin.show', [
            'role' => $request->session()->get('auth_role'), 'user' => $admin,
            'record' => $record->load(['staff', 'corrections.reviewer']),
        ]);
    }

    public function destroyRecord(Request $request, AttendanceRecord $record): RedirectResponse
    {
        $this->admin($request);
        abort_unless($record->staff && $record->staff->staff_type === 'coop_staff', 404);

        $record->loadMissing(['staff', 'corrections']);

        DB::transaction(function () use ($request, $record): void {
            foreach ($record->corrections as $correction) {
                if ($correction->supporting_document) {
                    Storage::disk('public')->delete($correction->supporting_document);
                }

                $correction->delete();
            }

            $this->audit(
                $request,
                'record_deleted',
                $record,
                'Rekod kehadiran dipadam untuk '.$record->staff?->nama.'.',
                [
                    'attendance_date' => optional($record->attendance_date)->format('Y-m-d'),
                    'staff_id' => $record->staff_id,
                ]
            );

            $record->delete();
        });

        return redirect()
            ->route('admin.attendance.records')
            ->with('success', 'Rekod kehadiran berjaya dipadam.');
    }

    public function workers(Request $request): View
    {
        $admin = $this->admin($request);
        $search = trim((string) $request->query('search', ''));
        $workers = Pekerja::query()->where('staff_type', 'coop_staff')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(fn ($scope) => $scope->where('nama', 'like', "%{$search}%")->orWhere('no_pekerja', 'like', "%{$search}%"));
            })
            ->orderBy('nama')
            ->paginate(30)->withQueryString();

        return view('attendance.admin.workers', compact('admin', 'workers', 'search') + ['role' => $request->session()->get('auth_role'), 'user' => $admin]);
    }

    public function correctionsAdmin(Request $request): View
    {
        $admin = $this->systemAdmin($request);
        $status = (string) $request->query('status', 'pending');
        $corrections = AttendanceCorrection::query()->with(['staff', 'attendance', 'reviewer'])
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($query) => $query->where('status', $status))
            ->latest()->paginate(30)->withQueryString();

        return view('attendance.admin.corrections', [
            'role' => 'admin', 'user' => $admin, 'corrections' => $corrections, 'status' => $status,
            'correctionTypes' => $this->correctionTypes(),
        ]);
    }

    public function reviewCorrection(Request $request, AttendanceCorrection $correction): RedirectResponse
    {
        $admin = $this->systemAdmin($request);
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'admin_remark' => ['nullable', 'string', 'max:1500'],
        ]);

        if ($correction->status !== 'pending') {
            return back()->withErrors(['correction' => 'Permohonan ini telah disemak sebelum ini.']);
        }

        DB::transaction(function () use ($request, $admin, $correction, $validated): void {
            if ($validated['decision'] === 'approved') {
                $record = $correction->attendance ?: AttendanceRecord::query()->firstOrNew([
                    'staff_id' => $correction->staff_id,
                    'attendance_date' => $correction->correction_date->toDateString(),
                ]);
                $setting = $this->attendance->setting();
                $requested = $correction->requested_time ?: ($correction->correction_type === 'missing_check_out' ? $setting->work_end_time : $setting->work_start_time);
                $requestedTime = $requested instanceof \DateTimeInterface
                    ? CarbonImmutable::instance($requested)->timezone('Asia/Kuala_Lumpur')->format('H:i:s')
                    : CarbonImmutable::parse((string) $requested, 'Asia/Kuala_Lumpur')->format('H:i:s');
                $timestamp = CarbonImmutable::parse($correction->correction_date->toDateString().' '.$requestedTime, 'Asia/Kuala_Lumpur');

                if (in_array($correction->correction_type, ['missing_check_in', 'wrong_check_in_time'], true)) {
                    $record->check_in_time = $timestamp;
                } elseif (in_array($correction->correction_type, ['missing_check_out', 'wrong_check_out_time'], true)) {
                    $record->check_out_time = $timestamp;
                } else {
                    $record->notes = trim(($record->notes ? $record->notes."\n" : '').'Pembetulan admin: '.($validated['admin_remark'] ?? $correction->reason));
                }

                $record->staff_id = $correction->staff_id;
                $record->attendance_date = $correction->correction_date;
                $record->location_name ??= 'Pembetulan admin';
                if ($record->check_in_time) {
                    $this->attendance->applyCheckInStatus($record, $setting);
                }
                if ($record->check_in_time && $record->check_out_time) {
                    $this->attendance->applyCheckoutStatus($record, $setting);
                }
                $record->save();
                $correction->attendance_id = $record->id;
            }

            $correction->update([
                'status' => $validated['decision'],
                'reviewed_by' => $admin->id_admin,
                'reviewed_at' => now(),
                'admin_remark' => $validated['admin_remark'] ?? null,
            ]);

            $this->audit($request, 'correction_'.$validated['decision'], $correction, 'Permohonan pembetulan kehadiran '.($validated['decision'] === 'approved' ? 'diluluskan.' : 'ditolak.'));
            $this->notifyWorker($correction->staff, 'Pembetulan kehadiran '.($validated['decision'] === 'approved' ? 'diluluskan' : 'ditolak'), $validated['admin_remark'] ?: 'Sila semak rekod kehadiran anda.', route('coop-staff.attendance.corrections'));
        });

        return back()->with('status', 'Keputusan pembetulan kehadiran berjaya disimpan.');
    }

    public function reports(Request $request): View
    {
        $admin = $this->admin($request);
        $this->attendance->finalizeDueDates();
        $filters = $this->reportFilters($request);
        $records = $this->filteredRecords($filters)->with('staff')->get();

        return view('attendance.admin.reports', [
            'role' => $request->session()->get('auth_role'), 'user' => $admin, 'filters' => $filters, 'records' => $records,
            'workers' => $this->workerList(), 'statuses' => $this->statusLabels(),
            'summary' => $this->reportSummary($records),
        ]);
    }

    public function exportReport(Request $request): StreamedResponse
    {
        $this->admin($request);
        $this->attendance->finalizeDueDates();
        $records = $this->filteredRecords($this->reportFilters($request))->with('staff')->get();

        return response()->streamDownload(function () use ($records): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['NAMA', 'NO PEKERJA', 'TARIKH', 'CHECK IN', 'CHECK OUT', 'LEWAT', 'KELUAR AWAL', 'JUMLAH JAM', 'STATUS', 'GPS']);
            foreach ($records as $record) {
                fputcsv($output, [
                    $record->staff?->nama ?? '-', $record->staff?->no_pekerja ?? '-', $record->attendance_date?->format('d/m/Y'),
                    $record->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-', $record->check_out_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-',
                    $this->duration($record->late_minutes), $this->duration($record->early_leave_minutes), $this->duration($record->working_minutes),
                    $this->statusLabels()[$record->status] ?? $record->status,
                    ($record->check_in_gps_verified && (!$record->check_out_time || $record->check_out_gps_verified)) ? 'Disahkan' : 'Tidak disahkan',
                ]);
            }
            fclose($output);
        }, 'laporan_kehadiran_'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function settings(Request $request): View
    {
        $admin = $this->systemAdmin($request);

        return view('attendance.admin.settings', ['role' => 'admin', 'user' => $admin, 'setting' => $this->attendance->setting()]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $admin = $this->systemAdmin($request);
        $validated = $request->validate([
            'work_start_time' => ['required', 'date_format:H:i'],
            'work_end_time' => ['required', 'date_format:H:i', 'after:work_start_time'],
            'break_start_time' => ['required', 'date_format:H:i', 'after_or_equal:work_start_time', 'before:work_end_time'],
            'break_end_time' => ['required', 'date_format:H:i', 'after:break_start_time', 'before_or_equal:work_end_time'],
            'grace_period_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'full_day_minutes' => ['required', 'integer', 'min:60', 'max:720'],
            'half_day_rate_multiplier' => ['required', 'numeric', 'min:0', 'max:1'],
            'checkout_cutoff_time' => ['required', 'date_format:H:i'],
            'working_days' => ['required', 'array', 'min:1'],
            'working_days.*' => ['integer', 'between:1,7'],
            'location_name' => ['required', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_if:status,1'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_if:status,1'],
            'allowed_radius_meter' => ['required', 'integer', 'min:20', 'max:5000'],
            'status' => ['nullable', 'boolean'],
        ]);
        $setting = $this->attendance->setting();
        $setting->update($validated + ['status' => $request->boolean('status')]);
        $this->audit($request, 'settings_updated', $setting, 'Tetapan Smart Attendance dikemaskini.');

        return back()->with('status', 'Tetapan Smart Attendance berjaya dikemaskini.');
    }

    private function worker(Request $request): Pekerja
    {
        $staff = $request->attributes->get('attendance_staff') ?: Pekerja::query()->find($request->session()->get('auth_id'));
        abort_unless($request->session()->get('auth_role') === 'staff' && $staff && $staff->status_aktif && $staff->staff_type === 'coop_staff', 403);

        return $staff;
    }

    private function admin(Request $request): AdminUser|Pekerja
    {
        if ($request->session()->get('auth_role') === 'admin') {
            return AdminUser::query()->findOrFail($request->session()->get('auth_id'));
        }

        abort_unless($request->session()->get('auth_role') === 'staff', 403);

        return Pekerja::query()
            ->whereKey($request->session()->get('auth_id'))
            ->where('staff_type', Pekerja::COOP_MANAGER_STAFF_TYPE)
            ->where('status_aktif', true)
            ->firstOrFail();
    }

    private function systemAdmin(Request $request): AdminUser
    {
        abort_unless($request->session()->get('auth_role') === 'admin', 403);

        return AdminUser::query()->findOrFail($request->session()->get('auth_id'));
    }

    private function locationInput(Request $request): array
    {
        return $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);
    }

    private function filteredRecords(array $filters)
    {
        return AttendanceRecord::query()
            ->whereHas('staff', fn ($query) => $query->where('staff_type', 'coop_staff'))
            ->when(! empty($filters['date']), fn ($query) => $query->whereDate('attendance_date', $filters['date']))
            ->when(! empty($filters['month']) && preg_match('/^\d{4}-\d{2}$/', $filters['month']), fn ($query) => $query->whereBetween('attendance_date', [$filters['month'].'-01', CarbonImmutable::parse($filters['month'].'-01')->endOfMonth()->toDateString()]))
            ->when(! empty($filters['from']), fn ($query) => $query->whereDate('attendance_date', '>=', $filters['from']))
            ->when(! empty($filters['to']), fn ($query) => $query->whereDate('attendance_date', '<=', $filters['to']))
            ->when(! empty($filters['staff_id']), fn ($query) => $query->where('staff_id', $filters['staff_id']))
            ->when(in_array($filters['status'] ?? '', $this->recordStatuses(), true), fn ($query) => $query->where('status', $filters['status']));
    }

    private function reportFilters(Request $request): array
    {
        return [
            'from' => (string) $request->query('from', now()->startOfMonth()->toDateString()),
            'to' => (string) $request->query('to', now()->toDateString()),
            'staff_id' => (string) $request->query('staff_id', ''),
            'status' => (string) $request->query('status', ''),
        ];
    }

    private function todaySummary($workers, $records, AttendanceSetting $setting): array
    {
        $summary = ['total' => $workers->count(), 'present' => 0, 'late' => 0, 'absent' => 0, 'not_checked_out' => 0];
        foreach ($workers as $worker) {
            $record = $records->get($worker->id_pekerja);
            if (! $record) {
                $summary['absent']++;
                continue;
            }
            if ($record->status === 'late') {
                $summary['late']++;
            } else {
                $summary['present']++;
            }
            if ($this->attendance->displayStatus($record, $setting) === 'working') {
                $summary['not_checked_out']++;
            }
        }

        return $summary;
    }

    private function reportSummary($records): array
    {
        return [
            'records' => $records->count(),
            'present' => $records->where('status', 'present')->count(),
            'late' => $records->whereIn('status', ['late', 'outside_area'])->count(),
            'early_leave' => $records->where('status', 'early_leave')->count(),
            'working_minutes' => $records->sum('working_minutes'),
        ];
    }

    private function workerList()
    {
        return Pekerja::query()->where('staff_type', 'coop_staff')->orderBy('nama')->get();
    }

    private function recordStatuses(): array
    {
        return ['present', 'late', 'early_leave', 'missing_checkout', 'outside_area', 'absent', 'leave', 'holiday', 'half_day'];
    }

    private function statusLabels(): array
    {
        return [
            'present' => 'Hadir', 'late' => 'Lewat', 'early_leave' => 'Keluar Awal',
            'missing_checkout' => 'Tiada Check Out', 'working' => 'Sedang Bekerja',
            'not_checked_in' => 'Belum Check In', 'outside_area' => 'Di Luar Kawasan', 'absent' => 'Tidak Hadir',
            'leave' => 'Cuti', 'holiday' => 'Cuti Umum', 'half_day' => 'Separuh Hari',
        ];
    }

    private function correctionTypes(): array
    {
        return [
            'missing_check_in' => 'Tiada Check In', 'missing_check_out' => 'Tiada Check Out',
            'wrong_check_in_time' => 'Masa Check In Salah', 'wrong_check_out_time' => 'Masa Check Out Salah',
            'other' => 'Lain-lain',
        ];
    }

    private function absenceReasons(): array
    {
        return [
            'sakit' => 'Sakit',
            'cuti_diluluskan' => 'Cuti Diluluskan',
            'urusan_kecemasan' => 'Urusan Kecemasan',
            'urusan_keluarga' => 'Urusan Keluarga',
            'masalah_pengangkutan' => 'Masalah Pengangkutan',
            'tanpa_kebenaran' => 'Tidak Hadir Tanpa Kebenaran',
            'lain_lain' => 'Lain-lain',
        ];
    }

    private function composeCorrectionReason(string $reason, ?string $absenceReason): string
    {
        $reason = trim($reason);

        if (! $absenceReason) {
            return $reason;
        }

        $label = $this->absenceReasons()[$absenceReason] ?? null;

        if (! $label) {
            return $reason;
        }

        return 'Sebab Tidak Hadir: '.$label.PHP_EOL.'Catatan: '.$reason;
    }

    private function duration(?int $minutes): string
    {
        $minutes ??= 0;

        return intdiv($minutes, 60).' jam '.($minutes % 60).' minit';
    }

    private function audit(Request $request, string $action, object $subject, string $description, array $meta = []): void
    {
        $role = $request->session()->get('auth_role');
        AuditLog::query()->create([
            'actor_role' => $role,
            'actor_id' => $request->session()->get('auth_id'),
            'action' => $action,
            'module' => 'smart_attendance',
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'description' => $description,
            'meta' => $meta ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }

    private function notifyWorker(?Pekerja $staff, string $title, string $message, string $link): void
    {
        if (! $staff) {
            return;
        }

        CooperativeNotification::query()->create(['recipient_role' => 'staff', 'recipient_id' => $staff->id_pekerja, 'title' => $title, 'message' => $message, 'link' => $link]);
    }

    private function notifyAdmins(string $title, string $message, string $link): void
    {
        AdminUser::query()->where('status_aktif', true)->each(fn (AdminUser $admin) => CooperativeNotification::query()->create([
            'recipient_role' => 'admin', 'recipient_id' => $admin->id_admin, 'title' => $title, 'message' => $message, 'link' => $link,
        ]));
    }
}
