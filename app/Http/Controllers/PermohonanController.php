<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Ahli;
use App\Models\AuditLog;
use App\Models\CooperativeNotification;
use App\Models\DocumentUpload;
use App\Models\Pekerja;
use App\Models\Permohonan;
use App\Models\Saham;
use App\Models\SahamStaff;
use App\Models\ShareTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermohonanController extends Controller
{
    public function studentIndex(Request $request, ?string $jenis = null): View|RedirectResponse
    {
        $authRole = $request->session()->get('auth_role');

        if (! in_array($authRole, ['ahli', 'staff'], true)) {
            return redirect()->route('login');
        }

        if ($authRole === 'staff') {
            $user = Pekerja::query()->with('sahamStaff')->find($request->session()->get('auth_id'));

            if (! $user || ! in_array($user->staff_type, ['lecturer_member', 'coop_staff', 'clothing_staff'], true)) {
                return redirect()->route('login');
            }

            $types = $this->applicationTypesForRole('staff', $user->staff_type);

            if ($types === []) {
                return redirect()
                    ->route('auth.dashboard')
                    ->withErrors(['permohonan' => 'Pekerja koperasi tidak mempunyai modul permohonan saham.']);
            }

            $staffMemberNumber = $this->staffMemberNumber($user);
            if (! $staffMemberNumber) {
                $types = collect($types)->only('anggota')->all();
            }

            $defaultType = $staffMemberNumber ? 'saham' : 'anggota';
            $activeType = $jenis && isset($types[$jenis]) ? $jenis : $defaultType;
            $portalRoutes = $this->staffPortalRoutes($user->staff_type);

            return view('student.permohonan.index', [
                'role' => 'staff',
                'user' => $user,
                'types' => $types,
                'activeType' => $activeType,
                'portalLabel' => 'Staff Portal',
                'identityLabel' => 'No Anggota',
                'identityValue' => $staffMemberNumber ?? 'Belum dijana',
                'currentShare' => (float) optional($user->sahamStaff)->syer + (float) optional($user->sahamStaff)->tambahan_saham,
                'portalRoutes' => $portalRoutes,
                'applications' => Permohonan::query()
                    ->whereNull('id_ahli')
                    ->where('no_matrik', $user->no_pekerja)
                    ->latest('tarikh_permohonan')
                    ->latest('id_permohonan')
                    ->paginate(20),
            ]);
        }

        $user = Ahli::query()->with('saham')->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        $types = $this->applicationTypesForRole('ahli');

        if (blank($user->no_anggota)) {
            $types = collect($types)->only('anggota')->all();
        }

        $activeType = $jenis && isset($types[$jenis]) ? $jenis : 'anggota';

        return view('student.permohonan.index', [
            'role' => 'ahli',
            'user' => $user,
            'types' => $types,
            'activeType' => $activeType,
            'portalLabel' => 'Student Portal',
            'identityLabel' => 'No Matrik',
            'identityValue' => $user->no_matrik,
            'currentShare' => (float) optional($user->saham)->syer + (float) optional($user->saham)->tambahan_saham,
            'applications' => Permohonan::query()
                ->where('id_ahli', $user->id_ahli)
                ->latest('tarikh_permohonan')
                ->latest('id_permohonan')
                ->paginate(20),
        ]);
    }

    public function studentStatus(Request $request): View|RedirectResponse
    {
        $authRole = $request->session()->get('auth_role');

        if (! in_array($authRole, ['ahli', 'staff'], true)) {
            return redirect()->route('login');
        }

        if ($authRole === 'staff') {
            $user = Pekerja::query()->find($request->session()->get('auth_id'));

            if (! $user || ! in_array($user->staff_type, ['lecturer_member', 'coop_staff', 'clothing_staff'], true)) {
                return redirect()->route('login');
            }

            return view('student.permohonan.status', [
                'role' => 'staff',
                'user' => $user,
                'portalLabel' => 'Staff Portal',
                'createRoute' => in_array($user->staff_type, ['lecturer_member', 'clothing_staff'], true)
                    ? $this->staffPortalRoutes($user->staff_type)['permohonan_index']
                    : null,
                'applications' => Permohonan::query()
                    ->whereNull('id_ahli')
                    ->where('no_matrik', $user->no_pekerja)
                    ->latest('tarikh_permohonan')
                    ->latest('id_permohonan')
                    ->paginate(15, ['*'], 'applications_page'),
                'documents' => DocumentUpload::query()
                    ->where('owner_role', 'staff')
                    ->where('owner_id', $user->id_pekerja)
                    ->where('category', '!=', 'penyata_bank')
                    ->latest()
                    ->paginate(15, ['*'], 'documents_page'),
            ]);
        }

        $user = Ahli::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        return view('student.permohonan.status', [
            'role' => 'ahli',
            'user' => $user,
            'portalLabel' => 'Student Portal',
            'createRoute' => 'student.permohonan.index',
            'applications' => Permohonan::query()
                ->where('id_ahli', $user->id_ahli)
                ->latest('tarikh_permohonan')
                ->latest('id_permohonan')
                ->paginate(15, ['*'], 'applications_page'),
            'documents' => DocumentUpload::query()
                ->where('owner_role', 'ahli')
                ->where('owner_id', $user->id_ahli)
                ->where('category', '!=', 'penyata_bank')
                ->latest()
                ->paginate(15, ['*'], 'documents_page'),
        ]);
    }

    public function store(Request $request, string $jenis): RedirectResponse
    {
        $authRole = $request->session()->get('auth_role');

        if (! in_array($authRole, ['ahli', 'staff'], true)) {
            return redirect()->route('login');
        }

        $user = $authRole === 'staff'
            ? Pekerja::query()->with('sahamStaff')->findOrFail($request->session()->get('auth_id'))
            : Ahli::query()->with('saham')->findOrFail($request->session()->get('auth_id'));

        if ($authRole === 'staff' && ! in_array($user->staff_type, ['lecturer_member', 'coop_staff', 'clothing_staff'], true)) {
            abort(403, 'Kategori staff ini tidak boleh menghantar permohonan koperasi.');
        }

        $types = $this->applicationTypesForRole($authRole, $authRole === 'staff' ? $user->staff_type : null);
        abort_unless(isset($types[$jenis]), 404);

        $isStaffApplicant = $authRole === 'staff';

        if (! $isStaffApplicant && $jenis !== 'anggota' && blank($user->no_anggota)) {
            return redirect()
                ->route('student.permohonan.index', ['jenis' => 'anggota'])
                ->withErrors(['permohonan' => 'Sila mohon menjadi anggota koperasi dahulu sebelum membuat permohonan lain.']);
        }

        if ($isStaffApplicant && $jenis !== 'anggota' && blank($this->staffMemberNumber($user))) {
            return redirect()
                ->route($this->staffPortalRoutes($user->staff_type)['permohonan_index'], ['jenis' => 'anggota'])
                ->withErrors(['permohonan' => 'Sila mohon menjadi anggota koperasi dahulu sebelum membuat permohonan lain.']);
        }

        $withdrawalTypes = $isStaffApplicant
            ? ['Berhenti Keahlian', 'Berpindah', 'Bersara', 'Lain-lain']
            : ['Berhenti Keahlian', 'Berpindah', 'Bersara', 'Tamat Pengajian', 'Lain-lain'];

        $rules = [
            'email' => ['nullable', 'email', 'max:100'],
            'no_tel' => ['nullable', 'string', 'max:20'],
            'catatan_pelajar' => ['nullable', 'string', 'max:1000'],
        ];

        $rules += match ($jenis) {
            'anggota' => [
                'tarikh_lahir' => ['required', 'date'],
                'jantina' => ['required', 'string', 'max:20'],
                'pekerjaan_pelajar' => ['required', 'string', 'max:100'],
                'bangsa' => ['required', 'string', 'max:50'],
                'agama' => ['required', 'string', 'max:50'],
                'taraf_perkahwinan' => ['required', 'string', 'max:50'],
                'alamat' => ['required', 'string', 'max:500'],
                'no_tel_rumah' => ['nullable', 'string', 'max:20'],
                'program_pengajian' => [$isStaffApplicant ? 'nullable' : 'required', Rule::in(['JTMK', 'JRKV'])],
                'kelas' => [$isStaffApplicant ? 'nullable' : 'required', Rule::in($this->studentClassOptions())],
                'yuran_anggota' => ['required', 'numeric', 'min:10'],
                'modal_saham' => ['required', 'numeric', 'min:10'],
                'salinan_ic' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                'slip_bayaran' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                'setuju_saham_tidak_dituntut' => ['accepted'],
                'penama_nama' => ['required', 'string', 'max:100'],
                'penama_nric' => ['required', 'string', 'max:20'],
                'penama_hubungan' => ['required', 'string', 'max:50'],
                'penama_no_tel' => ['required', 'string', 'max:20'],
                'penama_alamat' => ['required', 'string', 'max:500'],
                'penama_poskod' => ['required', 'string', 'max:10'],
                'penama_peratus' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'penama2_nama' => ['nullable', 'string', 'max:100'],
                'penama2_nric' => ['nullable', 'string', 'max:20'],
                'penama2_hubungan' => ['nullable', 'string', 'max:50'],
                'penama2_no_tel' => ['nullable', 'string', 'max:20'],
                'penama2_alamat' => ['nullable', 'string', 'max:500'],
                'penama2_poskod' => ['nullable', 'string', 'max:10'],
                'penama2_peratus' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'saksi_nama' => ['required', 'string', 'max:100'],
                'saksi_nric' => ['required', 'string', 'max:20'],
                'saksi_tarikh' => ['required', 'date'],
                'nama_waris' => ['required', 'string', 'max:100'],
                'telefon_waris' => ['required', 'string', 'max:20'],
                'hubungan_waris' => ['required', 'string', 'max:50'],
                'akuan_pemohon' => ['accepted'],
            ],
            'saham' => [
                'no_pendaftaran' => ['nullable', 'string', 'max:50'],
                'no_kp' => ['required', 'string', 'max:20'],
                'amaun_tambahan' => ['required', 'numeric', 'min:1'],
                'slip_bayaran' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                'tarikh_pengakuan' => ['required', 'date'],
                'akuan_saham' => ['accepted'],
            ],
            'berhenti' => [
                'no_anggota' => ['nullable', 'string', 'max:50'],
                'no_kp' => ['required', 'string', 'max:20'],
                'jenis_permohonan' => ['required', 'array', 'min:1'],
                'jenis_permohonan.*' => ['string', Rule::in($withdrawalTypes)],
                'lain_lain_sebab' => ['nullable', 'string', 'max:500'],
                'salinan_ic' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                'surat_sokongan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                'tarikh_pengakuan' => ['required', 'date'],
                'akuan_pengeluaran' => ['accepted'],
                'kaedah_terima_bayaran' => ['required', 'string', Rule::in(['Bayaran Atas Talian', 'Tunai'])],
                'nama_bank' => ['nullable', 'string', 'max:100'],
                'no_akaun_bank' => ['nullable', 'string', 'max:100'],
            ],
        };

        $validated = $request->validate(
            $rules,
            [
                'setuju_saham_tidak_dituntut.accepted' => 'Sila tandakan persetujuan saham tidak dituntut sebelum hantar permohonan.',
                'akuan_pemohon.accepted' => 'Sila tandakan akuan pemohon sebelum hantar permohonan.',
                'akuan_saham.accepted' => 'Sila tandakan akuan saham sebelum hantar permohonan.',
                'akuan_pengeluaran.accepted' => 'Sila tandakan akuan pengeluaran sebelum hantar permohonan.',
            ],
            [
                'setuju_saham_tidak_dituntut' => 'persetujuan saham tidak dituntut',
                'akuan_pemohon' => 'akuan pemohon',
                'akuan_saham' => 'akuan saham',
                'akuan_pengeluaran' => 'akuan pengeluaran',
            ]
        );

        if ($jenis === 'berhenti') {
            $letterRequiredTypes = ['Berhenti Keahlian', 'Berpindah', 'Bersara', 'Tamat Pengajian'];

            if (array_intersect($letterRequiredTypes, $validated['jenis_permohonan']) !== [] && ! $request->hasFile('surat_sokongan')) {
                return back()
                    ->withErrors(['surat_sokongan' => 'Sila muat naik surat pindah, berhenti atau dokumen berkaitan sebelum hantar permohonan.'])
                    ->withInput();
            }

        }

        $profileUpdates = [];

        if (blank($user->email) && filled($validated['email'] ?? null)) {
            $profileUpdates['email'] = $validated['email'];
        }

        if (blank($user->no_tel) && filled($validated['no_tel'] ?? null)) {
            $profileUpdates['no_tel'] = $validated['no_tel'];
        }

        if ($profileUpdates !== []) {
            $user->update($profileUpdates);
            $user->refresh();
        }

        $application = Permohonan::query()->create([
            'id_ahli' => $authRole === 'ahli' ? $user->id_ahli : null,
            'jenis' => $jenis,
            'nama_pemohon' => $user->nama,
            'no_matrik' => $authRole === 'ahli' ? $user->no_matrik : $user->no_pekerja,
            'email' => $validated['email'] ?? $user->email,
            'no_tel' => $validated['no_tel'] ?? $user->no_tel,
            'status' => 'baru',
            'data_permohonan' => $this->buildApplicationData($jenis, $validated, $user, $authRole),
            'catatan_pelajar' => $validated['catatan_pelajar'] ?? null,
            'tarikh_permohonan' => now()->toDateString(),
        ]);

        if ($jenis === 'anggota') {
            $this->storeApplicationDocuments($request, $application, $authRole, $user, [
                'salinan_ic' => 'salinan_ic',
                'slip_bayaran' => 'slip_bayaran',
            ], 'Permohonan Anggota Koperasi');
        }

        if ($jenis === 'saham') {
            $this->storeApplicationDocuments($request, $application, $authRole, $user, [
                'slip_bayaran' => 'slip_bayaran',
            ], 'Permohonan Penambahan Saham');
        }

        if ($jenis === 'berhenti') {
            $documents = ['salinan_ic' => 'salinan_ic'];

            if ($request->hasFile('surat_sokongan')) {
                $documents['surat_sokongan'] = 'surat_pindah_berhenti_persaraan';
            }

            $this->storeApplicationDocuments($request, $application, $authRole, $user, $documents, $this->withdrawalPurpose($validated['jenis_permohonan']));
        }

        $this->audit($request, $authRole, $user->getKey(), 'create', 'permohonan', $application, 'Permohonan koperasi dihantar.');
        $this->notifyAdmins('Permohonan baru', $user->nama.' menghantar '.$types[$jenis]['label'].'.', route('admin.permohonan.show', $application));

        $documentPrompt = $this->buildDocumentUploadPrompt($jenis, $validated);
        $redirectRoute = $authRole === 'staff'
            ? $this->staffPortalRoutes($user->staff_type)['permohonan_index']
            : 'student.permohonan.index';

        if ($documentPrompt !== []) {
            $request->session()->put('document_upload_prompt', [
                'application_id' => $application->getKey(),
                'application_label' => $types[$jenis]['label'],
                'documents' => $documentPrompt,
            ]);

            return redirect()
                ->route('koperasi.documents.index')
                ->with('status', 'Permohonan berjaya dihantar. Sila muat naik dokumen yang diperlukan.');
        }

        return redirect()
            ->route($redirectRoute, ['jenis' => $jenis])
            ->with('status', 'Permohonan berjaya dihantar untuk semakan admin.');
    }

    public function adminIndex(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'admin') {
            return redirect()->route('login');
        }

        $user = AdminUser::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        $types = $this->applicationTypesForRole('ahli');
        $selectedJenis = $request->string('jenis')->toString();
        $selectedStatus = $request->string('status')->toString();
        $selectedAudience = $request->string('pemohon')->toString();
        $selectedAudience = in_array($selectedAudience, ['pelajar', 'staff'], true) ? $selectedAudience : 'pelajar';

        if ($selectedAudience === 'staff') {
            $this->backfillApprovedStaffMemberNumbers();
        }

        $baseApplicationsQuery = Permohonan::query()
            ->with('ahli')
            ->when($selectedAudience === 'pelajar', fn ($query) => $query->whereNotNull('id_ahli'))
            ->when($selectedAudience === 'staff', fn ($query) => $query->whereNull('id_ahli'))
            ->when($selectedJenis !== '', fn ($query) => $query->where('jenis', $selectedJenis));

        $statusCounts = (clone $baseApplicationsQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $applications = (clone $baseApplicationsQuery)
            ->when($selectedStatus !== '', fn ($query) => $query->where('status', $selectedStatus))
            ->latest('tarikh_permohonan')
            ->latest('id_permohonan')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total' => $selectedStatus !== '' ? $applications->total() : (int) $statusCounts->sum(),
            'baru' => (int) ($statusCounts['baru'] ?? 0),
            'diluluskan' => (int) ($statusCounts['diluluskan'] ?? 0),
            'ditolak' => (int) ($statusCounts['ditolak'] ?? 0),
        ];

        return view('admin.permohonan.index', [
            'role' => 'admin',
            'user' => $user,
            'types' => $types,
            'applications' => $applications,
            'selectedJenis' => $selectedJenis,
            'selectedStatus' => $selectedStatus,
            'selectedAudience' => $selectedAudience,
            'statuses' => ['baru', 'dalam_semakan', 'diluluskan', 'ditolak'],
            'summary' => $summary,
        ]);
    }

    public function update(Request $request, Permohonan $permohonan): RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'admin') {
            return redirect()->route('login');
        }

        $adminId = (int) $request->session()->get('auth_id');
        $isDecisionAction = $request->boolean('decision_action');

        $validated = $request->validate([
            'status' => ['required', $isDecisionAction ? Rule::in(['diluluskan', 'ditolak']) : Rule::in(['baru', 'dalam_semakan', 'diluluskan', 'ditolak'])],
            'catatan_admin' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($isDecisionAction && in_array($permohonan->status, ['diluluskan', 'ditolak'], true)) {
            return back()->withErrors(['status' => 'Keputusan permohonan sudah dimuktamadkan dan tidak boleh diedit.']);
        }

        if (! $isDecisionAction && in_array($permohonan->status, ['diluluskan', 'ditolak'], true)) {
            return back()->withErrors(['status' => 'Keputusan permohonan sudah dimuktamadkan dan tidak boleh diedit.']);
        }

        $previousStatus = $permohonan->status;

        DB::transaction(function () use ($request, $permohonan, $validated, $adminId, $isDecisionAction, $previousStatus): void {
            $decisionDate = $isDecisionAction && in_array($validated['status'], ['diluluskan', 'ditolak'], true)
                ? now()->toDateString()
                : $permohonan->tarikh_keputusan;

            $permohonan->update([
                'status' => $validated['status'],
                'catatan_admin' => $validated['catatan_admin'] ?? null,
                'tarikh_keputusan' => $decisionDate,
            ]);

            if (
                $isDecisionAction
                &&
                $validated['status'] === 'diluluskan'
                && $previousStatus !== 'diluluskan'
                && $permohonan->jenis === 'anggota'
            ) {
                $data = $permohonan->data_permohonan ?? [];

                if ($this->isStaffApplication($permohonan)) {
                    $staff = $this->resolveStaffFromApplication($permohonan);

                    if ($staff->isEligibleForShares()) {
                        $memberNumber = $this->staffMemberNumber($staff) ?? ($data['no_anggota'] ?? null);

                        if (! $memberNumber || $this->memberNumberValue($memberNumber) < 1001) {
                            $memberNumber = $this->nextStaffMemberNumber();
                        }

                        if (Schema::hasColumn('pekerja', 'no_anggota') && ! $this->staffMemberNumber($staff)) {
                            $staff->update(['no_anggota' => $memberNumber]);
                        }

                        $data['no_anggota'] = $memberNumber;
                        $permohonan->update(['data_permohonan' => $data]);

                        $share = SahamStaff::query()->firstOrNew(['id_pekerja' => $staff->id_pekerja]);
                        if ($this->staffShareHasFeeColumn()) {
                            $share->yuran = (float) ($data['yuran_anggota'] ?? $share->yuran ?? 0);
                        }
                        $share->syer = max((float) ($share->syer ?? 0), (float) ($data['modal_saham'] ?? 0));
                        $share->tambahan_saham = (float) ($share->tambahan_saham ?? 0);
                        $share->tarikh_kemaskini = $decisionDate;
                        $share->save();

                        $this->recordShareTransaction('staff', $staff->id_pekerja, 'OPENING_BALANCE', 'CREDIT', (float) $share->syer, (float) $share->syer + (float) $share->tambahan_saham, $permohonan, 'admin', $adminId, 'Saham permulaan selepas permohonan anggota staff diluluskan.');
                    }
                } elseif ($permohonan->ahli) {
                    $memberUpdates = [];

                    if (! $permohonan->ahli->tarikh_daftar) {
                        $memberUpdates['tarikh_daftar'] = $decisionDate;
                    }

                    if (! $permohonan->ahli->no_anggota) {
                        $memberUpdates['no_anggota'] = $this->nextMemberNumber();
                    }

                    if ($memberUpdates !== []) {
                        $permohonan->ahli->update($memberUpdates);
                    }

                    $share = Saham::query()->firstOrNew(['id_ahli' => $permohonan->ahli->id_ahli]);
                    $share->yuran = (float) ($data['yuran_anggota'] ?? $share->yuran ?? 0);
                    $share->syer = max((float) ($share->syer ?? 0), (float) ($data['modal_saham'] ?? 0));
                    $share->tarikh_kemaskini = $decisionDate;
                    $share->save();

                    $this->recordShareTransaction('student', $permohonan->ahli->id_ahli, 'OPENING_BALANCE', 'CREDIT', (float) $share->syer, (float) $share->syer + (float) $share->tambahan_saham, $permohonan, 'admin', $adminId, 'Saham permulaan selepas permohonan anggota diluluskan.');
                }
            }

            $this->audit($request, 'admin', $adminId, $validated['status'], 'permohonan', $permohonan, 'Status permohonan dikemaskini kepada '.$validated['status'].'.');
            $this->notifyApplicant($permohonan, 'Status permohonan dikemaskini', 'Permohonan anda kini berstatus '.str_replace('_', ' ', $validated['status']).'.', route('student.permohonan.index'));
        });

        return redirect()->route('admin.permohonan.show', $permohonan)->with('status', 'Status permohonan berjaya dikemaskini.');
    }

    public function show(Request $request, Permohonan $permohonan): View|RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'admin') {
            return redirect()->route('login');
        }

        $user = AdminUser::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        $application = $permohonan->load('ahli');
        $ownerRole = $this->isStaffApplication($application) ? 'staff' : 'ahli';
        $ownerId = $ownerRole === 'staff'
            ? $this->resolveStaffFromApplication($application)->getKey()
            : $application->id_ahli;
        $applicationPurpose = $this->documentPurposeForApplication($application);
        $legacyCategories = $this->documentCategoriesForApplication($application);
        $hasApplicationPurpose = Schema::hasColumn('document_uploads', 'application_purpose');

        $documents = DocumentUpload::query()
            ->where('owner_role', $ownerRole)
            ->where('owner_id', $ownerId)
            ->where('category', '!=', 'penyata_bank')
            ->where(function ($query) use ($application, $applicationPurpose, $legacyCategories, $hasApplicationPurpose): void {
                $query->where(function ($linkedQuery) use ($application): void {
                    $linkedQuery
                        ->where('documentable_type', Permohonan::class)
                        ->where('documentable_id', $application->getKey());
                });

                if ($hasApplicationPurpose) {
                    $query->orWhere(function ($legacyQuery) use ($applicationPurpose): void {
                        $legacyQuery
                            ->whereNull('documentable_id')
                            ->where('application_purpose', $applicationPurpose);
                    });
                } else {
                    $query->orWhere(function ($legacyQuery) use ($legacyCategories): void {
                        $legacyQuery
                            ->whereNull('documentable_id')
                            ->whereIn('category', $legacyCategories);
                    });
                }
            })
            ->latest()
            ->get();

        return view('admin.permohonan.show', [
            'role' => 'admin',
            'user' => $user,
            'application' => $application,
            'documents' => $documents,
            'documentCategories' => $this->documentCategoryLabels(),
            'types' => $this->applicationTypesForRole('ahli'),
            'statuses' => ['baru', 'dalam_semakan', 'diluluskan', 'ditolak'],
        ]);
    }

    public function createShareAddition(Request $request, Permohonan $permohonan): View|RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'admin') {
            return redirect()->route('login');
        }

        $user = AdminUser::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        if ($permohonan->jenis !== 'saham') {
            return redirect()->route('admin.permohonan.show', $permohonan)
                ->withErrors(['saham' => 'Page tambah saham hanya untuk Permohonan Saham.']);
        }

        $application = $permohonan->load(['ahli.saham', 'ahli', 'ahli.saham']);

        if ($this->isStaffApplication($application) && ! $this->resolveStaffFromApplication($application)->isEligibleForShares()) {
            return redirect()->route('admin.permohonan.show', $permohonan)
                ->withErrors(['saham' => 'Pekerja koperasi tidak mempunyai rekod saham.']);
        }

        $data = $application->data_permohonan ?? [];
        $currentShare = $this->currentShareAmount($application);
        $additionalShare = (float) ($data['amaun_tambahan'] ?? 0);

        return view('admin.permohonan.tambah-saham', [
            'role' => 'admin',
            'user' => $user,
            'application' => $application,
            'data' => $data,
            'currentShare' => $currentShare,
            'additionalShare' => $additionalShare,
            'newShareTotal' => $currentShare + $additionalShare,
        ]);
    }

    public function createWithdrawalProcess(Request $request, Permohonan $permohonan): View|RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'admin') {
            return redirect()->route('login');
        }

        $user = AdminUser::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        if ($permohonan->jenis !== 'berhenti') {
            return redirect()->route('admin.permohonan.show', $permohonan)
                ->withErrors(['pengeluaran' => 'Page proses pengeluaran hanya untuk Permohonan Pengeluaran.']);
        }

        $application = $permohonan->load(['ahli.saham', 'ahli']);

        if ($this->isStaffApplication($application) && ! $this->resolveStaffFromApplication($application)->isEligibleForShares()) {
            return redirect()->route('admin.permohonan.show', $permohonan)
                ->withErrors(['saham' => 'Pekerja koperasi tidak mempunyai rekod saham.']);
        }

        $data = $application->data_permohonan ?? [];
        $currentShare = $this->currentShareAmount($application);
        $requestedShare = $currentShare;
        $types = collect($data['jenis_permohonan'] ?? []);
        $inactiveTypes = ['Berhenti / Berpindah / Bersara', 'Berhenti Keahlian', 'Berpindah', 'Bersara', 'Tamat Pengajian'];
        $defaultStudentStatus = $types->intersect($inactiveTypes)->isNotEmpty()
            ? 'pindah_berhenti'
            : ($this->isApplicationApplicantActive($application) ? 'aktif' : 'pindah_berhenti');

        return view('admin.permohonan.proses-pengeluaran', [
            'role' => 'admin',
            'user' => $user,
            'application' => $application,
            'data' => $data,
            'currentShare' => $currentShare,
            'requestedShare' => $requestedShare,
            'remainingShare' => max(0, $currentShare - $requestedShare),
            'defaultStudentStatus' => $defaultStudentStatus,
        ]);
    }

    public function storeShareAddition(Request $request, Permohonan $permohonan): RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'admin') {
            return redirect()->route('login');
        }

        if ($permohonan->jenis !== 'saham') {
            return redirect()->route('admin.permohonan.show', $permohonan)
                ->withErrors(['saham' => 'Page tambah saham hanya untuk Permohonan Saham.']);
        }

        $application = $permohonan->load(['ahli.saham', 'ahli']);
        $data = $application->data_permohonan ?? [];
        $validated = $request->validate([
            'amaun_tambahan' => ['required', 'numeric', 'min:1'],
            'catatan_admin' => ['nullable', 'string', 'max:1000'],
            'confirm_share_addition' => ['accepted'],
        ], [
            'confirm_share_addition.accepted' => 'Sila tekan Tambah Saham dahulu sebelum simpan.',
        ]);

        $adminId = (int) $request->session()->get('auth_id');

        DB::transaction(function () use ($request, $application, $validated, $adminId): void {
            if ($this->isStaffApplication($application)) {
                $share = $this->resolveStaffShare($application);
                $share->tambahan_saham = (float) ($share->tambahan_saham ?? 0) + (float) $validated['amaun_tambahan'];
                $share->tarikh_kemaskini = now()->toDateString();
                $share->save();
                $staff = $this->resolveStaffFromApplication($application);
                $memberType = 'staff';
                $memberId = $staff->id_pekerja;
            } else {
                $share = Saham::query()->firstOrNew(['id_ahli' => $application->id_ahli]);
                $share->syer = (float) ($share->syer ?? 0);
                $share->tambahan_saham = (float) ($share->tambahan_saham ?? 0) + (float) $validated['amaun_tambahan'];
                $share->yuran = (float) ($share->yuran ?? 10);
                $share->tarikh_kemaskini = now()->toDateString();
                $share->save();
                $memberType = 'student';
                $memberId = $application->id_ahli;
            }

            $this->recordShareTransaction(
                $memberType,
                $memberId,
                'SHARE_ADDITION',
                'CREDIT',
                (float) $validated['amaun_tambahan'],
                (float) $share->syer + (float) $share->tambahan_saham,
                $application,
                'admin',
                $adminId,
                'Penambahan saham melalui permohonan diluluskan.'
            );

            $application->update([
                'status' => 'diluluskan',
                'catatan_admin' => $validated['catatan_admin'] ?? ($this->isStaffApplication($application)
                    ? 'Permohonan penambahan saham staff telah diluluskan dan rekod saham staff dikemaskini.'
                    : 'Permohonan penambahan saham telah diluluskan dan rekod saham pelajar dikemaskini.'),
                'tarikh_keputusan' => now()->toDateString(),
            ]);

            $this->audit($request, 'admin', $adminId, 'approve', 'share_addition', $application, 'Permohonan tambah saham diluluskan.');
            $this->notifyApplicant($application, 'Tambah saham diluluskan', 'Permohonan tambah saham anda telah diluluskan.', route('student.permohonan.index', ['jenis' => 'saham']));
        });

        return redirect()
            ->route('admin.permohonan.show', $application)
            ->with('status', $this->isStaffApplication($application)
                ? 'Saham staff berjaya ditambah dan rekod itu sudah masuk ke senarai Saham Staff.'
                : 'Saham pelajar berjaya ditambah. Anda masih boleh simpan catatan admin di page ini, dan perubahan itu sudah masuk ke senarai Saham Pelajar.');
    }

    public function storeWithdrawalProcess(Request $request, Permohonan $permohonan): RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'admin') {
            return redirect()->route('login');
        }

        if ($permohonan->jenis !== 'berhenti') {
            return redirect()->route('admin.permohonan.show', $permohonan)
                ->withErrors(['pengeluaran' => 'Page proses pengeluaran hanya untuk Permohonan Pengeluaran.']);
        }

        $application = $permohonan->load(['ahli.saham', 'ahli']);
        $validated = $request->validate([
            'catatan_admin' => ['nullable', 'string', 'max:1000'],
            'confirm_withdrawal_process' => ['accepted'],
        ], [
            'confirm_withdrawal_process.accepted' => 'Sila tekan Proses Pengeluaran dahulu sebelum simpan.',
        ]);

        $share = $this->isStaffApplication($application)
            ? $this->resolveStaffShare($application)
            : Saham::query()->firstOrNew(['id_ahli' => $application->id_ahli]);
        $currentBaseShare = (float) ($share->syer ?? 0);
        $currentAdditionalShare = (float) ($share->tambahan_saham ?? 0);
        $currentShare = $currentBaseShare + $currentAdditionalShare;
        $shareAmount = $currentShare;

        if ($shareAmount > $currentShare) {
            return back()->withErrors(['saham_dipohon' => 'Amaun saham dipohon melebihi baki saham semasa.'])->withInput();
        }

        $adminId = (int) $request->session()->get('auth_id');

        DB::transaction(function () use ($request, $application, $validated, $shareAmount, $share, $currentBaseShare, $currentAdditionalShare, $currentShare, $adminId): void {
            $deductedFromAdditional = min($currentAdditionalShare, $shareAmount);
            $remainingDeduction = $shareAmount - $deductedFromAdditional;
            $share->tambahan_saham = $currentAdditionalShare - $deductedFromAdditional;
            $share->syer = $currentBaseShare - $remainingDeduction;
            if (! $this->isStaffApplication($application) || $this->staffShareHasFeeColumn()) {
                $share->yuran = (float) ($share->yuran ?? 10);
            }
            $share->tarikh_kemaskini = now()->toDateString();
            $share->save();

            if ($this->isStaffApplication($application)) {
                $staff = $this->resolveStaffFromApplication($application);
                $staff->update([
                    'status_aktif' => false,
                ]);
                $memberType = 'staff';
                $memberId = $staff->id_pekerja;
            } elseif ($application->ahli) {
                $application->ahli->update([
                    'status_aktif' => false,
                ]);
                $memberType = 'student';
                $memberId = $application->id_ahli;
            } else {
                $memberType = 'student';
                $memberId = (int) $application->id_ahli;
            }

            if ($shareAmount > 0) {
                $this->recordShareTransaction(
                    $memberType,
                    $memberId,
                    'SHARE_WITHDRAWAL',
                    'DEBIT',
                    $shareAmount,
                    (float) $share->syer + (float) $share->tambahan_saham,
                    $application,
                    'admin',
                    $adminId,
                    'Pengeluaran saham diproses.'
                );
            }

            $application->update([
                'status' => 'diluluskan',
                'catatan_admin' => $validated['catatan_admin'] ?? ($this->isStaffApplication($application)
                    ? 'Permohonan pengeluaran staff telah diproses dan baki saham staff dikemaskini.'
                    : 'Permohonan pengeluaran telah diproses dan baki saham pelajar dikemaskini.'),
                'tarikh_keputusan' => now()->toDateString(),
            ]);

            $this->audit($request, 'admin', $adminId, 'payment', 'withdrawal', $application, 'Permohonan pengeluaran diproses.');
            $this->notifyApplicant($application, 'Pengeluaran diproses', 'Permohonan pengeluaran anda telah diproses.', route('student.permohonan.index', ['jenis' => 'berhenti']));
        });

        return redirect()
            ->route('admin.permohonan.show', $application)
            ->with('status', $this->isStaffApplication($application)
                ? 'Pengeluaran staff berjaya diproses dan baki baharu sudah dikemaskini dalam rekod saham staff.'
                : 'Pengeluaran berjaya diproses. Anda masih boleh simpan catatan admin di page ini, dan baki baharu sudah dikemaskini dalam rekod saham.');
    }

    public function destroy(Request $request, Permohonan $permohonan): RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'admin') {
            return redirect()->route('login');
        }

        $permohonan->delete();

        return redirect()->route('admin.permohonan.index')->with('status', 'Permohonan berjaya dipadam.');
    }

    private function applicationTypesForRole(string $role, ?string $staffType = null): array
    {
        $types = [
            'anggota' => [
                'label' => 'Permohonan Anggota',
                'description' => 'Borang permohonan keanggotaan digital untuk pengesahan maklumat ahli.',
            ],
            'saham' => [
                'label' => 'Permohonan Penambahan Saham',
                'description' => 'Mohon penambahan saham koperasi beserta maklumat bayaran.',
            ],
            'berhenti' => [
                'label' => 'Permohonan Berhenti / Pindah / Bersara',
                'description' => 'Berhenti keahlian, berpindah, bersara atau tamat pengajian dengan pengeluaran penuh baki saham.',
            ],
        ];

        if ($role === 'staff' && ! in_array($staffType, Pekerja::SHAREHOLDER_STAFF_TYPES, true)) {
            return [];
        }

        return $types;
    }

    private function buildApplicationData(string $jenis, array $validated, Ahli|Pekerja $user, string $authRole): array
    {
        $baseData = [
            'pemohon_role' => $authRole,
            'no_kad_pengenalan' => $user->nric ?? null,
        ];

        if ($authRole === 'staff') {
            $baseData['no_pekerja'] = $user->no_pekerja;
            $baseData['staff_type'] = $user->staff_type;
            $baseData['jenis_staff'] = $user->staff_type_label;
        }

        return match ($jenis) {
            'anggota' => [
                ...$baseData,
                'tarikh_lahir' => $validated['tarikh_lahir'],
                'jantina' => $validated['jantina'],
                'pekerjaan_pelajar' => $validated['pekerjaan_pelajar'],
                'bangsa' => $validated['bangsa'],
                'agama' => $validated['agama'],
                'taraf_perkahwinan' => $validated['taraf_perkahwinan'],
                'alamat' => $validated['alamat'],
                'no_tel_rumah' => $validated['no_tel_rumah'] ?? null,
                ...($authRole === 'ahli' ? [
                    'program_pengajian' => $validated['program_pengajian'],
                    'kelas' => $validated['kelas'],
                    'semester' => $user->semester,
                ] : [
                    'jawatan' => $user->jawatan,
                    'tarikh_mula_kerja' => optional($user->tarikh_mula)->toDateString(),
                ]),
                'yuran_anggota' => (float) $validated['yuran_anggota'],
                'modal_saham' => (float) $validated['modal_saham'],
                'dokumen_sokongan' => $validated['dokumen_sokongan'] ?? [],
                'setuju_saham_tidak_dituntut' => true,
                'nama_waris' => $validated['nama_waris'],
                'telefon_waris' => $validated['telefon_waris'],
                'hubungan_waris' => $validated['hubungan_waris'],
                'penama_nama' => $validated['penama_nama'],
                'penama_nric' => $validated['penama_nric'],
                'penama_hubungan' => $validated['penama_hubungan'],
                'penama_no_tel' => $validated['penama_no_tel'],
                'penama_alamat' => $validated['penama_alamat'],
                'penama_poskod' => $validated['penama_poskod'],
                'penama_peratus' => $validated['penama_peratus'] ?? null,
                'penama2_nama' => $validated['penama2_nama'] ?? null,
                'penama2_nric' => $validated['penama2_nric'] ?? null,
                'penama2_hubungan' => $validated['penama2_hubungan'] ?? null,
                'penama2_no_tel' => $validated['penama2_no_tel'] ?? null,
                'penama2_alamat' => $validated['penama2_alamat'] ?? null,
                'penama2_poskod' => $validated['penama2_poskod'] ?? null,
                'penama2_peratus' => $validated['penama2_peratus'] ?? null,
                'saksi_nama' => $validated['saksi_nama'],
                'saksi_nric' => $validated['saksi_nric'],
                'saksi_tarikh' => $validated['saksi_tarikh'],
                'akuan_pemohon' => true,
            ],
            'saham' => [
                ...$baseData,
                'no_anggota' => $authRole === 'ahli' ? $user->no_anggota : $this->staffMemberNumber($user),
                'no_pendaftaran' => $validated['no_pendaftaran'] ?? ($authRole === 'ahli' ? $user->no_matrik : $user->no_pekerja),
                'no_kp' => $validated['no_kp'],
                'amaun_tambahan' => (float) $validated['amaun_tambahan'],
                'dokumen_sokongan' => $validated['dokumen_sokongan'] ?? [],
                'tarikh_pengakuan' => $validated['tarikh_pengakuan'],
                'akuan_saham' => true,
                'syer_semasa' => (float) ($authRole === 'ahli'
                    ? (($user->saham->syer ?? 0) + ($user->saham->tambahan_saham ?? 0))
                    : (($user->sahamStaff->syer ?? 0) + ($user->sahamStaff->tambahan_saham ?? 0))),
            ],
            'berhenti' => [
                ...$baseData,
                'no_anggota' => $validated['no_anggota'] ?? ($authRole === 'ahli' ? $user->no_anggota : $this->staffMemberNumber($user)),
                'no_kp' => $validated['no_kp'],
                'jenis_permohonan' => $validated['jenis_permohonan'],
                'lain_lain_sebab' => $validated['lain_lain_sebab'] ?? null,
                'dokumen_sokongan' => $validated['dokumen_sokongan'] ?? [],
                'saham_dipohon' => (float) ($authRole === 'ahli'
                    ? (($user->saham->syer ?? 0) + ($user->saham->tambahan_saham ?? 0))
                    : (($user->sahamStaff->syer ?? 0) + ($user->sahamStaff->tambahan_saham ?? 0))),
                'jumlah_dipohon' => (float) ($authRole === 'ahli'
                    ? (($user->saham->syer ?? 0) + ($user->saham->tambahan_saham ?? 0))
                    : (($user->sahamStaff->syer ?? 0) + ($user->sahamStaff->tambahan_saham ?? 0))),
                'tarikh_pengakuan' => $validated['tarikh_pengakuan'],
                'akuan_pengeluaran' => true,
                'kaedah_terima_bayaran' => $validated['kaedah_terima_bayaran'],
                'nama_bank' => $validated['nama_bank'] ?? null,
                'no_akaun_bank' => $validated['no_akaun_bank'] ?? null,
                'syer_semasa' => (float) ($authRole === 'ahli'
                    ? (($user->saham->syer ?? 0) + ($user->saham->tambahan_saham ?? 0))
                    : (($user->sahamStaff->syer ?? 0) + ($user->sahamStaff->tambahan_saham ?? 0))),
            ],
        };
    }

    private function isStaffApplication(Permohonan $permohonan): bool
    {
        return ($permohonan->data_permohonan['pemohon_role'] ?? null) === 'staff';
    }

    private function nextMemberNumber(): string
    {
        $memberNumbers = Ahli::query()
            ->whereNotNull('no_anggota')
            ->lockForUpdate()
            ->pluck('no_anggota');

        if (Schema::hasColumn('pekerja', 'no_anggota')) {
            $memberNumbers = $memberNumbers->merge(
                Pekerja::query()
                    ->whereNotNull('no_anggota')
                    ->lockForUpdate()
                    ->pluck('no_anggota')
            );
        }

        $memberNumbers = $memberNumbers->merge(
            Permohonan::query()
                ->where('jenis', 'anggota')
                ->where('status', 'diluluskan')
                ->where('data_permohonan->pemohon_role', 'staff')
                ->get()
                ->pluck('data_permohonan.no_anggota')
                ->filter()
        );

        $highestNumber = $memberNumbers
            ->map(function (string $number): int {
                return preg_match('/^PBT(\\d+)$/i', trim($number), $matches)
                    ? (int) $matches[1]
                    : 0;
            })
            ->max() ?? 0;

        return 'PBT'.max(123, $highestNumber + 1);
    }

    private function nextStaffMemberNumber(): string
    {
        $memberNumbers = collect();

        if (Schema::hasColumn('pekerja', 'no_anggota')) {
            $memberNumbers = $memberNumbers->merge(
                Pekerja::query()
                    ->whereNotNull('no_anggota')
                    ->lockForUpdate()
                    ->pluck('no_anggota')
            );
        }

        $memberNumbers = $memberNumbers->merge(
            Permohonan::query()
                ->where('jenis', 'anggota')
                ->where('status', 'diluluskan')
                ->where('data_permohonan->pemohon_role', 'staff')
                ->get()
                ->pluck('data_permohonan.no_anggota')
                ->filter()
        );

        $highestNumber = $memberNumbers
            ->map(fn (string $number): int => $this->memberNumberValue($number))
            ->filter(fn (int $number): bool => $number >= 1001)
            ->max() ?? 1000;

        return 'PBT'.($highestNumber + 1);
    }

    private function memberNumberValue(?string $number): int
    {
        return preg_match('/^PBT(\d+)$/i', trim((string) $number), $matches)
            ? (int) $matches[1]
            : 0;
    }

    /** @return array{dashboard:string,permohonan_index:string,permohonan_store:string,profile:string} */
    private function staffPortalRoutes(?string $staffType): array
    {
        $prefix = match ($staffType) {
            'lecturer_member' => 'lecturer-member',
            'clothing_staff' => 'clothing-staff',
            default => 'coop-staff',
        };

        return [
            'dashboard' => $prefix.'.dashboard',
            'permohonan_index' => $prefix.'.permohonan.index',
            'permohonan_store' => $prefix.'.permohonan.store',
            'profile' => $prefix.'.profile',
        ];
    }

    private function staffMemberNumber(Pekerja $staff): ?string
    {
        if (Schema::hasColumn('pekerja', 'no_anggota') && filled($staff->no_anggota)) {
            return $staff->no_anggota;
        }

        $approvedApplication = Permohonan::query()
            ->where('jenis', 'anggota')
            ->where('status', 'diluluskan')
            ->where('data_permohonan->pemohon_role', 'staff')
            ->where('no_matrik', $staff->no_pekerja)
            ->latest('tarikh_keputusan')
            ->latest('id_permohonan')
            ->first();

        return $approvedApplication->data_permohonan['no_anggota'] ?? null;
    }

    private function staffShareHasFeeColumn(): bool
    {
        return Schema::hasColumn('saham_staff', 'yuran');
    }

    private function backfillApprovedStaffMemberNumbers(): void
    {
        DB::transaction(function (): void {
            Permohonan::query()
                ->where('jenis', 'anggota')
                ->where('status', 'diluluskan')
                ->where('data_permohonan->pemohon_role', 'staff')
                ->get()
                ->each(function (Permohonan $application): void {
                    $data = $application->data_permohonan ?? [];

                    $staff = $this->resolveStaffFromApplication($application);
                    $memberNumber = $this->staffMemberNumber($staff) ?? ($data['no_anggota'] ?? null);

                    if ($memberNumber && $this->memberNumberValue($memberNumber) >= 1001) {
                        return;
                    }

                    $memberNumber = $this->nextStaffMemberNumber();

                    if (Schema::hasColumn('pekerja', 'no_anggota')) {
                        $staff->update(['no_anggota' => $memberNumber]);
                    }

                    $data['no_anggota'] = $memberNumber;
                    $application->update(['data_permohonan' => $data]);
                });
        });
    }

    /** @return array<int, array{label:string,hint:string,category:string,purpose:string}> */
    private function buildDocumentUploadPrompt(string $jenis, array $validated): array
    {
        return match ($jenis) {
            'anggota' => [],
            'saham' => [],
            'berhenti' => [],
            default => [],
        };
    }

    private function storeApplicationDocuments(Request $request, Permohonan $application, string $ownerRole, Ahli|Pekerja $user, array $documents, string $purpose): void
    {
        foreach ($documents as $field => $category) {
            $file = $request->file($field);
            $payload = [
                'owner_role' => $ownerRole,
                'owner_id' => $user->getKey(),
                'uploaded_by_role' => $ownerRole,
                'uploaded_by_id' => $user->getKey(),
                'documentable_type' => Permohonan::class,
                'documentable_id' => $application->getKey(),
                'category' => $category,
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $file->store('koperasi-documents'),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize() ?: 0,
            ];

            if (Schema::hasColumn('document_uploads', 'application_purpose')) {
                $payload['application_purpose'] = $purpose;
            }

            DocumentUpload::query()->create($payload);
        }
    }

    private function withdrawalPurpose(array $selectedTypes): string
    {
        $labels = array_values(array_intersect([
            'Berhenti Keahlian',
            'Berpindah',
            'Bersara',
            'Tamat Pengajian',
        ], $selectedTypes));

        return $labels === []
            ? 'Permohonan Berhenti / Pindah / Bersara'
            : 'Permohonan '.implode(' / ', $labels);
    }

    private function documentPurposeForApplication(Permohonan $application): string
    {
        return match ($application->jenis) {
            'anggota' => 'Permohonan Anggota Koperasi',
            'saham' => 'Permohonan Penambahan Saham',
            'berhenti' => $this->withdrawalPurpose($application->data_permohonan['jenis_permohonan'] ?? []),
            default => $this->applicationTypesForRole('ahli')[$application->jenis]['label'] ?? ucfirst($application->jenis),
        };
    }

    private function documentCategoryLabels(): array
    {
        return [
            'slip_bayaran' => 'Slip / Bukti Bayaran',
            'salinan_ic' => 'Salinan IC',
            'surat_pindah_berhenti_persaraan' => 'Surat Pindah / Berhenti / Kad Persaraan',
            'tambah_saham' => 'Dokumen Tambah Saham',
            'pengeluaran_saham' => 'Dokumen Pengeluaran Saham',
            'surat_berhenti' => 'Surat Berhenti Keahlian',
            'surat_pindah' => 'Surat Pindah',
            'kad_surat_persaraan' => 'Kad / Surat Persaraan',
            'surat_tamat_pengajian' => 'Surat Tamat Pengajian',
            'dokumen_sokongan_lain' => 'Dokumen Sokongan Lain',
        ];
    }

    private function documentCategoriesForApplication(Permohonan $application): array
    {
        return match ($application->jenis) {
            'anggota' => ['salinan_ic', 'slip_bayaran'],
            'saham' => ['slip_bayaran', 'tambah_saham'],
            'berhenti' => [
                'salinan_ic',
                'surat_pindah_berhenti_persaraan',
                'pengeluaran_saham',
                'surat_berhenti',
                'surat_pindah',
                'kad_surat_persaraan',
                'surat_tamat_pengajian',
                'dokumen_sokongan_lain',
            ],
            default => array_keys($this->documentCategoryLabels()),
        };
    }

    private function resolveStaffFromApplication(Permohonan $permohonan): Pekerja
    {
        $noPekerja = $permohonan->data_permohonan['no_pekerja'] ?? $permohonan->no_matrik;

        return Pekerja::query()
            ->where('no_pekerja', $noPekerja)
            ->firstOrFail();
    }

    private function resolveStaffShare(Permohonan $permohonan): SahamStaff
    {
        $staff = $this->resolveStaffFromApplication($permohonan);

        return SahamStaff::query()->firstOrNew(['id_pekerja' => $staff->id_pekerja]);
    }

    private function currentShareAmount(Permohonan $permohonan): float
    {
        if ($this->isStaffApplication($permohonan)) {
            $staff = $this->resolveStaffFromApplication($permohonan);

            if (! $staff->isEligibleForShares()) {
                return 0.0;
            }

            $share = $this->resolveStaffShare($permohonan);

            return (float) ($share->syer ?? 0) + (float) ($share->tambahan_saham ?? 0);
        }

        return (float) ($permohonan->ahli->saham->syer ?? 0) + (float) ($permohonan->ahli->saham->tambahan_saham ?? 0);
    }

    private function isApplicationApplicantActive(Permohonan $permohonan): bool
    {
        if ($this->isStaffApplication($permohonan)) {
            return (bool) $this->resolveStaffFromApplication($permohonan)->status_aktif;
        }

        return (bool) ($permohonan->ahli->status_aktif ?? false);
    }

    private function recordShareTransaction(string $memberType, int $memberId, string $type, string $direction, float $amount, float $balanceAfter, Permohonan $reference, string $processedByRole, int $processedById, string $notes): void
    {
        if (! Schema::hasTable('share_transactions') || $amount <= 0) {
            return;
        }

        $exists = ShareTransaction::query()
            ->where('reference_type', Permohonan::class)
            ->where('reference_id', $reference->getKey())
            ->where('transaction_type', $type)
            ->where('direction', $direction)
            ->exists();

        if ($exists) {
            return;
        }

        ShareTransaction::query()->create([
            'member_type' => $memberType,
            'member_id' => $memberId,
            'transaction_type' => $type,
            'direction' => $direction,
            'amount' => $amount,
            'balance_after' => $balanceAfter,
            'reference_type' => Permohonan::class,
            'reference_id' => $reference->getKey(),
            'processed_by_role' => $processedByRole,
            'processed_by_id' => $processedById,
            'notes' => $notes,
            'transacted_at' => now()->toDateString(),
        ]);
    }

    private function audit(Request $request, string $actorRole, int $actorId, string $action, string $module, ?object $subject, string $description): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        AuditLog::query()->create([
            'actor_role' => $actorRole,
            'actor_id' => $actorId,
            'action' => $action,
            'module' => $module,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }

    private function notifyApplicant(Permohonan $permohonan, string $title, string $message, ?string $link = null): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        if ($this->isStaffApplication($permohonan)) {
            $staff = $this->resolveStaffFromApplication($permohonan);
            $this->notify('staff', $staff->id_pekerja, $title, $message, $link);

            return;
        }

        if ($permohonan->id_ahli) {
            $this->notify('ahli', (int) $permohonan->id_ahli, $title, $message, $link);
        }
    }

    private function notifyAdmins(string $title, string $message, ?string $link = null): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        AdminUser::query()->where('status_aktif', true)->each(function (AdminUser $admin) use ($title, $message, $link): void {
            $this->notify('admin', $admin->id_admin, $title, $message, $link);
        });
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

    private function notify(string $role, int $id, string $title, string $message, ?string $link = null): void
    {
        CooperativeNotification::query()->create([
            'recipient_role' => $role,
            'recipient_id' => $id,
            'title' => $title,
            'message' => $message,
            'link' => $link,
        ]);
    }
}
