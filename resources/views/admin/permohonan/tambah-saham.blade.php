@extends('layouts.app')

@section('title', 'Tambah Saham')
@section('page-title', 'Tambah Saham')
@section('page-subtitle', 'Kemaskini saham pemohon berdasarkan permohonan penambahan saham.')

@section('page-actions')
    <a class="link-button secondary" href="{{ route('admin.permohonan.show', $application) }}">Kembali</a>
@endsection

@section('content')
@php
    $money = fn ($value) => 'RM ' . number_format((float) $value, 2);
    $isStaffRequest = ($data['pemohon_role'] ?? 'ahli') === 'staff';
    $initials = collect(explode(' ', $application->nama_pemohon))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'ST';
    $displayNumber = $isStaffRequest ? ($data['no_anggota'] ?? 'Belum dijana') : ($application->no_matrik ?? '-');
@endphp

<div class="share-add-page">
    <section class="share-add-hero">
        <div>
            <span class="hero-kicker">Penambahan Saham</span>
            <h1>Tambah Saham</h1>
            <p>Kemaskini jumlah saham pemohon selepas bayaran disemak dan permohonan diluluskan.</p>
        </div>
        <div class="hero-meta">
            <span>{{ now()->format('d M Y') }}</span>
            <strong>{{ $isStaffRequest ? 'Staff' : 'Pelajar' }}</strong>
        </div>
    </section>

    <section class="panel share-add-card">
        <div class="share-add__head">
            <div class="applicant-cell">
                <span class="applicant-avatar">{{ $initials }}</span>
                <div>
                    <h2>{{ $application->nama_pemohon }}</h2>
                    <p>{{ $displayNumber }} &middot; {{ $application->no_tel ?: 'Telefon tiada' }}</p>
                </div>
            </div>
            <span class="status-pill status-pill--{{ $application->status }}">{{ str_replace('_', ' ', ucfirst($application->status)) }}</span>
        </div>

        <div class="share-summary">
            <div>
                <span>Saham Semasa</span>
                <strong>{{ $money($currentShare) }}</strong>
            </div>
            <div>
                <span>Mohon Tambah</span>
                <strong>{{ $money($additionalShare) }}</strong>
            </div>
            <div>
                <span>Jumlah Selepas Tambah</span>
                <strong>{{ $money($newShareTotal) }}</strong>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.permohonan.saham.store', $application) }}" class="share-form">
            @csrf
            <input type="hidden" name="confirm_share_addition" value="1">
            <div class="field field-full">
                <label for="amaun_tambahan">Amaun Tambahan Saham</label>
                <input type="hidden" name="amaun_tambahan" value="{{ $additionalShare }}">
                <input id="amaun_tambahan" type="text" value="{{ $money($additionalShare) }}" readonly>
                @error('amaun_tambahan')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="field field-full">
                <label>Maklumat Bayaran {{ $isStaffRequest ? 'Staff' : 'Pelajar' }}</label>
                <div class="payment-info">
                    <span>Tarikh Pengakuan</span>
                    <strong>{{ filled($data['tarikh_pengakuan'] ?? null) ? \Illuminate\Support\Carbon::parse($data['tarikh_pengakuan'])->format('d/m/Y') : '-' }}</strong>
                </div>
            </div>

            <div class="field field-full">
                <label for="catatan_admin">Catatan Admin</label>
                <textarea id="catatan_admin" name="catatan_admin" rows="4">{{ $isStaffRequest ? 'Permohonan penambahan saham staff telah diluluskan dan rekod saham staff dikemaskini.' : 'Permohonan penambahan saham telah diluluskan dan rekod saham pelajar dikemaskini.' }}</textarea>
                @error('catatan_admin')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-actions">
                <a class="link-button secondary" href="{{ route('admin.permohonan.show', $application) }}">Batal</a>
                <button class="button share-pick-button" type="submit">Diluluskan</button>
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

    body.dialog-mode .share-add-page {
        padding: 20px;
    }

    body.dialog-mode .share-add-hero {
        display: none;
    }

    body.dialog-mode .share-add-card {
        margin: 0;
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }

    .share-add-page {
        display: grid;
        gap: 22px;
    }

    .share-add-hero {
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

    .share-add-hero h1 {
        margin: 20px 0 10px;
        color: #020617;
        font-size: clamp(30px, 3vw, 44px);
        line-height: 1.05;
        letter-spacing: 0;
    }

    .share-add-hero p {
        max-width: 720px;
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

    .share-add-card {
        max-width: 1080px;
        width: 100%;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 20px 44px rgba(15, 23, 42, 0.07);
    }

    .share-add__head {
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

    .share-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        border-bottom: 1px solid var(--line);
        background: #f8fbff;
    }

    .share-summary div {
        padding: 22px 28px;
        border-right: 1px solid var(--line);
    }

    .share-summary div:last-child {
        border-right: 0;
        background: var(--secondary-soft);
    }

    .share-summary span {
        display: block;
        color: var(--muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .share-summary strong {
        display: block;
        margin-top: 8px;
        color: #0f172a;
        font-size: 26px;
        line-height: 1.2;
    }

    .share-form {
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

    .payment-info {
        display: grid;
        grid-template-columns: minmax(170px, .34fr) minmax(0, 1fr);
        border: 1px solid var(--line);
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    .payment-info span,
    .payment-info strong {
        padding: 14px 16px;
        border-bottom: 1px solid var(--line);
    }

    .payment-info span:nth-last-child(2),
    .payment-info strong:last-child {
        border-bottom: 0;
    }

    .payment-info span {
        background: #f8fafc;
        color: var(--muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .payment-info strong {
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

    .share-pick-button.is-selected {
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
        display: block;
        margin-top: 6px;
        color: var(--danger);
        font-size: 12px;
        font-weight: 800;
    }

    @media (max-width: 900px) {
        .share-add-hero,
        .share-add__head {
            align-items: flex-start;
            flex-direction: column;
        }

        .hero-meta {
            justify-items: start;
        }

        .share-summary,
        .share-form,
        .payment-info {
            grid-template-columns: 1fr;
        }

        .share-summary div {
            border-right: 0;
            border-bottom: 1px solid var(--line);
        }

        .share-summary div:last-child {
            border-bottom: 0;
        }
    }

    @media (max-width: 640px) {
        .share-add-hero,
        .share-add__head,
        .share-form {
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
