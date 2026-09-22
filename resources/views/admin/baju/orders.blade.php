@extends('layouts.app')

@section('title', 'Senarai Tempahan Baju')
@section('page-title', 'Senarai Tempahan Baju')
@section('page-subtitle', 'Staff boleh semak status tempahan baju, tarikh siap dan tarikh ambil.')

@section('content')
@php
    $money = fn ($value) => 'RM '.number_format((float) $value, 2);
    $isAdminBaju = ($role ?? null) === 'admin';
    $isClothingStaff = request()->routeIs('clothing-staff.orders.*');
    $orderRoutes = [
        'update' => $isAdminBaju ? 'admin.baju.orders.update' : 'clothing-staff.orders.update',
        'destroy' => $isAdminBaju ? 'admin.baju.orders.destroy' : 'clothing-staff.orders.destroy',
    ];
    $ordersDashboardRoute = $isAdminBaju ? 'admin.dashboard.baju' : 'clothing-staff.dashboard.baju';
    $normalizedStatus = fn ($status, $pickupDate = null) => filled($pickupDate)
        ? 'sudah_ambil'
        : match (strtolower((string) $status)) {
            'pending' => 'baru',
            'belum ambil', 'diproses', 'dalam proses', 'siap', 'menunggu pengambilan', 'menunggu_ambil' => 'belum_ambil',
            'sudah ambil', 'diambil', 'siap diambil', 'selesai' => 'sudah_ambil',
            default => strtolower((string) $status),
        };
@endphp

@if (session('status'))
    <div class="alert success">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="alert danger">{{ $errors->first() }}</div>
@endif

@if ($isClothingStaff)
    <section class="baju-orders-hero">
        <div>
            <h1>Senarai Tempahan Baju</h1>
            <p>Urus status pesanan, tarikh siap dan pengambilan.</p>
        </div>
        <a class="clothing-dashboard-backlink" href="{{ route($ordersDashboardRoute) }}">Dashboard Tempahan</a>
    </section>
@endif

<section class="panel order-panel">
    <div class="panel-head">
        <div>
            <h2>Senarai Tempahan Baju</h2>
            <span>Lihat siapa sudah ambil, siapa belum ambil, size, kuantiti dan jumlah.</span>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>No Matrik</th>
                    <th>Baju</th>
                    <th>Size</th>
                    <th>Kuantiti</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Tarikh Siap</th>
                    <th>Tarikh Ambil</th>
                    <th style="text-align:right">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    @php
                        $currentStatus = $normalizedStatus($order->status, $order->tarikh_ambil);
                        $statusDisplay = ['baru' => 'Baru', 'belum_ambil' => 'Belum Ambil', 'sudah_ambil' => 'Sudah Ambil'][$currentStatus] ?? ucfirst($currentStatus);
                        $pickupDone = $currentStatus === 'sudah_ambil';
                        $pickupLabel = $pickupDone ? 'Sudah Ambil' : 'Belum Ambil';
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $order->nama }}</strong>
                            <div class="sub">{{ $pickupLabel }}</div>
                        </td>
                        <td>{{ $order->no_matrik }}</td>
                        <td>{{ $order->item_name ?? '-' }}</td>
                        <td>{{ $order->saiz ?? '-' }}</td>
                        <td>{{ $order->kuantiti ?? 0 }}</td>
                        <td>{{ $money($order->subtotal ?? $order->jumlah_total) }}</td>
                        <td>
                            <span class="status-pill {{ $pickupDone ? 'status-pill--diluluskan' : 'status-pill--baru' }}">
                                {{ $statusDisplay }}
                            </span>
                        </td>
                        <td>{{ optional($order->tarikh_siap)->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ optional($order->tarikh_ambil)->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route($orderRoutes['update'], $order->id_tempahan) }}" class="order-actions">
                                @csrf
                                @method('PUT')
                                <select name="status">
                                    @foreach ($orderStatuses as $status => $label)
                                        <option value="{{ $status }}" @selected($currentStatus === $status)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input name="tarikh_siap" type="date" value="{{ $order->tarikh_siap }}">
                                <input name="tarikh_ambil" type="date" value="{{ $order->tarikh_ambil }}">
                                <button class="button" type="submit">Simpan</button>
                            </form>
                            <form method="POST" action="{{ route($orderRoutes['destroy'], $order->id_tempahan) }}" onsubmit="return confirm('Padam tempahan ini?');" style="margin-top:8px">
                                @csrf
                                @method('DELETE')
                                <button class="link-button danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10"><div class="empty-state">Tiada tempahan baju ditemui.</div></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($orders->hasPages())
        <div class="pagination-wrap">{{ $orders->links() }}</div>
    @endif
</section>
@endsection

@push('styles')
<style>
    .alert{padding:14px 16px;border-radius:10px;margin-bottom:16px}
    .alert.success{background:#ecfdf5;color:#166534;border:1px solid #bbf7d0}
    .alert.danger{background:var(--danger-soft);color:var(--primary-dark);border:1px solid var(--danger-soft)}
    .baju-orders-hero{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:24px;padding:34px 36px;border:1px solid var(--line);border-left:5px solid var(--secondary);border-radius:22px;background:#fff;box-shadow:0 14px 34px rgba(15,23,42,.055)}
    .baju-orders-hero h1{margin:0;color:var(--text);font-size:38px;line-height:1.15;font-weight:900;letter-spacing:0}
    .baju-orders-hero p{margin:14px 0 0;color:var(--muted-2);font-size:16px;font-weight:800}
    .clothing-dashboard-backlink{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:0 20px;border:1px solid #D7E3F5;border-radius:10px;background:#fff;color:var(--secondary);font-size:15px;font-weight:900;text-decoration:none;white-space:nowrap}
    .clothing-dashboard-backlink::before{content:'\2190';margin-right:8px;color:var(--primary);font-weight:900}
    .clothing-dashboard-backlink:hover{border-color:#AFC7EA;background:#F8FBFF}
    .order-panel{overflow:hidden;border-radius:24px;background:#fff;border:1px solid var(--line);box-shadow:var(--shadow-sm)}
    .panel-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:18px 20px;border-bottom:1px solid var(--line);background:#fff}
    .panel-head h2{margin:0;font-size:18px}
    .panel-head span{margin-top:6px;display:block;color:#64748b;font-size:13px}
    .table-wrap{overflow:auto;padding:0 0 4px}
    table{width:100%;border-collapse:separate;border-spacing:0}
    thead th{background:var(--surface-soft);color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:.03em}
    th,td{padding:14px 16px;border-bottom:1px solid var(--line);vertical-align:top}
    .sub{margin-top:4px;font-size:12px;color:#64748b}
    .order-actions{display:grid;gap:8px;min-width:170px}
    .order-actions select,.order-actions input{width:100%;border:1px solid var(--line);border-radius:8px;padding:8px 10px}
    .pagination-wrap{padding:0 18px 18px}
    .empty-state{padding:28px;text-align:center;color:#64748b;font-weight:700}
    @media (max-width:640px){.baju-orders-hero{align-items:flex-start;flex-direction:column;padding:24px}.baju-orders-hero h1{font-size:30px}}
</style>
@endpush
