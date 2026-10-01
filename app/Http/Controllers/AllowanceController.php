<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\AttendanceRecord;
use App\Models\MonthlyAllowance;
use App\Models\Pekerja;
use App\Services\AllowanceService;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AllowanceController extends Controller
{
    public function __construct(
        private readonly AllowanceService $allowance,
        private readonly AttendanceService $attendance,
    ) {
    }

    public function worker(Request $request): View
    {
        $staff = $this->workerAccount($request);
        [$month, $year] = $this->monthYear($request);

        return $this->render($request, $staff, $month, $year, true);
    }

    public function index(Request $request): View
    {
        $user = $this->backOfficeUser($request);
        [$month, $year] = $this->monthYear($request);
        $staffId = (int) $request->query('staff_id', 0);
        $staff = $staffId > 0
            ? Pekerja::query()->coopWorkers()->findOrFail($staffId)
            : Pekerja::query()->coopWorkers()->orderBy('nama')->first();

        abort_unless($staff, 404, 'Tiada pekerja koperasi didaftarkan.');

        return $this->render($request, $staff, $month, $year, false, $user);
    }

    public function pdf(Request $request): View
    {
        $user = $this->backOfficeUser($request);
        [$month, $year] = $this->monthYear($request);
        $staff = Pekerja::query()->coopWorkers()->findOrFail((int) $request->query('staff_id'));
        $data = $this->allowanceData($staff, $month, $year);

        return view('allowances.pdf', $data + [
            'role' => $request->session()->get('auth_role'),
            'user' => $user,
            'filters' => compact('month', 'year') + ['staff_id' => $staff->id_pekerja],
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $this->backOfficeUser($request);
        $validated = $request->validate([
            'staff_id' => ['required', 'integer'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2020,2100'],
        ]);
        $staff = Pekerja::query()->coopWorkers()->findOrFail($validated['staff_id']);

        $this->allowance->generateMonthly(
            $staff,
            (int) $validated['month'],
            (int) $validated['year'],
            (string) $request->session()->get('auth_role'),
            (int) $request->session()->get('auth_id')
        );

        return back()->with('status', 'Elaun bulanan berjaya dijana dan berada dalam status Pending.');
    }

    public function approve(Request $request, MonthlyAllowance $monthlyAllowance): RedirectResponse
    {
        $this->backOfficeUser($request);
        abort_unless($monthlyAllowance->status === MonthlyAllowance::STATUS_PENDING, 422);

        $monthlyAllowance->update([
            'status' => MonthlyAllowance::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $request->session()->get('auth_id'),
        ]);

        return back()->with('status', 'Elaun bulanan berjaya diluluskan.');
    }

    public function markPaid(Request $request, MonthlyAllowance $monthlyAllowance): RedirectResponse
    {
        abort_unless($request->session()->get('auth_role') === 'admin', 403);
        abort_unless(in_array($monthlyAllowance->status, [MonthlyAllowance::STATUS_PENDING, MonthlyAllowance::STATUS_APPROVED], true), 422);

        $monthlyAllowance->update([
            'status' => MonthlyAllowance::STATUS_PAID,
            'paid_at' => now(),
            'paid_by' => $request->session()->get('auth_id'),
        ]);

        return back()->with('status', 'Elaun bulanan telah ditanda sebagai Paid.');
    }

    public function complete(Request $request): RedirectResponse
    {
        $this->backOfficeUser($request);
        $validated = $request->validate([
            'staff_id' => ['required', 'integer'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2020,2100'],
        ]);
        $staff = Pekerja::query()->coopWorkers()->findOrFail($validated['staff_id']);
        $allowance = $this->allowance->generateMonthly(
            $staff,
            (int) $validated['month'],
            (int) $validated['year'],
            (string) $request->session()->get('auth_role'),
            (int) $request->session()->get('auth_id')
        );

        $allowance->update([
            'status' => MonthlyAllowance::STATUS_COMPLETED,
            'approved_at' => $allowance->approved_at ?? now(),
            'approved_by' => $allowance->approved_by ?? $request->session()->get('auth_id'),
            'notes' => trim(($allowance->notes ? $allowance->notes.PHP_EOL : '').'Ditanda selesai pada '.now()->timezone('Asia/Kuala_Lumpur')->format('d/m/Y h:i A').'.'),
        ]);

        return back()->with('status', 'Elaun bulanan telah ditanda sebagai selesai.');
    }

    private function render(Request $request, Pekerja $staff, int $month, int $year, bool $ownView, AdminUser|Pekerja|null $backOfficeUser = null): View
    {
        $data = $this->allowanceData($staff, $month, $year);

        return view('allowances.index', [
            'role' => $request->session()->get('auth_role'),
            'staffType' => $request->session()->get('staff_type'),
            'user' => $ownView ? $staff : $backOfficeUser,
            'workers' => $ownView ? collect([$staff]) : Pekerja::query()->coopWorkers()->orderBy('nama')->get(),
            'filters' => compact('month', 'year') + ['staff_id' => $staff->id_pekerja],
            'ownView' => $ownView,
        ] + $data);
    }

    private function allowanceData(Pekerja $staff, int $month, int $year): array
    {
        $setting = $this->attendance->setting();
        $records = AttendanceRecord::query()
            ->with('staff')
            ->where('staff_id', $staff->id_pekerja)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->orderBy('attendance_date')
            ->get();

        $records->each(fn (AttendanceRecord $record) => $this->allowance->calculateRecord($record, $setting));

        $monthlyAllowance = MonthlyAllowance::query()
            ->where('staff_id', $staff->id_pekerja)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        return [
            'staff' => $staff,
            'records' => $records,
            'summary' => $this->allowance->summary($records, $setting),
            'monthlyAllowance' => $monthlyAllowance,
        ];
    }

    private function monthYear(Request $request): array
    {
        return [
            (int) $request->query('month', now()->month),
            (int) $request->query('year', now()->year),
        ];
    }

    private function workerAccount(Request $request): Pekerja
    {
        abort_unless($request->session()->get('auth_role') === 'staff', 403);

        return Pekerja::query()
            ->whereKey($request->session()->get('auth_id'))
            ->where('staff_type', Pekerja::COOP_WORKER_STAFF_TYPE)
            ->where('status_aktif', true)
            ->firstOrFail();
    }

    private function backOfficeUser(Request $request): AdminUser|Pekerja
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
}
