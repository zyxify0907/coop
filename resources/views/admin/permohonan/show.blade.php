@extends('layouts.app')

@section('title', 'Butiran Permohonan')
@section('page-title', 'Butiran Permohonan')
@section('page-subtitle', 'Semak maklumat lengkap permohonan dan buat keputusan.')

@php
    $isDialogMode = request()->boolean('dialog');
    $data = $application->data_permohonan ?? [];
    $isStaffRequest = ($data['pemohon_role'] ?? 'ahli') === 'staff';
    $amountFields = ['amaun_tambahan', 'amaun_dipohon', 'syer_semasa', 'yuran_anggota', 'modal_saham', 'saham_dipohon', 'jumlah_dipohon'];
    $initials = collect(explode(' ', $application->nama_pemohon))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'ST';
    $heirFields = ['nama_waris', 'telefon_waris', 'hubungan_waris', 'penama_nama', 'penama_nric', 'penama_hubungan', 'penama_no_tel', 'penama_alamat', 'penama_poskod', 'penama_peratus', 'penama2_nama', 'penama2_nric', 'penama2_hubungan', 'penama2_no_tel', 'penama2_alamat', 'penama2_poskod', 'penama2_peratus'];
    $witnessFields = ['saksi_nama', 'saksi_nric', 'saksi_tarikh'];
    $hiddenStaffFields = $isStaffRequest ? ['no_pekerja', 'dokumen_sokongan', 'jawatan'] : [];
    $memberData = collect($data)->reject(fn ($value, $key) => in_array($key, array_merge($heirFields, $witnessFields, $hiddenStaffFields), true));
    $heirData = collect($data)->only($heirFields)->filter(fn ($value) => filled($value));
    $witnessData = collect($data)->only($witnessFields)->filter(fn ($value) => filled($value));
    $isFinalDecision = in_array($application->status, ['diluluskan', 'ditolak'], true);
    $displayNumber = $isStaffRequest ? ($data['no_anggota'] ?? 'Belum dijana') : ($application->no_matrik ?? '-');
@endphp

