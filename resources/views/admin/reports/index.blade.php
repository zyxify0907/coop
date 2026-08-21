@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-subtitle', 'Ringkasan operasi, stok, jualan dan kewangan.')

@section('content')
    <div class="card-grid">
        <section class="panel metric"><span>Students</span><div class="metric-row"><strong>{{ $stats['students'] }}</strong></div></section>
        <section class="panel metric"><span>Staff</span><div class="metric-row"><strong>{{ $stats['staff'] }}</strong></div></section>
        <section class="panel metric"><span>Pending Orders</span><div class="metric-row"><strong>{{ $stats['orders_pending'] }}</strong></div></section>
        <section class="panel metric"><span>Nilai Stok</span><div class="metric-row"><strong>RM {{ number_format((float) $stats['stock_value'], 2) }}</strong></div></section>
        <section class="panel metric"><span>Jumlah Jualan</span><div class="metric-row"><strong>RM {{ number_format((float) $stats['sales_total'], 2) }}</strong></div></section>
        <section class="panel metric"><span>Bayaran Vendor</span><div class="metric-row"><strong>RM {{ number_format((float) $stats['vendor_payments'], 2) }}</strong></div></section>
    </div>

    <section class="panel" style="overflow:hidden;margin-top:18px">
        <div class="panel-pad"><h2 style="margin:0">Jualan Terkini</h2></div>
        <table>
            <thead><tr><th>Tarikh</th><th>Item</th><th>Kuantiti</th><th>Jumlah</th></tr></thead>
            <tbody>
                @forelse ($recentSales as $sale)
                    <tr>
                        <td>{{ $sale->tarikh ? \Illuminate\Support\Carbon::parse($sale->tarikh)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $sale->nama_item ?? '-' }}</td>
                        <td>{{ $sale->quantity }}</td>
                        <td>RM {{ number_format((float) $sale->jumlah, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Tiada jualan terkini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
