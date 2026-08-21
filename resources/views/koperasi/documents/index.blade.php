@extends('layouts.app')

@section('title', 'Dokumen')
@section('page-title', 'Dokumen')
@section('page-subtitle', 'Semak dokumen yang dihantar bersama permohonan.')

@section('content')
    @include('components.coop-page-style')

    @php
        $ownerLabels = [
            'ahli' => 'Pelajar / Ahli',
            'staff' => 'Staff',
        ];
        $categoryPurpose = [
            'slip_bayaran' => 'Bukti bayaran untuk permohonan anggota atau tambah saham.',
            'salinan_ic' => 'Dokumen pengenalan pemohon.',
            'surat_pindah_berhenti_persaraan' => 'Surat sokongan untuk permohonan pindah, berhenti atau persaraan.',
            'tambah_saham' => 'Lampiran sokongan permohonan tambah saham.',
            'pengeluaran_saham' => 'Lampiran sokongan pengeluaran saham.',
            'surat_berhenti' => 'Surat rasmi berhenti keahlian koperasi.',
            'surat_pindah' => 'Surat pindah untuk proses keluar / bertukar tempat.',
            'kad_surat_persaraan' => 'Dokumen persaraan yang berkaitan.',
            'surat_tamat_pengajian' => 'Dokumen tamat pengajian pelajar.',
            'dokumen_sokongan_lain' => 'Dokumen tambahan yang diminta oleh koperasi.',
        ];
        $documentPurposeLabel = fn (string $purpose, string $category): string => match (true) {
            str_contains($purpose, 'Penambahan Saham') => 'Permohonan Penambahan Saham',
            str_contains($purpose, 'Anggota') => 'Permohonan Anggota Koperasi',
            str_contains($purpose, 'Pengeluaran') || str_contains($purpose, 'Berhenti') || str_contains($purpose, 'Pindah') || str_contains($purpose, 'Bersara') => 'Permohonan Pengeluaran Saham / Berhenti',
            $category === 'slip_bayaran' => 'Permohonan Anggota Koperasi / Penambahan Saham',
            $category === 'salinan_ic' => 'Permohonan Anggota Koperasi',
            $category === 'surat_pindah_berhenti_persaraan' => 'Permohonan Pengeluaran Saham / Berhenti',
            default => 'Dokumen Sokongan',
        };
    @endphp

    <div class="coop-wrap document-page">
        <section class="coop-hero document-hero">
            <div>
                <span class="coop-kicker">DOKUMEN KOPERASI</span>
                <h1>Dokumen</h1>
                <p>Semak rekod dokumen yang dihantar bersama permohonan anda.</p>
            </div>
            <div class="document-note">
                <strong>PDF, JPG, PNG</strong>
                <span>Maksimum 5MB setiap fail.</span>
            </div>
        </section>

        <section class="panel document-panel">
            <div class="document-panel__head">
                <div>
                    <h2>Senarai Dokumen</h2>
                    <p>{{ $role === 'admin' ? 'Admin boleh mencari semua dokumen pelajar dan staff.' : 'Paparan ini hanya untuk dokumen milik akaun anda.' }}</p>
                </div>
            </div>

            <form class="document-filter" method="GET" action="{{ route('koperasi.documents.index') }}">
                <div class="field">
                    <label for="search">{{ $role === 'admin' ? 'Cari Dokumen / Pemilik' : 'Cari Dokumen' }}</label>
                    <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ $role === 'admin' ? 'Nama, no matrik, permohonan atau nama fail' : 'Nama fail, kategori atau permohonan' }}">
                </div>
                @if ($role === 'admin')
                    <div class="field">
                        <label for="owner_role">Pemilik</label>
                        <select id="owner_role" name="owner_role">
                            <option value="">Semua pemilik</option>
                            @foreach ($ownerLabels as $key => $label)
                                <option value="{{ $key }}" @selected(($filters['owner_role'] ?? '') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="field">
                    <label for="category">Kategori</label>
                    <select id="category" name="category">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['category'] ?? '') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="document-filter__actions">
                    <button class="button" type="submit">Cari</button>
                    <a class="link-button secondary" href="{{ route('koperasi.documents.index') }}">Reset</a>
                </div>
            </form>

            <div class="document-table-wrap">
                <table class="document-table">
                    <thead>
                        <tr>
                            <th>Dokumen</th>
                            <th>Permohonan</th>
                            <th>Kategori</th>
                            <th>Pemilik</th>
                            <th>Saiz</th>
                            <th>Tarikh Upload</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $document)
                            @php
                                $owner = $document->owner_role === 'staff' ? $document->staff : $document->student;
                                $ownerName = $owner->nama ?? 'Pemilik tidak dijumpai';
                                $ownerNumber = $document->owner_role === 'staff'
                                    ? ($owner->no_pekerja ?? 'Staff #'.$document->owner_id)
                                    : ($owner->no_matrik ?? 'Pelajar #'.$document->owner_id);
                                $ownerLabel = $ownerLabels[$document->owner_role] ?? strtoupper($document->owner_role);
                                $extension = strtoupper(pathinfo($document->original_name, PATHINFO_EXTENSION) ?: 'FILE');
                            @endphp
                            <tr>
                                <td>
                                    <div class="document-file-cell">
                                        <span class="file-badge">{{ $extension }}</span>
                                        <div>
                                            <strong>{{ $document->original_name }}</strong>
                                            <span>{{ $document->mime_type }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="purpose-pill">{{ $documentPurposeLabel($document->application_purpose ?? ($categoryPurpose[$document->category] ?? ''), $document->category) }}</span>
                                </td>
                                <td>
                                    <span class="category-pill">{{ $categories[$document->category] ?? $document->category }}</span>
                                    <span class="category-purpose">{{ $categoryPurpose[$document->category] ?? 'Dokumen koperasi.' }}</span>
                                </td>
                                <td>
                                    <div class="owner-cell">
                                        <span class="owner-avatar">{{ strtoupper(substr($ownerName, 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $ownerName }}</strong>
                                            <span>{{ $ownerNumber }} &middot; {{ $ownerLabel }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ number_format($document->size / 1024, 1) }} KB</td>
                                <td>{{ $document->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <div class="document-actions">
                                        <a class="action-view" href="{{ route('koperasi.documents.view', $document) }}" target="_blank" rel="noopener">Lihat</a>
                                        <a class="action-download" href="{{ route('koperasi.documents.download', $document) }}">Download</a>
                                        <form method="POST" action="{{ route('koperasi.documents.destroy', $document) }}" onsubmit="return confirm('Padam dokumen ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="action-delete" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">Tiada dokumen untuk paparan ini.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $documents->links() }}
    </div>
@endsection

@push('styles')
    <style>
        .document-page {
            gap: 22px;
        }

        .document-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .document-note {
            min-width: 210px;
            padding: 16px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: #fff;
        }

        .document-note strong,
        .document-note span {
            display: block;
        }

        .document-note strong {
            color: var(--secondary);
            font-size: 16px;
            font-weight: 900;
        }

        .document-note span {
            margin-top: 5px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .document-panel {
            overflow: hidden;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 18px 38px rgba(15, 23, 42, .06);
        }

        .document-panel__head {
            padding: 24px 28px;
            border-bottom: 1px solid var(--line);
        }

        .document-panel__head h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
        }

        .document-panel__head p {
            margin: 8px 0 0;
            color: var(--muted);
            font-weight: 700;
        }

        .document-prompt-list {
            display: grid;
            gap: 12px;
        }

        .document-required-block {
            display: grid;
            gap: 12px;
            padding: 18px;
            border-bottom: 1px solid var(--line);
            background: #fff;
        }

        .document-required-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .document-required-head strong {
            color: var(--text);
            font-size: 15px;
            font-weight: 900;
        }

        .document-required-head span {
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
            text-align: right;
        }

        .document-prompt-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 16px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fbfdff;
        }

        .document-prompt-copy {
            display: grid;
            gap: 4px;
            min-width: 220px;
        }

        .document-prompt-copy strong {
            color: var(--text);
            font-size: 14px;
            font-weight: 900;
        }

        .document-prompt-copy em {
            width: fit-content;
            padding: 3px 8px;
            border-radius: 999px;
            background: var(--secondary-soft);
            color: var(--secondary);
            font-size: 12px;
            font-style: normal;
            font-weight: 900;
        }

        .document-prompt-copy span {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .document-prompt-upload {
            display: grid;
            grid-template-columns: minmax(180px, 1fr) auto;
            gap: 10px;
            align-items: center;
            min-width: min(100%, 360px);
        }

        .document-prompt-upload input[type="file"] {
            position: absolute;
            inline-size: 1px;
            block-size: 1px;
            overflow: hidden;
            opacity: 0;
        }

        .document-upload,
        .document-filter {
            display: grid;
            gap: 12px;
            padding: 18px;
            border-bottom: 1px solid var(--line);
            background: #fbfdff;
            align-items: end;
        }

        .document-upload {
            grid-template-columns: minmax(260px, 1fr) minmax(220px, .85fr) minmax(260px, .9fr) auto;
            column-gap: 12px;
            justify-content: stretch;
        }

        .document-filter {
            grid-template-columns: minmax(260px, 1.4fr) repeat(2, minmax(170px, .7fr)) auto;
        }

        .document-upload .field > label:not(.file-trigger),
        .document-filter .field > label {
            display: block;
            margin-bottom: 7px;
            color: #475569;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .document-upload input:not([type="file"]),
        .document-upload select,
        .document-filter input,
        .document-filter select {
            width: 100%;
            min-height: 44px;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 10px 12px;
            background: #fff;
            font: inherit;
        }

        .field--file input[type="file"] {
            position: absolute;
            inline-size: 1px;
            block-size: 1px;
            overflow: hidden;
            opacity: 0;
        }

        .file-trigger {
            min-height: 44px;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin: 0;
            border: 1px dashed #bfdbfe;
            border-radius: 12px;
            background: var(--secondary-soft);
            color: var(--secondary) !important;
            cursor: pointer;
            text-transform: none !important;
            letter-spacing: 0 !important;
            font-size: 14px !important;
        }

        .document-upload__actions,
        .document-filter__actions,
        .document-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .document-upload__actions {
            align-self: end;
        }

        .document-upload__actions .button {
            min-height: 44px;
            padding-inline: 20px;
            white-space: nowrap;
        }

        .document-table-wrap {
            overflow-x: auto;
        }

        .document-table {
            width: 100%;
            min-width: 1060px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .document-table th {
            padding: 15px 18px;
            background: #f8fafc;
            color: var(--muted);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .03em;
            text-align: left;
            text-transform: uppercase;
        }

        .document-table td {
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }

        .document-file-cell,
        .owner-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .file-badge {
            width: 52px;
            height: 42px;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            background: var(--secondary-soft);
            color: var(--secondary);
            font-size: 12px;
            font-weight: 900;
        }

        .owner-avatar {
            width: 42px;
            height: 42px;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            background: var(--secondary-soft);
            color: var(--secondary);
            font-weight: 900;
        }

        .document-file-cell strong,
        .document-file-cell span,
        .owner-cell strong,
        .owner-cell span {
            display: block;
        }

        .document-file-cell span,
        .owner-cell span {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .category-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            background: var(--secondary-soft);
            color: var(--secondary);
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }

        .purpose-pill {
            display: inline-flex;
            max-width: 230px;
            min-height: 28px;
            align-items: center;
            padding: 4px 9px;
            border: 1px solid #c7d2fe;
            border-radius: 10px;
            background: #eef2ff;
            color: #1e3a8a;
            font-size: 12px;
            font-weight: 900;
            line-height: 1.25;
            white-space: normal;
        }

        .category-purpose {
            display: block;
            max-width: 260px;
            margin-top: 6px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            line-height: 1.35;
        }

        .action-view,
        .action-download,
        .action-delete {
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 10px;
            padding: 0 16px;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
        }

        .action-view,
        .action-download {
            background: var(--secondary-soft);
            color: var(--secondary);
        }

        .action-delete {
            background: var(--danger-soft);
            color: var(--danger);
        }

        @media (max-width: 1100px) {
            .document-upload,
            .document-filter {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 760px) {
            .document-hero {
                align-items: flex-start;
                flex-direction: column;
            }

            .document-prompt-item {
                align-items: stretch;
                flex-direction: column;
            }

            .document-required-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .document-required-head span {
                text-align: left;
            }

            .document-prompt-upload {
                grid-template-columns: 1fr;
                min-width: 0;
            }

            .document-note {
                width: 100%;
            }

            .document-upload,
            .document-filter {
                grid-template-columns: 1fr;
            }

            .document-upload__actions .button,
            .document-filter__actions .button,
            .document-filter__actions .link-button {
                width: 100%;
            }
        }
    </style>
@endpush
