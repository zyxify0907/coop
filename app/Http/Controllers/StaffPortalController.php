<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSetting;
use App\Models\CooperativeNotification;
use App\Models\Pekerja;
use App\Models\Permohonan;
use App\Models\ShareTransaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffPortalController extends Controller
{
    public function lecturerDashboard(Request $request): View
    {
        return $this->memberDashboard($request, 'lecturer_member');
    }

    public function cooperativeDashboard(Request $request): View
    {
        return $this->memberDashboard($request, 'coop_staff');
    }

    public function clothingDashboard(Request $request): View
    {
        $user = $this->staff($request, ['sahamStaff']);
        $orders = Schema::hasTable('tempahan') ? DB::table('tempahan') : collect();

        $countStatus = function (array $statuses) use ($orders): int {
            if ($orders instanceof Collection) {
                return 0;
            }

            return (clone $orders)
                ->whereIn(DB::raw('LOWER(status)'), $statuses)
                ->count();
        };

        $countNotPickedUp = function () use ($orders): int {
            if ($orders instanceof Collection) {
                return 0;
            }

            return (clone $orders)
                ->whereNotIn(DB::raw('LOWER(status)'), ['diambil', 'siap diambil'])
                ->count();
        };

        $products = Schema::hasTable('item_baju') ? DB::table('item_baju') : collect();

        return view('dashboard.clothing-staff', [
            'role' => 'staff',
            'staffType' => 'clothing_staff',
            'user' => $user,
            'share' => $user->sahamStaff,
            'home' => $this->staffHomeData($request, $user, 'clothing_staff'),
            'stats' => [
                'jumlah_tempahan' => $orders instanceof Collection ? 0 : (clone $orders)->count(),
                'baru' => $countStatus(['baru', 'pending']),
                'diambil' => $countStatus(['diambil', 'siap diambil']),
                'belum_ambil' => $countNotPickedUp(),
                'produk' => $products instanceof Collection ? 0 : (clone $products)->distinct('nama_item')->count('nama_item'),
                'stok_rendah' => $products instanceof Collection ? 0 : (clone $products)->where('stok_tertinggal', '<=', 5)->count(),
            ],
            'recentOrders' => $this->recentClothingOrders(),
        ]);
    }

    public function shareManagerDashboard(Request $request): View
    {
        return $this->memberDashboard($request, Pekerja::SHARE_MANAGER_STAFF_TYPE);
    }

    public function coopManagerDashboard(Request $request): View
    {
        return $this->memberDashboard($request, Pekerja::COOP_MANAGER_STAFF_TYPE);
    }

    public function clothingOrderDashboard(Request $request): View
    {
        $user = $this->staff($request, ['sahamStaff']);
        $orders = Schema::hasTable('tempahan') ? DB::table('tempahan') : null;
        $items = Schema::hasTable('item_baju') ? DB::table('item_baju') : null;
        $orderDate = Schema::hasTable('tempahan') && Schema::hasColumn('tempahan', 'tarikh_tempahan') ? 'tarikh_tempahan' : 'created_at';
        $popularSizes = collect(['S', 'M', 'L', 'XL', 'XXL'])->map(fn ($size) => [
            'label' => $size,
            'value' => 0,
        ])->keyBy('label');

        if ($orders) {
            $sizeQuery = null;

            if (Schema::hasColumn('tempahan', 'saiz') || Schema::hasColumn('tempahan', 'size')) {
                $sizeColumn = Schema::hasColumn('tempahan', 'saiz') ? 'tempahan.saiz' : 'tempahan.size';
                $quantityColumn = Schema::hasColumn('tempahan', 'kuantiti')
                    ? 'tempahan.kuantiti'
                    : (Schema::hasColumn('tempahan', 'quantity') ? 'tempahan.quantity' : null);
                $valueExpression = $quantityColumn ? "COALESCE(SUM({$quantityColumn}), 0)" : 'COUNT(*)';

                $sizeQuery = DB::table('tempahan')
                    ->selectRaw("UPPER(COALESCE({$sizeColumn}, 'LAIN')) as label, {$valueExpression} as value")
                    ->groupBy('label');
            } elseif (
                Schema::hasTable('item_tempahan')
                && Schema::hasTable('item_baju')
                && Schema::hasColumn('item_tempahan', 'id_tempahan')
                && Schema::hasColumn('item_tempahan', 'id_item')
                && Schema::hasColumn('item_baju', 'saiz')
            ) {
                $tempahanKey = Schema::hasColumn('tempahan', 'id_tempahan') ? 'id_tempahan' : (Schema::hasColumn('tempahan', 'tempahan_id') ? 'tempahan_id' : null);
                $quantityColumn = Schema::hasColumn('item_tempahan', 'kuantiti')
                    ? 'item_tempahan.kuantiti'
                    : (Schema::hasColumn('item_tempahan', 'quantity') ? 'item_tempahan.quantity' : null);
                $valueExpression = $quantityColumn ? "COALESCE(SUM({$quantityColumn}), 0)" : 'COUNT(*)';

                if ($tempahanKey) {
                    $sizeQuery = DB::table('tempahan')
                        ->join('item_tempahan', "tempahan.{$tempahanKey}", '=', 'item_tempahan.id_tempahan')
                        ->leftJoin('item_baju', 'item_tempahan.id_item', '=', 'item_baju.id_item')
                        ->selectRaw("UPPER(COALESCE(item_baju.saiz, 'LAIN')) as label, {$valueExpression} as value")
                        ->groupBy('label');
                }
            }

            $sizeQuery?->get()->each(function ($row) use ($popularSizes): void {
                $label = strtoupper((string) $row->label);
                $popularSizes->put($label, [
                    'label' => $label,
                    'value' => (int) $row->value,
                ]);
            });
        }

        $stockByItem = $items
            ? DB::table('item_baju')
                ->selectRaw('nama_item as label, COALESCE(SUM(stok_tertinggal), 0) as value, SUM(CASE WHEN stok_tertinggal <= 5 THEN 1 ELSE 0 END) as low_count')
                ->groupBy('nama_item')
                ->orderByDesc('value')
                ->limit(8)
                ->get()
                ->map(fn ($item) => [
                    'label' => $item->label ?? 'Item Baju',
                    'value' => (int) $item->value,
                    'low_count' => (int) $item->low_count,
                ])
                ->all()
            : [];

        return view('admin.dashboards.baju', [
            'role' => 'staff',
            'staffType' => 'clothing_staff',
            'user' => $user,
            'ordersRoute' => route('clothing-staff.orders.index'),
            'stockRoute' => route('clothing-staff.baju.index'),
            'summary' => [
                'total_orders' => $orders ? (clone $orders)->count() : 0,
                'new_orders' => $orders ? (clone $orders)->whereIn(DB::raw('LOWER(status)'), ['baru', 'pending'])->count() : 0,
                'pickup_orders' => $orders ? (clone $orders)->whereIn(DB::raw('LOWER(status)'), ['belum_ambil', 'belum ambil', 'diproses', 'dalam proses', 'siap', 'menunggu pengambilan', 'menunggu_ambil'])->count() : 0,
                'completed_orders' => $orders ? (clone $orders)->whereIn(DB::raw('LOWER(status)'), ['sudah_ambil', 'sudah ambil', 'diambil', 'siap diambil', 'selesai'])->count() : 0,
                'stock_total' => $items ? (int) (clone $items)->sum('stok_tertinggal') : 0,
                'low_stock' => $items ? (clone $items)->where('stok_tertinggal', '<=', 5)->count() : 0,
                'sales_total' => $orders && Schema::hasColumn('tempahan', 'jumlah_total') ? (float) (clone $orders)->sum('jumlah_total') : 0.0,
            ],
            'charts' => [
                'orders' => [
                    ['label' => 'Baru', 'value' => $orders ? (clone $orders)->whereIn(DB::raw('LOWER(status)'), ['baru', 'pending'])->count() : 0],
                    ['label' => 'Belum Ambil', 'value' => $orders ? (clone $orders)->whereIn(DB::raw('LOWER(status)'), ['belum_ambil', 'belum ambil', 'diproses', 'dalam proses', 'siap', 'menunggu pengambilan', 'menunggu_ambil'])->count() : 0],
                    ['label' => 'Sudah Ambil', 'value' => $orders ? (clone $orders)->whereIn(DB::raw('LOWER(status)'), ['sudah_ambil', 'sudah ambil', 'diambil', 'siap diambil', 'selesai'])->count() : 0],
                ],
                'stock' => [
                    ['label' => 'Stok Semasa', 'value' => $items ? (int) (clone $items)->sum('stok_tertinggal') : 0],
                    ['label' => 'Stok Rendah', 'value' => $items ? (clone $items)->where('stok_tertinggal', '<=', 5)->count() : 0],
                ],
                'stockByItem' => $stockByItem,
                'popularSizes' => $popularSizes->values()->all(),
            ],
            'recentOrders' => $orders
                ? DB::table('tempahan')
                    ->leftJoin('ahli', 'tempahan.id_ahli', '=', 'ahli.id_ahli')
                    ->select('tempahan.*', 'ahli.nama', 'ahli.no_matrik')
                    ->orderByDesc("tempahan.{$orderDate}")
                    ->limit(8)
                    ->get()
                : collect(),
            'lowStockItems' => $items
                ? DB::table('item_baju')->where('stok_tertinggal', '<=', 5)->orderBy('stok_tertinggal')->limit(8)->get()
                : collect(),
        ]);
    }

    public function shares(Request $request): View
    {
        $user = $this->staff($request, ['sahamStaff']);
        abort_unless($user->isEligibleForShares(), 404);
        $memberNumber = $this->ensureStaffMemberNumber($user);

        $share = $user->sahamStaff;

        return view('staff.member.shares', [
            'role' => 'staff',
            'staffType' => $user->staff_type,
            'user' => $user,
            'share' => $share,
            'staffMemberNumber' => $memberNumber,
            'totalShare' => (float) optional($share)->syer + (float) optional($share)->tambahan_saham,
            'portalPrefix' => $this->portalPrefix($user->staff_type),
        ]);
    }

    public function shareDashboard(Request $request): View
    {
        $user = $this->staff($request, ['sahamStaff']);
        abort_unless($user->isEligibleForShares(), 404);
        $memberNumber = $this->ensureStaffMemberNumber($user);

        $recentTransactions = Schema::hasTable('share_transactions')
            ? ShareTransaction::query()
                ->where('member_type', 'staff')
                ->where('member_id', $user->id_pekerja)
                ->latest('transacted_at')
                ->latest()
                ->limit(6)
                ->get()
            : collect();
        $latestApplications = Permohonan::query()
            ->whereNull('id_ahli')
            ->where('no_matrik', $user->no_pekerja)
            ->whereIn('jenis', ['anggota', 'saham', 'berhenti', 'pengeluaran', 'pindah', 'bersara'])
            ->latest('tarikh_permohonan')
            ->latest('id_permohonan')
            ->limit(5)
            ->get();

        return view('dashboard.staff-saham', [
            'role' => 'staff',
            'staffType' => $user->staff_type,
            'user' => $user,
            'share' => $user->sahamStaff,
            'staffMemberNumber' => $memberNumber,
            'portalPrefix' => $this->portalPrefix($user->staff_type),
            'recentTransactions' => $recentTransactions,
            'latestApplications' => $latestApplications,
        ]);
    }

    public function profile(Request $request): View
    {
        $user = $this->staff($request, ['sahamStaff']);
        $memberNumber = $this->ensureStaffMemberNumber($user);
        $latestApplications = Permohonan::query()
            ->whereNull('id_ahli')
            ->where('no_matrik', $user->no_pekerja)
            ->latest('tarikh_permohonan')
            ->latest('id_permohonan')
            ->limit(5)
            ->get();

        $recentTransactions = Schema::hasTable('share_transactions')
            ? ShareTransaction::query()
                ->where('member_type', 'staff')
                ->where('member_id', $user->id_pekerja)
                ->latest('transacted_at')
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        $attendanceRecords = Schema::hasTable('attendance_records') && $user->staff_type === 'coop_staff'
            ? AttendanceRecord::query()
                ->where('staff_id', $user->id_pekerja)
                ->latest('attendance_date')
                ->limit(5)
                ->get()
            : collect();

        return view('staff.member.profile', [
            'role' => 'staff',
            'staffType' => $user->staff_type,
            'user' => $user,
            'share' => $user->sahamStaff,
            'staffMemberNumber' => $memberNumber,
            'portalPrefix' => $this->portalPrefix($user->staff_type),
            'latestApplications' => $latestApplications,
            'recentTransactions' => $recentTransactions,
            'attendanceRecords' => $attendanceRecords,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $this->staff($request);

        $validated = $request->validate([
            'no_tel' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('pekerja', 'email')->ignore($user->id_pekerja, 'id_pekerja')],
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

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $this->staff($request);

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

    private function memberDashboard(Request $request, string $type): View
    {
        $user = $this->staff($request, ['sahamStaff']);
        $memberNumber = $this->ensureStaffMemberNumber($user);
        $share = $user->sahamStaff;
        $applications = Permohonan::query()
            ->whereNull('id_ahli')
            ->where('no_matrik', $user->no_pekerja)
            ->latest('tarikh_permohonan')
            ->limit(5)
            ->get();
        $notifications = CooperativeNotification::query()
            ->where('recipient_role', 'staff')
            ->where('recipient_id', $user->id_pekerja)
            ->latest()
            ->limit(5)
            ->get();
        $attendanceRecord = $type === 'coop_staff'
            ? AttendanceRecord::query()->where('staff_id', $user->id_pekerja)->whereDate('attendance_date', today())->first()
            : null;

        return view('dashboard.staff-member', [
            'role' => 'staff',
            'staffType' => $type,
            'user' => $user,
            'share' => $share,
            'staffMemberNumber' => $memberNumber,
            'applications' => $applications,
            'notifications' => $notifications,
            'home' => $this->staffHomeData($request, $user, $type, $applications, $notifications, $attendanceRecord),
            'portalPrefix' => $this->portalPrefix($type),
            'portalTitle' => match ($type) {
                'lecturer_member' => 'Dashboard Anggota Staf',
                Pekerja::SHARE_MANAGER_STAFF_TYPE => 'Dashboard Staff Pengurus Saham',
                Pekerja::COOP_MANAGER_STAFF_TYPE => 'Dashboard Staff Pengurus Pekerja Koperasi',
                default => 'Dashboard Pekerja Koperasi',
            },
            'portalDescription' => match ($type) {
                'lecturer_member', Pekerja::SHARE_MANAGER_STAFF_TYPE, Pekerja::COOP_MANAGER_STAFF_TYPE => 'Maklumat keanggotaan dan saham koperasi anda.',
                default => 'Pusat maklumat kerja harian dan kehadiran koperasi.',
            },
            'attendanceRecord' => $attendanceRecord,
            'attendanceEnabled' => $type === 'coop_staff' && (bool) AttendanceSetting::query()->value('status'),
        ]);
    }

    private function staff(Request $request, array $with = []): Pekerja
    {
        abort_unless($request->session()->get('auth_role') === 'staff', 403);

        return Pekerja::query()->with($with)->findOrFail($request->session()->get('auth_id'));
    }

    private function ensureStaffMemberNumber(Pekerja $staff): ?string
    {
        if (! $staff->isEligibleForShares()) {
            return null;
        }

        if (Schema::hasColumn('pekerja', 'no_anggota') && filled($staff->no_anggota)) {
            return $staff->no_anggota;
        }

        return DB::transaction(function () use ($staff): ?string {
            $staff->refresh();

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

            $memberNumber = $approvedApplication->data_permohonan['no_anggota'] ?? null;

            if (! $memberNumber && ($approvedApplication || $staff->sahamStaff)) {
                $memberNumber = $this->nextStaffMemberNumber();
            }

            if (! $memberNumber) {
                return null;
            }

            if (Schema::hasColumn('pekerja', 'no_anggota')) {
                $staff->update(['no_anggota' => $memberNumber]);
            }

            if ($approvedApplication) {
                $data = $approvedApplication->data_permohonan ?? [];
                $data['no_anggota'] = $memberNumber;
                $approvedApplication->update(['data_permohonan' => $data]);
            }

            return $memberNumber;
        });
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

    private function passwordMatches(Pekerja $user, string $password): bool
    {
        $storedHash = (string) $user->password_hash;

        if ($storedHash === '') {
            return false;
        }

        if (str_starts_with($storedHash, '$2a$')) {
            $storedHash = '$2y$'.substr($storedHash, 4);
        }

        return password_verify($password, $storedHash) || Hash::check($password, (string) $user->password_hash);
    }

    private function staffHomeData(Request $request, Pekerja $staff, string $type, ?Collection $applications = null, ?Collection $notifications = null, ?AttendanceRecord $attendanceRecord = null): array
    {
        $applications ??= Permohonan::query()
            ->whereNull('id_ahli')
            ->where('no_matrik', $staff->no_pekerja)
            ->latest('tarikh_permohonan')
            ->limit(5)
            ->get();

        $notifications ??= Schema::hasTable('notifications')
            ? CooperativeNotification::query()
                ->where('recipient_role', 'staff')
                ->where('recipient_id', $staff->id_pekerja)
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        $activities = collect();
        if ($type !== 'coop_staff') {
            $applications->take(3)->each(fn (Permohonan $application) => $activities->push([
                'name' => 'Permohonan '.ucwords(str_replace('_', ' ', $application->jenis)),
                'user' => ucfirst(str_replace('_', ' ', $application->status)),
                'time' => $application->tarikh_permohonan?->diffForHumans() ?? '-',
            ]));
        }
        $notifications->take(3)->each(fn (CooperativeNotification $notification) => $activities->push([
            'name' => $notification->title,
            'user' => 'Notifikasi',
            'time' => $notification->created_at?->diffForHumans() ?? '-',
        ]));

        if ($type === 'clothing_staff') {
            $this->recentClothingOrders()->take(3)->each(fn ($order) => $activities->push([
                'name' => 'Tempahan baju '.($order->nama ?? $order->no_matrik ?? 'pelajar'),
                'user' => ucfirst(str_replace('_', ' ', (string) ($order->status ?? 'baru'))),
                'time' => isset($order->tarikh_tempahan) ? Carbon::parse($order->tarikh_tempahan)->diffForHumans() : '-',
            ]));
        }

        if ($type === 'coop_staff' && Schema::hasTable('attendance_records')) {
            AttendanceRecord::query()
                ->where('staff_id', $staff->id_pekerja)
                ->when($attendanceRecord, fn ($query) => $query->whereDate('attendance_date', '!=', today()))
                ->latest('attendance_date')
                ->latest()
                ->limit(5)
                ->get()
                ->each(fn (AttendanceRecord $record) => $activities->push([
                    'name' => $record->check_out_time ? 'Check out direkodkan' : 'Check in direkodkan',
                    'user' => $record->attendance_date?->format('d/m/Y') ?? 'Kehadiran',
                    'time' => $record->updated_at?->diffForHumans() ?? '-',
                ]));
        }

        if ($attendanceRecord) {
            $activities->prepend([
                'name' => $attendanceRecord->check_out_time ? 'Kehadiran hari ini selesai' : 'Check in hari ini direkodkan',
                'user' => $staff->nama,
                'time' => $attendanceRecord->updated_at?->diffForHumans() ?? '-',
            ]);
        }

        return [
            'last_login_at' => $request->session()->get('last_login_at'),
            'announcements' => Announcement::query()->visibleTo('staff')->orderByDesc('is_pinned')->latest()->limit(4)->get(),
            'messages' => $notifications,
            'recent_activities' => $activities->take(6)->values(),
        ];
    }

    private function portalPrefix(string $type): string
    {
        return match ($type) {
            'lecturer_member' => 'lecturer-member',
            'clothing_staff' => 'clothing-staff',
            Pekerja::SHARE_MANAGER_STAFF_TYPE => 'share-staff',
            Pekerja::COOP_MANAGER_STAFF_TYPE => 'coop-manager',
            default => 'coop-staff',
        };
    }

    private function recentClothingOrders(): Collection
    {
        if (! Schema::hasTable('tempahan')) {
            return collect();
        }

        if (Schema::hasColumn('tempahan', 'id_ahli')) {
            return DB::table('tempahan')
                ->leftJoin('ahli', 'tempahan.id_ahli', '=', 'ahli.id_ahli')
                ->select('tempahan.*', 'ahli.nama', 'ahli.no_matrik')
                ->latest('tempahan.tarikh_tempahan')
                ->limit(6)
                ->get();
        }

        return DB::table('tempahan')->latest()->limit(6)->get();
    }
}
