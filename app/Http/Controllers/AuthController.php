<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Ahli;
use App\Models\AhliImport;
use App\Models\Announcement;
use App\Models\CooperativeNotification;
use App\Models\DocumentUpload;
use App\Models\Pekerja;
use App\Models\Permohonan;
use App\Models\ShareTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showForgotPassword(): View
    {
        return view('auth.password.forgot');
    }

    public function showRegister(): View
    {
        return view('auth.register', [
            'studentClasses' => $this->studentClassOptions(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable'],
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

        if ($request->boolean('remember')) {
            Cookie::queue($this->rememberMeCookie($role, $user));
        } else {
            Cookie::queue(Cookie::forget($this->rememberMeCookieName()));
        }

        return redirect()->route('auth.dashboard');
    }

    public function resetForgottenPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
            'nric' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        [$role, $user] = $this->findLoginUser($validated['identifier']);
        $normalizedNric = preg_replace('/\D+/', '', $validated['nric']) ?: $validated['nric'];

        if (! $user || (string) ($user->nric ?? '') !== (string) $normalizedNric) {
            return back()
                ->withErrors(['identifier' => 'Maklumat pengesahan tidak sah. Sila semak ID akaun dan No. KP anda.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $user->password_hash = Hash::make($validated['password']);
        $user->save();

        $roleLabel = match ($role) {
            'admin' => 'admin',
            'staff' => 'staff',
            default => 'pelajar',
        };

        return redirect()
            ->route('login')
            ->with('status', 'Kata laluan akaun '.$roleLabel.' berjaya dikemaskini. Sila log masuk semula.');
    }

    public function register(Request $request): RedirectResponse
    {
        $role = $request->string('role')->toString();

        abort_unless(in_array($role, ['student', 'staff'], true), 422);

        $baseRules = [
            'role' => ['required', Rule::in(['student', 'staff'])],
            'nama' => ['required', 'string', 'max:100'],
            'nric' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'no_tel' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];

        $rules = $role === 'student'
            ? $baseRules + [
                'no_matrik' => ['required', 'string', 'max:20', 'unique:ahli,no_matrik'],
                'nric' => ['required', 'string', 'max:20', 'unique:ahli,nric', 'unique:pekerja,nric', 'unique:admin,nric'],
                'email' => ['nullable', 'email', 'max:100', 'unique:ahli,email', 'unique:pekerja,email'],
                'kelas' => ['nullable', Rule::in($this->studentClassOptions())],
            ]
            : $baseRules + [
                'nric' => ['required', 'string', 'max:20', 'unique:pekerja,nric', 'unique:ahli,nric', 'unique:admin,nric'],
                'email' => ['nullable', 'email', 'max:100', 'unique:pekerja,email', 'unique:ahli,email'],
                'staff_type' => ['required', Rule::in(['lecturer_member', 'coop_staff', 'clothing_staff'])],
            ];

        $validated = $request->validate($rules);

        if ($role === 'student') {
            $academic = $this->academicFromClass($validated['kelas'] ?? null);
            $user = Ahli::query()->create([
                'no_matrik' => strtoupper(trim($validated['no_matrik'])),
                'nama' => $validated['nama'],
                'nric' => preg_replace('/\D+/', '', $validated['nric']) ?: $validated['nric'],
                'email' => $validated['email'] ?? null,
                'no_tel' => $validated['no_tel'] ?? null,
                'kelas' => $validated['kelas'] ?? null,
                'semester' => $academic['semester'] ?? null,
                'program' => $academic['program'] ?? null,
                'password_hash' => Hash::make($validated['password']),
                'tarikh_daftar' => now()->toDateString(),
                'status_aktif' => true,
            ]);

            return $this->completeRegistrationLogin($request, 'ahli', $user);
        }

        $user = Pekerja::query()->create([
            'no_pekerja' => $this->nextStaffNumber(),
            'nama' => $validated['nama'],
            'nric' => preg_replace('/\D+/', '', $validated['nric']) ?: $validated['nric'],
            'email' => $validated['email'] ?? null,
            'no_tel' => $validated['no_tel'] ?? null,
            'staff_type' => $validated['staff_type'],
            'kadar_elaun' => 0,
            'tarikh_mula' => now()->toDateString(),
            'password_hash' => Hash::make($validated['password']),
            'status_aktif' => true,
        ]);

        return $this->completeRegistrationLogin($request, 'staff', $user);
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

        $home = $this->studentHomeData($request, $user);

        return view('dashboard.student', compact('role', 'user', 'memberApplication', 'home'));
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

        $latestApplications = Permohonan::query()
            ->where('id_ahli', $user->id_ahli)
            ->latest('tarikh_permohonan')
            ->latest('id_permohonan')
            ->limit(5)
            ->get();

        $recentTransactions = Schema::hasTable('share_transactions')
            ? ShareTransaction::query()
                ->where('member_type', 'student')
                ->where('member_id', $user->id_ahli)
                ->latest('transacted_at')
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        $recentOrders = Schema::hasTable('tempahan')
            ? DB::table('tempahan')
                ->where('id_ahli', $user->id_ahli)
                ->latest('tarikh_tempahan')
                ->limit(5)
                ->get()
            : collect();

        return view('student.profile', compact('role', 'user', 'latestApplications', 'recentTransactions', 'recentOrders'));
    }

    public function updateStudentProfile(Request $request): RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'ahli') {
            return redirect()->route('login');
        }

        $user = Ahli::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'no_tel' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('ahli', 'email')->ignore($user->id_ahli, 'id_ahli')],
        ], [
            'email.email' => 'Format email tidak sah.',
            'email.unique' => 'Email ini sudah digunakan oleh akaun lain.',
            'no_tel.max' => 'No telefon terlalu panjang.',
        ]);

        $user->update([
            'no_tel' => $validated['no_tel'] ?? null,
            'email' => $validated['email'] ?? null,
        ]);

        return back()->with('success', 'Profil berjaya dikemaskini.');
    }

    public function updateStudentPassword(Request $request): RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'ahli') {
            return redirect()->route('login');
        }

        $user = Ahli::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Sila masukkan kata laluan semasa.',
            'password.required' => 'Sila masukkan kata laluan baharu.',
            'password.min' => 'Kata laluan baharu mesti sekurang-kurangnya 6 aksara.',
            'password.confirmed' => 'Pengesahan kata laluan baharu tidak sepadan.',
        ]);

        if (! $this->passwordMatches($user, $validated['current_password'])) {
            return back()->withErrors(['current_password' => 'Kata laluan semasa tidak sah.']);
        }

        $user->password_hash = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Kata laluan berjaya dikemaskini.');
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
        Cookie::queue(Cookie::forget($this->rememberMeCookieName()));

        return redirect()->route('login');
    }

    private function adminHomeData(Request $request): array
    {
        $notifications = Schema::hasTable('notifications')
            ? CooperativeNotification::query()
                ->where('recipient_role', 'admin')
                ->where('recipient_id', $request->session()->get('auth_id'))
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        return [
            'last_login_at' => $request->session()->get('last_login_at'),
            'announcements' => Announcement::query()->orderByDesc('is_pinned')->latest()->limit(4)->get(),
            'notifications' => $notifications,
        ];
    }

    private function studentHomeData(Request $request, Ahli $student): array
    {
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
                'name' => 'Permohonan '.ucwords(str_replace('_', ' ', (string) $application->jenis)),
                'detail' => ucfirst(str_replace('_', ' ', (string) $application->status)),
                'time' => $application->tarikh_permohonan?->diffForHumans() ?? '-',
                'sort_at' => $application->tarikh_permohonan,
                'route' => route('student.permohonan.status'),
            ]));

        if (Schema::hasTable('tempahan') && Schema::hasColumn('tempahan', 'id_ahli')) {
            DB::table('tempahan')
                ->where('id_ahli', $student->id_ahli)
                ->latest('tarikh_tempahan')
                ->limit(3)
                ->get()
                ->each(fn ($order) => $activities->push([
                    'name' => 'Tempahan Baju',
                    'detail' => ucfirst(str_replace('_', ' ', (string) ($order->status ?? 'baru'))),
                    'time' => isset($order->tarikh_tempahan) ? CarbonImmutable::parse($order->tarikh_tempahan)->diffForHumans() : '-',
                    'sort_at' => isset($order->tarikh_tempahan) ? CarbonImmutable::parse($order->tarikh_tempahan) : null,
                    'route' => route('student.tempahan.index'),
                ]));
        }

        $notifications->take(3)->each(fn (CooperativeNotification $notification) => $activities->push([
            'name' => $notification->title,
            'detail' => 'Notifikasi',
            'time' => $notification->created_at?->diffForHumans() ?? '-',
            'sort_at' => $notification->created_at,
            'route' => $notification->link ?: route('notifications.index'),
        ]));

        return [
            'last_login_at' => $request->session()->get('last_login_at'),
            'announcements' => Announcement::query()->visibleTo('ahli')->orderByDesc('is_pinned')->latest()->limit(2)->get(),
            'messages' => $notifications,
            'recent_activities' => $activities
                ->sortByDesc(fn (array $activity) => optional($activity['sort_at'] ?? null)->getTimestamp() ?? 0)
                ->take(6)
                ->map(fn (array $activity) => collect($activity)->except('sort_at')->all())
                ->values(),
            'help' => [
                'email' => 'koperasi@polibesut.edu.my',
                'route' => 'mailto:koperasi@polibesut.edu.my',
            ],
        ];
    }

    private function adminStats(): array
    {
        $studentShares = (float) DB::table('saham')->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')->value('total');
        $staffShares = Schema::hasTable('saham_staff') && Schema::hasTable('pekerja')
            ? (float) DB::table('saham_staff')
                ->join('pekerja', 'saham_staff.id_pekerja', '=', 'pekerja.id_pekerja')
                ->whereIn('pekerja.staff_type', Pekerja::SHAREHOLDER_STAFF_TYPES)
                ->selectRaw('COALESCE(SUM(saham_staff.syer + saham_staff.tambahan_saham), 0) as total')
                ->value('total')
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

    private function completeRegistrationLogin(Request $request, string $role, Model $user): RedirectResponse
    {
        $request->session()->regenerate();
        $request->session()->put('auth_role', $role);
        $request->session()->put('auth_id', $user->getKey());
        $request->session()->put('staff_type', $role === 'staff' ? $user->staff_type : null);
        $request->session()->put('last_login_at', now()->timezone('Asia/Kuala_Lumpur')->toDateTimeString());

        return redirect()
            ->route('auth.dashboard')
            ->with('status', 'Akaun berjaya didaftarkan.');
    }

    /** @return array<int, string> */
    private function studentClassOptions(): array
    {
        $classes = [];

        foreach (range(1, 6) as $semester) {
            $classes[] = 'DIT'.$semester.'A';
            $classes[] = 'DIT'.$semester.'B';
            $classes[] = 'DDC'.$semester.'A';
            $classes[] = 'DBF'.$semester.'A';
        }

        return $classes;
    }

    /** @return array{semester:string,program:string}|null */
    private function academicFromClass(?string $kelas): ?array
    {
        if (! $kelas || ! preg_match('/^(DIT|DDC|DBF)([1-6])[A-Z]$/', strtoupper(trim($kelas)), $matches)) {
            return null;
        }

        return [
            'semester' => 'Sem '.$matches[2],
            'program' => $matches[1] === 'DIT' ? 'JTMK' : 'JRKV',
        ];
    }

    private function nextStaffNumber(): string
    {
        $next = ((int) Pekerja::query()
            ->where('no_pekerja', 'like', 'PBT-%')
            ->pluck('no_pekerja')
            ->map(fn ($value): int => (int) preg_replace('/\D+/', '', (string) $value))
            ->max()) + 1;

        return 'PBT-'.$next;
    }

    private function staffDashboardRoute(?string $staffType): string
    {
        return match ($staffType) {
            'lecturer_member' => 'lecturer-member.dashboard',
            'clothing_staff' => 'clothing-staff.dashboard',
            default => 'coop-staff.dashboard',
        };
    }

    public static function rememberedUserFromCookie(Request $request): ?array
    {
        $payload = $request->cookie(self::rememberMeCookieNameStatic());

        if (! is_string($payload) || $payload === '') {
            return null;
        }

        $payload = json_decode($payload, true);

        if (! is_array($payload)) {
            return null;
        }

        $role = $payload['role'] ?? null;
        $id = isset($payload['id']) ? (int) $payload['id'] : null;
        $staffType = $payload['staff_type'] ?? null;

        if (! in_array($role, ['ahli', 'staff', 'admin'], true) || ! $id) {
            return null;
        }

        $user = match ($role) {
            'ahli' => Ahli::query()->whereKey($id)->first(),
            'staff' => Pekerja::query()->whereKey($id)->where('status_aktif', true)->first(),
            'admin' => AdminUser::query()->whereKey($id)->where('status_aktif', true)->first(),
            default => null,
        };

        if (! $user) {
            return null;
        }

        return [
            'role' => $role,
            'id' => $user->getKey(),
            'staff_type' => $role === 'staff' ? ($user->staff_type ?? $staffType) : null,
        ];
    }

    private function rememberMeCookie(string $role, Model $user)
    {
        $payload = json_encode([
            'role' => $role,
            'id' => $user->getKey(),
            'staff_type' => $role === 'staff' ? $user->staff_type : null,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return cookie(
            $this->rememberMeCookieName(),
            $payload ?: '',
            60 * 24 * 30,
            config('session.path', '/'),
            config('session.domain'),
            (bool) config('session.secure'),
            true,
            false,
            config('session.same_site', 'lax')
        );
    }

    private function rememberMeCookieName(): string
    {
        return self::rememberMeCookieNameStatic();
    }

    private static function rememberMeCookieNameStatic(): string
    {
        return 'coopbest_remember';
    }
}