@section('content')
    <section class="application-detail-hero">
        <div class="application-detail-hero__main">
            <span class="detail-kicker">Semakan Permohonan</span>
            <h1>Butiran Permohonan</h1>
            <p>Semak maklumat lengkap pemohon, penama, saksi dan keputusan pentadbiran dalam satu paparan yang lebih kemas.</p>
        </div>
        <div class="application-detail-hero__meta">
            <span class="status-pill status-pill--{{ $application->status }}">{{ str_replace('_', ' ', ucfirst($application->status)) }}</span>
            <strong>{{ $types[$application->jenis]['label'] ?? ucfirst($application->jenis) }}</strong>
            <small>{{ optional($application->tarikh_permohonan)->format('d/m/Y') ?? '-' }}</small>
        </div>
    </section>

    <section class="panel detail-summary-card">
        <div class="summary-profile">
            <div class="student-cell">
                <span class="student-avatar">{{ $initials }}</span>
                <div>
                    <h2>{{ $application->nama_pemohon }}</h2>
                    <p>{{ $displayNumber }} &middot; {{ $application->email ?: 'Email tiada' }} &middot; {{ $application->no_tel ?: 'Telefon tiada' }}</p>
                </div>
            </div>
            <div class="summary-badges">
                <span class="detail-chip">{{ $isStaffRequest ? 'Permohonan Staff' : 'Permohonan Anggota' }}</span>
                <span class="detail-chip detail-chip--soft">ID #{{ $application->id_permohonan }}</span>
            </div>
        </div>
        <div class="summary-stats">
            <div class="summary-stat">
                <span>Status Semasa</span>
                <strong>{{ str_replace('_', ' ', ucfirst($application->status)) }}</strong>
            </div>
            <div class="summary-stat">
                <span>Tarikh Mohon</span>
                <strong>{{ optional($application->tarikh_permohonan)->format('d/m/Y') ?? '-' }}</strong>
            </div>
            <div class="summary-stat">
                <span>Jenis Permohonan</span>
                <strong>{{ $types[$application->jenis]['label'] ?? ucfirst($application->jenis) }}</strong>
            </div>
        </div>
    </section>

    <section class="panel detail-section-card">
        <div class="section-title section-title--wide">
            <h2>Maklumat Permohonan</h2>
            <p>Semua data yang dihantar oleh pemohon melalui borang digital dipaparkan mengikut blok maklumat.</p>
        </div>

        <div class="compact-sections">
            @include('admin.permohonan.partials.detail-list', ['title' => $isStaffRequest ? 'Maklumat Staff' : 'Maklumat Anggota', 'items' => $memberData, 'amountFields' => $amountFields, 'open' => true])

            @if ($heirData->isNotEmpty())
                @include('admin.permohonan.partials.detail-list', ['title' => 'Maklumat Pewaris / Penama', 'items' => $heirData, 'amountFields' => $amountFields, 'open' => false])
            @endif

            @if ($witnessData->isNotEmpty())
                @include('admin.permohonan.partials.detail-list', ['title' => 'Maklumat Saksi', 'items' => $witnessData, 'amountFields' => $amountFields, 'open' => false])
            @endif

            @if ($application->catatan_pelajar)
            <details class="detail-group">
                <summary><h3>Catatan Pemohon</h3></summary>
                <div class="detail-list">
                    <div class="detail-row">
                        <span>Catatan</span>
                        <strong>{{ $application->catatan_pelajar }}</strong>
                    </div>
                </div>
            </details>
            @endif
        </div>
    </section>

    <section class="panel application-documents">
        <div class="section-title section-title--wide">
            <h2>Dokumen Pemohon</h2>
            <p>Dokumen yang dimuat naik oleh {{ $isStaffRequest ? 'staff' : 'pelajar' }} untuk permohonan ini.</p>
        </div>

        @if ($documents->isNotEmpty())
            <div class="application-document-list">
                @foreach ($documents as $document)
                    @php
                        $extension = strtoupper(pathinfo($document->original_name, PATHINFO_EXTENSION) ?: 'FILE');
                    @endphp
                    <article class="application-document-row">
                        <span class="application-document-type">{{ $extension }}</span>
                        <div class="application-document-name">
                            <strong>{{ $document->original_name }}</strong>
                            <span>{{ $documentCategories[$document->category] ?? $document->category }} &middot; {{ number_format($document->size / 1024, 1) }} KB</span>
                        </div>
                        <time>{{ $document->created_at?->format('d/m/Y H:i') ?? '-' }}</time>
                        <div class="application-document-actions">
                            <a href="{{ route('koperasi.documents.view', $document) }}" target="_blank" rel="noopener">Lihat</a>
                            <a href="{{ route('koperasi.documents.download', $document) }}">Muat Turun</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="application-document-empty">
                <strong>Belum ada dokumen</strong>
                <span>Pemohon belum memuat naik dokumen untuk permohonan ini.</span>
            </div>
        @endif
    </section>

    <section class="panel decision-card">
        <div class="section-title">
            <h2>Keputusan Admin</h2>
            <p>Pilih keputusan akhir permohonan.</p>
        </div>

        @if ($isFinalDecision)
            <div class="decision-actions decision-actions--locked">
                <button class="mini-button mini-button--approve" type="button" disabled>Diluluskan</button>
                <button class="mini-button mini-button--reject" type="button" disabled>Ditolak</button>
                <span class="decision-lock-note">Keputusan sudah dimuktamadkan.</span>
            </div>
        @else
            <form method="POST" action="{{ route('admin.permohonan.update', $application) }}" class="decision-actions" data-decision-form>
                @csrf
                @method('PUT')
                <input type="hidden" name="decision_action" value="1">
                <input type="hidden" name="status" value="{{ old('status', '') }}" data-decision-status>
                <div class="decision-note-field">
                    <label for="catatan_admin">Catatan Admin</label>
                    <textarea id="catatan_admin" name="catatan_admin" rows="5" placeholder="Catatan admin">{{ $application->catatan_admin }}</textarea>
                </div>
                @if ($application->jenis === 'saham')
                    @if ($isDialogMode)
                        <a class="mini-button mini-button--share" href="{{ route('admin.permohonan.saham.create', ['permohonan' => $application, 'dialog' => 1]) }}">Tambah Saham</a>
                    @else
                        <a class="mini-button mini-button--share" href="{{ route('admin.permohonan.saham.create', $application) }}" data-share-dialog-open data-dialog-title="Tambah Saham" data-dialog-url="{{ route('admin.permohonan.saham.create', ['permohonan' => $application, 'dialog' => 1]) }}">Tambah Saham</a>
                    @endif
                @elseif ($application->jenis === 'berhenti')
                    @if ($isDialogMode)
                        <a class="mini-button mini-button--withdraw" href="{{ route('admin.permohonan.pengeluaran.create', ['permohonan' => $application, 'dialog' => 1]) }}">Proses Pengeluaran</a>
                    @else
                        <a class="mini-button mini-button--withdraw" href="{{ route('admin.permohonan.pengeluaran.create', $application) }}" data-share-dialog-open data-dialog-title="Proses Pengeluaran Saham" data-dialog-url="{{ route('admin.permohonan.pengeluaran.create', ['permohonan' => $application, 'dialog' => 1]) }}">Proses Pengeluaran</a>
                    @endif
                @else
                    <button class="mini-button mini-button--approve" type="button" data-decision-choice="diluluskan">Diluluskan</button>
                @endif
                <button class="mini-button mini-button--reject" type="{{ in_array($application->jenis, ['saham', 'berhenti'], true) ? 'submit' : 'button' }}" data-decision-choice="ditolak">Ditolak</button>
                @if (! in_array($application->jenis, ['saham', 'berhenti'], true))
                    <button class="mini-button mini-button--save" type="submit">Simpan Keputusan</button>
                @endif
            </form>
        @endif

        @if ($isFinalDecision)
            <div class="note-readonly">
                <strong>Catatan Admin</strong>
                <p>{{ filled($application->catatan_admin) ? $application->catatan_admin : 'Tiada catatan admin.' }}</p>
            </div>
        @endif

        @if ($role === 'admin')
            <form method="POST" action="{{ route('admin.permohonan.destroy', $application) }}" onsubmit="return confirm('Padam permohonan ini? Tindakan ini tidak boleh dibatalkan.');">
                @csrf
                @method('DELETE')
                <button class="delete-button" type="submit">Delete Permohonan</button>
            </form>
        @endif

        @unless ($isDialogMode)
            <a class="back-under-delete" href="{{ route('admin.permohonan.index') }}">Kembali</a>
        @endunless
    </section>

    @unless ($isDialogMode)
        <dialog class="share-process-dialog" data-share-process-dialog aria-labelledby="shareProcessDialogTitle">
            <div class="share-process-dialog__shell">
                <header class="share-process-dialog__head">
                    <div>
                        <span>Permohonan Saham</span>
                        <h2 id="shareProcessDialogTitle">Proses Permohonan</h2>
                    </div>
                    <button class="share-process-dialog__close" type="button" data-share-dialog-close aria-label="Tutup dialog">&times;</button>
                </header>
                <div class="share-process-dialog__body" data-share-dialog-body>
                    <iframe class="share-process-dialog__frame" data-share-dialog-frame title="Dialog permohonan saham" scrolling="no" tabindex="0"></iframe>
                </div>
            </div>
        </dialog>
    @endunless
