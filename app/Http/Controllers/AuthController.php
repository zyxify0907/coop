<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Ahli;
use App\Models\AhliImport;
use App\Models\Announcement;
use App\Models\AttendanceCorrection;
use App\Models\AuditLog;
use App\Models\CooperativeNotification;
use App\Models\DocumentUpload;
use App\Models\Pekerja;
use App\Models\Permohonan;
use App\Models\ShareTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        [$role, $user] = $this->findLoginUser($validated['identifier']);

        if (! $user || ! $this->passwordMatches($user, $validated['password'])) {
            return back()
                ->withErrors(['identifier' => 'Login tidak sah. Sila semak maklumat anda.'])
                ->withInput($request->except('password'));
        }

        $request->session()->regenerate();
        $request->session()->put('auth_role', $role);
        $request->session()->put('auth_id', $user->getKey());
        $request->session()->put('staff_type', $role === 'staff' ? $user->staff_type : null);
        $request->session()->put('last_login_at', now()->timezone('Asia/Kuala_Lumpur')->toDateTimeString());

        return redirect()->route('auth.dashboard');
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        $role = $request->session()->get('auth_role');
        $id = $request->session()->get('auth_id');

        if (! $role || ! $id) {
            return redirect()->route('login');
        }

        $user = match ($role) {
            'ahli' => Ahli::query()->with('saham')->find($id),
            'staff' => Pekerja::query()->find($id),
            'admin' => AdminUser::query()->find($id),
            default => null,
        };

        if (! $user) {
            $request->session()->flush();

            return redirect()->route('login');
        }

        if ($role === 'admin') {
            $home = $this->adminHomeData($request);

            return view('dashboard.admin-home', compact('role', 'user', 'home'));
        }

        if ($role === 'staff') {
            return redirect()->route($this->staffDashboardRoute($user->staff_type));
        }

        $memberApplication = Permohonan::query()
            ->where('id_ahli', $user->id_ahli)
            ->where('jenis', 'anggota')
            ->where('status', 'diluluskan')
            ->latest('tarikh_keputusan')
            ->latest('id_permohonan')
            ->first();

        $latestApplication = Permohonan::query()
            ->where('id_ahli', $user->id_ahli)
            ->latest('tarikh_permohonan')
            ->latest('id_permohonan')
            ->first();

        $home = $this->studentHomeData($request, $user, $latestApplication);

        return view('dashboard.student', compact('role', 'user', 'memberApplication', 'latestApplication', 'home'));
    }

    public function adminDashboard(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'admin') {
            return redirect()->route('login');
        }

        $role = 'admin';
        $user = AdminUser::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            $request->session()->flush();

            return redirect()->route('login');
        }

        $stats = $this->adminStats();
        $chartData = $this->adminChartData();
        $dashboard = $this->adminDashboardData();

        return view('dashboard.admin', compact('role', 'user', 'stats', 'chartData', 'dashboard'));
    }

    public function dashboardChartData(Request $request): JsonResponse
    {
        if ($request->session()->get('auth_role') !== 'admin') {
            abort(403);
        }

        return response()->json([
            'stats' => $this->adminStats(),
            'chart' => $this->adminChartData(),
            'generated_at' => now()->toDateTimeString(),
        ]);
    }

    public function studentProfile(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'ahli') {
            return redirect()->route('login');
        }

        $role = 'ahli';
        $user = Ahli::query()
            ->with('saham')
            ->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        return view('student.profile', compact('role', 'user'));
    }

    public function studentShareDashboard(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'ahli') {
            return redirect()->route('login');
        }

        $role = 'ahli';
        $user = Ahli::query()
            ->with('saham')
            ->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        $memberApplication = Permohonan::query()
            ->where('id_ahli', $user->id_ahli)
            ->where('jenis', 'anggota')
            ->where('status', 'diluluskan')
            ->latest('tarikh_keputusan')
            ->latest('id_permohonan')
            ->first();

        $recentTransactions = Schema::hasTable('share_transactions')
            ? ShareTransaction::query()
                ->where('member_type', 'student')
                ->where('member_id', $user->id_ahli)
                ->latest('transacted_at')
                ->latest()
                ->limit(6)
                ->get()
            : collect();

        $latestApplications = Permohonan::query()
            ->where('id_ahli', $user->id_ahli)
            ->whereIn('jenis', ['anggota', 'saham', 'berhenti', 'pengeluaran', 'pindah', 'bersara'])
            ->latest('tarikh_permohonan')
            ->latest('id_permohonan')
            ->limit(5)
            ->get();

        return view('dashboard.student-saham', compact('role', 'user', 'memberApplication', 'recentTransactions', 'latestApplications'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function adminHomeData(Request $request): array
    {
        $pendingStatuses = ['baru', 'semak', 'pending', 'dalam_semakan'];
        $membershipPending = Permohonan::query()->where('jenis', 'anggota')->whereIn('status', $pendingStatuses)->count();
        $sharePending = Permohonan::query()
            ->where(function ($query): void {
                $query->where('jenis', 'saham')
                    ->orWhereIn('jenis', ['berhenti', 'pengeluaran', 'pindah', 'bersara']);
            })
            ->whereIn('status', $pendingStatuses)
            ->count();
        $shareAdditionPending = Permohonan::query()->where('jenis', 'saham')->whereIn('status', $pendingStatuses)->count();
        $shareWithdrawalPending = Permohonan::query()->whereIn('jenis', ['berhenti', 'pengeluaran', 'pindah', 'bersara'])->whereIn('status', $pendingStatuses)->count();
        $pendingApplicationIds = Permohonan::query()->whereIn('status', $pendingStatuses)->pluck('id_permohonan');
        $paymentSlipPending = Schema::hasTable('document_uploads')
            ? DocumentUpload::query()
                ->where('category', 'slip_bayaran')
                ->whereIn('documentable_id', $pendingApplicationIds)
                ->count()
            : 0;
        $ordersPending = Schema::hasTable('tempahan')
            ? DB::table('tempahan')->whereNotIn(DB::raw('LOWER(status)'), ['sudah_ambil', 'sudah ambil', 'diambil', 'siap diambil', 'selesai'])->whereNull('tarikh_ambil')->count()
            : 0;
        $ordersNew = Schema::hasTable('tempahan')
            ? DB::table('tempahan')->whereIn(DB::raw('LOWER(status)'), ['baru', 'pending'])->count()
            : 0;
        $attendanceCorrections = Schema::hasTable('attendance_corrections')
            ? AttendanceCorrection::query()->where('status', 'pending')->count()
            : 0;
        $lateToday = Schema::hasTable('attendance_records')
            ? DB::table('attendance_records')->whereDate('attendance_date', today())->where('late_minutes', '>', 0)->count()
            : 0;
        $earlyLeaveToday = Schema::hasTable('attendance_records')
            ? DB::table('attendance_records')->whereDate('attendance_date', today())->where('early_leave_minutes', '>', 0)->count()
            : 0;
        $missingCheckout = Schema::hasTable('attendance_records')
            ? DB::table('attendance_records')->whereDate('attendance_date', today())->whereNotNull('check_in_time')->whereNull('check_out_time')->count()
            : 0;
        $attendanceIssues = $lateToday + $earlyLeaveToday + $missingCheckout;

        $recentActivities = $this->recentAdminActivities();
        return [
            'last_login_at' => $request->session()->get('last_login_at'),
            'announcements' => Announcement::query()->orderByDesc('is_pinned')->latest()->limit(4)->get(),
            'notifications' => [
                ['tone' => 'blue', 'count' => $membershipPending, 'label' => 'permohonan anggota baharu menunggu semakan', 'route' => route('admin.permohonan.index', ['jenis' => 'anggota'])],
                ['tone' => 'yellow', 'count' => $shareAdditionPending, 'label' => 'permohonan tambah saham menunggu kelulusan', 'route' => route('admin.permohonan.index', ['jenis' => 'saham'])],
                ['tone' => 'red', 'count' => $shareWithdrawalPending, 'label' => 'permohonan pengeluaran / berhenti perlu diproses', 'route' => route('admin.permohonan.index', ['jenis' => 'berhenti'])],
                ['tone' => 'yellow', 'count' => $paymentSlipPending, 'label' => 'slip bayaran perlu disemak', 'route' => route('admin.permohonan.index')],
                ['tone' => 'green', 'count' => $ordersNew, 'label' => 'tempahan baju baharu diterima', 'route' => route('admin.tempahan.index')],
                ['tone' => 'red', 'count' => $attendanceIssues, 'label' => 'rekod kehadiran bermasalah hari ini', 'route' => route('admin.attendance.records')],
            ],
            'recent_activities' => $recentActivities,
        ];
    }

    private function studentHomeData(Request $request, Ahli $student, ?Permohonan $latestApplication): array
    {
        $share = $student->saham;
        $totalShare = (float) optional($share)->syer + (float) optional($share)->tambahan_saham;
        $orderCount = Schema::hasTable('tempahan')
            ? DB::table('tempahan')->where('id_ahli', $student->id_ahli)->count()
            : 0;
        $pendingApplications = Permohonan::query()
            ->where('id_ahli', $student->id_ahli)
            ->whereIn('status', ['baru', 'semak', 'pending', 'dalam_semakan'])
            ->count();
        $notifications = Schema::hasTable('notifications')
            ? CooperativeNotification::query()
                ->where('recipient_role', 'ahli')
                ->where('recipient_id', $student->id_ahli)
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        $activities = collect();
        Permohonan::query()
            ->where('id_ahli', $student->id_ahli)
            ->latest('tarikh_permohonan')
            ->latest('id_permohonan')
            ->limit(4)
            ->get()
            ->each(fn (Permohonan $application) => $activities->push([
                'name' => 'Permohonan '.ucwords(str_replace('_', ' ', $application->jenis)),
                'user' => ucfirst(str_replace('_', ' ', $application->status)),
                'time' => $application->tarikh_permohonan?->diffForHumans() ?? '-',
            ]));

        if (Schema::hasTable('tempahan')) {
            DB::table('tempahan')
                ->where('id_ahli', $student->id_ahli)
                ->latest('tarikh_tempahan')
                ->limit(2)
                ->get()
                ->each(fn ($order) => $activities->push([
                    'name' => 'Tempahan baju dihantar',
                    'user' => ucfirst(str_replace('_', ' ', (string) ($order->status ?? 'baru'))),
                    'time' => isset($order->tarikh_tempahan) ? \Carbon\Carbon::parse($order->tarikh_tempahan)->diffForHumans() : '-',
                ]));
        }

        return [
            'last_login_at' => $request->session()->get('last_login_at'),
            'announcements' => Announcement::query()->visibleTo('ahli')->orderByDesc('is_pinned')->latest()->limit(4)->get(),
            'notifications' => [
                ['tone' => 'blue', 'count' => $latestApplication ? 1 : 0, 'label' => $latestApplication ? 'status permohonan terkini: '.ucfirst(str_replace('_', ' ', $latestApplication->status)) : 'tiada permohonan terkini', 'route' => route('student.permohonan.status')],
                ['tone' => 'green', 'count' => $share ? 1 : 0, 'label' => $share ? 'jumlah saham semasa RM '.number_format($totalShare, 2) : 'rekod saham belum diwujudkan', 'route' => route('student.profile')],
                ['tone' => 'yellow', 'count' => $pendingApplications, 'label' => 'permohonan masih menunggu semakan', 'route' => route('student.permohonan.status')],
                ['tone' => 'blue', 'count' => $orderCount, 'label' => 'rekod tempahan baju anda', 'route' => route('student.tempahan.index')],
            ],
            'messages' => $notifications,
            'recent_activities' => $activities->sortByDesc('time')->take(6)->values(),
        ];
    }

    private function recentAdminActivities(): \Illuminate\Support\Collection
    {
        if (Schema::hasTable('audit_logs')) {
            return AuditLog::query()
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn (AuditLog $log): array => [
                    'name' => $log->description ?: ucfirst($log->action).' '.$log->module,
                    'user' => ucfirst($log->actor_role ?? 'Sistem').' #'.($log->actor_id ?? '-'),
                    'time' => $log->created_at?->diffForHumans() ?? '-',
                ]);
        }

        $applications = Permohonan::query()
            ->latest('tarikh_permohonan')
            ->latest('id_permohonan')
            ->limit(3)
            ->get()
            ->map(fn (Permohonan $application): array => [
                'name' => 'Permohonan '.$application->jenis.' dihantar',
                'user' => $application->nama_pemohon,
                'time' => $application->tarikh_permohonan?->diffForHumans() ?? '-',
            ]);

        $announcements = Announcement::query()
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (Announcement $announcement): array => [
                'name' => 'Pengumuman baharu dicipta',
                'user' => 'Admin',
                'time' => $announcement->created_at?->diffForHumans() ?? '-',
            ]);

        return $applications->merge($announcements)->take(6)->values();
    }

    private function adminStats(): array
    {
        $studentShares = (float) DB::table('saham')->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')->value('total');
        $staffShares = Schema::hasTable('saham_staff')
            ? (float) DB::table('saham_staff')->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')->value('total')
            : 0.0;
        $staffQuery = Pekerja::query();
        $statusCounts = Permohonan::query()
            ->selectRaw("
                SUM(CASE WHEN status = 'baru' THEN 1 ELSE 0 END) as baru_count,
                SUM(CASE WHEN status IN ('semak', 'pending', 'dalam_semakan') THEN 1 ELSE 0 END) as semakan_count,
                SUM(CASE WHEN status = 'diluluskan' THEN 1 ELSE 0 END) as lulus_count,
                SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as tolak_count
            ")
            ->first();
        $typeCounts = Permohonan::query()
            ->selectRaw("
                SUM(CASE WHEN jenis = 'anggota' THEN 1 ELSE 0 END) as anggota_count,
                SUM(CASE WHEN jenis = 'saham' THEN 1 ELSE 0 END) as saham_count,
                SUM(CASE WHEN jenis IN ('berhenti', 'pengeluaran', 'pindah', 'bersara') THEN 1 ELSE 0 END) as pengeluaran_count
            ")
            ->first();
        $orderCounts = Schema::hasTable('tempahan')
            ? DB::table('tempahan')
                ->selectRaw("
                    SUM(CASE WHEN LOWER(status) IN ('baru', 'pending') THEN 1 ELSE 0 END) as baru_count,
                    SUM(CASE WHEN LOWER(status) IN ('belum_ambil', 'belum ambil', 'diproses', 'dalam proses', 'siap', 'menunggu pengambilan', 'menunggu_ambil') THEN 1 ELSE 0 END) as belum_ambil_count,
                    SUM(CASE WHEN LOWER(status) IN ('sudah_ambil', 'sudah ambil', 'diambil', 'siap diambil', 'selesai') OR tarikh_ambil IS NOT NULL THEN 1 ELSE 0 END) as diambil_count
                ")
                ->first()
            : null;

        return [
            'students' => Ahli::query()->count(),
            'active_students' => Ahli::query()->where('status_aktif', true)->count(),
            'inactive_students' => Ahli::query()->where('status_aktif', false)->count(),
            'staff' => $staffQuery->count(),
            'active_staff' => Pekerja::query()->where('status_aktif', true)->count(),
            'inactive_staff' => Pekerja::query()->where('status_aktif', false)->count(),
            'coop_workers' => Pekerja::query()->where('staff_type', 'coop_staff')->count(),
            'lecturer_members' => Pekerja::query()->where('staff_type', 'lecturer_member')->count(),
            'clothing_staff' => Pekerja::query()->where('staff_type', 'clothing_staff')->count(),
            'inactive_total' => Ahli::query()->where('status_aktif', false)->count() + Pekerja::query()->where('status_aktif', false)->count(),
            'failed_imports' => (int) (AhliImport::query()->latest()->value('failed_count') ?? 0),
            'total_applications' => Permohonan::query()->count(),
            'pending_applications' => Permohonan::query()->whereIn('status', ['baru', 'semak', 'pending'])->count(),
            'approved_applications' => Permohonan::query()->where('status', 'diluluskan')->count(),
            'rejected_applications' => Permohonan::query()->where('status', 'ditolak')->count(),
            'applications_new' => (int) ($statusCounts?->baru_count ?? 0),
            'applications_review' => (int) ($statusCounts?->semakan_count ?? 0),
            'applications_approved' => (int) ($statusCounts?->lulus_count ?? 0),
            'applications_rejected' => (int) ($statusCounts?->tolak_count ?? 0),
            'pending_membership' => Permohonan::query()->where('jenis', 'anggota')->whereIn('status', ['baru', 'semak', 'pending'])->count(),
            'pending_share_additions' => Permohonan::query()->where('jenis', 'saham')->whereIn('status', ['baru', 'semak', 'pending'])->count(),
            'pending_withdrawals' => Permohonan::query()->whereIn('jenis', ['berhenti', 'pengeluaran', 'pindah', 'bersara'])->whereIn('status', ['baru', 'semak', 'pending'])->count(),
            'applications_membership_total' => (int) ($typeCounts?->anggota_count ?? 0),
            'applications_share_total' => (int) ($typeCounts?->saham_count ?? 0),
            'applications_withdrawal_total' => (int) ($typeCounts?->pengeluaran_count ?? 0),
            'total_shares' => $studentShares + $staffShares,
            'student_shares' => $studentShares,
            'staff_shares' => $staffShares,
            'documents' => Schema::hasTable('document_uploads') ? DocumentUpload::query()->count() : 0,
            'transactions' => Schema::hasTable('share_transactions') ? ShareTransaction::query()->count() : 0,
            'orders_total' => Schema::hasTable('tempahan') ? DB::table('tempahan')->count() : 0,
            'orders_pending' => Schema::hasTable('tempahan')
                ? DB::table('tempahan')->whereNotIn(DB::raw('LOWER(status)'), ['sudah_ambil', 'sudah ambil', 'diambil', 'siap diambil', 'selesai'])->whereNull('tarikh_ambil')->count()
                : 0,
            'orders_new' => (int) ($orderCounts?->baru_count ?? 0),
            'orders_processing' => 0,
            'orders_waiting_pickup' => (int) ($orderCounts?->belum_ambil_count ?? 0),
            'orders_picked' => (int) ($orderCounts?->diambil_count ?? 0),
            'attendance_today' => Schema::hasTable('attendance_records')
                ? DB::table('attendance_records')->whereDate('attendance_date', today())->count()
                : 0,
        ];
    }

    private function adminDashboardData(): array
    {
        $recentApplications = Permohonan::query()
            ->with('ahli')
            ->latest('tarikh_permohonan')
            ->latest('id_permohonan')
            ->limit(5)
            ->get();

        $recentTransactions = Schema::hasTable('share_transactions')
            ? ShareTransaction::query()
                ->with(['student', 'staff'])
                ->latest('transacted_at')
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        $recentDocuments = Schema::hasTable('document_uploads')
            ? DocumentUpload::query()
                ->with(['student', 'staff'])
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        $recentOrders = Schema::hasTable('tempahan')
            ? DB::table('tempahan')
                ->leftJoin('ahli', 'tempahan.id_ahli', '=', 'ahli.id_ahli')
                ->select('tempahan.*', 'ahli.nama', 'ahli.no_matrik')
                ->latest('tempahan.tarikh_tempahan')
                ->limit(5)
                ->get()
            : collect();

        return [
            'recent_applications' => $recentApplications,
            'recent_transactions' => $recentTransactions,
            'recent_documents' => $recentDocuments,
            'recent_orders' => $recentOrders,
        ];
    }

    private function adminChartData(): array
    {
        $salesDateColumn = $this->tableColumn('jualan', ['tarikh', 'tarikh_jualan', 'created_at']);
        $ordersDateColumn = $this->tableColumn('tempahan', ['created_at', 'tarikh_tempahan']);
        $endDate = $this->latestChartDate($salesDateColumn, $ordersDateColumn);
        $dates = collect(range(6, 0))
            ->map(fn (int $daysAgo) => $endDate->subDays($daysAgo));

        $sales = $salesDateColumn
            ? DB::table('jualan')
                ->selectRaw("DATE({$salesDateColumn}) as chart_date, COALESCE(SUM(jumlah), 0) as total")
                ->whereDate($salesDateColumn, '>=', $dates->first()->toDateString())
                ->groupBy('chart_date')
                ->pluck('total', 'chart_date')
            : collect();

        $orders = $ordersDateColumn
            ? DB::table('tempahan')
                ->selectRaw("DATE({$ordersDateColumn}) as chart_date, COUNT(*) as total")
                ->whereDate($ordersDateColumn, '>=', $dates->first()->toDateString())
                ->groupBy('chart_date')
                ->pluck('total', 'chart_date')
            : collect();

        $imports = Schema::hasTable('ahli_imports')
            ? DB::table('ahli_imports')
                ->selectRaw('DATE(created_at) as chart_date, COALESCE(SUM(imported_count), 0) as total')
                ->whereDate('created_at', '>=', $dates->first()->toDateString())
                ->groupBy('chart_date')
                ->pluck('total', 'chart_date')
            : collect();

        return [
            'labels' => $dates->map(fn (CarbonImmutable $date) => $date->format('d M'))->values(),
            'sales' => $dates->map(fn (CarbonImmutable $date) => round((float) ($sales[$date->toDateString()] ?? 0), 2))->values(),
            'orders' => $dates->map(fn (CarbonImmutable $date) => (int) ($orders[$date->toDateString()] ?? 0))->values(),
            'imports' => $dates->map(fn (CarbonImmutable $date) => (int) ($imports[$date->toDateString()] ?? 0))->values(),
            'range' => [
                'from' => $dates->first()->toDateString(),
                'to' => $dates->last()->toDateString(),
            ],
        ];
    }

    private function latestChartDate(?string $salesDateColumn, ?string $ordersDateColumn): CarbonImmutable
    {
        $latestDates = collect();

        if ($salesDateColumn) {
            $latestDates->push(DB::table('jualan')->max(DB::raw("DATE({$salesDateColumn})")));
        }

        if ($ordersDateColumn) {
            $latestDates->push(DB::table('tempahan')->max(DB::raw("DATE({$ordersDateColumn})")));
        }

        if (Schema::hasTable('ahli_imports')) {
            $latestDates->push(DB::table('ahli_imports')->max(DB::raw('DATE(created_at)')));
        }

        $latest = $latestDates
            ->filter()
            ->map(fn (string $date) => CarbonImmutable::parse($date))
            ->sortDesc()
            ->first();

        return $latest ?? CarbonImmutable::today();
    }

    private function tableColumn(string $table, array $columns): ?string
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function passwordMatches(Model $user, string $password): bool
    {
        $storedHash = (string) $user->password_hash;

        if ($storedHash === '') {
            return false;
        }

        if (str_starts_with($storedHash, '$2a$')) {
            $normalizedHash = '$2y$'.substr($storedHash, 4);

            if (! password_verify($password, $normalizedHash)) {
                return false;
            }

            $user->password_hash = Hash::make($password);
            $user->save();

            return true;
        }

        return Hash::check($password, $storedHash);
    }

    /**
     * @return array{0:string|null,1:Model|null}
     */
    private function findLoginUser(string $identifier): array
    {
        $student = Ahli::query()
            ->where(function ($query) use ($identifier): void {
                $query->where('no_matrik', $identifier)
                    ->orWhere('nric', $identifier)
                    ->orWhere('email', $identifier);
            })
            ->first();

        if ($student) {
            return ['ahli', $student];
        }

        $staff = Pekerja::query()
            ->where(function ($query) use ($identifier): void {
                $query->where('nric', $identifier)
                    ->orWhere('email', $identifier)
                    ->orWhere('no_pekerja', $identifier);
            })
            ->where('status_aktif', true)
            ->first();

        if ($staff) {
            return ['staff', $staff];
        }

        $admin = AdminUser::query()
            ->where(function ($query) use ($identifier): void {
                $query->where('nric', $identifier)
                    ->orWhere('username', $identifier);
            })
            ->where('status_aktif', true)
            ->first();

        if ($admin) {
            return ['admin', $admin];
        }

        return [null, null];
    }

    private function staffDashboardRoute(?string $staffType): string
    {
        return match ($staffType) {
            'lecturer_member' => 'lecturer-member.dashboard',
            'clothing_staff' => 'clothing-staff.dashboard',
            default => 'coop-staff.dashboard',
        };
    }
}
