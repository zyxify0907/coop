<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Ahli;
use App\Models\AhliImport;
use App\Models\Pekerja;
use App\Models\Permohonan;
use App\Models\Saham;
use App\Models\SahamStaff;
use App\Models\ShareTransaction;
use App\Notifications\AhliImportCompleted;
use App\Services\AhliImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AhliController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->session()->get('auth_role');
        $user = match ($role) {
            'admin' => AdminUser::query()->find($request->session()->get('auth_id')),
            'staff' => Pekerja::query()->find($request->session()->get('auth_id')),
            default => null,
        };

        return view('admin.ahli.index', [
            'role' => $role,
            'user' => $user,
            'ahli' => Ahli::query()->with('saham')->orderByDesc('id_ahli')->paginate(20),
            'latestImport' => AhliImport::query()->latest()->first(),
        ]);
    }

    public function import(Request $request, AhliImportService $importer): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $validated['file'];
        $result = $importer->import($file);

        $adminEmail = config('mail.from.address');

        if ($adminEmail) {
            Notification::route('mail', $adminEmail)->notify(new AhliImportCompleted($result));
        }

        return redirect()
            ->route('admin.ahli.index')
            ->with('status', "Berjaya mengimport {$result->importedCount} rekod. Gagal: {$result->failedCount} rekod.");
    }

    public function anggotaIndex(Request $request): View|RedirectResponse
    {
        return redirect()->route('admin.anggota.students');
    }

    public function anggotaStudents(Request $request): View|RedirectResponse
    {
        return $this->renderAnggotaList($request, 'student');
    }

    public function anggotaStaff(Request $request): View|RedirectResponse
    {
        return $this->renderAnggotaList($request, 'staff');
    }

    private function renderAnggotaList(Request $request, string $memberType): View|RedirectResponse
    {
        $role = $request->session()->get('auth_role');

        if ($role !== 'admin') {
            return redirect()->route('login');
        }

        $user = AdminUser::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));

        $membersQuery = Permohonan::query()
            ->where('jenis', 'anggota')
            ->where('status', 'diluluskan');

        if ($memberType === 'staff') {
            $matchingStaffNumbers = collect();

            if ($search !== '') {
                $staffHasMemberNumber = Schema::hasColumn('pekerja', 'no_anggota');
                $matchingStaffNumbers = Pekerja::query()
                    ->where(function ($staffQuery) use ($search, $staffHasMemberNumber): void {
                        if ($staffHasMemberNumber) {
                            $staffQuery->where('no_anggota', 'like', "%{$search}%");
                        }

                        $staffQuery
                            ->orWhere('no_pekerja', 'like', "%{$search}%")
                            ->orWhere('nama', 'like', "%{$search}%")
                            ->orWhere('nric', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('staff_type', 'like', "%{$search}%");
                    })
                    ->pluck('no_pekerja');
            }

            $membersQuery
                ->where('data_permohonan->pemohon_role', 'staff')
                ->when($search !== '', function ($query) use ($search, $matchingStaffNumbers): void {
                    $query->where(function ($searchQuery) use ($search, $matchingStaffNumbers): void {
                        $searchQuery
                            ->where('nama_pemohon', 'like', "%{$search}%")
                            ->orWhere('no_matrik', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('data_permohonan->no_kad_pengenalan', 'like', "%{$search}%");

                        if ($matchingStaffNumbers->isNotEmpty()) {
                            $searchQuery->orWhereIn('no_matrik', $matchingStaffNumbers);
                        }
                    });
                });
        } else {
            $membersQuery
                ->with(['ahli.saham'])
                ->where(function ($query): void {
                    $query
                        ->whereNull('data_permohonan->pemohon_role')
                        ->orWhere('data_permohonan->pemohon_role', '!=', 'staff');
                })
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($searchQuery) use ($search): void {
                        $searchQuery
                            ->where('nama_pemohon', 'like', "%{$search}%")
                            ->orWhere('no_matrik', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhereHas('ahli', function ($memberQuery) use ($search): void {
                                $memberQuery
                                    ->where('no_anggota', 'like', "%{$search}%")
                                    ->orWhere('nric', 'like', "%{$search}%")
                                    ->orWhere('program', 'like', "%{$search}%");
                            });
                    });
                });
        }

        $members = $membersQuery
            ->latest('tarikh_keputusan')
            ->latest('id_permohonan')
            ->paginate(15)
            ->withQueryString();

        return view('admin.anggota.index', [
            'role' => $role,
            'user' => $user,
            'memberType' => $memberType,
            'members' => $members,
            'staffMembers' => Pekerja::query()
                ->with('sahamStaff')
                ->whereIn('no_pekerja', $memberType === 'staff' ? $members->getCollection()->pluck('no_matrik')->filter()->values() : [])
                ->get()
                ->keyBy('no_pekerja'),
            'filters' => [
                'search' => $search,
            ],
            'latestApproved' => Permohonan::query()
                ->where('jenis', 'anggota')
                ->where('status', 'diluluskan')
                ->when($memberType === 'staff', fn ($query) => $query->where('data_permohonan->pemohon_role', 'staff'))
                ->when($memberType !== 'staff', function ($query): void {
                    $query->where(function ($roleQuery): void {
                        $roleQuery
                            ->whereNull('data_permohonan->pemohon_role')
                            ->orWhere('data_permohonan->pemohon_role', '!=', 'staff');
                    });
                })
                ->latest('tarikh_keputusan')
                ->value('tarikh_keputusan'),
        ]);
    }

    public function anggotaShow(Request $request, Permohonan $permohonan): View|RedirectResponse
    {
        $role = $request->session()->get('auth_role');

        if ($role !== 'admin') {
            return redirect()->route('login');
        }

        $user = AdminUser::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        abort_unless($permohonan->jenis === 'anggota' && $permohonan->status === 'diluluskan', 404);

        $application = $permohonan->load('ahli.saham');
        $staffMember = ($application->data_permohonan['pemohon_role'] ?? null) === 'staff'
            ? Pekerja::query()->with('sahamStaff')->where('no_pekerja', $application->no_matrik)->first()
            : null;

        return view('admin.anggota.show', [
            'role' => $role,
            'user' => $user,
            'memberApplication' => $application,
            'staffMember' => $staffMember,
        ]);
    }

    public function anggotaDestroy(Request $request, Permohonan $permohonan): RedirectResponse
    {
        $role = $request->session()->get('auth_role');

        if ($role !== 'admin') {
            return redirect()->route('login');
        }

        $user = AdminUser::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        abort_unless($permohonan->jenis === 'anggota' && $permohonan->status === 'diluluskan', 404);

        $isStaffMember = ($permohonan->data_permohonan['pemohon_role'] ?? null) === 'staff';
        $redirectRoute = $isStaffMember ? 'admin.anggota.staff' : 'admin.anggota.students';

        DB::transaction(function () use ($permohonan, $isStaffMember): void {
            if ($isStaffMember) {
                $staffNumber = $permohonan->data_permohonan['no_pekerja'] ?? $permohonan->no_matrik;
                $staff = Pekerja::query()->where('no_pekerja', $staffNumber)->first();

                if ($staff) {
                    SahamStaff::query()->where('id_pekerja', $staff->id_pekerja)->delete();

                    if (Schema::hasColumn('pekerja', 'no_anggota')) {
                        $staff->update(['no_anggota' => null]);
                    }
                }
            } elseif ($permohonan->id_ahli) {
                Saham::query()->where('id_ahli', $permohonan->id_ahli)->delete();
                Ahli::query()->where('id_ahli', $permohonan->id_ahli)->update(['no_anggota' => null]);
            }

            if (Schema::hasTable('share_transactions')) {
                ShareTransaction::query()
                    ->where('reference_type', Permohonan::class)
                    ->where('reference_id', $permohonan->getKey())
                    ->delete();
            }

            $permohonan->delete();
        });

        return redirect()
            ->route($redirectRoute)
            ->with('status', 'Rekod anggota berjaya dipadam.');
    }
}
