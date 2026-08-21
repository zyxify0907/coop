<?php

namespace App\Http\Controllers;

use App\Models\CooperativeNotification;
use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSetting;
use App\Models\Pekerja;
use App\Models\Permohonan;
use App\Models\ShareTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
            if ($orders instanceof \Illuminate\Support\Collection) {
                return 0;
            }

            return (clone $orders)
                ->whereIn(DB::raw('LOWER(status)'), $statuses)
                ->count();
        };

        $countNotPickedUp = function () use ($orders): int {
            if ($orders instanceof \Illuminate\Support\Collection) {
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
                'jumlah_tempahan' => $orders instanceof \Illuminate\Support\Collection ? 0 : (clone $orders)->count(),
                'baru' => $countStatus(['baru', 'pending']),
                'diambil' => $countStatus(['diambil', 'siap diambil']),
                'belum_ambil' => $countNotPickedUp(),
                'produk' => $products instanceof \Illuminate\Support\Collection ? 0 : (clone $products)->distinct('nama_item')->count('nama_item'),
                'stok_rendah' => $products instanceof \Illuminate\Support\Collection ? 0 : (clone $products)->where('stok_tertinggal', '<=', 5)->count(),
            ],
            'recentOrders' => $this->recentClothingOrders(),
        ]);
    }

    public function clothingOrderDashboard(Request $request): View
    {
        $user = $this->staff($request, ['sahamStaff']);
        $orders = Schema::hasTable('tempahan') ? DB::table('tempahan') : null;
        $items = Schema::hasTable('item_baju') ? DB::table('item_baju') : null;
        $orderDate = Schema::hasTable('tempahan') && Schema::hasColumn('tempahan', 'tarikh_tempahan') ? 'tarikh_tempahan' : 'created_at';

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
        abort_if($user->staff_type === 'coop_staff', 404);

        $share = $user->sahamStaff;

        return view('staff.member.shares', [
            'role' => 'staff',
            'staffType' => $user->staff_type,
            'user' => $user,
            'share' => $share,
            'totalShare' => (float) optional($share)->syer + (float) optional($share)->tambahan_saham,
            'portalPrefix' => $this->portalPrefix($user->staff_type),
        ]);
    }

    public function shareDashboard(Request $request): View
    {
        $user = $this->staff($request, ['sahamStaff']);
        abort_if($user->staff_type === 'coop_staff', 404);

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
            'portalPrefix' => $this->portalPrefix($user->staff_type),
            'recentTransactions' => $recentTransactions,
            'latestApplications' => $latestApplications,
        ]);
    }

    public function profile(Request $request): View
    {
        $user = $this->staff($request, ['sahamStaff']);

        return view('staff.member.profile', [
            'role' => 'staff',
            'staffType' => $user->staff_type,
            'user' => $user,
            'share' => $user->sahamStaff,
            'portalPrefix' => $this->portalPrefix($user->staff_type),
        ]);
    }

    private function memberDashboard(Request $request, string $type): View
    {
        $user = $this->staff($request, ['sahamStaff']);
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
            'applications' => $applications,
            'notifications' => $notifications,
            'home' => $this->staffHomeData($request, $user, $type, $applications, $notifications, $attendanceRecord),
            'portalPrefix' => $this->portalPrefix($type),
            'portalTitle' => $type === 'lecturer_member' ? 'Dashboard Anggota Staf' : 'Dashboard Pekerja Koperasi',
            'portalDescription' => $type === 'lecturer_member'
                ? 'Maklumat keanggotaan dan saham koperasi anda.'
                : 'Pusat maklumat kerja harian dan kehadiran koperasi.',
            'attendanceRecord' => $attendanceRecord,
            'attendanceEnabled' => $type === 'coop_staff' && (bool) AttendanceSetting::query()->value('status'),
        ]);
    }

    private function staff(Request $request, array $with = []): Pekerja
    {
        abort_unless($request->session()->get('auth_role') === 'staff', 403);

        return Pekerja::query()->with($with)->findOrFail($request->session()->get('auth_id'));
    }

    private function staffHomeData(Request $request, Pekerja $staff, string $type, ?\Illuminate\Support\Collection $applications = null, ?\Illuminate\Support\Collection $notifications = null, ?AttendanceRecord $attendanceRecord = null): array
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

        $share = $type === 'coop_staff' ? null : $staff->sahamStaff;
        $totalShare = (float) optional($share)->syer + (float) optional($share)->tambahan_saham;
        $pendingApplications = $applications->whereIn('status', ['baru', 'semak', 'pending', 'dalam_semakan'])->count();
        $unreadNotifications = $notifications->whereNull('read_at')->count();
        $clothingOrders = $type === 'clothing_staff' && Schema::hasTable('tempahan')
            ? DB::table('tempahan')->whereIn(DB::raw('LOWER(status)'), ['baru', 'pending', 'belum_ambil', 'belum ambil'])->count()
            : 0;

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
                'time' => isset($order->tarikh_tempahan) ? \Carbon\Carbon::parse($order->tarikh_tempahan)->diffForHumans() : '-',
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
            'notifications' => $type === 'coop_staff'
                ? [
                    ['tone' => 'blue', 'count' => $attendanceRecord ? 1 : 0, 'label' => $attendanceRecord ? 'rekod kehadiran hari ini telah direkodkan' : 'belum check in hari ini', 'route' => route('coop-staff.attendance.index')],
                    ['tone' => 'yellow', 'count' => $unreadNotifications, 'label' => 'notifikasi belum dibaca', 'route' => route('koperasi.notifications.index')],
                    ['tone' => 'green', 'count' => $attendanceRecord?->check_out_time ? 1 : 0, 'label' => $attendanceRecord?->check_out_time ? 'check out hari ini selesai' : 'check out belum direkodkan', 'route' => route('coop-staff.attendance.index')],
                ]
                : [
                    ['tone' => 'blue', 'count' => $pendingApplications, 'label' => 'permohonan anda masih menunggu semakan', 'route' => route($this->portalPrefix($type).'.permohonan.index')],
                    ['tone' => 'green', 'count' => $share ? 1 : 0, 'label' => $share ? 'jumlah saham semasa RM '.number_format($totalShare, 2) : 'rekod saham belum diwujudkan', 'route' => route($this->portalPrefix($type).'.shares')],
                    ['tone' => 'yellow', 'count' => $unreadNotifications, 'label' => 'notifikasi belum dibaca', 'route' => route($type === 'clothing_staff' ? 'clothing-staff.notifications.index' : 'koperasi.notifications.index')],
                    ['tone' => 'blue', 'count' => $clothingOrders, 'label' => $type === 'clothing_staff' ? 'tempahan baju perlu tindakan' : 'akses perkhidmatan koperasi tersedia', 'route' => $type === 'clothing_staff' ? route('clothing-staff.orders.index') : route($this->portalPrefix($type).'.profile')],
                ],
            'messages' => $notifications,
            'recent_activities' => $activities->take(6)->values(),
        ];
    }

    private function portalPrefix(string $type): string
    {
        return match ($type) {
            'lecturer_member' => 'lecturer-member',
            'clothing_staff' => 'clothing-staff',
            default => 'coop-staff',
        };
    }

    private function recentClothingOrders(): \Illuminate\Support\Collection
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
