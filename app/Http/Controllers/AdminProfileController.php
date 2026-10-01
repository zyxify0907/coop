<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Ahli;
use App\Models\AhliImport;
use App\Models\AuditLog;
use App\Models\Pekerja;
use App\Models\Permohonan;
use App\Models\Saham;
use App\Models\SahamStaff;
use App\Services\AhliImportService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    private const SYSTEM_ADMIN_TYPE = 'system_admin';

    public function profile(Request $request): View|RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('login');
        }

        $role = 'admin';
        $user = AdminUser::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        $staffProfile = filled($user->nric)
            ? Pekerja::query()->with('sahamStaff')->where('nric', $user->nric)->first()
            : null;

        $recentActivities = Schema::hasTable('audit_logs')
            ? AuditLog::query()
                ->where('actor_role', 'admin')
                ->where('actor_id', $user->getKey())
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        $loginEntries = collect([
            [
                'title' => 'Log masuk semasa',
                'detail' => 'Sesi admin aktif',
                'time' => $request->session()->get('last_login_at') ?: '-',
                'ip' => $request->ip(),
            ],
        ]);

        return view('admin.profile', compact('role', 'user', 'staffProfile', 'recentActivities', 'loginEntries'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('login');
        }

        $user = AdminUser::query()->find($request->session()->get('auth_id'));

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

    public function index(Request $request): RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('login');
        }

        return redirect()->route('admin.users.students');
    }

    public function students(Request $request): View|RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('login');
        }

        $role = 'admin';
        $user = AdminUser::query()->find($request->session()->get('auth_id'));
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'program' => ['nullable', Rule::in($this->studentProgramOptions())],
            'kelas' => ['nullable', Rule::in($this->studentClassOptions())],
            'semester' => ['nullable', Rule::in(['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'])],
            'tarikh_masuk' => ['nullable', 'date'],
        ]);

        $studentsQuery = Ahli::query()->orderBy('nama');
        $search = trim((string) ($validated['search'] ?? ''));
        $program = $validated['program'] ?? '';
        $kelas = $validated['kelas'] ?? '';
        $semester = $validated['semester'] ?? '';
        $tarikhMasuk = $validated['tarikh_masuk'] ?? '';

        if ($search !== '') {
            $studentsQuery->where(function ($query) use ($search) {
                $dateSearch = $this->normalizeSearchDate($search);

                $query
                    ->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('no_matrik', 'like', '%'.$search.'%')
                    ->orWhere('nric', 'like', '%'.$search.'%')
                    ->orWhere('kelas', 'like', '%'.$search.'%')
                    ->orWhere('program', 'like', '%'.$search.'%')
                    ->orWhere('semester', 'like', '%'.$search.'%')
                    ->orWhereRaw("DATE_FORMAT(tarikh_daftar, '%d/%m/%Y') like ?", ['%'.$search.'%'])
                    ->orWhereRaw("DATE_FORMAT(tarikh_daftar, '%d-%b-%y') like ?", ['%'.$search.'%'])
                    ->orWhereRaw("DATE_FORMAT(tarikh_daftar, '%Y-%m-%d') like ?", ['%'.$search.'%']);

                if ($dateSearch !== null) {
                    $query->orWhereDate('tarikh_daftar', $dateSearch);
                }
            });
        }

        $studentsQuery
            ->when($program !== '', fn ($query) => $query->where('program', $program))
            ->when($kelas !== '', fn ($query) => $query->where('kelas', $kelas))
            ->when($semester !== '', fn ($query) => $query->where('semester', $semester))
            ->when($tarikhMasuk !== '', fn ($query) => $query->whereDate('tarikh_daftar', $tarikhMasuk));

        return view('admin.admin_users.students', [
            'role' => $role,
            'user' => $user,
            'students' => $studentsQuery->paginate(30)->withQueryString(),
            'search' => $search,
            'filters' => [
                'program' => $program,
                'kelas' => $kelas,
                'semester' => $semester,
                'tarikh_masuk' => $tarikhMasuk,
            ],
            'programOptions' => $this->studentProgramOptions(),
            'classOptions' => $this->studentClassOptions(),
            'semesterOptions' => ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'],
            'latestImport' => AhliImport::query()->latest()->first(),
        ]);
    }

    public function staff(Request $request): View|RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('login');
        }

        $role = 'admin';
        $user = AdminUser::query()->find($request->session()->get('auth_id'));
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'staff_type' => ['nullable', Rule::in([
                self::SYSTEM_ADMIN_TYPE,
                'lecturer_member',
                'clothing_staff',
                Pekerja::SHARE_MANAGER_STAFF_TYPE,
                Pekerja::COOP_MANAGER_STAFF_TYPE,
            ])],
        ]);

        $staffQuery = Pekerja::query()
            ->whereIn('staff_type', array_unique([
                ...Pekerja::SHAREHOLDER_STAFF_TYPES,
                Pekerja::SHARE_MANAGER_STAFF_TYPE,
                Pekerja::COOP_MANAGER_STAFF_TYPE,
            ]))
            ->orderBy('nama');
        $search = trim((string) ($validated['search'] ?? ''));
        $staffType = $validated['staff_type'] ?? '';

        if ($search !== '') {
            $staffQuery->where(function ($query) use ($search) {
                $query
                    ->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('nric', 'like', '%'.$search.'%');
            });
        }

        $activeAdminNrics = AdminUser::query()
            ->where('status_aktif', true)
            ->whereNotNull('nric')
            ->pluck('nric')
            ->all();

        $staffQuery
            ->when($staffType !== '' && $staffType !== self::SYSTEM_ADMIN_TYPE, fn ($query) => $query->where('staff_type', $staffType))
            ->when($staffType === self::SYSTEM_ADMIN_TYPE, fn ($query) => $query->whereIn('nric', $activeAdminNrics ?: ['__none__']));

        return view('admin.admin_users.staff', [
            'role' => $role,
            'user' => $user,
            'staff' => $staffQuery->paginate(30)->withQueryString(),
            'search' => $search,
            'filters' => [
                'staff_type' => $staffType,
            ],
            'staffTypeOptions' => [
                self::SYSTEM_ADMIN_TYPE => 'Admin Pengurusan Sistem',
                'lecturer_member' => 'Pensyarah / Staf Akademik',
                'clothing_staff' => 'Staff Pengurus Baju',
                Pekerja::SHARE_MANAGER_STAFF_TYPE => 'Staff Pengurus Saham',
                Pekerja::COOP_MANAGER_STAFF_TYPE => 'Staff Pengurus Pekerja Koperasi',
            ],
            'activeAdminNrics' => $activeAdminNrics,
            'listTitle' => 'Senarai Staff',
            'listSubtitle' => 'Paparan staff ahli koperasi dan Support Admin.',
            'heroTitle' => 'Urus Staf',
            'heroSubtitle' => 'Kemaskini maklumat staf ahli koperasi dan pentadbir sokongan.',
            'recordLabel' => 'rekod staff',
            'addButtonLabel' => 'Tambah Staff',
            'emptyTitle' => 'Tiada rekod staff.',
            'emptySubtitle' => 'Pekerja koperasi dipaparkan dalam senarai berasingan.',
            'searchLabel' => 'Cari Staff',
            'searchPlaceholder' => 'Nama atau No. KP',
            'listRoute' => route('admin.users.staff'),
            'addButtonRoute' => route('admin.users.create', ['type' => 'staff']),
            'importRoute' => route('admin.users.staff.import'),
            'showWorkerFields' => false,
        ]);
    }

    public function coopWorkers(Request $request): View|RedirectResponse
    {
        if (! $this->canManageCoopWorkers($request)) {
            return redirect()->route('login');
        }

        $role = (string) $request->session()->get('auth_role');
        $user = $this->currentBackOfficeUser($request);
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $staffQuery = Pekerja::query()->coopWorkers()->orderBy('nama');
        $search = trim((string) ($validated['search'] ?? ''));

        if ($search !== '') {
            $staffQuery->where(function ($query) use ($search) {
                $query
                    ->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('nric', 'like', '%'.$search.'%');
            });
        }

        return view('admin.admin_users.staff', [
            'role' => $role,
            'user' => $user,
            'staff' => $staffQuery->paginate(30)->withQueryString(),
            'search' => $search,
            'listTitle' => 'Senarai Pekerja Koperasi',
            'listSubtitle' => 'Paparan pekerja koperasi sahaja, tanpa rekod saham.',
            'heroTitle' => 'Manage Pekerja Koperasi',
            'heroSubtitle' => 'Kemaskini akaun pekerja koperasi yang tidak mempunyai saham.',
            'recordLabel' => 'rekod pekerja koperasi',
            'addButtonLabel' => 'Tambah Pekerja',
            'emptyTitle' => 'Tiada rekod pekerja koperasi.',
            'emptySubtitle' => 'Klik Tambah Pekerja untuk daftar pekerja koperasi baru.',
            'searchLabel' => 'Cari Pekerja Koperasi',
            'searchPlaceholder' => 'Nama atau No. KP',
            'listRoute' => route('admin.users.coop-workers'),
            'addButtonRoute' => route('admin.users.create', ['type' => 'staff', 'staff_type' => Pekerja::COOP_WORKER_STAFF_TYPE]),
            'showWorkerFields' => true,
        ]);
    }

    public function create(Request $request, string $type): View|RedirectResponse
    {
        $restrictToCoopWorkers = $this->isCoopManager($request);

        if (! $this->isAdmin($request) && (! $restrictToCoopWorkers || $type !== 'staff' || $request->query('staff_type') !== Pekerja::COOP_WORKER_STAFF_TYPE)) {
            return redirect()->route('login');
        }

        abort_unless(in_array($type, ['student', 'staff'], true), 404);

        $role = (string) $request->session()->get('auth_role');
        $user = $this->currentBackOfficeUser($request);

        return view('admin.admin_users.create', [
            'type' => $type,
            'role' => $role,
            'user' => $user,
            'restrictToCoopWorkers' => $restrictToCoopWorkers,
        ]);
    }

    public function storeStudent(Request $request): RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('login');
        }

        $this->normalizeNricInput($request);

        $validated = $request->validate([
            'no_matrik' => ['required', 'string', 'max:20', 'unique:ahli,no_matrik'],
            'nama' => ['required', 'string', 'max:100'],
            'nric' => ['required', 'string', 'max:20', 'unique:ahli,nric'],
            'semester' => ['nullable', Rule::in(['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'])],
            'program' => ['nullable', Rule::in($this->studentProgramOptions())],
            'kelas' => ['nullable', Rule::in($this->studentClassOptions())],
            'no_tel' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100', 'unique:ahli,email'],
            'tarikh_daftar' => ['nullable', 'date'],
        ]);

        $generatedPassword = $this->generateStudentPassword($validated['no_matrik']);
        $academic = $this->academicFromClass($validated['kelas'] ?? null);

        try {
            Ahli::query()->create([
                'no_matrik' => $validated['no_matrik'],
                'nama' => $validated['nama'],
                'nric' => $validated['nric'],
                'semester' => $academic['semester'] ?? ($validated['semester'] ?? null),
                'program' => $academic['program'] ?? ($validated['program'] ?? null),
                'kelas' => $validated['kelas'] ?? null,
                'no_tel' => $validated['no_tel'] ?? null,
                'email' => $validated['email'] ?? null,
                'password_hash' => Hash::make($generatedPassword),
                'tarikh_daftar' => $validated['tarikh_daftar'] ?? null,
                'status_aktif' => true,
            ]);
        } catch (UniqueConstraintViolationException $exception) {
            return $this->backWithDuplicateAhliError($request, $exception);
        }

        return redirect()
            ->route('admin.users.students')
            ->with('status', 'Student berjaya ditambah. Password dijana automatik: '.$generatedPassword);
    }

    public function importStudents(Request $request, AhliImportService $importer): RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240'],
        ]);

        $result = $importer->importStudents($validated['file']);

        return redirect()
            ->route('admin.users.students')
            ->with('status', "Import selesai. Berjaya: {$result->importedCount} rekod. Gagal: {$result->failedCount} rekod. Password student dijana automatik: No Matrik + @123.");
    }

    public function importStaff(Request $request, AhliImportService $importer): RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240'],
        ]);

        $result = $importer->importStaff($validated['file']);

        return redirect()
            ->route('admin.users.staff')
            ->with('status', "Import staff selesai. Berjaya: {$result->importedCount} rekod. Gagal: {$result->failedCount} rekod. Password staff default: staff12345.");
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $requestedStaffType = (string) $request->input('staff_type');

        if (! $this->isAdmin($request) && (! $this->isCoopManager($request) || $requestedStaffType !== Pekerja::COOP_WORKER_STAFF_TYPE)) {
            return redirect()->route('login');
        }

        $this->normalizeNricInput($request);

        $validated = $request->validate([
            'no_pekerja' => [Rule::requiredIf($requestedStaffType === Pekerja::COOP_WORKER_STAFF_TYPE), 'nullable', 'string', 'max:20', 'regex:/^PBT-\d+$/', 'unique:pekerja,no_pekerja'],
            'nama' => ['required', 'string', 'max:100'],
            'nric' => ['required', 'string', 'max:20', 'unique:pekerja,nric'],
            'staff_type' => ['required', Rule::in(Pekerja::STAFF_TYPES)],
            'no_tel' => ['nullable', 'string', 'max:15'],
            'email' => ['nullable', 'email', 'max:100', 'unique:pekerja,email'],
            'kadar_elaun' => ['nullable', 'numeric', 'min:0'],
            'tarikh_mula' => ['nullable', 'date'],
            'status_aktif' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $staffNumber = $validated['staff_type'] === Pekerja::COOP_WORKER_STAFF_TYPE
            ? strtoupper(trim((string) $validated['no_pekerja']))
            : $this->nextStaffNumber();

        $staff = Pekerja::query()->create([
            'no_pekerja' => $staffNumber,
            'nama' => $validated['nama'],
            'nric' => $validated['nric'],
            'staff_type' => $validated['staff_type'],
            'no_tel' => $validated['no_tel'] ?? null,
            'email' => $validated['email'] ?? null,
            'kadar_elaun' => $validated['kadar_elaun'] ?? 0,
            'tarikh_mula' => $validated['tarikh_mula'] ?? null,
            'password_hash' => Hash::make($validated['password'] ?? 'staff12345'),
            'status_aktif' => (bool) ($validated['status_aktif'] ?? true),
        ]);

        if ($validated['staff_type'] === Pekerja::COOP_WORKER_STAFF_TYPE) {
            SahamStaff::query()->where('id_pekerja', $staff->id_pekerja)->delete();
        }

        return redirect()
            ->route($validated['staff_type'] === Pekerja::COOP_WORKER_STAFF_TYPE ? 'admin.users.coop-workers' : 'admin.users.staff')
            ->with('status', 'Staff berjaya ditambah. Password default: staff12345.');
    }

    public function edit(Request $request, string $type, int $id): View|RedirectResponse
    {
        if (! $this->isAdmin($request) && ! $this->isCoopManager($request)) {
            return redirect()->route('login');
        }

        $user = $this->findUser($type, $id);

        abort_if(! $user, 404);

        if ($this->isCoopManager($request) && ($type !== 'staff' || $user->staff_type !== Pekerja::COOP_WORKER_STAFF_TYPE)) {
            abort(403);
        }

        $role = (string) $request->session()->get('auth_role');
        $adminUser = $this->currentBackOfficeUser($request);

        return view('admin.admin_users.edit', [
            'type' => $type,
            'profile' => $user,
            'role' => $role,
            'user' => $adminUser,
            'systemAdminForProfile' => $type === 'staff'
                && filled($user->nric)
                && AdminUser::query()->where('nric', $user->nric)->where('status_aktif', true)->exists(),
            'restrictToCoopWorkers' => $this->isCoopManager($request),
        ]);
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        if (! $this->isAdmin($request) && ! $this->isCoopManager($request)) {
            return redirect()->route('login');
        }

        $user = $this->findUser($type, $id);

        abort_if(! $user, 404);

        if ($this->isCoopManager($request) && ($type !== 'staff' || $user->staff_type !== Pekerja::COOP_WORKER_STAFF_TYPE || $request->input('staff_type') !== Pekerja::COOP_WORKER_STAFF_TYPE)) {
            abort(403);
        }

        $this->normalizeNricInput($request);

        $rules = [
            'nama' => ['required', 'string', 'max:100'],
            'nric' => ['nullable', 'string', 'max:20'],
            'no_tel' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'password' => ['nullable', 'string', 'min:6'],
        ];

        if ($type === 'student') {
            $rules['nric'][] = Rule::unique('ahli', 'nric')->ignore($id, 'id_ahli');
            $rules['email'][] = Rule::unique('ahli', 'email')->ignore($id, 'id_ahli');
            $rules['semester'] = ['nullable', Rule::in(['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'])];
            $rules['program'] = ['nullable', Rule::in($this->studentProgramOptions())];
            $rules['kelas'] = ['nullable', Rule::in($this->studentClassOptions())];
            $rules['tarikh_daftar'] = ['nullable', 'date'];
        } else {
            $rules['nric'][] = Rule::unique('pekerja', 'nric')->ignore($id, 'id_pekerja');
            $rules['email'][] = Rule::unique('pekerja', 'email')->ignore($id, 'id_pekerja');
            $requestedStaffType = (string) $request->input('staff_type', $user->staff_type);
            $rules['no_pekerja'] = [Rule::requiredIf($requestedStaffType === Pekerja::COOP_WORKER_STAFF_TYPE), 'nullable', 'string', 'max:20', 'regex:/^PBT-\d+$/', Rule::unique('pekerja', 'no_pekerja')->ignore($id, 'id_pekerja')];
            $rules['staff_type'] = ['required', Rule::in([...Pekerja::STAFF_TYPES, self::SYSTEM_ADMIN_TYPE])];
            $rules['kadar_elaun'] = ['nullable', 'numeric', 'min:0'];
            $rules['tarikh_mula'] = ['nullable', 'date'];
            $rules['status_aktif'] = ['nullable', 'boolean'];
        }

        $validated = $request->validate($rules);

        if ($type === 'student') {
            $academic = $this->academicFromClass($validated['kelas'] ?? null);

            if ($academic !== null) {
                $validated['semester'] = $academic['semester'];
                $validated['program'] = $academic['program'];
            }
        }

        if ($type === 'staff' && ($validated['staff_type'] ?? null) === self::SYSTEM_ADMIN_TYPE) {
            unset($validated['staff_type'], $validated['no_pekerja'], $validated['kadar_elaun']);
        }

        if ($type === 'staff' && ($validated['staff_type'] ?? null) !== Pekerja::COOP_WORKER_STAFF_TYPE) {
            unset($validated['no_pekerja'], $validated['kadar_elaun']);
        }

        $user->fill(Arr::except($validated, ['password']));

        if (! empty($validated['password'])) {
            $user->password_hash = Hash::make($validated['password']);
        }

        $user->save();

        if ($type === 'staff' && (string) $request->input('staff_type') === self::SYSTEM_ADMIN_TYPE) {
            $admin = $this->createOrUpdateAdminFromStaff($user);

            return redirect()
                ->route('admin.users.edit', ['type' => 'staff', 'id' => $user->id_pekerja])
                ->with('status', 'Staff berjaya dijadikan Admin Pengurusan Sistem. Username admin: '.$admin->username);
        }

        if ($type === 'staff' && filled($user->nric)) {
            AdminUser::query()
                ->where('nric', $user->nric)
                ->where('id_admin', '!=', $request->session()->get('auth_id'))
                ->update(['status_aktif' => false]);
        }

        if ($type === 'staff' && $user->staff_type === Pekerja::COOP_WORKER_STAFF_TYPE) {
            SahamStaff::query()->where('id_pekerja', $user->id_pekerja)->delete();
        }

        $redirectRoute = match (true) {
            $type === 'student' => 'admin.users.students',
            $user->staff_type === Pekerja::COOP_WORKER_STAFF_TYPE => 'admin.users.coop-workers',
            default => 'admin.users.staff',
        };

        return redirect()
            ->route($redirectRoute)
            ->with('status', 'Profil berjaya dikemaskini.');
    }

    public function promoteStaffToAdmin(Request $request, int $id): RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('login');
        }

        $staff = Pekerja::query()->find($id);

        abort_if(! $staff, 404);

        $admin = $this->createOrUpdateAdminFromStaff($staff);

        return redirect()
            ->route('admin.users.edit', ['type' => 'staff', 'id' => $staff->id_pekerja])
            ->with('status', 'Staff berjaya dijadikan admin. Username admin: '.$admin->username);
    }

    private function createOrUpdateAdminFromStaff(Pekerja $staff): AdminUser
    {
        $admin = filled($staff->nric)
            ? AdminUser::query()->where('nric', $staff->nric)->first()
            : null;

        if (! $admin) {
            $baseUsername = str($staff->email ?: $staff->no_pekerja ?: $staff->nama)
                ->before('@')
                ->lower()
                ->replaceMatches('/[^a-z0-9._-]+/', '')
                ->trim('._-')
                ->value() ?: 'admin';
            $username = $baseUsername;
            $suffix = 1;

            while (AdminUser::query()->where('username', $username)->exists()) {
                $username = $baseUsername.$suffix++;
            }

            $admin = new AdminUser([
                'username' => $username,
            ]);
        }

        $admin->fill([
            'nama' => $staff->nama,
            'nric' => $staff->nric,
            'password_hash' => $staff->password_hash,
            'peranan' => 'Admin',
            'status_aktif' => true,
        ]);
        $admin->save();

        return $admin;
    }

    public function destroy(Request $request, string $type, int $id): JsonResponse|RedirectResponse
    {
        if (! $this->isAdmin($request) && ! $this->isCoopManager($request)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi tamat. Sila log masuk semula.'], 401);
            }

            return redirect()->route('login');
        }

        $user = $this->findUser($type, $id);

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Rekod tidak dijumpai.'], 404);
            }

            abort(404);
        }

        if ($this->isCoopManager($request) && ($type !== 'staff' || $user->staff_type !== Pekerja::COOP_WORKER_STAFF_TYPE)) {
            abort(403);
        }

        $redirectRoute = $type === 'student'
            ? 'admin.users.students'
            : ($user->staff_type === Pekerja::COOP_WORKER_STAFF_TYPE ? 'admin.users.coop-workers' : 'admin.users.staff');

        try {
            DB::transaction(function () use ($type, $user): void {
                if ($type === 'student') {
                    Permohonan::query()->where('id_ahli', $user->getKey())->delete();
                    Saham::query()->where('id_ahli', $user->getKey())->delete();
                } else {
                    Permohonan::query()
                        ->where('jenis', 'anggota')
                        ->where('no_matrik', $user->no_pekerja)
                        ->where('data_permohonan->pemohon_role', 'staff')
                        ->delete();

                    SahamStaff::query()->where('id_pekerja', $user->getKey())->delete();
                }

                $user->delete();
            });
        } catch (QueryException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Rekod tidak boleh dipadam kerana masih mempunyai data berkaitan.'], 422);
            }

            return redirect()
                ->route($redirectRoute)
                ->withErrors(['delete' => 'Rekod tidak boleh dipadam kerana masih mempunyai data berkaitan.']);
        }

        $message = $type === 'student' ? 'Student berjaya dipadam.' : 'Staff berjaya dipadam.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()
            ->route($redirectRoute)
            ->with('status', $message);
    }

    private function isAdmin(Request $request): bool
    {
        return $request->session()->get('auth_role') === 'admin';
    }

    private function isCoopManager(Request $request): bool
    {
        if ($request->session()->get('auth_role') !== 'staff') {
            return false;
        }

        return Pekerja::query()
            ->whereKey($request->session()->get('auth_id'))
            ->where('staff_type', Pekerja::COOP_MANAGER_STAFF_TYPE)
            ->where('status_aktif', true)
            ->exists();
    }

    private function canManageCoopWorkers(Request $request): bool
    {
        return $this->isAdmin($request) || $this->isCoopManager($request);
    }

    private function currentBackOfficeUser(Request $request): AdminUser|Pekerja|null
    {
        return $this->isAdmin($request)
            ? AdminUser::query()->find($request->session()->get('auth_id'))
            : Pekerja::query()->find($request->session()->get('auth_id'));
    }

    private function findUser(string $type, int $id): Ahli|Pekerja|null
    {
        return match ($type) {
            'student' => Ahli::query()->find($id),
            'staff' => Pekerja::query()->find($id),
            default => null,
        };
    }

    private function normalizeNricInput(Request $request): void
    {
        if (! $request->has('nric')) {
            return;
        }

        $request->merge([
            'nric' => $this->normalizeNric($request->input('nric')),
        ]);
    }

    private function normalizeNric(mixed $nric): ?string
    {
        $nric = trim((string) $nric);

        if ($nric === '') {
            return null;
        }

        return preg_replace('/\D+/', '', $nric) ?: $nric;
    }

    private function backWithDuplicateAhliError(Request $request, UniqueConstraintViolationException $exception): RedirectResponse
    {
        $message = $exception->getMessage();
        $field = match (true) {
            str_contains($message, 'ahli.nric') || str_contains($message, "'nric'") => 'nric',
            str_contains($message, 'ahli.no_matrik') || str_contains($message, "'no_matrik'") => 'no_matrik',
            str_contains($message, 'ahli.email') || str_contains($message, "'email'") => 'email',
            default => 'nric',
        };

        $messages = [
            'nric' => 'No. KP ini sudah digunakan oleh ahli lain.',
            'no_matrik' => 'No. matrik ini sudah digunakan oleh ahli lain.',
            'email' => 'Email ini sudah digunakan oleh ahli lain.',
        ];

        return back()
            ->withErrors([$field => $messages[$field]])
            ->withInput($request->except('password'));
    }

    private function passwordMatches(AdminUser $user, string $password): bool
    {
        $storedHash = (string) $user->password_hash;

        if ($storedHash === '') {
            return false;
        }

        if (str_starts_with($storedHash, '$2a$')) {
            return password_verify($password, '$2y$'.substr($storedHash, 4));
        }

        return Hash::check($password, $storedHash);
    }

    /**
     * @return array<int, string>
     */
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

    /**
     * @return array<int, string>
     */
    private function studentProgramOptions(): array
    {
        return ['JTMK', 'JRKV'];
    }

    /**
     * @return array{semester: string, program: string}|null
     */
    private function academicFromClass(?string $kelas): ?array
    {
        if (! preg_match('/^(DIT|DDC|DBF)([1-6])[A-Z]$/', strtoupper(trim((string) $kelas)), $matches)) {
            return null;
        }

        return [
            'semester' => 'Sem '.$matches[2],
            'program' => $matches[1] === 'DIT' ? 'JTMK' : 'JRKV',
        ];
    }

    private function generateStudentPassword(string $noMatrik): string
    {
        return strtoupper(trim($noMatrik)).'@123';
    }

    private function normalizeSearchDate(string $search): ?string
    {
        foreach (['d/m/Y', 'd/m/y', 'Y-m-d', 'd-M-y', 'd-M-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim($search))->toDateString();
            } catch (\Throwable) {
                //
            }
        }

        return null;
    }

    private function nextStaffNumber(): string
    {
        $numbers = Pekerja::query()
            ->where('no_pekerja', 'like', 'PBT-%')
            ->pluck('no_pekerja')
            ->map(fn ($value): int => (int) preg_replace('/\D+/', '', (string) $value))
            ->filter(fn (int $number): bool => $number > 0);

        return 'PBT-'.(((int) $numbers->max()) + 1);
    }
}
