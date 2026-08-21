<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Ahli;
use App\Models\AuditLog;
use App\Models\Jualan;
use App\Models\Pekerja;
use App\Models\Pembayaran;
use App\Models\Permohonan;
use App\Models\Saham;
use App\Models\SahamStaff;
use App\Models\ShareTransaction;
use App\Models\Stok;
use App\Models\Tempahan;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperationsController extends Controller
{
    public function shareDashboard(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $studentTotal = (float) Saham::query()->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')->value('total');
        $staffTotal = Schema::hasTable('saham_staff')
            ? (float) SahamStaff::query()->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')->value('total')
            : 0.0;
        $pendingStatuses = ['baru', 'semak', 'pending', 'dalam_semakan'];
        $membershipPending = Permohonan::query()->where('jenis', 'anggota')->whereIn('status', $pendingStatuses)->count();
        $shareAdditionPending = Permohonan::query()->where('jenis', 'saham')->whereIn('status', $pendingStatuses)->count();
        $shareExitPending = Permohonan::query()->whereIn('jenis', ['berhenti', 'pengeluaran', 'pindah', 'bersara'])->whereIn('status', $pendingStatuses)->count();

        return view('admin.dashboards.saham', [
            ...$auth,
            'summary' => [
                'student_members' => Saham::query()->whereRaw('(COALESCE(syer, 0) + COALESCE(tambahan_saham, 0)) > 0')->count(),
                'staff_members' => Schema::hasTable('saham_staff') ? SahamStaff::query()->whereRaw('(COALESCE(syer, 0) + COALESCE(tambahan_saham, 0)) > 0')->count() : 0,
                'student_total' => $studentTotal,
                'staff_total' => $staffTotal,
                'grand_total' => $studentTotal + $staffTotal,
                'pending_additions' => $shareAdditionPending,
                'pending_exits' => $shareExitPending,
            ],
            'recentTransactions' => Schema::hasTable('share_transactions')
                ? ShareTransaction::query()->with(['student', 'staff'])->latest('transacted_at')->latest()->limit(8)->get()
                : collect(),
            'charts' => [
                'shareTotals' => [
                    ['label' => 'Pelajar', 'value' => $studentTotal],
                    ['label' => 'Staff', 'value' => $staffTotal],
                ],
                'pending' => [
                    ['label' => 'Permohonan Anggota', 'value' => $membershipPending],
                    ['label' => 'Tambah Saham', 'value' => $shareAdditionPending],
                    ['label' => 'Berhenti / Pindah', 'value' => $shareExitPending],
                ],
            ],
            'pendingMembershipApplications' => Permohonan::query()
                ->where('jenis', 'anggota')
                ->whereIn('status', $pendingStatuses)
                ->latest('tarikh_permohonan')
                ->latest('id_permohonan')
                ->limit(5)
                ->get(),
            'pendingAdditionApplications' => Permohonan::query()
                ->where('jenis', 'saham')
                ->whereIn('status', $pendingStatuses)
                ->latest('tarikh_permohonan')
                ->latest('id_permohonan')
                ->limit(5)
                ->get(),
            'pendingExitApplications' => Permohonan::query()
                ->whereIn('jenis', ['berhenti', 'pengeluaran', 'pindah', 'bersara'])
                ->whereIn('status', $pendingStatuses)
                ->latest('tarikh_permohonan')
                ->latest('id_permohonan')
                ->limit(5)
                ->get(),
        ]);
    }

    public function clothingDashboard(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $orders = Schema::hasTable('tempahan') ? DB::table('tempahan') : null;
        $items = Schema::hasTable('item_baju') ? DB::table('item_baju') : null;
        $orderDate = Schema::hasTable('tempahan') && Schema::hasColumn('tempahan', 'tarikh_tempahan') ? 'tarikh_tempahan' : 'created_at';

        return view('admin.dashboards.baju', [
            ...$auth,
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

    public function studentOrders(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'ahli') {
            return redirect()->route('login');
        }

        $user = Ahli::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        return view('student.tempahan.index', [
            'role' => 'ahli',
            'user' => $user,
            'items' => $this->studentBajuItemsQuery()->where('item_baju.stok_tertinggal', '>', 0)->orderBy('item_baju.nama_item')->orderBy('item_baju.saiz')->get(),
            'orders' => $this->ordersQuery()->where('no_matrik', $user->no_matrik)->paginate(20),
            'cartItems' => $this->studentOrderCartItems($request),
        ]);
    }

    public function addStudentOrderCart(Request $request): RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'ahli') {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'item_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        if (! Schema::hasTable('item_baju')) {
            return back()->withErrors(['item_id' => 'Senarai baju tidak tersedia.'])->withInput();
        }

        $item = DB::table('item_baju')->where('id_item', $validated['item_id'])->first();
        abort_if(! $item, 404);

        $cart = $request->session()->get($this->studentOrderCartKey(), []);
        $itemId = (string) $item->id_item;
        $currentQuantity = (int) ($cart[$itemId]['quantity'] ?? 0);
        $nextQuantity = $currentQuantity + (int) $validated['quantity'];

        if ((int) $item->stok_tertinggal < $nextQuantity) {
            return back()->withErrors(['quantity' => 'Kuantiti dalam troli melebihi stok baju semasa.'])->withInput();
        }

        $cart[$itemId] = ['quantity' => $nextQuantity];
        $request->session()->put($this->studentOrderCartKey(), $cart);

        return redirect()->route('student.tempahan.index')->with('status', 'Baju berjaya dimasukkan ke troli.');
    }

    public function removeStudentOrderCart(Request $request, int $itemId): RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'ahli') {
            return redirect()->route('login');
        }

        $cart = $request->session()->get($this->studentOrderCartKey(), []);
        unset($cart[(string) $itemId]);
        $request->session()->put($this->studentOrderCartKey(), $cart);

        return redirect()->route('student.tempahan.index')->with('status', 'Item troli telah dibuang.');
    }

    public function checkoutStudentOrderCart(Request $request): RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'ahli') {
            return redirect()->route('login');
        }

        $user = Ahli::query()->findOrFail($request->session()->get('auth_id'));
        $cart = $request->session()->get($this->studentOrderCartKey(), []);

        if ($cart === []) {
            return back()->withErrors(['cart' => 'Troli masih kosong.']);
        }

        DB::transaction(function () use ($user, $cart): void {
            $itemIds = array_map('intval', array_keys($cart));
            $items = DB::table('item_baju')
                ->whereIn('id_item', $itemIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id_item');

            foreach ($cart as $itemId => $cartItem) {
                $item = $items->get((int) $itemId);
                $quantity = (int) ($cartItem['quantity'] ?? 0);

                if (! $item || $quantity < 1 || (int) $item->stok_tertinggal < $quantity) {
                    throw ValidationException::withMessages([
                        'cart' => 'Kuantiti dalam troli melebihi stok baju semasa.',
                    ]);
                }
            }

            $total = collect($cart)->sum(function ($cartItem, $itemId) use ($items): float {
                $item = $items->get((int) $itemId);

                return ((float) $item->harga) * (int) ($cartItem['quantity'] ?? 0);
            });

            $tempahanId = DB::table('tempahan')->insertGetId([
                'id_ahli' => $user->id_ahli,
                'tarikh_tempahan' => now()->toDateString(),
                'status' => 'baru',
                'jumlah_total' => $total,
            ]);

            foreach ($cart as $itemId => $cartItem) {
                $item = $items->get((int) $itemId);
                $quantity = (int) $cartItem['quantity'];

                DB::table('item_tempahan')->insert([
                    'id_tempahan' => $tempahanId,
                    'id_item' => $item->id_item,
                    'kuantiti' => $quantity,
                    'harga_seunit' => $item->harga,
                    'subtotal' => ((float) $item->harga) * $quantity,
                ]);

                DB::table('item_baju')->where('id_item', $item->id_item)->decrement('stok_tertinggal', $quantity);
            }
        });

        $request->session()->forget($this->studentOrderCartKey());

        return redirect()->route('student.tempahan.index')->with('status', 'Tempahan dalam troli berjaya dihantar.');
    }

    public function storeStudentOrder(Request $request): RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'ahli') {
            return redirect()->route('login');
        }

        $user = Ahli::query()->findOrFail($request->session()->get('auth_id'));
        $validated = $request->validate([
            'item_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        if (Schema::hasTable('item_baju')) {
            $item = DB::table('item_baju')->where('id_item', $validated['item_id'])->first();
            abort_if(! $item, 404);

            if ((int) $item->stok_tertinggal < $validated['quantity']) {
                return back()->withErrors(['quantity' => 'Kuantiti melebihi stok baju semasa.'])->withInput();
            }

            DB::transaction(function () use ($user, $item, $validated): void {
                $tempahanId = DB::table('tempahan')->insertGetId([
                    'id_ahli' => $user->id_ahli,
                    'tarikh_tempahan' => now()->toDateString(),
                    'status' => 'baru',
                    'jumlah_total' => ((float) $item->harga) * (int) $validated['quantity'],
                ]);

                DB::table('item_tempahan')->insert([
                    'id_tempahan' => $tempahanId,
                    'id_item' => $item->id_item,
                    'kuantiti' => $validated['quantity'],
                    'harga_seunit' => $item->harga,
                    'subtotal' => ((float) $item->harga) * (int) $validated['quantity'],
                ]);

                DB::table('item_baju')->where('id_item', $item->id_item)->decrement('stok_tertinggal', $validated['quantity']);
            });

            return redirect()->route('student.tempahan.index')->with('status', 'Tempahan baju berjaya dihantar.');
        }

        $stockKey = $this->stockKeyColumn();
        $stockQty = $this->stockQuantityColumn();
        $item = DB::table('stok')->where($stockKey, $validated['item_id'])->first();

        abort_if(! $item, 404);

        if ($item->{$stockQty} < $validated['quantity']) {
            return back()->withErrors(['quantity' => 'Kuantiti melebihi stok semasa.'])->withInput();
        }

        DB::transaction(function () use ($user, $item, $validated, $stockKey): void {
            if ($this->hasColumn('tempahan', 'no_matrik')) {
                DB::table('tempahan')->insert([
                    'no_matrik' => $user->no_matrik,
                    'item_id' => $item->{$stockKey},
                    'item' => $item->nama_item,
                    'quantity' => $validated['quantity'],
                    'status' => 'baru',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return;
            }

            $tempahanId = DB::table('tempahan')->insertGetId([
                'id_ahli' => $user->id_ahli,
                'tarikh_tempahan' => now()->toDateString(),
                'status' => 'baru',
                'jumlah_total' => ((float) ($item->harga_jual ?? $item->harga ?? 0)) * (int) $validated['quantity'],
            ]);

            if (Schema::hasTable('item_tempahan')) {
                DB::table('item_tempahan')->insert([
                    'id_tempahan' => $tempahanId,
                    'id_item' => $item->{$stockKey},
                    'kuantiti' => $validated['quantity'],
                    'harga_seunit' => $item->harga_jual ?? $item->harga ?? 0,
                    'subtotal' => ((float) ($item->harga_jual ?? $item->harga ?? 0)) * (int) $validated['quantity'],
                ]);
            }
        });

        return redirect()->route('student.tempahan.index')->with('status', 'Tempahan berjaya dihantar.');
    }

    public function tempahan(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:baru,belum_ambil,sudah_ambil'],
        ]);

        $ordersQuery = $this->ordersQuery();
        $search = trim((string) ($validated['search'] ?? ''));

        if ($search !== '') {
            $hasTempahanNoMatrik = $this->hasColumn('tempahan', 'no_matrik');

            $ordersQuery->where(function ($query) use ($search, $hasTempahanNoMatrik): void {
                $query
                    ->where('ahli.nama', 'like', "%{$search}%")
                    ->orWhere('ahli.no_matrik', 'like', "%{$search}%");

                if ($hasTempahanNoMatrik) {
                    $query->orWhere('tempahan.no_matrik', 'like', "%{$search}%");
                }
            });
        }

        if (filled($validated['status'] ?? null)) {
            $ordersQuery->whereIn(DB::raw('LOWER(tempahan.status)'), $this->orderStatusAliases($validated['status']));
        }

        return view('staff.tempahan.index', [
            ...$auth,
            'orders' => $ordersQuery->paginate(20)->withQueryString(),
            'statuses' => ['baru', 'belum_ambil', 'sudah_ambil'],
        ]);
    }

    public function updateTempahan(Request $request, int $id): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'status' => ['required', 'string'],
            'tarikh_ambil' => ['nullable', 'date'],
        ]);
        $status = $this->normalizeOrderStatus($validated['status'], $validated['tarikh_ambil'] ?? null);

        if (! $status) {
            return back()->withErrors(['status' => 'Status tempahan tidak sah.']);
        }

        $payload = [
            'status' => $status,
        ];

        if ($this->hasColumn('tempahan', 'tarikh_ambil')) {
            $payload['tarikh_ambil'] = $validated['tarikh_ambil'] ?: null;
        }

        DB::table('tempahan')->where($this->orderKeyColumn(), $id)->update($payload);

        return redirect()->route($this->tempahanIndexRoute($auth))->with('status', 'Status tempahan berjaya dikemaskini.');
    }

    public function destroyTempahan(Request $request, int $id): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        try {
            DB::table('tempahan')->where($this->orderKeyColumn(), $id)->delete();
        } catch (QueryException) {
            return back()->withErrors(['delete' => 'Tempahan tidak boleh dipadam kerana masih mempunyai data berkaitan.']);
        }

        return redirect()->route($this->tempahanIndexRoute($auth))->with('status', 'Tempahan berjaya dipadam.');
    }

    public function stok(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        return view('staff.stok.index', [
            ...$auth,
            'items' => $this->stockQuery()->orderBy('nama_item')->paginate(20),
        ]);
    }

    public function storeStok(Request $request): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'nama_item' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
            'harga' => ['required', 'numeric', 'min:0'],
        ]);

        DB::table('stok')->insert([
            'nama_item' => $validated['nama_item'],
            $this->stockQuantityColumn() => $validated['quantity'],
            $this->stockPriceColumn() => $validated['harga'],
            ...($this->hasColumn('stok', 'tarikh_kemaskini') ? ['tarikh_kemaskini' => now()->toDateString()] : []),
            ...($this->hasColumn('stok', 'created_at') ? ['created_at' => now(), 'updated_at' => now()] : []),
        ]);

        return redirect()->route('staff.stok.index')->with('status', 'Item stok berjaya ditambah.');
    }

    public function updateStok(Request $request, int $id): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'nama_item' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
            'harga' => ['required', 'numeric', 'min:0'],
        ]);

        DB::table('stok')->where($this->stockKeyColumn(), $id)->update([
            'nama_item' => $validated['nama_item'],
            $this->stockQuantityColumn() => $validated['quantity'],
            $this->stockPriceColumn() => $validated['harga'],
            ...($this->hasColumn('stok', 'tarikh_kemaskini') ? ['tarikh_kemaskini' => now()->toDateString()] : []),
            ...($this->hasColumn('stok', 'updated_at') ? ['updated_at' => now()] : []),
        ]);

        return redirect()->route('staff.stok.index')->with('status', 'Item stok berjaya dikemaskini.');
    }

    public function destroyStok(Request $request, int $id): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        try {
            DB::table('stok')->where($this->stockKeyColumn(), $id)->delete();
        } catch (QueryException) {
            return back()->withErrors(['delete' => 'Stok tidak boleh dipadam kerana masih mempunyai rekod jualan atau tempahan.']);
        }

        return redirect()->route('staff.stok.index')->with('status', 'Item stok berjaya dipadam.');
    }

    public function jualan(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        return view('staff.jualan.index', [
            ...$auth,
            'items' => $this->stockQuery()->orderBy('nama_item')->get(),
            'sales' => $this->salesQuery()->paginate(20),
        ]);
    }

    public function storeJualan(Request $request): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'item_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'tarikh' => ['required', 'date'],
        ]);

        $stockKey = $this->stockKeyColumn();
        $stockQty = $this->stockQuantityColumn();
        $stockPrice = $this->stockPriceColumn();

        DB::transaction(function () use ($validated): void {
            $stockKey = $this->stockKeyColumn();
            $stockQty = $this->stockQuantityColumn();
            $stockPrice = $this->stockPriceColumn();
            $item = DB::table('stok')->where($stockKey, $validated['item_id'])->lockForUpdate()->first();

            abort_if(! $item, 404);

            if ($item->{$stockQty} < $validated['quantity']) {
                abort(422, 'Kuantiti jualan melebihi stok semasa.');
            }

            $jumlah = (float) $item->{$stockPrice} * (int) $validated['quantity'];

            DB::table('jualan')->insert([
                $this->salesStockColumn() => $item->{$stockKey},
                $this->salesQuantityColumn() => $validated['quantity'],
                ...($this->hasColumn('jualan', 'harga_seunit') ? ['harga_seunit' => $item->{$stockPrice}] : []),
                'jumlah' => $jumlah,
                $this->salesDateColumn() => $validated['tarikh'],
                ...($this->hasColumn('jualan', 'created_at') ? ['created_at' => now(), 'updated_at' => now()] : []),
            ]);

            DB::table('stok')->where($stockKey, $item->{$stockKey})->decrement($stockQty, $validated['quantity']);
        });

        return redirect()->route('staff.jualan.index')->with('status', 'Jualan berjaya direkod.');
    }

    public function vendors(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['staff']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        return view('admin.vendors.index', [
            ...$auth,
            'vendors' => $this->vendorsQuery()->paginate(20),
        ]);
    }

    public function storeVendor(Request $request): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'nama_vendor' => ['required', 'string', 'max:255'],
            'no_akaun' => ['required', 'string', 'max:255'],
            'bank' => ['required', 'string', 'max:255'],
        ]);

        DB::table('vendor')->insert([
            'nama_vendor' => $validated['nama_vendor'],
            'no_akaun' => $validated['no_akaun'],
            'bank' => $validated['bank'],
            ...($this->hasColumn('vendor', 'created_at') ? ['created_at' => now(), 'updated_at' => now()] : []),
        ]);

        return redirect()->route('admin.vendors.index')->with('status', 'Vendor berjaya ditambah.');
    }

    public function updateVendor(Request $request, int $id): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'nama_vendor' => ['required', 'string', 'max:255'],
            'no_akaun' => ['required', 'string', 'max:255'],
            'bank' => ['required', 'string', 'max:255'],
        ]);

        DB::table('vendor')->where($this->vendorKeyColumn(), $id)->update([
            'nama_vendor' => $validated['nama_vendor'],
            'no_akaun' => $validated['no_akaun'],
            'bank' => $validated['bank'],
            ...($this->hasColumn('vendor', 'updated_at') ? ['updated_at' => now()] : []),
        ]);

        return redirect()->route('admin.vendors.index')->with('status', 'Vendor berjaya dikemaskini.');
    }

    public function destroyVendor(Request $request, int $id): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        try {
            DB::table('vendor')->where($this->vendorKeyColumn(), $id)->delete();
        } catch (QueryException) {
            return back()->withErrors(['delete' => 'Vendor tidak boleh dipadam kerana masih mempunyai rekod bayaran.']);
        }

        return redirect()->route('admin.vendors.index')->with('status', 'Vendor berjaya dipadam.');
    }

    public function payments(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        return view('admin.pembayaran.index', [
            ...$auth,
            'vendors' => $this->vendorsQuery()->get(),
            'payments' => $this->paymentsQuery()->paginate(20),
        ]);
    }

    public function storePayment(Request $request): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'vendor_id' => ['required', 'integer'],
            'jumlah_jualan' => ['required', 'numeric', 'min:0'],
            'komisen' => ['required', 'numeric', 'min:0'],
        ]);

        $table = $this->paymentTable();
        $bayaranAkhir = max(0, (float) $validated['jumlah_jualan'] - (float) $validated['komisen']);
        DB::table($table)->insert([
            $this->paymentVendorColumn() => $validated['vendor_id'],
            'jumlah_jualan' => $validated['jumlah_jualan'],
            $this->paymentCommissionColumn() => $validated['komisen'],
            $this->paymentFinalColumn() => $bayaranAkhir,
            ...($this->hasColumn($table, 'tarikh_bayar') ? ['tarikh_bayar' => now()->toDateString()] : []),
            ...($this->hasColumn($table, 'status') ? ['status' => 'Selesai'] : []),
            ...($this->hasColumn($table, 'created_at') ? ['created_at' => now(), 'updated_at' => now()] : []),
        ]);

        return redirect()->route('admin.pembayaran.index')->with('status', 'Pembayaran vendor berjaya direkod.');
    }

    public function adminBaju(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $itemRows = DB::table('item_baju')
            ->leftJoin('kategori_baju', 'item_baju.id_kategori', '=', 'kategori_baju.id_kategori')
            ->select([
                'item_baju.id_item',
                'item_baju.id_kategori',
                'item_baju.nama_item',
                'item_baju.saiz',
                'item_baju.harga',
                'item_baju.stok_tertinggal',
                'item_baju.image_path',
                'kategori_baju.nama_kategori',
            ])
            ->orderBy('item_baju.nama_item')
            ->orderBy('item_baju.saiz')
            ->get();

        $items = $itemRows
            ->groupBy(fn ($item) => implode('|', [
                $item->nama_item,
                $item->id_kategori ?? 'none',
                number_format((float) $item->harga, 2, '.', ''),
                $item->image_path ?? 'none',
            ]))
            ->map(function ($group) {
                $first = $group->first();
                $first->sizes = $group
                    ->sortBy(fn ($size) => ['S' => 1, 'M' => 2, 'L' => 3, 'XL' => 4, 'XXL' => 5][strtoupper((string) $size->saiz)] ?? 99)
                    ->values();
                $first->total_stock = $group->sum('stok_tertinggal');

                return $first;
            })
            ->values();

        return view('admin.baju.index', [
            ...$auth,
            'items' => $items,
            'sizeOptions' => ['S', 'M', 'L', 'XL', 'XXL'],
            'categories' => DB::table('kategori_baju')->orderBy('nama_kategori')->get(),
        ]);
    }

    public function adminBajuOrders(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $orders = DB::table('tempahan')
            ->join('ahli', 'tempahan.id_ahli', '=', 'ahli.id_ahli')
            ->leftJoin('item_tempahan', 'tempahan.id_tempahan', '=', 'item_tempahan.id_tempahan')
            ->leftJoin('item_baju', 'item_tempahan.id_item', '=', 'item_baju.id_item')
            ->select([
                'tempahan.id_tempahan',
                'tempahan.tarikh_tempahan',
                'tempahan.status',
                'tempahan.tarikh_siap',
                'tempahan.tarikh_ambil',
                'tempahan.jumlah_total',
                'ahli.nama',
                'ahli.no_matrik',
                'item_baju.nama_item as item_name',
                'item_baju.saiz',
                'item_tempahan.kuantiti',
                'item_tempahan.harga_seunit',
                'item_tempahan.subtotal',
            ])
            ->orderByDesc('tempahan.id_tempahan')
            ->paginate(20, ['*'], 'tempahan_page')
            ->withQueryString();

        return view('admin.baju.orders', [
            ...$auth,
            'orders' => $orders,
            'orderStatuses' => [
                'baru' => 'Baru',
                'belum_ambil' => 'Belum Ambil',
                'sudah_ambil' => 'Sudah Ambil',
            ],
        ]);
    }

    public function storeAdminBaju(Request $request): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);
        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'id_kategori' => ['nullable', 'integer'],
            'nama_item' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok_saiz' => ['required', 'array'],
            'stok_saiz.*' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $imagePath = $this->storeBajuImage($request->file('image'));
        $rows = collect($validated['stok_saiz'])
            ->filter(fn ($stock) => (int) $stock > 0)
            ->map(fn ($stock, $size) => [
                'id_kategori' => $validated['id_kategori'] ?? null,
                'nama_item' => $validated['nama_item'],
                'saiz' => strtoupper((string) $size),
                'harga' => $validated['harga'],
                'stok_tertinggal' => (int) $stock,
                'image_path' => $imagePath,
            ])
            ->values()
            ->all();

        if ($rows === []) {
            return back()->withErrors(['stok_saiz' => 'Masukkan stok untuk sekurang-kurangnya satu size.'])->withInput();
        }

        DB::table('item_baju')->insert($rows);

        return redirect()->route($this->bajuIndexRoute($auth))->with('status', 'Baju berjaya ditambah.');
    }

    public function editAdminBaju(Request $request, int $id): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);
        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $item = DB::table('item_baju')->where('id_item', $id)->first();
        abort_if(! $item, 404);

        $sizes = $this->bajuGroupRows($item)
            ->sortBy(fn ($size) => ['S' => 1, 'M' => 2, 'L' => 3, 'XL' => 4, 'XXL' => 5][strtoupper((string) $size->saiz)] ?? 99)
            ->keyBy(fn ($size) => strtoupper((string) $size->saiz));

        return view('admin.baju.edit', [
            ...$auth,
            'item' => $item,
            'sizes' => $sizes,
            'sizeOptions' => ['S', 'M', 'L', 'XL', 'XXL'],
            'backRoute' => route($this->bajuIndexRoute($auth)),
            'updateRoute' => route($this->bajuUpdateRoute($auth), $item->id_item),
        ]);
    }

    public function updateAdminBaju(Request $request, int $id): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);
        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'id_kategori' => ['nullable', 'integer'],
            'nama_item' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok_saiz' => ['required', 'array'],
            'stok_saiz.*' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $item = DB::table('item_baju')->where('id_item', $id)->first();
        abort_if(! $item, 404);

        $groupRows = $this->bajuGroupRows($item);
        $imagePath = $item->image_path;

        if ($request->hasFile('image')) {
            $this->deleteBajuImage($imagePath);
            $imagePath = $this->storeBajuImage($request->file('image'));
        }

        $categoryId = array_key_exists('id_kategori', $validated) ? $validated['id_kategori'] : $item->id_kategori;

        DB::transaction(function () use ($validated, $groupRows, $imagePath, $categoryId): void {
            $existingBySize = $groupRows->keyBy(fn ($row) => strtoupper((string) $row->saiz));

            foreach ($validated['stok_saiz'] as $size => $stock) {
                $size = strtoupper((string) $size);
                $stock = (int) $stock;
                $existing = $existingBySize->get($size);
                $payload = [
                    'id_kategori' => $categoryId,
                    'nama_item' => $validated['nama_item'],
                    'saiz' => $size,
                    'harga' => $validated['harga'],
                    'stok_tertinggal' => $stock,
                    'image_path' => $imagePath,
                ];

                if ($existing) {
                    DB::table('item_baju')->where('id_item', $existing->id_item)->update($payload);
                } elseif ($stock > 0) {
                    DB::table('item_baju')->insert($payload);
                }
            }
        });

        return redirect()->route($this->bajuIndexRoute($auth))->with('status', 'Maklumat baju berjaya dikemaskini.');
    }

    public function destroyAdminBaju(Request $request, int $id): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);
        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $item = DB::table('item_baju')->where('id_item', $id)->first();
        abort_if(! $item, 404);

        $groupIds = DB::table('item_baju')
            ->where('nama_item', $item->nama_item)
            ->where('harga', $item->harga)
            ->where(function ($query) use ($item): void {
                $item->id_kategori === null
                    ? $query->whereNull('id_kategori')
                    : $query->where('id_kategori', $item->id_kategori);
            })
            ->where(function ($query) use ($item): void {
                $item->image_path === null
                    ? $query->whereNull('image_path')
                    : $query->where('image_path', $item->image_path);
            })
            ->pluck('id_item');

        try {
            DB::table('item_baju')->whereIn('id_item', $groupIds)->delete();
            $this->deleteBajuImage($item->image_path);
        } catch (QueryException) {
            return back()->withErrors(['delete' => 'Baju tidak boleh dipadam kerana masih ada tempahan berkaitan.']);
        }

        return redirect()->route($this->bajuIndexRoute($auth))->with('status', 'Baju berjaya dipadam.');
    }

    public function updateAdminTempahanBaju(Request $request, int $id): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);
        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'max:20'],
            'tarikh_siap' => ['nullable', 'date'],
            'tarikh_ambil' => ['nullable', 'date'],
        ]);

        $status = $this->normalizeOrderStatus($validated['status'], $validated['tarikh_ambil'] ?? null);

        if (! $status) {
            return back()->withErrors(['status' => 'Status tempahan tidak sah.']);
        }

        DB::table('tempahan')->where('id_tempahan', $id)->update([
            'status' => $status,
            'tarikh_siap' => $validated['tarikh_siap'] ?: null,
            'tarikh_ambil' => $validated['tarikh_ambil'] ?: null,
        ]);

        return redirect()->route($this->bajuOrdersRoute($auth))->with('status', 'Tempahan baju berjaya dikemaskini.');
    }

    public function destroyAdminTempahanBaju(Request $request, int $id): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);
        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        try {
            DB::table('tempahan')->where('id_tempahan', $id)->delete();
        } catch (QueryException) {
            return back()->withErrors(['delete' => 'Tempahan baju tidak boleh dipadam.']);
        }

        return redirect()->route($this->bajuOrdersRoute($auth))->with('status', 'Tempahan baju berjaya dipadam.');
    }

    public function saham(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $category = $request->query('kategori', 'pelajar');
        $category = in_array($category, ['pelajar', 'staff', 'rumusan'], true) ? $category : 'pelajar';
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', '');
        $status = in_array($status, ['aktif', 'tidak_aktif'], true) ? $status : '';
        $program = trim((string) $request->query('program', ''));
        $kelas = trim((string) $request->query('kelas', ''));
        $staffType = trim((string) $request->query('staff_type', ''));
        $tarikh = trim((string) $request->query('tarikh', ''));

        $studentSharesQuery = $this->filteredStudentSharesQuery($request);
        $printStudentShares = $category === 'pelajar' ? (clone $studentSharesQuery)->get() : collect();
        $studentShares = $studentSharesQuery
            ->paginate(20)
            ->withQueryString();
        $staffMembersQuery = $this->filteredStaffSharesQuery($request);
        $printStaffMembers = $category === 'staff' ? (clone $staffMembersQuery)->get() : collect();
        $staffMembers = $staffMembersQuery
            ->paginate(20)
            ->withQueryString();
        $memberProfiles = Permohonan::query()
            ->where('jenis', 'anggota')
            ->where('status', 'diluluskan')
            ->pluck('id_permohonan', 'id_ahli');
        $inactiveReasons = $this->inactiveMemberReasons();
        $inactiveStaffReasons = $this->inactiveStaffReasons();
        $positiveStudentShares = fn () => Saham::query()->whereRaw('(COALESCE(syer, 0) + COALESCE(tambahan_saham, 0)) > 0');
        $positiveStaffShares = fn () => SahamStaff::query()->whereRaw('(COALESCE(syer, 0) + COALESCE(tambahan_saham, 0)) > 0');
        $studentCount = $positiveStudentShares()->count();
        $studentTotal = (float) Saham::query()->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')->value('total');
        $staffCount = $positiveStaffShares()->count();
        $staffTotal = (float) SahamStaff::query()->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')->value('total');
        $stoppedStudentShares = $positiveStudentShares()
            ->whereHas('ahli', fn ($query) => $query->where('status_aktif', false))
            ->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')
            ->value('total');
        $stoppedStaffShares = (float) $positiveStaffShares()
            ->whereHas('pekerja', fn ($query) => $query->where('status_aktif', false))
            ->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')
            ->value('total');
        $stoppedStudentCount = $positiveStudentShares()
            ->whereHas('ahli', fn ($query) => $query->where('status_aktif', false))
            ->count();
        $stoppedStaffCount = $positiveStaffShares()
            ->whereHas('pekerja', fn ($query) => $query->where('status_aktif', false))
            ->count();
        $fiscalEndYear = now()->month >= 9 ? now()->year + 1 : now()->year;
        $fiscalStart = now()->setDate($fiscalEndYear - 1, 9, 1)->startOfDay();
        $fiscalEnd = now()->setDate($fiscalEndYear, 8, 31)->endOfDay();
        $periodStudentCount = $positiveStudentShares()
            ->whereHas('ahli', fn ($query) => $query->whereBetween('tarikh_daftar', [$fiscalStart, $fiscalEnd]))
            ->count();
        $periodStudentTotal = (float) $positiveStudentShares()
            ->whereHas('ahli', fn ($query) => $query->whereBetween('tarikh_daftar', [$fiscalStart, $fiscalEnd]))
            ->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')
            ->value('total');
        $periodStaffCount = $positiveStaffShares()
            ->whereHas('pekerja', fn ($query) => $query->whereBetween('tarikh_mula', [$fiscalStart, $fiscalEnd]))
            ->count();
        $periodStaffTotal = (float) $positiveStaffShares()
            ->whereHas('pekerja', fn ($query) => $query->whereBetween('tarikh_mula', [$fiscalStart, $fiscalEnd]))
            ->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')
            ->value('total');
        $previousStudentCount = $positiveStudentShares()
            ->whereHas('ahli', fn ($query) => $query->whereNotNull('tarikh_daftar')->where('tarikh_daftar', '<', $fiscalStart))
            ->count();
        $previousStudentTotal = (float) $positiveStudentShares()
            ->whereHas('ahli', fn ($query) => $query->whereNotNull('tarikh_daftar')->where('tarikh_daftar', '<', $fiscalStart))
            ->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')
            ->value('total');
        $previousStaffCount = $positiveStaffShares()
            ->whereHas('pekerja', fn ($query) => $query->whereNotNull('tarikh_mula')->where('tarikh_mula', '<', $fiscalStart))
            ->count();
        $previousStaffTotal = (float) $positiveStaffShares()
            ->whereHas('pekerja', fn ($query) => $query->whereNotNull('tarikh_mula')->where('tarikh_mula', '<', $fiscalStart))
            ->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')
            ->value('total');

        return view('admin.saham.index', [
            ...$auth,
            'category' => $category,
            'records' => $studentShares,
            'staffMembers' => $staffMembers,
            'printStudentRecords' => $printStudentShares,
            'printStaffRecords' => $printStaffMembers,
            'memberProfiles' => $memberProfiles,
            'inactiveReasons' => $inactiveReasons,
            'inactiveStaffReasons' => $inactiveStaffReasons,
            'annualSummaryTables' => $this->annualShareSummaries(),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'program' => $program,
                'kelas' => $kelas,
                'staff_type' => $staffType,
                'tarikh' => $tarikh,
            ],
            'studentPrograms' => collect(['JTMK', 'JRKV']),
            'studentClasses' => collect(range(1, 5))
                ->flatMap(fn ($semester) => ['DIT'.$semester.'A', 'DIT'.$semester.'B', 'DDC'.$semester.'A', 'DBF'.$semester.'A']),
            'staffTypes' => collect([
                'lecturer_member' => 'Pensyarah / Staf Akademik (Anggota)',
                'coop_staff' => 'Pekerja Koperasi',
                'clothing_staff' => 'Staff Pengurusan Baju',
            ]),
            'students' => Ahli::query()->doesntHave('saham')->orderBy('nama')->get(),
            'summary' => [
                'student_count' => $studentCount,
                'student_total' => $studentTotal,
                'staff_count' => $staffCount,
                'staff_total' => $staffTotal,
                'grand_count' => $studentCount + $staffCount,
                'grand_total' => $studentTotal + $staffTotal,
                'stopped_student_count' => $stoppedStudentCount,
                'stopped_staff_count' => $stoppedStaffCount,
                'stopped_total' => (float) $stoppedStudentShares,
                'stopped_staff_total' => $stoppedStaffShares,
                'fiscal_start' => $fiscalStart,
                'fiscal_end' => $fiscalEnd,
                'fiscal_end_year' => $fiscalEndYear,
                'period_student_count' => $periodStudentCount,
                'period_student_total' => $periodStudentTotal,
                'period_staff_count' => $periodStaffCount,
                'period_staff_total' => $periodStaffTotal,
                'period_count' => $periodStudentCount + $periodStaffCount,
                'period_total' => $periodStudentTotal + $periodStaffTotal,
                'previous_count' => $previousStudentCount + $previousStaffCount,
                'previous_total' => $previousStudentTotal + $previousStaffTotal,
                'current_cumulative_count' => $previousStudentCount + $previousStaffCount + $periodStudentCount + $periodStaffCount,
                'current_cumulative_total' => $previousStudentTotal + $previousStaffTotal + $periodStudentTotal + $periodStaffTotal,
                'stopped_count' => $stoppedStudentCount + $stoppedStaffCount,
                'stopped_share_total' => (float) $stoppedStudentShares + $stoppedStaffShares,
                'active_count' => max(($studentCount + $staffCount) - ($stoppedStudentCount + $stoppedStaffCount), 0),
                'active_total' => max(($studentTotal + $staffTotal) - ((float) $stoppedStudentShares + $stoppedStaffShares), 0),
            ],
        ]);
    }

    public function exportSahamCsv(Request $request): StreamedResponse|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $category = $request->query('kategori', 'pelajar');
        $category = in_array($category, ['pelajar', 'staff', 'rumusan'], true) ? $category : 'pelajar';
        $filename = match ($category) {
            'pelajar' => 'senarai_saham_pelajar_'.now()->format('Y-m-d').'.csv',
            'staff' => 'senarai_saham_staff_'.now()->format('Y-m-d').'.csv',
            default => 'saham-'.$category.'-'.now()->format('Y-m-d-His').'.csv',
        };

        return response()->streamDownload(function () use ($category, $request): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputs($handle, "\xEF\xBB\xBF");

            if ($category === 'pelajar') {
                fputcsv($handle, ['BIL', 'NAMA', 'NO KP', 'NO MATRIK', 'PROGRAM', 'KELAS', 'TARIKH DAFTAR AHLI', 'YURAN AHLI', 'SAHAM SEMASA', 'TAMBAHAN SAHAM', 'JUMLAH SAHAM', 'STATUS PELAJAR']);

                $records = $this->filteredStudentSharesQuery($request)->get();
                $inactiveReasons = $this->inactiveMemberReasons();

                foreach ($records as $index => $record) {
                    $statusLabel = ($record->ahli->status_aktif ?? false)
                        ? 'Aktif'
                        : ($inactiveReasons[$record->id_ahli] ?? 'Pindah / Berhenti');

                    fputcsv($handle, [
                        $index + 1,
                        $record->ahli->nama ?? '-',
                        $record->ahli->nric ?? '-',
                        $record->ahli->no_matrik ?? '-',
                        $record->ahli->program ?? '-',
                        $record->ahli->kelas ?? '-',
                        optional($record->ahli->tarikh_daftar)->format('d/m/Y') ?? '-',
                        $this->csvMoney($record->yuran),
                        $this->csvMoney($record->syer),
                        $this->csvMoney($record->tambahan_saham),
                        $this->csvMoney((float) $record->syer + (float) $record->tambahan_saham),
                        $statusLabel,
                    ]);
                }
            } elseif ($category === 'staff') {
                fputcsv($handle, ['BIL', 'NAMA', 'NO KP', 'NO PEKERJA', 'JENIS STAFF', 'TARIKH MULA KERJA', 'SAHAM SEMASA', 'TAMBAHAN SAHAM', 'JUMLAH SAHAM', 'STATUS STAFF']);

                $staffMembers = $this->filteredStaffSharesQuery($request)->get();
                $inactiveStaffReasons = $this->inactiveStaffReasons();

                foreach ($staffMembers as $index => $staff) {
                    $statusLabel = $staff->status_aktif
                        ? 'Aktif'
                        : ($inactiveStaffReasons[$staff->id_pekerja] ?? 'Pindah / Berhenti');

                    fputcsv($handle, [
                        $index + 1,
                        $staff->nama,
                        $staff->nric ?? '-',
                        $staff->no_pekerja ?? '-',
                        $staff->staff_type_label,
                        optional($staff->tarikh_mula)->format('d/m/Y') ?? '-',
                        $this->csvMoney($staff->sahamStaff->syer ?? 0),
                        $this->csvMoney($staff->sahamStaff->tambahan_saham ?? 0),
                        $this->csvMoney((float) ($staff->sahamStaff->syer ?? 0) + (float) ($staff->sahamStaff->tambahan_saham ?? 0)),
                        $statusLabel,
                    ]);
                }
            } else {
                $studentCount = Saham::query()->count();
                $studentTotal = (float) Saham::query()->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')->value('total');
                $staffCount = SahamStaff::query()->count();
                $staffTotal = (float) SahamStaff::query()->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')->value('total');
                $stoppedStudentCount = Ahli::query()->where('status_aktif', false)->count();
                $stoppedStudentShares = (float) Saham::query()
                    ->whereHas('ahli', fn ($query) => $query->where('status_aktif', false))
                    ->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')
                    ->value('total');
                $stoppedStaffCount = Pekerja::query()->where('status_aktif', false)->count();
                $stoppedStaffShares = (float) SahamStaff::query()
                    ->whereHas('pekerja', fn ($query) => $query->where('status_aktif', false))
                    ->selectRaw('COALESCE(SUM(syer + tambahan_saham), 0) as total')
                    ->value('total');

                fputcsv($handle, ['Perkara', 'Orang', 'Jumlah Saham']);
                fputcsv($handle, ['Jumlah Saham Pelajar', $studentCount, number_format($studentTotal, 2, '.', '')]);
                fputcsv($handle, ['Jumlah Saham Staff', $staffCount, number_format($staffTotal, 2, '.', '')]);
                fputcsv($handle, ['Pelajar Pindah / Berhenti', $stoppedStudentCount, number_format($stoppedStudentShares, 2, '.', '')]);
                fputcsv($handle, ['Staff Pindah / Berhenti', $stoppedStaffCount, number_format($stoppedStaffShares, 2, '.', '')]);
                fputcsv($handle, ['Jumlah Saham Anggota Koperasi Polibesut', $studentCount + $staffCount, number_format($studentTotal + $staffTotal, 2, '.', '')]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function storeSaham(Request $request): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'id_ahli' => ['required', 'exists:ahli,id_ahli', 'unique:saham,id_ahli'],
            'syer' => ['required', 'numeric', 'min:0'],
            'tambahan_saham' => ['nullable', 'numeric', 'min:0'],
            'yuran' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['tarikh_kemaskini'] = now()->toDateString();
        $saham = Saham::query()->create($validated);
        $balance = (float) $saham->syer + (float) $saham->tambahan_saham;

        $this->recordShareTransaction('student', $saham->id_ahli, 'MANUAL_CREATE', 'CREDIT', $balance, $balance, Saham::class, $saham->id_saham, $auth, 'Rekod saham pelajar diwujudkan oleh admin.');
        $this->audit($request, $auth, 'create', 'saham', $saham, 'Rekod saham pelajar diwujudkan.');

        return redirect()->route('admin.saham.index')->with('status', 'Rekod saham berjaya ditambah.');
    }

    public function updateSaham(Request $request, Saham $saham): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'jumlah_saham' => ['required', 'numeric', 'min:0'],
            'yuran' => ['required', 'numeric', 'min:0'],
        ]);

        $jumlahSaham = (float) $validated['jumlah_saham'];
        $oldTotal = (float) ($saham->syer ?? 0) + (float) ($saham->tambahan_saham ?? 0);
        $syerAsas = (float) ($saham->syer ?? 0);

        $saham->update([
            'syer' => $jumlahSaham < $syerAsas ? $jumlahSaham : $syerAsas,
            'tambahan_saham' => $jumlahSaham >= $syerAsas ? $jumlahSaham - $syerAsas : 0,
            'yuran' => $validated['yuran'],
            'tarikh_kemaskini' => now()->toDateString(),
        ]);

        $difference = $jumlahSaham - $oldTotal;
        if ($difference !== 0.0) {
            $this->recordShareTransaction(
                'student',
                $saham->id_ahli,
                'MANUAL_ADJUSTMENT',
                $difference > 0 ? 'CREDIT' : 'DEBIT',
                abs($difference),
                $jumlahSaham,
                Saham::class,
                $saham->id_saham,
                $auth,
                'Jumlah saham dikemaskini secara manual oleh admin.'
            );
        }

        $this->audit($request, $auth, 'update', 'saham', $saham, 'Rekod saham pelajar dikemaskini.');

        return redirect()->route('admin.saham.index')->with('status', 'Rekod saham berjaya dikemaskini.');
    }

    public function updateStaffSaham(Request $request, Pekerja $staff): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $validated = $request->validate([
            'jumlah_saham' => ['required', 'numeric', 'min:0'],
        ]);

        $share = SahamStaff::query()->firstOrNew(['id_pekerja' => $staff->id_pekerja]);
        $oldTotal = (float) ($share->syer ?? 0) + (float) ($share->tambahan_saham ?? 0);
        $jumlahSaham = (float) $validated['jumlah_saham'];
        $syerAsas = (float) ($share->syer ?? 0);

        if (! $share->exists && $jumlahSaham > 0) {
            $syerAsas = min($jumlahSaham, 10.0);
        }

        $share->fill([
            'syer' => $jumlahSaham < $syerAsas ? $jumlahSaham : $syerAsas,
            'tambahan_saham' => $jumlahSaham >= $syerAsas ? $jumlahSaham - $syerAsas : 0,
            'tarikh_kemaskini' => now()->toDateString(),
        ]);
        $share->save();

        $difference = $jumlahSaham - $oldTotal;
        if ($difference !== 0.0) {
            $this->recordShareTransaction(
                'staff',
                $staff->id_pekerja,
                'MANUAL_ADJUSTMENT',
                $difference > 0 ? 'CREDIT' : 'DEBIT',
                abs($difference),
                $jumlahSaham,
                SahamStaff::class,
                $share->id_saham_staff,
                $auth,
                'Jumlah saham staff dikemaskini secara manual oleh admin.'
            );
        }

        $this->audit($request, $auth, 'update', 'saham_staff', $share, 'Rekod saham staff dikemaskini.');

        return redirect()
            ->route('admin.saham.index', ['kategori' => 'staff'])
            ->with('status', 'Rekod saham staff berjaya dikemaskini.');
    }

    public function destroySaham(Request $request, Saham $saham): RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $this->audit($request, $auth, 'delete', 'saham', $saham, 'Rekod saham pelajar dipadam.');
        $saham->delete();

        return redirect()->route('admin.saham.index')->with('status', 'Rekod saham berjaya dipadam.');
    }

    public function reports(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        return view('admin.reports.index', [
            ...$auth,
            'stats' => [
                'students' => Ahli::query()->count(),
                'staff' => Pekerja::query()->count(),
                'stock_value' => DB::table('stok')->selectRaw("COALESCE(SUM({$this->stockQuantityColumn()} * {$this->stockPriceColumn()}), 0) as total")->value('total'),
                'sales_total' => DB::table('jualan')->sum('jumlah'),
                'orders_pending' => DB::table('tempahan')->whereIn(DB::raw('LOWER(status)'), ['baru', 'pending', 'belum_ambil', 'belum ambil'])->count(),
                'vendor_payments' => Schema::hasTable($this->paymentTable()) ? DB::table($this->paymentTable())->sum($this->paymentFinalColumn()) : 0,
                'shares_total' => Saham::query()->sum('syer'),
            ],
            'recentSales' => $this->salesQuery()->limit(8)->get(),
        ]);
    }

    public function rules(Request $request): View|RedirectResponse
    {
        $auth = $this->requireRole($request, ['admin', 'staff']);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        return view('shared.rules.index', $auth);
    }

    private function stockQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::table('stok')->select([
            "{$this->stockKeyColumn()} as item_id",
            'nama_item',
            "{$this->stockQuantityColumn()} as quantity",
            "{$this->stockPriceColumn()} as harga",
        ]);
    }

    private function ordersQuery(): \Illuminate\Database\Query\Builder
    {
        if ($this->hasColumn('tempahan', 'no_matrik')) {
            return DB::table('tempahan')
                ->leftJoin('ahli', 'tempahan.no_matrik', '=', 'ahli.no_matrik')
                ->select([
                    'tempahan.tempahan_id',
                    'tempahan.no_matrik',
                    'ahli.nama',
                    'tempahan.item',
                    'tempahan.quantity',
                    'tempahan.status',
                    ...($this->hasColumn('tempahan', 'tarikh_ambil') ? ['tempahan.tarikh_ambil'] : [DB::raw('NULL as tarikh_ambil')]),
                    'tempahan.created_at',
                ])
                ->orderByDesc('tempahan.tempahan_id');
        }

        return DB::table('tempahan')
            ->leftJoin('ahli', 'tempahan.id_ahli', '=', 'ahli.id_ahli')
            ->leftJoin('item_tempahan', 'tempahan.id_tempahan', '=', 'item_tempahan.id_tempahan')
            ->leftJoin('item_baju', 'item_tempahan.id_item', '=', 'item_baju.id_item')
            ->select([
                'tempahan.id_tempahan as tempahan_id',
                'ahli.no_matrik',
                'ahli.nama',
                DB::raw('COALESCE(item_baju.nama_item, "-") as item'),
                DB::raw('COALESCE(item_baju.saiz, "-") as saiz'),
                DB::raw('COALESCE(item_tempahan.kuantiti, 1) as quantity'),
                'tempahan.status',
                'tempahan.tarikh_ambil',
                DB::raw('tempahan.tarikh_tempahan as created_at'),
            ])
            ->orderByDesc('tempahan.id_tempahan');
    }

    private function normalizeOrderStatus(string $status, ?string $pickupDate = null): ?string
    {
        if (filled($pickupDate)) {
            return 'sudah_ambil';
        }

        $key = strtolower(trim($status));

        return match ($key) {
            'baru', 'pending' => 'baru',
            'belum_ambil', 'belum ambil', 'diproses', 'dalam proses', 'siap', 'menunggu pengambilan', 'menunggu_ambil' => 'belum_ambil',
            'sudah_ambil', 'sudah ambil', 'diambil', 'siap diambil', 'selesai' => 'sudah_ambil',
            'batal', 'cancel', 'cancelled' => 'baru',
            default => null,
        };
    }

    private function orderStatusAliases(string $status): array
    {
        return match ($status) {
            'baru' => ['baru', 'pending'],
            'belum_ambil' => ['belum_ambil', 'belum ambil', 'diproses', 'dalam proses', 'siap', 'menunggu pengambilan', 'menunggu_ambil'],
            'sudah_ambil' => ['sudah_ambil', 'sudah ambil', 'diambil', 'siap diambil', 'selesai'],
            default => [strtolower($status)],
        };
    }

    private function studentBajuItemsQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::table('item_baju')
            ->leftJoin('kategori_baju', 'item_baju.id_kategori', '=', 'kategori_baju.id_kategori')
            ->select([
                'item_baju.id_item as item_id',
                'item_baju.nama_item',
                'item_baju.saiz',
                'item_baju.harga',
                'item_baju.stok_tertinggal as quantity',
                'item_baju.image_path',
                'kategori_baju.nama_kategori',
            ]);
    }

    private function studentOrderCartKey(): string
    {
        return 'student_tempahan_cart';
    }

    private function studentOrderCartItems(Request $request): \Illuminate\Support\Collection
    {
        $cart = $request->session()->get($this->studentOrderCartKey(), []);

        if ($cart === [] || ! Schema::hasTable('item_baju')) {
            return collect();
        }

        $itemIds = array_map('intval', array_keys($cart));

        return DB::table('item_baju')
            ->whereIn('id_item', $itemIds)
            ->orderBy('nama_item')
            ->orderBy('saiz')
            ->get()
            ->map(function ($item) use ($cart): object {
                $item->quantity_ordered = (int) ($cart[(string) $item->id_item]['quantity'] ?? 0);

                return $item;
            })
            ->filter(fn ($item): bool => $item->quantity_ordered > 0)
            ->values();
    }

    private function salesQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::table('jualan')
            ->leftJoin('stok', "jualan.{$this->salesStockColumn()}", '=', "stok.{$this->stockKeyColumn()}")
            ->select([
                "{$this->salesDateColumn()} as tarikh",
                'stok.nama_item',
                "{$this->salesQuantityColumn()} as quantity",
                'jualan.jumlah',
            ])
            ->orderByDesc($this->salesDateColumn());
    }

    private function vendorsQuery(): \Illuminate\Database\Query\Builder
    {
        $vendorKey = $this->vendorKeyColumn();
        $paymentTable = $this->paymentTable();
        $paymentVendor = $this->paymentVendorColumn();

        $query = DB::table('vendor')
            ->select([
                "{$vendorKey} as vendor_id",
                'nama_vendor',
                'no_akaun',
                'bank',
            ])
            ->orderBy('nama_vendor');

        if (Schema::hasTable($paymentTable)) {
            $query->selectSub(
                DB::table($paymentTable)
                    ->selectRaw('COUNT(*)')
                    ->whereColumn("{$paymentTable}.{$paymentVendor}", "vendor.{$vendorKey}"),
                'pembayaran_count'
            );
        } else {
            $query->selectRaw('0 as pembayaran_count');
        }

        return $query;
    }

    private function paymentsQuery(): \Illuminate\Database\Query\Builder
    {
        $table = $this->paymentTable();

        if (! Schema::hasTable($table)) {
            return DB::table('vendor')->whereRaw('1 = 0')->selectRaw('NULL as nama_vendor, 0 as jumlah_jualan, 0 as komisen, 0 as bayaran_akhir, NULL as created_at');
        }

        return DB::table($table)
            ->leftJoin('vendor', "{$table}.{$this->paymentVendorColumn()}", '=', "vendor.{$this->vendorKeyColumn()}")
            ->select([
                'vendor.nama_vendor',
                "{$table}.jumlah_jualan",
                "{$table}.{$this->paymentCommissionColumn()} as komisen",
                "{$table}.{$this->paymentFinalColumn()} as bayaran_akhir",
                DB::raw($this->hasColumn($table, 'created_at') ? "{$table}.created_at" : "{$table}.tarikh_bayar as created_at"),
            ])
            ->orderByDesc($this->hasColumn($table, 'created_at') ? "{$table}.created_at" : "{$table}.tarikh_bayar");
    }

    private function stockKeyColumn(): string
    {
        return $this->hasColumn('stok', 'item_id') ? 'item_id' : 'id_stok';
    }

    private function stockQuantityColumn(): string
    {
        return $this->hasColumn('stok', 'quantity') ? 'quantity' : 'kuantiti_semasa';
    }

    private function stockPriceColumn(): string
    {
        return $this->hasColumn('stok', 'harga') ? 'harga' : 'harga_jual';
    }

    private function orderKeyColumn(): string
    {
        return $this->hasColumn('tempahan', 'tempahan_id') ? 'tempahan_id' : 'id_tempahan';
    }

    private function salesStockColumn(): string
    {
        return $this->hasColumn('jualan', 'item_id') ? 'item_id' : 'id_stok';
    }

    private function salesQuantityColumn(): string
    {
        return $this->hasColumn('jualan', 'quantity') ? 'quantity' : 'kuantiti';
    }

    private function salesDateColumn(): string
    {
        return $this->hasColumn('jualan', 'tarikh') ? 'tarikh' : 'tarikh_jualan';
    }

    private function vendorKeyColumn(): string
    {
        return $this->hasColumn('vendor', 'vendor_id') ? 'vendor_id' : 'id_vendor';
    }

    private function paymentTable(): string
    {
        return Schema::hasTable('pembayaran') ? 'pembayaran' : 'pembayaran_vendor';
    }

    private function paymentVendorColumn(): string
    {
        return $this->hasColumn($this->paymentTable(), 'vendor_id') ? 'vendor_id' : 'id_vendor';
    }

    private function paymentCommissionColumn(): string
    {
        return $this->hasColumn($this->paymentTable(), 'komisen') ? 'komisen' : 'komisen_dipotong';
    }

    private function paymentFinalColumn(): string
    {
        return $this->hasColumn($this->paymentTable(), 'bayaran_akhir') ? 'bayaran_akhir' : 'jumlah_bayaran';
    }

    private function hasColumn(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }

    private function storeBajuImage(?UploadedFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        $directory = public_path('uploads/baju');
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = uniqid('baju_', true).'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/baju/'.$filename;
    }

    private function deleteBajuImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        $fullPath = public_path($path);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    private function inactiveMemberReasons(): array
    {
        return Permohonan::query()
            ->where('jenis', 'berhenti')
            ->where('status', 'diluluskan')
            ->whereNotNull('id_ahli')
            ->orderByDesc('tarikh_keputusan')
            ->orderByDesc('id_permohonan')
            ->get(['id_ahli', 'data_permohonan'])
            ->unique('id_ahli')
            ->mapWithKeys(function (Permohonan $permohonan): array {
                $types = collect($permohonan->data_permohonan['jenis_permohonan'] ?? []);
                $label = $this->inactiveReasonLabel($types);

                return [$permohonan->id_ahli => $label];
            })
            ->all();
    }

    private function inactiveStaffReasons(): array
    {
        return Permohonan::query()
            ->where('jenis', 'berhenti')
            ->where('status', 'diluluskan')
            ->whereNull('id_ahli')
            ->orderByDesc('tarikh_keputusan')
            ->orderByDesc('id_permohonan')
            ->get(['no_matrik', 'data_permohonan'])
            ->unique('no_matrik')
            ->mapWithKeys(function (Permohonan $permohonan): array {
                $types = collect($permohonan->data_permohonan['jenis_permohonan'] ?? []);
                $label = $this->inactiveReasonLabel($types);
                $staff = Pekerja::query()->where('no_pekerja', $permohonan->no_matrik)->first();

                return $staff ? [$staff->id_pekerja => $label] : [];
            })
            ->all();
    }

    private function inactiveReasonLabel($types): string
    {
        if ($types->contains('Berpindah')) {
            return 'Berpindah';
        }

        if ($types->contains('Bersara')) {
            return 'Bersara';
        }

        if ($types->contains('Tamat Pengajian')) {
            return 'Tamat Pengajian';
        }

        if ($types->contains('Berhenti Keahlian') || $types->contains('Berhenti / Berpindah / Bersara')) {
            return 'Berhenti';
        }

        return 'Tidak Aktif';
    }

    /**
     * @param array{role:string,user:AdminUser|Pekerja} $auth
     */
    private function bajuIndexRoute(array $auth): string
    {
        return $auth['role'] === 'admin' ? 'admin.baju.index' : 'clothing-staff.baju.index';
    }

    /**
     * @param array{role:string,user:AdminUser|Pekerja} $auth
     */
    private function bajuUpdateRoute(array $auth): string
    {
        return $auth['role'] === 'admin' ? 'admin.baju.update' : 'clothing-staff.baju.update';
    }

    /**
     * @param array{role:string,user:AdminUser|Pekerja} $auth
     */
    private function bajuOrdersRoute(array $auth): string
    {
        return $auth['role'] === 'admin' ? 'admin.baju.orders.index' : 'clothing-staff.orders.index';
    }

    /**
     * @param array{role:string,user:AdminUser|Pekerja} $auth
     */
    private function tempahanIndexRoute(array $auth): string
    {
        return $auth['role'] === 'admin' ? 'admin.tempahan.index' : 'clothing-staff.orders.index';
    }

    private function bajuGroupRows(object $item): \Illuminate\Support\Collection
    {
        return DB::table('item_baju')
            ->where('nama_item', $item->nama_item)
            ->where('harga', $item->harga)
            ->where(function ($query) use ($item): void {
                $item->id_kategori === null
                    ? $query->whereNull('id_kategori')
                    : $query->where('id_kategori', $item->id_kategori);
            })
            ->where(function ($query) use ($item): void {
                $item->image_path === null
                    ? $query->whereNull('image_path')
                    : $query->where('image_path', $item->image_path);
            })
            ->get();
    }

    private function filteredStudentSharesQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', '');
        $status = in_array($status, ['aktif', 'tidak_aktif'], true) ? $status : '';
        $program = trim((string) $request->query('program', ''));
        $kelas = trim((string) $request->query('kelas', ''));
        $tarikh = trim((string) $request->query('tarikh', ''));

        return Saham::query()
            ->with('ahli')
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('ahli', function ($memberQuery) use ($search): void {
                    $memberQuery
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('no_matrik', 'like', "%{$search}%")
                        ->orWhere('nric', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->whereHas('ahli', fn ($memberQuery) => $memberQuery->where('status_aktif', $status === 'aktif')))
            ->when($program !== '', fn ($query) => $query->whereHas('ahli', fn ($memberQuery) => $memberQuery->where('program', $program)))
            ->when($kelas !== '', fn ($query) => $query->whereHas('ahli', fn ($memberQuery) => $memberQuery->where('kelas', $kelas)))
            ->when($tarikh !== '', fn ($query) => $query->whereHas('ahli', fn ($memberQuery) => $memberQuery->whereDate('tarikh_daftar', $tarikh)))
            ->latest('tarikh_kemaskini');
    }

    private function filteredStaffSharesQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', '');
        $status = in_array($status, ['aktif', 'tidak_aktif'], true) ? $status : '';
        $staffType = trim((string) $request->query('staff_type', ''));
        $tarikh = trim((string) $request->query('tarikh', ''));

        return Pekerja::query()
            ->with('sahamStaff')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($staffQuery) use ($search): void {
                    $staffQuery
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('no_pekerja', 'like', "%{$search}%")
                        ->orWhere('nric', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status_aktif', $status === 'aktif'))
            ->when($staffType !== '', fn ($query) => $query->where('staff_type', $staffType))
            ->when($tarikh !== '', fn ($query) => $query->whereDate('tarikh_mula', $tarikh))
            ->orderBy('nama');
    }

    private function annualShareSummaries(): \Illuminate\Support\Collection
    {
        $rows = collect();

        Saham::query()->with('ahli')->get()->each(function (Saham $share) use ($rows): void {
            $member = $share->ahli;
            $date = optional($member)->tarikh_daftar ?? $share->tarikh_kemaskini ?? now();
            $amount = (float) ($share->syer ?? 0) + (float) ($share->tambahan_saham ?? 0);

            if ($amount <= 0) {
                return;
            }

            $rows->push([
                'year' => (int) $date->format('Y'),
                'type' => 'student',
                'active' => (bool) (optional($member)->status_aktif ?? false),
                'amount' => $amount,
            ]);
        });

        SahamStaff::query()->with('pekerja')->get()->each(function (SahamStaff $share) use ($rows): void {
            $member = $share->pekerja;
            $date = optional($member)->tarikh_mula ?? $share->tarikh_kemaskini ?? now();
            $amount = (float) ($share->syer ?? 0) + (float) ($share->tambahan_saham ?? 0);

            if ($amount <= 0) {
                return;
            }

            $rows->push([
                'year' => (int) $date->format('Y'),
                'type' => 'staff',
                'active' => (bool) (optional($member)->status_aktif ?? false),
                'amount' => $amount,
            ]);
        });

        if ($rows->isEmpty()) {
            return collect();
        }

        return $rows
            ->pluck('year')
            ->unique()
            ->sort()
            ->values()
            ->map(function (int $year) use ($rows): array {
                $yearRows = $rows->where('year', $year);
                $previousRows = $rows->where('year', '<', $year);
                $currentRows = $rows->where('year', '<=', $year);
                $stoppedRows = $currentRows->where('active', false);
                $activeRows = $currentRows->where('active', true);
                $studentRows = $yearRows->where('type', 'student');
                $staffRows = $yearRows->where('type', 'staff');

                return [
                    'year' => $year,
                    'student_count' => $studentRows->count(),
                    'student_total' => $studentRows->sum('amount'),
                    'staff_count' => $staffRows->count(),
                    'staff_total' => $staffRows->sum('amount'),
                    'period_count' => $yearRows->count(),
                    'period_total' => $yearRows->sum('amount'),
                    'previous_count' => $previousRows->count(),
                    'previous_total' => $previousRows->sum('amount'),
                    'current_cumulative_count' => $currentRows->count(),
                    'current_cumulative_total' => $currentRows->sum('amount'),
                    'stopped_count' => $stoppedRows->count(),
                    'stopped_total' => $stoppedRows->sum('amount'),
                    'active_count' => $activeRows->count(),
                    'active_total' => $activeRows->sum('amount'),
                ];
            })
            ->sortByDesc('year')
            ->values();
    }

    private function csvMoney(mixed $value): string
    {
        return 'RM '.number_format((float) $value, 2);
    }

    /**
     * @return array{role:string,user:AdminUser|Pekerja}|RedirectResponse
     */
    private function requireRole(Request $request, array $allowed): array|RedirectResponse
    {
        $role = $request->session()->get('auth_role');

        if (! in_array($role, $allowed, true)) {
            return redirect()
                ->route('auth.dashboard')
                ->with('error', 'Akses halaman ini tidak dibenarkan untuk akaun anda.');
        }

        $user = match ($role) {
            'admin' => AdminUser::query()->find($request->session()->get('auth_id')),
            'staff' => Pekerja::query()->find($request->session()->get('auth_id')),
            default => null,
        };

        if (! $user) {
            return redirect()->route('login');
        }

        return compact('role', 'user');
    }

    private function recordShareTransaction(string $memberType, int $memberId, string $type, string $direction, float $amount, float $balanceAfter, string $referenceType, int $referenceId, array $auth, string $notes): void
    {
        if (! Schema::hasTable('share_transactions') || $amount <= 0) {
            return;
        }

        ShareTransaction::query()->create([
            'member_type' => $memberType,
            'member_id' => $memberId,
            'transaction_type' => $type,
            'direction' => $direction,
            'amount' => $amount,
            'balance_after' => $balanceAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'processed_by_role' => $auth['role'],
            'processed_by_id' => $auth['user']->getKey(),
            'notes' => $notes,
            'transacted_at' => now()->toDateString(),
        ]);
    }

    private function audit(Request $request, array $auth, string $action, string $module, ?object $subject, string $description): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        AuditLog::query()->create([
            'actor_role' => $auth['role'],
            'actor_id' => $auth['user']->getKey(),
            'action' => $action,
            'module' => $module,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }
}
