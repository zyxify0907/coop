<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Ahli;
use App\Models\AhliImport;
use App\Models\Pekerja;
use App\Services\AhliImportService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
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
            'semester' => ['nullable', Rule::in(['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5'])],
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
            'students' => $studentsQuery->get(),
            'search' => $search,
            'filters' => [
                'program' => $program,
                'kelas' => $kelas,
                'semester' => $semester,
                'tarikh_masuk' => $tarikhMasuk,
            ],
            'programOptions' => $this->studentProgramOptions(),
            'classOptions' => $this->studentClassOptions(),
            'semesterOptions' => ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5'],
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
        ]);

        $staffQuery = Pekerja::query()->orderBy('nama');
        $search = trim((string) ($validated['search'] ?? ''));

        if ($search !== '') {
            $staffQuery->where(function ($query) use ($search) {
                $query
                    ->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('no_pekerja', 'like', '%'.$search.'%')
                    ->orWhere('nric', 'like', '%'.$search.'%')
                    ->orWhere('staff_type', 'like', '%'.$search.'%');
            });
        }

        return view('admin.admin_users.staff', [
            'role' => $role,
            'user' => $user,
            'staff' => $staffQuery->get(),
            'search' => $search,
        ]);
    }

    public function create(Request $request, string $type): View|RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('login');
        }

        abort_unless(in_array($type, ['student', 'staff'], true), 404);

        $role = 'admin';
        $user = AdminUser::query()->find($request->session()->get('auth_id'));

        return view('admin.admin_users.create', [
            'type' => $type,
            'role' => $role,
            'user' => $user,
        ]);
    }

    public function storeStudent(Request $request): RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'no_matrik' => ['required', 'string', 'max:20', 'unique:ahli,no_matrik'],
            'nama' => ['required', 'string', 'max:100'],
            'nric' => ['required', 'string', 'max:20', 'unique:ahli,nric'],
            'semester' => ['nullable', Rule::in(['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5'])],
            'program' => ['nullable', Rule::in($this->studentProgramOptions())],
            'kelas' => ['nullable', Rule::in($this->studentClassOptions())],
            'no_tel' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100', 'unique:ahli,email'],
            'tarikh_daftar' => ['nullable', 'date'],
        ]);

        $generatedPassword = $this->generateStudentPassword($validated['no_matrik']);
        $academic = $this->academicFromClass($validated['kelas'] ?? null);

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

    public function storeStaff(Request $request): RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'no_pekerja' => ['required', 'string', 'max:20', 'regex:/^PBT-\d+$/', 'unique:pekerja,no_pekerja'],
            'nama' => ['required', 'string', 'max:100'],
            'nric' => ['required', 'string', 'max:20', 'unique:pekerja,nric'],
            'staff_type' => ['required', Rule::in(['lecturer_member', 'coop_staff', 'clothing_staff'])],
            'no_tel' => ['nullable', 'string', 'max:15'],
            'email' => ['nullable', 'email', 'max:100', 'unique:pekerja,email'],
            'kadar_elaun' => ['nullable', 'numeric', 'min:0'],
            'tarikh_mula' => ['nullable', 'date'],
            'status_aktif' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        Pekerja::query()->create([
            'no_pekerja' => $validated['no_pekerja'],
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

        return redirect()
            ->route('admin.users.staff')
            ->with('status', 'Staff berjaya ditambah. Password default: staff12345.');
    }

    public function edit(Request $request, string $type, int $id): View|RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('login');
        }

        $user = $this->findUser($type, $id);

        abort_if(! $user, 404);

        $role = 'admin';
        $adminUser = AdminUser::query()->find($request->session()->get('auth_id'));

        return view('admin.admin_users.edit', [
            'type' => $type,
            'profile' => $user,
            'role' => $role,
            'user' => $adminUser,
        ]);
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('login');
        }

        $user = $this->findUser($type, $id);

        abort_if(! $user, 404);

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
            $rules['semester'] = ['nullable', Rule::in(['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5'])];
            $rules['program'] = ['nullable', Rule::in($this->studentProgramOptions())];
            $rules['kelas'] = ['nullable', Rule::in($this->studentClassOptions())];
            $rules['tarikh_daftar'] = ['nullable', 'date'];
        } else {
            $rules['nric'][] = Rule::unique('pekerja', 'nric')->ignore($id, 'id_pekerja');
            $rules['email'][] = Rule::unique('pekerja', 'email')->ignore($id, 'id_pekerja');
            $rules['no_pekerja'] = ['required', 'string', 'max:20', 'regex:/^PBT-\d+$/', Rule::unique('pekerja', 'no_pekerja')->ignore($id, 'id_pekerja')];
            $rules['staff_type'] = ['required', Rule::in(['lecturer_member', 'coop_staff', 'clothing_staff'])];
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

        $user->fill(Arr::except($validated, ['password']));

        if (! empty($validated['password'])) {
            $user->password_hash = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->route($type === 'student' ? 'admin.users.students' : 'admin.users.staff')
            ->with('status', 'Profil berjaya dikemaskini.');
    }

    public function destroy(Request $request, string $type, int $id): JsonResponse|RedirectResponse
    {
        if (! $this->isAdmin($request)) {
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

        try {
            $user->delete();
        } catch (QueryException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Rekod tidak boleh dipadam kerana masih mempunyai data berkaitan.'], 422);
            }

            return redirect()
                ->route($type === 'student' ? 'admin.users.students' : 'admin.users.staff')
                ->withErrors(['delete' => 'Rekod tidak boleh dipadam kerana masih mempunyai data berkaitan.']);
        }

        $message = $type === 'student' ? 'Student berjaya dipadam.' : 'Staff berjaya dipadam.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()
            ->route($type === 'student' ? 'admin.users.students' : 'admin.users.staff')
            ->with('status', $message);
    }

    private function isAdmin(Request $request): bool
    {
        return $request->session()->get('auth_role') === 'admin';
    }

    private function findUser(string $type, int $id): Ahli|Pekerja|null
    {
        return match ($type) {
            'student' => Ahli::query()->find($id),
            'staff' => Pekerja::query()->find($id),
            default => null,
        };
    }

    /**
     * @return array<int, string>
     */
    private function studentClassOptions(): array
    {
        $classes = [];

        foreach (range(1, 5) as $semester) {
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
        if (! preg_match('/^(DIT|DDC|DBF)([1-5])[A-Z]$/', strtoupper(trim((string) $kelas)), $matches)) {
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
}
