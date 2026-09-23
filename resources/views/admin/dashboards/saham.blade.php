@extends('layouts.app')

@section('title', 'Dashboard Saham')
@section('page-title', 'Dashboard Saham')
@section('page-subtitle', 'Ringkasan operasi saham koperasi.')

@section('content')
    <div class="module-dashboard">
        <section class="module-hero">
            <div>
                <span>DASHBOARD SAHAM</span>
                <h1>Share Management Dashboard</h1>
                <p>Pantau jumlah saham, permohonan anggota, penambahan saham, pengeluaran / pindah dan transaksi terkini.</p>
            </div>
            <a href="{{ route('admin.saham.index') }}">Buka Senarai Saham</a>
        </section>

        <section class="module-metrics">
            <div><span>Jumlah Saham</span><strong>RM {{ number_format($summary['grand_total'], 2) }}</strong><small>Pelajar + Staff</small></div>
            <div><span>Saham Pelajar</span><strong>RM {{ number_format($summary['student_total'], 2) }}</strong><small>{{ number_format($summary['student_members']) }} anggota</small></div>
            <div><span>Saham Staff</span><strong>RM {{ number_format($summary['staff_total'], 2) }}</strong><small>{{ number_format($summary['staff_members']) }} staff</small></div>
            <div><span>Berhenti / Pindah</span><strong>{{ number_format($summary['pending_exits']) }}</strong><small>Menunggu proses</small></div>
        </section>

        <section class="module-grid">
            <article class="module-panel">
                <header><div><span>CARTA</span><h2>Jumlah Saham Mengikut Kategori</h2></div></header>
                @include('admin.dashboards.partials.bar-chart', ['items' => $charts['shareTotals'], 'money' => true])
            </article>
            <article class="module-panel">
                <header><div><span>CARTA</span><h2>Permohonan Pending</h2></div></header>
                @include('admin.dashboards.partials.donut-chart', ['items' => $charts['pending'], 'money' => false])
            </article>
        </section>

        <section class="module-grid">
            <article class="module-panel">
                <header>
                    <div><span>TREND TAHUNAN</span><h2>Trend Saham Mengikut Tahun</h2></div>
                </header>
                @include('admin.dashboards.partials.line-chart', ['items' => $charts['annualTrend']])
            </article>
            <article class="module-panel">
                <header>
                    <div><span>ANGGOTA</span><h2>Pelajar dan Staff Mengikut Tahun</h2></div>
                </header>
                @include('admin.dashboards.partials.grouped-bar-chart', ['items' => $charts['yearlyMembers']])
            </article>
        </section>

        <section class="module-action-layout">
            <div class="module-action-stack">
            <article class="module-panel">
                <header>
                    <div><span>PENDING ACTION</span><h2>Permohonan Anggota</h2></div>
                    <a href="{{ route('admin.permohonan.index', ['jenis' => 'anggota']) }}">Lihat Semua</a>
                </header>
                <div class="module-list">
                    @forelse ($pendingMembershipApplications as $application)
                        <a class="module-row" href="{{ route('admin.permohonan.show', $application) }}">
                            <div><strong>{{ $application->nama_pemohon }}</strong><small>{{ $application->no_matrik ?? $application->nric ?? '-' }} - {{ $application->tarikh_permohonan?->format('d/m/Y') }}</small></div>
                            <span>{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                        </a>
                    @empty
                        <div class="module-empty">Tiada permohonan anggota pending.</div>
                    @endforelse
                </div>
            </article>

            <article class="module-panel">
                <header>
                    <div><span>PENDING ACTION</span><h2>Penambahan Saham</h2></div>
                    <a href="{{ route('admin.permohonan.index', ['jenis' => 'saham']) }}">Lihat Semua</a>
                </header>
                <div class="module-list">
                    @forelse ($pendingAdditionApplications as $application)
                        <a class="module-row" href="{{ route('admin.permohonan.show', $application) }}">
                            <div><strong>{{ $application->nama_pemohon }}</strong><small>{{ $application->no_matrik ?? $application->nric ?? '-' }} - {{ $application->tarikh_permohonan?->format('d/m/Y') }}</small></div>
                            <span>{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                        </a>
                    @empty
                        <div class="module-empty">Tiada penambahan saham pending.</div>
                    @endforelse
                </div>
            </article>

            <article class="module-panel">
                <header>
                    <div><span>PENDING ACTION</span><h2>Pengeluaran Saham</h2></div>
                    <a href="{{ route('admin.permohonan.index') }}">Lihat Semua</a>
                </header>
                <div class="module-list">
                    @forelse ($pendingExitApplications as $application)
                        <a class="module-row" href="{{ route('admin.permohonan.show', $application) }}">
                            <div><strong>{{ $application->nama_pemohon }}</strong><small>{{ ucwords(str_replace('_', ' ', $application->jenis)) }} - {{ $application->tarikh_permohonan?->format('d/m/Y') }}</small></div>
                            <span>{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                        </a>
                    @empty
                        <div class="module-empty">Tiada pengeluaran saham pending.</div>
                    @endforelse
                </div>
            </article>
            </div>

            <article class="module-panel">
                <header>
                    <div><span>RECENT</span><h2>Transaksi Saham Terkini</h2></div>
                    <a href="{{ route('koperasi.transactions.index') }}">Transaksi</a>
                </header>
                <div class="module-list">
                    @forelse ($recentTransactions as $transaction)
                        @php($owner = $transaction->member_type === 'staff' ? $transaction->staff : $transaction->student)
                        <div class="module-row">
                            <div><strong>{{ $owner?->nama ?? 'Rekod Saham' }}</strong><small>{{ $transaction->transaction_type }} - {{ $transaction->transacted_at?->format('d/m/Y') }}</small></div>
                            <span>RM {{ number_format((float) $transaction->amount, 2) }}</span>
                        </div>
                    @empty
                        <div class="module-empty">Tiada transaksi saham lagi.</div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
@endsection

@include('admin.dashboards.styles')
