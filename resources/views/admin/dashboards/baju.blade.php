@extends('layouts.app')

@section('title', 'Dashboard Baju')
@section('page-title', 'Dashboard Baju')
@section('page-subtitle', 'Ringkasan tempahan dan stok baju koperasi.')

@section('content')
    @php
        $ordersRoute = $ordersRoute ?? route('admin.tempahan.index');
        $stockRoute = $stockRoute ?? route('admin.baju.index');
        $isClothingStaff = ($role ?? null) === 'staff' && ($staffType ?? null) === 'clothing_staff';
        $orderStatusLabel = fn ($status, $pickupDate = null) => filled($pickupDate)
            ? 'Sudah Ambil'
            : match (strtolower((string) $status)) {
                'pending' => 'Baru',
                'belum_ambil', 'belum ambil', 'diproses', 'dalam proses', 'siap', 'menunggu pengambilan', 'menunggu_ambil' => 'Belum Ambil',
                'sudah_ambil', 'sudah ambil', 'diambil', 'siap diambil', 'selesai' => 'Sudah Ambil',
                default => ucfirst((string) $status),
            };
    @endphp
    <div class="module-dashboard">
        <section class="module-hero">
            <div>
                <span>DASHBOARD BAJU</span>
                <h1>Clothing Order Dashboard</h1>
                <p>Pantau tempahan baju, stok rendah dan status pembayaran/tempahan.</p>
            </div>
            @unless ($isClothingStaff)
                <a href="{{ $ordersRoute }}">Buka Senarai Tempahan</a>
            @endunless
        </section>

        @if ($isClothingStaff)
            <section class="clothing-order-actions" aria-label="Tindakan tempahan">
                <a href="{{ $stockRoute }}">
                    <strong>Stok Baju</strong>
                    <span>Semak, tambah dan kemas kini stok baju.</span>
                </a>
                <a href="{{ $ordersRoute }}">
                    <strong>Senarai Tempahan</strong>
                    <span>Urus pesanan dan status pengambilan.</span>
                </a>
            </section>
        @endif

        <section class="module-metrics">
            <div><span>Jumlah Tempahan</span><strong>{{ number_format($summary['total_orders']) }}</strong><small>Semua rekod</small></div>
            <div><span>Tempahan Baru</span><strong>{{ number_format($summary['new_orders']) }}</strong><small>Menunggu proses</small></div>
            <div><span>Stok Semasa</span><strong>{{ number_format($summary['stock_total']) }}</strong><small>{{ number_format($summary['low_stock']) }} stok rendah</small></div>
            <div><span>Jumlah Bayaran</span><strong>RM {{ number_format($summary['sales_total'], 2) }}</strong><small>Daripada tempahan</small></div>
        </section>

        <section class="module-grid">
            <article class="module-panel">
                <header><div><span>CARTA</span><h2>Status Tempahan Baju</h2></div></header>
                @include('admin.dashboards.partials.donut-chart', ['items' => $charts['orders'], 'money' => false])
            </article>
            <article class="module-panel">
                <header><div><span>CARTA</span><h2>Ringkasan Stok Baju</h2></div></header>
                @include('admin.dashboards.partials.bar-chart', ['items' => $charts['stock'], 'money' => false])
            </article>
        </section>

        <section class="module-grid">
            <article class="module-panel">
                <header><div><span>ORDER</span><h2>Tempahan Terkini</h2></div><a href="{{ $ordersRoute }}">Lihat Semua</a></header>
                <div class="module-list">
                    @forelse ($recentOrders as $order)
                        <div class="module-row">
                            <div><strong>{{ $order->nama ?? $order->no_matrik ?? 'Tempahan Baju' }}</strong><small>{{ isset($order->tarikh_tempahan) ? \Carbon\Carbon::parse($order->tarikh_tempahan)->format('d/m/Y') : '-' }}</small></div>
                            <span>{{ $orderStatusLabel($order->status ?? '-', $order->tarikh_ambil ?? null) }}</span>
                        </div>
                    @empty
                        <div class="module-empty">Tiada tempahan baju lagi.</div>
                    @endforelse
                </div>
            </article>

            <article class="module-panel">
                <header><div><span>STOCK</span><h2>Stok Rendah</h2></div><a href="{{ $stockRoute }}">Urus Stok</a></header>
                <div class="module-list">
                    @forelse ($lowStockItems as $item)
                        <div class="module-row">
                            <div><strong>{{ $item->nama_item ?? 'Item Baju' }}</strong><small>Saiz {{ $item->saiz ?? '-' }}</small></div>
                            <span>{{ number_format((int) ($item->stok_tertinggal ?? 0)) }}</span>
                        </div>
                    @empty
                        <div class="module-empty">Tiada stok rendah.</div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
@endsection

@include('admin.dashboards.styles')

@push('styles')
<style>
    .clothing-order-actions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .clothing-order-actions a{display:grid;gap:6px;min-height:94px;padding:18px;border:1px solid #D7E3F5;border-radius:12px;background:#fff;color:inherit;text-decoration:none}
    .clothing-order-actions a:hover{border-color:#AFC7EA;background:#F8FBFF}
    .clothing-order-actions strong{color:#0F172A;font-size:16px;font-weight:900}
    .clothing-order-actions span{color:#64748B;font-size:13px;font-weight:750;line-height:1.45}
    @media (max-width:720px){.clothing-order-actions{grid-template-columns:1fr}}
</style>
@endpush
