<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Ahli;
use App\Models\AuditLog;
use App\Models\CooperativeNotification;
use App\Models\CooperativeSetting;
use App\Models\DocumentUpload;
use App\Models\Pekerja;
use App\Models\Permohonan;
use App\Models\ShareTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CooperativeController extends Controller
{
    public function auditLog(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        return view('admin.audit.index', [
            ...$auth,
            'logs' => AuditLog::query()->latest()->paginate(30),
        ]);
    }

    public function documents(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff', 'ahli']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $categories = $this->documentCategories();
        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));
        $ownerRole = trim((string) $request->query('owner_role', ''));
        $allowedOwnerRoles = ['ahli', 'staff'];

        $documents = DocumentUpload::query()
            ->with(['student', 'staff'])
            ->where('category', '!=', 'penyata_bank')
            ->when(array_key_exists($category, $categories), fn ($query) => $query->where('category', $category))
            ->when($auth['role'] === 'admin' && in_array($ownerRole, $allowedOwnerRoles, true), fn ($query) => $query->where('owner_role', $ownerRole))
            ->when($search !== '', function ($query) use ($search): void {
                $staffHasMemberNumber = Schema::hasColumn('pekerja', 'no_anggota');

                $query->where(function ($searchQuery) use ($search, $staffHasMemberNumber): void {
                    $searchQuery
                        ->where('original_name', 'like', "%{$search}%")
                        ->when(Schema::hasColumn('document_uploads', 'application_purpose'), fn ($purposeQuery) => $purposeQuery->orWhere('application_purpose', 'like', "%{$search}%"))
                        ->orWhere(function ($studentScope) use ($search): void {
                            $studentScope
                                ->where('owner_role', 'ahli')
                                ->whereHas('student', function ($studentQuery) use ($search): void {
                                    $studentQuery
                                        ->where('nama', 'like', "%{$search}%")
                                        ->orWhere('no_matrik', 'like', "%{$search}%")
                                        ->orWhere('nric', 'like', "%{$search}%");
                                });
                        })
                        ->orWhere(function ($staffScope) use ($search, $staffHasMemberNumber): void {
                            $staffScope
                                ->where('owner_role', 'staff')
                                ->whereHas('staff', function ($staffQuery) use ($search, $staffHasMemberNumber): void {
                                    $staffQuery
                                        ->where('nama', 'like', "%{$search}%")
                                        ->orWhere('no_pekerja', 'like', "%{$search}%")
                                        ->orWhere('nric', 'like', "%{$search}%");

                                    if ($staffHasMemberNumber) {
                                        $staffQuery->orWhere('no_anggota', 'like', "%{$search}%");
                                    }
                                });
                        });
                });
            })
            ->latest();

        if ($auth['role'] !== 'admin') {
            $documents->where('owner_role', $auth['role'])->where('owner_id', $auth['user']->getKey());
        }

        return view('koperasi.documents.index', [
            ...$auth,
            'documents' => $documents->paginate(20)->withQueryString(),
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => array_key_exists($category, $categories) ? $category : '',
                'owner_role' => in_array($ownerRole, $allowedOwnerRoles, true) ? $ownerRole : '',
            ],
        ]);
    }

    public function storeDocument(Request $request): RedirectResponse
    {
        $auth = $this->requireRole($request, ['staff', 'ahli']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $rules = [
            'category' => ['required', 'string', 'max:80'],
            'application_purpose' => ['nullable', 'string', 'max:160'],
            'application_id' => ['nullable', 'integer'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];

        $validated = $request->validate($rules);
        abort_unless(array_key_exists($validated['category'], $this->documentCategories()), 422, 'Kategori dokumen tidak sah.');

        $ownerRole = $auth['role'];
        $ownerId = (int) $auth['user']->getKey();
        $this->assertOwnerExists($ownerRole, $ownerId);
        $application = $this->resolveOwnedApplication($validated['application_id'] ?? null, $ownerRole, $auth['user']);

        $file = $request->file('document');
        $path = $file->store('koperasi-documents');

        $payload = [
            'owner_role' => $ownerRole,
            'owner_id' => $ownerId,
            'uploaded_by_role' => $auth['role'],
            'uploaded_by_id' => $auth['user']->getKey(),
            'documentable_type' => $application ? Permohonan::class : null,
            'documentable_id' => $application?->getKey(),
            'category' => $validated['category'],
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize() ?: 0,
        ];

        if (Schema::hasColumn('document_uploads', 'application_purpose')) {
            $payload['application_purpose'] = $validated['application_purpose'] ?: $this->defaultDocumentPurpose($validated['category']);
        }

        $document = DocumentUpload::query()->create($payload);

        $this->audit($request, $auth, 'upload', 'document', $document, 'Dokumen koperasi dimuat naik.');
        $this->clearUploadedPromptItem($request, $validated['category']);

        return back()->with('status', 'Dokumen berjaya dimuat naik.');
    }

    public function downloadDocument(Request $request, DocumentUpload $document): StreamedResponse|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff', 'ahli']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        if (! $this->canAccessDocument($auth, $document)) {
            return redirect()->route('auth.dashboard')->withErrors(['access' => 'Anda tidak dibenarkan membuka dokumen ini.']);
        }

        $this->audit($request, $auth, 'download', 'document', $document, 'Dokumen koperasi dimuat turun.');

        return Storage::download($document->stored_path, $document->original_name);
    }

    public function viewDocument(Request $request, DocumentUpload $document): BinaryFileResponse|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff', 'ahli']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        if (! $this->canAccessDocument($auth, $document)) {
            return redirect()->route('auth.dashboard')->withErrors(['access' => 'Anda tidak dibenarkan membuka dokumen ini.']);
        }

        abort_unless(Storage::exists($document->stored_path), 404, 'Fail dokumen tidak dijumpai.');
        $this->audit($request, $auth, 'view', 'document', $document, 'Dokumen koperasi dilihat.');

        return response()->file(Storage::path($document->stored_path), [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $document->original_name).'"',
        ]);
    }

    public function destroyDocument(Request $request, DocumentUpload $document): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff', 'ahli']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        if (! $this->canAccessDocument($auth, $document, false)) {
            return redirect()->route('auth.dashboard')->withErrors(['access' => 'Anda tidak dibenarkan memadam dokumen ini.']);
        }

        Storage::delete($document->stored_path);
        $document->delete();
        $this->audit($request, $auth, 'delete', 'document', $document, 'Dokumen koperasi dipadam.');

        return back()->with('status', 'Dokumen berjaya dipadam.');
    }

    public function transactions(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff', 'ahli']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $search = trim((string) $request->query('search', ''));
        $type = trim((string) $request->query('type', ''));
        $direction = trim((string) $request->query('direction', ''));
        $memberType = trim((string) $request->query('member_type', ''));
        $scope = trim((string) $request->query('scope', ''));
        $allowedTypes = ['OPENING_BALANCE', 'SHARE_ADDITION', 'SHARE_WITHDRAWAL', 'MANUAL_CREATE', 'MANUAL_UPDATE', 'MANUAL_ADJUSTMENT'];
        $allowedDirections = ['CREDIT', 'DEBIT'];
        $allowedMemberTypes = ['student', 'staff'];
        $canViewAllTransactions = $auth['role'] === 'admin'
            || (
                $auth['role'] === 'staff'
                && ($auth['user']->staff_type ?? null) === Pekerja::SHARE_MANAGER_STAFF_TYPE
            );
        $showOwnTransactions = ! $canViewAllTransactions || $scope === 'mine';

        $transactions = ShareTransaction::query()
            ->with(['student', 'staff'])
            ->when(in_array($type, $allowedTypes, true), fn ($query) => $query->where('transaction_type', $type))
            ->when(in_array($direction, $allowedDirections, true), fn ($query) => $query->where('direction', $direction))
            ->when($canViewAllTransactions && in_array($memberType, $allowedMemberTypes, true), fn ($query) => $query->where('member_type', $memberType))
            ->when($search !== '', function ($query) use ($search): void {
                $staffHasMemberNumber = Schema::hasColumn('pekerja', 'no_anggota');

                $query->where(function ($searchQuery) use ($search, $staffHasMemberNumber): void {
                    $searchQuery
                        ->where(function ($studentScope) use ($search): void {
                            $studentScope
                                ->where('member_type', 'student')
                                ->whereHas('student', function ($studentQuery) use ($search): void {
                                    $studentQuery
                                        ->where('nama', 'like', "%{$search}%")
                                        ->orWhere('no_matrik', 'like', "%{$search}%")
                                        ->orWhere('nric', 'like', "%{$search}%");
                                });
                        })
                        ->orWhere(function ($staffScope) use ($search, $staffHasMemberNumber): void {
                            $staffScope
                                ->where('member_type', 'staff')
                                ->whereHas('staff', function ($staffQuery) use ($search, $staffHasMemberNumber): void {
                                    $staffQuery
                                        ->where('nama', 'like', "%{$search}%")
                                        ->orWhere('no_pekerja', 'like', "%{$search}%")
                                        ->orWhere('nric', 'like', "%{$search}%");

                                    if ($staffHasMemberNumber) {
                                        $staffQuery->orWhere('no_anggota', 'like', "%{$search}%");
                                    }
                                });
                        });
                });
            })
            ->latest('transacted_at')
            ->latest();

        if ($auth['role'] === 'ahli') {
            $transactions->where('member_type', 'student')->where('member_id', $auth['user']->getKey());
        } elseif ($auth['role'] === 'staff' && $showOwnTransactions) {
            $transactions->where('member_type', 'staff')->where('member_id', $auth['user']->getKey());
        }

        return view('koperasi.transactions.index', [
            ...$auth,
            'transactions' => $transactions->paginate(30)->withQueryString(),
            'canViewAllTransactions' => $canViewAllTransactions,
            'showOwnTransactions' => $showOwnTransactions,
            'filters' => [
                'search' => $search,
                'type' => in_array($type, $allowedTypes, true) ? $type : '',
                'direction' => in_array($direction, $allowedDirections, true) ? $direction : '',
                'member_type' => $canViewAllTransactions && in_array($memberType, $allowedMemberTypes, true) ? $memberType : '',
                'scope' => $showOwnTransactions ? 'mine' : 'all',
            ],
        ]);
    }

    public function settings(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $this->seedDefaultSettings();

        return view('admin.settings.index', [
            ...$auth,
            'settings' => CooperativeSetting::query()->orderBy('key')->get(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($validated['settings'] as $key => $value) {
            CooperativeSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        $this->audit($request, $auth, 'update', 'settings', null, 'Tetapan koperasi dikemaskini.');

        return back()->with('status', 'Tetapan koperasi berjaya dikemaskini.');
    }

    public function notifications(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff', 'ahli']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,unread,read'],
            'period' => ['nullable', 'in:all,today,week,month'],
        ]);
        $filters['status'] = $filters['status'] ?? 'all';
        $filters['period'] = $filters['period'] ?? 'all';

        $notificationQuery = CooperativeNotification::query()
            ->where('recipient_role', $auth['role'])
            ->where('recipient_id', $auth['user']->getKey());

        $summary = [
            'total' => (clone $notificationQuery)->count(),
            'unread' => (clone $notificationQuery)->whereNull('read_at')->count(),
            'read' => (clone $notificationQuery)->whereNotNull('read_at')->count(),
        ];

        if ($filters['search'] ?? null) {
            $search = trim($filters['search']);
            $notificationQuery->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        match ($filters['status']) {
            'unread' => $notificationQuery->whereNull('read_at'),
            'read' => $notificationQuery->whereNotNull('read_at'),
            default => null,
        };

        match ($filters['period']) {
            'today' => $notificationQuery->whereDate('created_at', today()),
            'week' => $notificationQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $notificationQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
            default => null,
        };

        return view('koperasi.notifications.index', [
            ...$auth,
            'filters' => $filters,
            'summary' => $summary,
            'notifications' => $notificationQuery
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function markNotificationsRead(Request $request): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff', 'ahli']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'notification_id' => ['nullable', 'integer'],
        ]);

        $query = CooperativeNotification::query()
            ->where('recipient_role', $auth['role'])
            ->where('recipient_id', $auth['user']->getKey())
            ->whereNull('read_at');

        if (! empty($validated['notification_id'])) {
            $query->whereKey($validated['notification_id']);
        }

        $query->update(['read_at' => now()]);

        return back()->with('status', 'Notifikasi ditanda sudah dibaca.');
    }

    public function staffWorkflow(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['staff']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        return view('staff.workflow.index', [
            ...$auth,
            'applications' => Permohonan::query()->latest('tarikh_permohonan')->latest('id_permohonan')->limit(10)->get(),
            'pendingDocuments' => DocumentUpload::query()->latest()->limit(10)->get(),
            'todayOrders' => DB::table('tempahan')->latest($this->orderDateColumn())->limit(10)->get(),
        ]);
    }

    private function requireRole(Request $request, array $allowed): array|RedirectResponse
    {
        $role = $request->session()->get('auth_role');

        if (! in_array($role, $allowed, true)) {
            return redirect()->route('auth.dashboard')->withErrors(['access' => 'Anda tidak dibenarkan membuka page ini.']);
        }

        $user = match ($role) {
            'admin' => AdminUser::query()->find($request->session()->get('auth_id')),
            'staff' => Pekerja::query()->find($request->session()->get('auth_id')),
            'ahli' => Ahli::query()->find($request->session()->get('auth_id')),
            default => null,
        };

        if (! $user) {
            $request->session()->flush();

            return redirect()->route('login');
        }

        return compact('role', 'user');
    }

    private function audit(Request $request, array $auth, string $action, string $module, ?object $subject = null, ?string $description = null, array $meta = []): void
    {
        AuditLog::query()->create([
            'actor_role' => $auth['role'],
            'actor_id' => $auth['user']->getKey(),
            'action' => $action,
            'module' => $module,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'meta' => $meta ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
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

    private function clearUploadedPromptItem(Request $request, string $category): void
    {
        $prompt = $request->session()->get('document_upload_prompt');

        if (! is_array($prompt) || empty($prompt['documents']) || ! is_array($prompt['documents'])) {
            return;
        }

        $prompt['documents'] = array_values(array_filter(
            $prompt['documents'],
            fn ($item) => ($item['category'] ?? null) !== $category
        ));

        if ($prompt['documents'] === []) {
            $request->session()->forget('document_upload_prompt');

            return;
        }

        $request->session()->put('document_upload_prompt', $prompt);
    }

    private function documentCategories(): array
    {
        return [
            'slip_bayaran' => 'Slip / Bukti Bayaran',
            'salinan_ic' => 'Salinan IC',
            'surat_pindah_berhenti_persaraan' => 'Salinan Surat Pindah / Berhenti / Kad Persaraan',
            'tambah_saham' => 'Dokumen Tambah Saham',
            'pengeluaran_saham' => 'Dokumen Pengeluaran Saham',
            'surat_berhenti' => 'Surat Berhenti Keahlian',
            'surat_pindah' => 'Surat Pindah',
            'kad_surat_persaraan' => 'Kad / Surat Persaraan',
            'surat_tamat_pengajian' => 'Surat Tamat Pengajian',
            'dokumen_sokongan_lain' => 'Dokumen Sokongan Lain',
        ];
    }

    private function defaultDocumentPurpose(string $category): string
    {
        return match ($category) {
            'slip_bayaran', 'tambah_saham' => 'Permohonan Anggota / Penambahan Saham',
            'salinan_ic' => 'Permohonan Anggota / Pengeluaran',
            'pengeluaran_saham' => 'Permohonan Pengeluaran Saham / Berhenti',
            'surat_pindah_berhenti_persaraan',
            'surat_berhenti',
            'surat_pindah',
            'kad_surat_persaraan',
            'surat_tamat_pengajian' => 'Permohonan Berhenti / Pindah / Bersara',
            default => 'Dokumen Koperasi',
        };
    }

    private function assertOwnerExists(string $role, int $id): void
    {
        $exists = $role === 'staff'
            ? Pekerja::query()->whereKey($id)->exists()
            : Ahli::query()->whereKey($id)->exists();

        abort_unless($exists, 422, 'Pemilik dokumen tidak wujud.');
    }

    private function resolveOwnedApplication(?int $applicationId, string $ownerRole, Ahli|Pekerja $owner): ?Permohonan
    {
        if (! $applicationId) {
            return null;
        }

        $application = Permohonan::query()->findOrFail($applicationId);
        $isOwned = $ownerRole === 'staff'
            ? $application->id_ahli === null
                && ($application->data_permohonan['pemohon_role'] ?? null) === 'staff'
                && $application->no_matrik === $owner->no_pekerja
            : (int) $application->id_ahli === (int) $owner->getKey();

        abort_unless($isOwned, 403, 'Permohonan ini bukan milik akaun anda.');

        return $application;
    }

    private function canAccessDocument(array $auth, DocumentUpload $document, bool $allowShareManager = true): bool
    {
        if ($auth['role'] === 'admin') {
            return true;
        }

        if ($allowShareManager
            && $auth['role'] === 'staff'
            && ($auth['user']->staff_type ?? null) === Pekerja::SHARE_MANAGER_STAFF_TYPE
            && ($auth['user']->status_aktif ?? false)) {
            return true;
        }

        return $document->owner_role === $auth['role'] && (int) $document->owner_id === (int) $auth['user']->getKey();
    }

    private function seedDefaultSettings(): void
    {
        $defaults = [
            'membership_fee' => ['value' => '10.00', 'type' => 'money', 'label' => 'Yuran Anggota'],
            'minimum_initial_share' => ['value' => '10.00', 'type' => 'money', 'label' => 'Saham Minimum'],
            'cheque_processing_fee' => ['value' => '0.00', 'type' => 'money', 'label' => 'Caj Cek'],
            'allowed_payment_methods' => ['value' => 'Online', 'type' => 'string', 'label' => 'Kaedah Bayaran'],
            'maximum_upload_size' => ['value' => '5120', 'type' => 'number', 'label' => 'Saiz Upload Maksimum KB'],
        ];

        foreach ($defaults as $key => $data) {
            CooperativeSetting::query()->firstOrCreate(['key' => $key], $data);
        }
    }

    private function orderDateColumn(): string
    {
        return Schema::hasColumn('tempahan', 'tarikh_tempahan') ? 'tarikh_tempahan' : 'created_at';
    }
}
