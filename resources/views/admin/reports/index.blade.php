@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-subtitle', 'Ringkasan anggota, stok, tempahan dan saham.')

@section('content')
    <div class="card-grid">
        <section class="panel metric"><span>Students</span><div class="metric-row"><strong>{{ $stats['students'] }}</strong></div></section>
        <section class="panel metric"><span>Staff</span><div class="metric-row"><strong>{{ $stats['staff'] }}</strong></div></section>
        <section class="panel metric"><span>Pending Orders</span><div class="metric-row"><strong>{{ $stats['orders_pending'] }}</strong></div></section>
        <section class="panel metric"><span>Nilai Stok</span><div class="metric-row"><strong>RM {{ number_format((float) $stats['stock_value'], 2) }}</strong></div></section>
        <section class="panel metric"><span>Jumlah Saham</span><div class="metric-row"><strong>RM {{ number_format((float) $stats['shares_total'], 2) }}</strong></div></section>
    </div>
@endsection
