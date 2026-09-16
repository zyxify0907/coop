@extends('layouts.app')

@section('title', 'Proses Pengeluaran')
@section('page-title', 'Proses Pengeluaran')
@section('page-subtitle', 'Proses pengeluaran saham, berhenti, berpindah, bersara atau tamat pengajian.')

@section('page-actions')
    <a class="link-button secondary" href="{{ route('admin.permohonan.show', $application) }}">Kembali</a>
@endsection

@section('content')
@php
    $money = fn ($value) => 'RM ' . number_format((float) $value, 2);
    $isStaffRequest = ($data['pemohon_role'] ?? 'ahli') === 'staff';
    $personLabel = $isStaffRequest ? 'Staff' : 'Pelajar';
    $initials = collect(explode(' ', $application->nama_pemohon))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'ST';
    $selectedTypes = collect($data['jenis_permohonan'] ?? [])->filter();
    $requestedTotal = (float) ($requestedShare ?? 0);
    $shareInputValue = (float) $requestedShare;
    $shareBalanceAfter = $currentShare - $shareInputValue;
    $displayNumber = $isStaffRequest ? ($data['no_anggota'] ?? 'Belum dijana') : ($application->no_matrik ?? '-');
@endphp

<div class="withdrawal-page">
    <section class="withdrawal-hero">
        <div>
            <span class="hero-kicker">Proses Permohonan</span>
            <h1>Pengeluaran & Status Keahlian</h1>
            <p>Semua jenis permohonan pengeluaran diproses di sini: saham, berhenti, berpindah, bersara, tamat pengajian atau lain-lain.</p>
        </div>
        <div class="hero-meta">
            <span>{{ now()->format('d M Y') }}</span>
            <strong>{{ $personLabel }}</strong>
        </div>
    </section>

    <section class="panel withdrawal-card">
        <div class="withdrawal-process__head">
            <div class="applicant-cell">
                <span class="applicant-avatar">{{ $initials }}</span>
                <div>
                    <h2>{{ $application->nama_pemohon }}</h2>
                    <p>{{ $displayNumber }} &middot; {{ $application->no_tel ?: 'Telefon tiada' }}</p>
                </div>
            </div>
            <span class="status-pill status-pill--{{ $application->status }}">{{ str_replace('_', ' ', ucfirst($application->status)) }}</span>
        </div>

        <div class="type-strip">
            <span class="type-strip__label">Jenis Permohonan</span>
            <div class="type-list">
                @forelse ($selectedTypes as $type)
                    <span class="type-chip">{{ $type }}</span>
                @empty
                    <span class="type-chip type-chip--muted">Tiada pilihan direkodkan</span>
                @endforelse
            </div>
        </div>

        <div class="withdrawal-summary">
            <div>
                <span>Saham Semasa</span>
                <strong>{{ $money($currentShare) }}</strong>
            </div>
            <div>
                <span>Jumlah Dipohon</span>
                <strong>{{ $money($requestedTotal) }}</strong>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.permohonan.pengeluaran.store', $application) }}" class="withdrawal-form">
            @csrf
            @if (request()->boolean('dialog'))
                <input type="hidden" name="dialog" value="1">
            @endif
            <input type="hidden" name="confirm_withdrawal_process" value="1">

            <div class="field">
                <label for="saham_dipohon">Amaun Pengeluaran Saham</label>
                <input type="hidden" name="saham_dipohon" value="{{ $shareInputValue }}">
                <input id="saham_dipohon" type="text" value="{{ $money($shareInputValue) }}" readonly>
                <small id="share-balance-message" class="{{ $shareBalanceAfter < 0 ? 'error' : '' }}" data-balance="{{ $currentShare }}">
                    {{ $shareBalanceAfter < 0 ? 'Amaun melebihi baki saham sebanyak '.$money(abs($shareBalanceAfter)).'.' : 'Baki selepas proses: '.$money($shareBalanceAfter) }}
                </small>
                @error('saham_dipohon')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="field field-full">
                <label>Maklumat Penyelesaian / Dokumen {{ $personLabel }}</label>
                <div class="info-table">
                    <span>Status Semasa</span>
                    <strong>{{ $defaultStudentStatus === 'aktif' ? 'Aktif' : 'Tidak Aktif / Berhenti / Pindah' }}</strong>
                    <span>Kaedah Penyelesaian</span>
                    <strong>{{ $data['kaedah_terima_bayaran'] ?? '-' }}</strong>
                    <span>Lain-lain Sebab</span>
                    <strong>{{ $data['lain_lain_sebab'] ?? '-' }}</strong>
                </div>
            </div>

            <div class="field">
                <label>Status {{ $personLabel }} Selepas Proses</label>
                <input type="hidden" name="status_pelajar" value="pindah_berhenti">
                <input type="text" value="{{ $personLabel }} Tidak Aktif / Berhenti / Pindah" readonly>
                <small>Status akan terus ditetapkan tidak aktif selepas permohonan selesai diproses.</small>
            </div>

            <div class="field field-full">
                <label for="catatan_admin">Catatan Admin</label>
                <textarea id="catatan_admin" name="catatan_admin" rows="4">{{ old('catatan_admin', $isStaffRequest ? 'Permohonan pengeluaran staff telah diproses dan baki saham staff dikemaskini.' : 'Permohonan pengeluaran telah diproses dan baki saham pelajar dikemaskini.') }}</textarea>
                @error('catatan_admin')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-actions">
                <a class="link-button secondary" href="{{ route('admin.permohonan.show', $application) }}">Batal</a>
                <button class="button withdrawal-pick-button" type="submit">Diluluskan</button>
            </div>
        </form>
    </section>