@endsection

@push('styles')
    <style>
        .detail-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
        }
        .student-cell {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }
        .student-avatar {
            width: 48px;
            height: 48px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: var(--secondary-soft);
            color: var(--secondary);
            border: 1px solid #dbeafe;
            font-size: 13px;
            font-weight: 900;
            flex: 0 0 auto;
        }
        .student-cell h2 {
            margin: 0;
            font-size: 20px;
        }
        .student-cell p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 13px;
        }
        .hero-meta {
            display: grid;
            gap: 7px;
            justify-items: end;
        }
        .hero-meta > span:first-child {
            color: var(--muted);
            font-size: 12px;
            font-weight: 900;
        }
        .section-title {
            margin-bottom: 18px;
        }
        .section-title h2 {
            margin: 0;
            font-size: 18px;
        }
        .section-title p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 13px;
        }
        .compact-sections {
            display: grid;
            gap: 12px;
        }
        .detail-group {
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .detail-group summary {
            min-height: 46px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0 14px;
            cursor: pointer;
            list-style: none;
            background: #f8fafc;
        }
        .detail-group summary::-webkit-details-marker {
            display: none;
        }
        .detail-group summary::after {
            content: '+';
            color: var(--primary);
            font-weight: 900;
        }
        .detail-group[open] summary::after {
            content: '-';
        }
        .detail-group h3 {
            margin: 0;
            color: #16345c;
            font-size: 15px;
        }
        .detail-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            column-gap: 22px;
            padding: 6px 14px;
        }
        .detail-row {
            display: grid;
            grid-template-columns: minmax(130px, .45fr) minmax(0, 1fr);
            gap: 12px;
            align-items: start;
            padding: 10px 0;
            border-bottom: 1px solid var(--line);
        }
        .detail-row:last-child,
        .detail-row:nth-last-child(2):nth-child(odd) {
            border-bottom: 0;
        }
        .detail-row span {
            display: block;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: capitalize;
        }
        .detail-row strong {
            display: block;
            font-size: 14px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }
        .decision-card {
            margin-top: 24px;
            display: grid;
            gap: 18px;
        }
        .decision-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .decision-note-field {
            display: grid;
            flex: 1 0 100%;
            gap: 8px;
        }
        .mini-button,
        .delete-button {
            min-height: 34px;
            border: 0;
            border-radius: 8px;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 900;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        .mini-button--share {
            background: var(--secondary);
            color: #fff;
        }
        .mini-button--withdraw {
            background: #0f766e;
            color: #fff;
        }
        .mini-button--review {
            background: var(--secondary-soft);
            color: var(--secondary);
        }
        .mini-button--approve {
            background: var(--secondary);
            color: #fff;
        }
        .mini-button--reject,
        .delete-button {
            background: var(--danger);
            color: #fff;
        }
        .mini-button--save {
            background: #0f172a;
            color: #fff;
        }
        .mini-button--approve.is-selected {
            background: var(--success) !important;
            border-color: var(--success) !important;
            color: #fff !important;
            box-shadow: 0 0 0 4px rgba(22, 163, 74, .16) !important;
            transform: translateY(-1px) !important;
        }
        .mini-button--reject.is-selected {
            background: var(--danger) !important;
            border-color: var(--danger) !important;
            color: #fff !important;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, .16) !important;
            transform: translateY(-1px) !important;
        }
        .decision-actions--locked {
            align-items: center;
        }
        .mini-button:disabled {
            cursor: not-allowed;
            opacity: .55;
            box-shadow: none;
        }
        .decision-lock-note {
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
        }
        .note-form,
        .decision-note-field {
            display: grid;
            gap: 8px;
        }
        .note-form label,
        .decision-note-field label {
            color: #334155;
            font-size: 13px;
            font-weight: 800;
        }
        .note-form textarea,
        .decision-note-field textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px 12px;
        }
        .note-readonly {
            display: grid;
            gap: 8px;
            padding: 14px 16px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #f8fafc;
        }
        .note-readonly strong {
            color: #334155;
            font-size: 13px;
            font-weight: 900;
        }
        .note-readonly p {
            margin: 0;
            color: #475569;
            line-height: 1.5;
        }
        .delete-button {
            width: 100%;
        }
        .back-under-delete {
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            color: var(--secondary);
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
        }
        @media (max-width: 1000px) {
            .detail-hero {
                align-items: flex-start;
                flex-direction: column;
            }
            .hero-meta {
                justify-items: start;
            }
            .detail-list,
            .detail-row {
                grid-template-columns: 1fr;
            }
            .detail-list {
                padding: 6px 14px 10px;
            }
            .detail-row {
                gap: 6px;
            }
        }
        .page-heading {
            display: none;
        }
        .application-detail-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 22px;
            padding: 30px 32px;
            border: 1px solid var(--line);
            border-left: 5px solid var(--secondary);
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 24px 50px rgba(15, 23, 42, 0.08);
        }
        .application-detail-hero__main {
            max-width: 760px;
        }
        .detail-kicker {
            display: inline-flex;
            align-items: center;
            min-height: 32px;
            padding: 0 14px;
            border-radius: 999px;
            background: var(--secondary-soft);
            color: var(--secondary);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .application-detail-hero__main h1 {
            margin: 16px 0 10px;
            font-size: 38px;
            line-height: 1.08;
        }
        .application-detail-hero__main p {
            margin: 0;
            max-width: 720px;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.75;
        }
        .application-detail-hero__meta {
            display: grid;
            gap: 10px;
            min-width: 210px;
            justify-items: end;
            text-align: right;
        }
        .application-detail-hero__meta strong {
            color: #0f172a;
            font-size: 18px;
        }
        .application-detail-hero__meta small {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }
        .detail-summary-card {
            margin-bottom: 24px;
            overflow: hidden;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 20px 44px rgba(15, 23, 42, 0.07);
        }
        .summary-profile {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 24px 28px;
            border-bottom: 1px solid var(--line);
        }
        .summary-badges {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 10px;
        }
        .detail-chip {
            display: inline-flex;
            align-items: center;
            min-height: 36px;
            padding: 0 14px;
            border-radius: 999px;
            background: var(--secondary);
            color: #fff;
            font-size: 12px;
            font-weight: 900;
        }
        .detail-chip--soft {
            background: var(--secondary-soft);
            color: var(--secondary);
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .summary-stat {
            display: grid;
            gap: 8px;
            padding: 22px 28px;
            border-right: 1px solid var(--line);
        }
        .summary-stat:last-child {
            border-right: 0;
        }
        .summary-stat span {
            color: var(--muted);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .05em;
            text-transform: uppercase;
        }
        .summary-stat strong {
            font-size: 22px;
            line-height: 1.2;
        }
        .student-cell {
            gap: 16px;
        }
        .student-avatar {
            width: 56px;
            height: 56px;
            border-radius: 999px;
            background: var(--secondary-soft);
            color: var(--secondary);
            border: 1px solid #dbeafe;
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.12);
            font-size: 16px;
        }
        .student-cell h2 {
            font-size: 30px;
            line-height: 1.1;
        }
        .student-cell p {
            margin-top: 8px;
            font-size: 14px;
            line-height: 1.7;
        }
        .detail-section-card {
            margin-bottom: 24px;
            padding: 26px 24px 18px;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 20px 44px rgba(15, 23, 42, 0.07);
        }
        .section-title--wide {
            padding: 0 4px 4px;
        }
        .section-title h2 {
            font-size: 24px;
            line-height: 1.15;
        }
        .section-title p {
            margin-top: 8px;
            font-size: 14px;
            line-height: 1.7;
        }
        .compact-sections {
            gap: 14px;
        }
        .detail-group {
            border-radius: 18px;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.04);
        }
        .detail-group summary {
            min-height: 60px;
            padding: 0 18px;
            background: #f8fbff;
        }
        .detail-group summary::after {
            font-size: 18px;
        }
        .detail-group h3 {
            font-size: 18px;
        }
        .detail-list {
            column-gap: 28px;
            padding: 10px 18px 12px;
        }
        .detail-row {
            grid-template-columns: minmax(180px, .46fr) minmax(0, 1fr);
            gap: 16px;
            padding: 14px 0;
        }
        .detail-row span {
            color: var(--muted-2);
            font-size: 11px;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .detail-row strong {
            color: #0f172a;
            font-size: 15px;
            line-height: 1.7;
        }
        .decision-card {
            padding: 24px;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 20px 44px rgba(15, 23, 42, 0.07);
        }
        .application-documents {
            padding: 0;
            overflow: hidden;
        }
        .application-documents > .section-title {
            padding: 22px 24px 18px;
            border-bottom: 1px solid var(--line, #e2e8f0);
        }
        .application-document-list {
            display: grid;
        }
        .application-document-row {
            display: grid;
            grid-template-columns: 54px minmax(0, 1fr) 140px auto;
            align-items: center;
            gap: 16px;
            padding: 16px 24px;
            border-bottom: 1px solid var(--line, #e2e8f0);
        }
        .application-document-row:last-child {
            border-bottom: 0;
        }
        .application-document-type {
            display: grid;
            place-items: center;
            min-height: 38px;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
        }
        .application-document-name {
            display: grid;
            gap: 4px;
            min-width: 0;
        }
        .application-document-name strong {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .application-document-name span,
        .application-document-row time {
            color: var(--muted);
            font-size: 13px;
        }
        .application-document-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .application-document-actions a {
            padding: 9px 12px;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }
        .application-document-actions a:last-child {
            border-color: var(--line, #e2e8f0);
            background: #fff;
            color: #334155;
        }
        .application-document-empty {
            display: grid;
            justify-items: center;
            gap: 5px;
            padding: 34px 24px;
            color: var(--muted);
            text-align: center;
        }
        .application-document-empty strong {
            color: #0f172a;
        }
        .decision-actions {
            gap: 10px;
        }
        .mini-button,
        .delete-button {
            min-height: 42px;
            border-radius: 12px;
            padding: 0 16px;
            font-size: 13px;
        }
        .note-form,
        .decision-note-field {
            gap: 10px;
        }
        .note-form textarea,
        .decision-note-field textarea {
            border-radius: 14px;
            padding: 12px 14px;
            min-height: 140px;
            resize: vertical;
        }
        .share-process-dialog {
            width: min(1040px, calc(100vw - 32px));
            max-width: 1040px;
            height: min(780px, calc(100dvh - 32px));
            max-height: 780px;
            padding: 0;
            border: 0;
            border-radius: 18px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 28px 80px rgba(15, 23, 42, .28);
        }
        .share-process-dialog::backdrop {
            background: rgba(15, 23, 42, .58);
            backdrop-filter: blur(3px);
        }
        .share-process-dialog__shell {
            display: grid;
            grid-template-rows: auto minmax(0, 1fr);
            height: 100%;
            min-height: 0;
            background: #fff;
        }
        .share-process-dialog__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 22px;
            border-bottom: 1px solid var(--line);
            background: #fff;
        }
        .share-process-dialog__head span {
            color: var(--secondary);
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .share-process-dialog__head h2 {
            margin: 4px 0 0;
            color: #0f172a;
            font-size: 21px;
            line-height: 1.2;
        }
        .share-process-dialog__close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border: 1px solid var(--line);
            border-radius: 9px;
            background: #fff;
            color: #0f172a;
            font-size: 22px;
            font-weight: 900;
            line-height: 1;
            cursor: pointer;
        }
        .share-process-dialog__close:hover {
            background: var(--primary-soft);
            color: var(--primary);
        }
        .share-process-dialog__frame {
            display: block;
            width: 100%;
            height: 100%;
            min-height: 0;
            border: 0;
            background: #fff;
            overflow: hidden;
        }
        .share-process-dialog__body {
            min-height: 0;
            overflow-y: scroll;
            overflow-x: hidden;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            background: #f8fafc;
        }
        @media (max-width: 1000px) {
            .application-detail-hero,
            .summary-profile {
                align-items: flex-start;
                flex-direction: column;
            }
            .application-detail-hero {
                padding: 24px 22px;
            }
            .application-detail-hero__main h1 {
                font-size: 30px;
            }
            .application-detail-hero__meta,
            .summary-badges {
                justify-items: start;
                text-align: left;
            }
            .summary-stats {
                grid-template-columns: 1fr;
            }
            .summary-stat {
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }
            .summary-stat:last-child {
                border-bottom: 0;
            }
        }
        @media (max-width: 640px) {
            .detail-summary-card,
            .detail-section-card,
            .decision-card {
                border-radius: 20px;
            }
            .summary-profile,
            .summary-stat,
            .decision-card {
                padding-left: 18px;
                padding-right: 18px;
            }
            .student-cell h2 {
                font-size: 24px;
            }
            .application-document-row {
                grid-template-columns: 48px minmax(0, 1fr);
            }
            .application-document-row time,
            .application-document-actions {
                grid-column: 2;
            }
            .application-document-actions {
                flex-wrap: wrap;
            }
            .share-process-dialog {
                width: 100vw;
                height: 100dvh;
                max-height: none;
                border-radius: 0;
            }
            .share-process-dialog__head {
                padding: 14px 16px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('[data-decision-form]');
            const dialog = document.querySelector('[data-share-process-dialog]');
            const frame = document.querySelector('[data-share-dialog-frame]');
            const body = document.querySelector('[data-share-dialog-body]');
            const title = document.getElementById('shareProcessDialogTitle');
            const openButtons = document.querySelectorAll('[data-share-dialog-open]');
            const closeButton = document.querySelector('[data-share-dialog-close]');
            const completionPath = @json(parse_url(route('admin.permohonan.show', $application), PHP_URL_PATH));

            openButtons.forEach((button) => {
                button.addEventListener('click', (event) => {
                    if (!dialog || !frame) {
                        return;
                    }

                    event.preventDefault();
                    title.textContent = button.dataset.dialogTitle || 'Proses Permohonan';
                    frame.src = button.dataset.dialogUrl || button.href;

                    if (typeof dialog.showModal === 'function') {
                        dialog.showModal();
                    }
                });
            });

            closeButton?.addEventListener('click', () => {
                dialog?.close();
            });

            dialog?.addEventListener('close', () => {
                if (frame) {
                    frame.removeAttribute('src');
                }
            });

            frame?.addEventListener('load', () => {
                try {
                    const doc = frame.contentDocument;
                    const framePath = frame.contentWindow.location.pathname;

                    if (framePath === completionPath) {
                        dialog?.close();
                        window.location.reload();
                        return;
                    }

                    doc.documentElement.style.setProperty('height', 'auto', 'important');
                    doc.documentElement.style.setProperty('overflow-y', 'auto', 'important');
                    doc.body.style.setProperty('height', 'auto', 'important');
                    doc.body.style.setProperty('overflow-y', 'auto', 'important');

                    const frameHeight = Math.max(
                        doc.documentElement.scrollHeight,
                        doc.body.scrollHeight,
                        doc.querySelector('.content')?.scrollHeight || 0,
                        doc.querySelector('#main-content')?.scrollHeight || 0,
                        body?.clientHeight || 0
                    );
                    frame.style.setProperty('height', `${frameHeight}px`, 'important');
                    body.scrollTop = 0;

                    doc.addEventListener('wheel', (event) => {
                        body?.scrollBy({ top: event.deltaY, left: event.deltaX });
                        event.preventDefault();
                    }, { passive: false });

                    doc.addEventListener('keydown', (event) => {
                        if (!body) return;
                        const offsets = { ArrowDown: 48, ArrowUp: -48, PageDown: body.clientHeight * .8, PageUp: -body.clientHeight * .8, Home: -body.scrollTop, End: body.scrollHeight };
                        if (!(event.key in offsets)) return;
                        body.scrollBy({ top: offsets[event.key] });
                        event.preventDefault();
                    });
                } catch (error) {
                    // Ignore cross-document timing while iframe is loading.
                }
            });

            if (!form) {
                return;
            }

            const statusInput = form.querySelector('[data-decision-status]');
            const choices = form.querySelectorAll('[data-decision-choice]');

            choices.forEach((button) => {
                button.addEventListener('click', () => {
                    statusInput.value = button.dataset.decisionChoice;
                    choices.forEach((choice) => {
                        choice.classList.remove('is-selected');
                        choice.setAttribute('aria-pressed', 'false');
                    });
                    button.classList.add('is-selected');
                    button.setAttribute('aria-pressed', 'true');
                });
            });

            form.addEventListener('submit', (event) => {
                if (!statusInput.value) {
                    event.preventDefault();
                    alert('Sila pilih Diluluskan atau Ditolak sebelum simpan.');
                }
            });
        });
    </script>
@endpush
