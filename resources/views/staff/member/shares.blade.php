@extends('layouts.app')

@section('title', 'Saham Saya')
@section('page-title', 'Saham Saya')
@section('page-subtitle', 'Baki saham dan rekod transaksi keanggotaan staff.')

@section('content')
@include('components.coop-page-style')

@php
    $shareRecord = $share;
    $statusLabel = $shareRecord ? 'Aktif' : 'Belum Aktif';
    $staffTypeLabel = match ($staffType) {
        'lecturer_member' => 'Pensyarah / Staf Akademik',
        'clothing_staff' => 'Staff Pengurusan Baju',
        default => 'Pekerja Koperasi',
    };
    $memberNumber = $user->no_pekerja ?? '-';
    $moneyOrPending = fn ($value) => $shareRecord ? 'RM '.number_format((float) $value, 2) : 'Belum diisi admin';
@endphp

<div class="coop-wrap staff-shares-page">
    <section class="coop-hero shares-hero">
        <div>
            <span class="coop-kicker">Keanggotaan Koperasi</span>
            <h1>Saham Saya</h1>
            <p>Semak nilai semasa dan maklumat utama saham koperasi anda dalam satu paparan.</p>
        </div>
    </section>

    <div class="shares-metrics">
        <section>
            <span>No Anggota</span>
            <strong>{{ $memberNumber }}</strong>
            <small>{{ $staffTypeLabel }}</small>
        </section>
        <section>
            <span>Status Anggota</span>
            <strong>{{ $statusLabel }}</strong>
            <small>{{ $shareRecord ? 'Rekod saham aktif' : 'Menunggu rekod admin' }}</small>
        </section>
        <section>
            <span>Jumlah Saham Semasa</span>
            <strong>{{ $moneyOrPending($totalShare) }}</strong>
            <small>Syer asas + tambahan saham</small>
        </section>
        <section>
            <span>Kemaskini Terakhir</span>
            <strong>{{ optional(optional($shareRecord)->tarikh_kemaskini)->format('d/m/Y') ?? '-' }}</strong>
            <small>Tarikh rekod saham disemak</small>
        </section>
    </div>

    <section class="shares-actions-bar">
        <div>
            <strong>Tindakan Saham</strong>
            <span>Pilih urusan yang anda mahu hantar kepada koperasi.</span>
        </div>
        <div class="shares-actions-bar__buttons">
            <a class="shares-primary" href="{{ route($portalPrefix.'.permohonan.index', ['jenis' => 'saham']) }}">Tambah Saham</a>
            <a class="coop-button-soft" href="{{ route($portalPrefix.'.permohonan.index', ['jenis' => 'pengeluaran']) }}">Pengeluaran Saham</a>
        </div>
    </section>

    <div class="shares-layout">
        <aside class="panel share-profile-card">
            <div class="panel-section-head">
                <div>
                    <h2>Maklumat Saham</h2>
                    <p>Butiran utama akaun saham anda.</p>
                </div>
            </div>
            <table class="detail-table">
                <tr><th>Nama</th><td>{{ $user->nama }}</td></tr>
                <tr><th>No Pekerja</th><td>{{ $user->no_pekerja ?? '-' }}</td></tr>
                <tr><th>No. KP</th><td>{{ $user->nric ?? '-' }}</td></tr>
                <tr><th>Jenis Staff</th><td>{{ $staffTypeLabel }}</td></tr>
                <tr><th>Syer Asas</th><td>{{ $moneyOrPending(optional($shareRecord)->syer) }}</td></tr>
                <tr><th>Tambahan Saham</th><td>{{ $moneyOrPending(optional($shareRecord)->tambahan_saham) }}</td></tr>
                <tr><th>Yuran Anggota</th><td>{{ $moneyOrPending(optional($shareRecord)->yuran) }}</td></tr>
            </table>
        </aside>
    </div>

</div>
@endsection

@push('styles')
<style>
    .staff-shares-page { gap: 20px; }
    .shares-hero { min-height: 144px; }

    .shares-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 14px;
        border-radius: 8px;
        background: var(--primary);
        color: #fff;
        text-decoration: none;
        font-weight: 700;
    }

    .shares-metrics {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: #fff;
        overflow: hidden;
    }

    .shares-metrics section {
        min-height: 120px;
        padding: 20px;
        border-right: 1px solid var(--line);
        display: grid;
        align-content: start;
        gap: 8px;
    }

    .shares-metrics section:last-child { border-right: 0; }
    .shares-metrics span {
        color: var(--muted);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .shares-metrics strong {
        color: var(--ink);
        font-size: 26px;
        line-height: 1.15;
    }

    .shares-metrics small {
        color: var(--muted);
        font-size: 13px;
        font-weight: 600;
    }

    .shares-actions-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 18px;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: #fff;
    }

    .shares-actions-bar strong {
        display: block;
        color: var(--ink);
        font-size: 15px;
    }

    .shares-actions-bar span {
        display: block;
        margin-top: 4px;
        color: var(--muted);
        font-size: 13px;
        font-weight: 600;
    }

    .shares-actions-bar__buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .shares-layout {
        display: block;
    }

    .share-profile-card {
        border-radius: 10px !important;
        overflow: hidden;
    }

    .share-profile-card .detail-table {
        margin: 22px 24px 24px;
        width: calc(100% - 48px);
    }

    @media (max-width: 1180px) {
        .shares-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .shares-metrics section:nth-child(2n) { border-right: 0; }
        .shares-metrics section:not(:nth-last-child(-n+2)) { border-bottom: 1px solid var(--line); }
    }

    @media (max-width: 760px) {
        .shares-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .shares-actions-bar {
            align-items: flex-start;
            flex-direction: column;
        }

        .shares-actions-bar__buttons {
            width: 100%;
        }

        .shares-metrics {
            grid-template-columns: 1fr;
        }

        .shares-metrics section {
            border-right: 0;
            border-bottom: 1px solid var(--line);
        }

        .shares-metrics section:last-child { border-bottom: 0; }
    }
</style>
@endpush