</div>
@endsection

@push('styles')
<style>
    .page-heading {
        display: none;
    }

    body.dialog-mode .withdrawal-page {
        padding: 20px;
    }

    body.dialog-mode .withdrawal-hero {
        display: none;
    }

    body.dialog-mode .withdrawal-card {
        margin: 0;
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }

    .withdrawal-page {
        display: grid;
        gap: 22px;
    }

    .withdrawal-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        min-height: 176px;
        padding: 34px 38px;
        border: 1px solid var(--line);
        border-left: 4px solid var(--secondary);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 18px 38px rgba(15, 23, 42, .06);
    }

    .hero-kicker {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 0 16px;
        border-radius: 999px;
        background: var(--secondary-soft);
        color: var(--secondary);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .withdrawal-hero h1 {
        margin: 20px 0 10px;
        color: #020617;
        font-size: clamp(30px, 3vw, 44px);
        line-height: 1.05;
        letter-spacing: 0;
    }

    .withdrawal-hero p {
        max-width: 760px;
        margin: 0;
        color: var(--muted);
        font-size: 16px;
        line-height: 1.6;
    }

    .hero-meta {
        display: grid;
        gap: 7px;
        justify-items: end;
        color: var(--muted);
        font-size: 14px;
        font-weight: 700;
        white-space: nowrap;
    }

    .hero-meta strong {
        display: inline-flex;
        align-items: center;
        min-height: 32px;
        padding: 0 14px;
        border-radius: 999px;
        background: var(--success-soft);
        color: var(--success);
        font-size: 13px;
    }

    .withdrawal-card {
        max-width: 1080px;
        width: 100%;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 20px 44px rgba(15, 23, 42, 0.07);
    }

    .withdrawal-process__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 24px 28px;
        border-bottom: 1px solid var(--line);
        background: #fff;
    }

    .applicant-cell {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
    }

    .applicant-avatar {
        width: 56px;
        height: 56px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border: 1px solid #dbeafe;
        background: var(--secondary-soft);
        color: var(--secondary);
        box-shadow: 0 12px 30px rgba(37, 99, 235, 0.12);
        font-size: 16px;
        font-weight: 900;
    }

    .applicant-cell h2 {
        margin: 0;
        color: #020617;
        font-size: 28px;
        line-height: 1.15;
    }

    .applicant-cell p {
        margin: 8px 0 0;
        color: var(--muted);
        font-size: 14px;
        line-height: 1.7;
    }

    .type-strip {
        display: grid;
        gap: 12px;
        padding: 20px 28px;
        border-bottom: 1px solid var(--line);
        background: #fbfdff;
    }

    .type-strip__label {
        color: var(--muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .type-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .type-chip {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 13px;
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        background: var(--secondary-soft);
        color: var(--secondary);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0;
        text-transform: none;
    }

    .type-chip--muted {
        border-color: var(--line);
        background: #f8fafc;
        color: var(--muted);
    }

    .withdrawal-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        border-bottom: 1px solid var(--line);
        background: #f8fbff;
    }

    .withdrawal-summary div {
        padding: 22px 28px;
        border-right: 1px solid var(--line);
    }

    .withdrawal-summary div:last-child {
        border-right: 0;
        background: var(--secondary-soft);
    }

    .withdrawal-summary span {
        display: block;
        color: var(--muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .withdrawal-summary strong {
        display: block;
        margin-top: 8px;
        color: #0f172a;
        font-size: 26px;
        line-height: 1.2;
    }

    .withdrawal-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        padding: 26px 28px 28px;
    }

    .field-full {
        grid-column: 1 / -1;
    }

    .field label {
        display: block;
        margin-bottom: 8px;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .field input,
    .field select,
    .field textarea {
        width: 100%;
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 13px 14px;
        background: #fff;
        color: #020617;
        font: inherit;
    }

    .field textarea {
        min-height: 132px;
        resize: vertical;
    }

    .field input[readonly] {
        background: #f8fafc;
        color: #475569;
        cursor: default;
        font-weight: 800;
    }

    .field small {
        display: block;
        margin-top: 7px;
        color: var(--muted);
        font-size: 12px;
    }

    .info-table {
        display: grid;
        grid-template-columns: minmax(190px, .34fr) minmax(0, 1fr);
        border: 1px solid var(--line);
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    .info-table span,
    .info-table strong {
        padding: 14px 16px;
        border-bottom: 1px solid var(--line);
    }

    .info-table span:nth-last-child(2),
    .info-table strong:last-child {
        border-bottom: 0;
    }

    .info-table span {
        background: #f8fafc;
        color: var(--muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .info-table strong {
        color: #0f172a;
        font-weight: 800;
    }

    .form-actions {
        grid-column: 1 / -1;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        padding-top: 4px;
    }

    .withdrawal-pick-button.is-selected {
        background: var(--success) !important;
        border-color: var(--success) !important;
        color: #fff !important;
        box-shadow: 0 0 0 4px rgba(22, 163, 74, .16) !important;
    }

    .save-decision-button {
        background: #0f172a !important;
        border-color: #0f172a !important;
        color: #fff !important;
    }

    .error {
        color: var(--danger) !important;
        font-weight: 800;
    }

    @media (max-width: 900px) {
        .withdrawal-hero,
        .withdrawal-process__head {
            align-items: flex-start;
            flex-direction: column;
        }

        .hero-meta {
            justify-items: start;
        }

        .withdrawal-summary,
        .withdrawal-form,
        .info-table {
            grid-template-columns: 1fr;
        }

        .withdrawal-summary div {
            border-right: 0;
            border-bottom: 1px solid var(--line);
        }

        .withdrawal-summary div:last-child {
            border-bottom: 0;
        }
    }

    @media (max-width: 640px) {
        .withdrawal-hero,
        .withdrawal-process__head,
        .type-strip,
        .withdrawal-form {
            padding-left: 20px;
            padding-right: 20px;
        }

        .applicant-cell h2 {
            font-size: 24px;
        }

        .form-actions {
            align-items: stretch;
            flex-direction: column;
        }
    }
</style>
@endpush
