@extends('layouts.app')

@section('title', 'Senarai Ahli')
@section('page-title', 'Senarai Ahli')
@section('page-subtitle', 'Urus rekod ahli koperasi dan import data ahli.')

@section('content')
    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert--success" role="alert">
            <svg class="alert__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <div>
                <strong>Berjaya!</strong>
                <span>{{ session('success') }}</span>
            </div>
            <button class="alert__close" type="button" aria-label="Tutup notifikasi">&times;</button>
        </div>
    @endif

    @error('file')
        <div class="alert alert--danger" role="alert">
            <svg class="alert__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <div>
                <strong>Ralat!</strong>
                <span>{{ $message }}</span>
            </div>
            <button class="alert__close" type="button" aria-label="Tutup notifikasi">&times;</button>
        </div>
    @enderror

    {{-- Import Section --}}
    <section class="panel panel--import">
        <div class="panel__header">
            <div class="panel__title-group">
                <h2 class="panel__title">Import Data Ahli</h2>
                <p class="panel__subtitle">Muat naik fail CSV atau Excel (.xlsx) dengan kolum Nama, No Matrik dan Semester.</p>
            </div>
            <div class="panel__actions">
                <a href="#" class="btn btn--ghost btn--sm" download>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Template
                </a>
            </div>
        </div>

        <div class="panel__body">
            <form action="{{ route('admin.ahli.import') }}" method="POST" enctype="multipart/form-data" class="import-form" id="importForm">
                @csrf
                <div class="file-drop-zone" id="fileDropZone">
                    <input type="file" name="file" id="fileInput" accept=".csv,.xlsx" required>
                    <div class="file-drop-zone__content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <div>
                            <span class="file-drop-zone__title">Seret & lepaskan fail di sini</span>
                            <span class="file-drop-zone__subtitle">atau klik untuk pilih fail</span>
                        </div>
                        <div class="file-drop-zone__info">
                            <span>Format: CSV, Excel (.xlsx)</span>
                            <span>Maksimum: 5MB</span>
                        </div>
                    </div>
                    <div class="file-drop-zone__preview" id="filePreview" hidden>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        <div>
                            <strong id="fileName">file.csv</strong>
                            <span id="fileSize">0 KB</span>
                        </div>
                        <button type="button" class="btn-remove-file" id="removeFile" aria-label="Buang fail">&times;</button>
                    </div>
                </div>

                <div class="import-form__actions">
                    <button type="submit" class="btn btn--primary" id="importBtn" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        Import Data Ahli
                    </button>
                    <div class="import-form__progress" id="uploadProgress" hidden>
                        <div class="progress-bar">
                            <div class="progress-bar__fill" id="progressFill" style="width: 0%"></div>
                        </div>
                        <span class="progress-text" id="progressText">Memuat naik...</span>
                    </div>
                </div>
            </form>
        </div>
    </section>

    {{-- Main Content Grid --}}
    <div class="content-grid">
        {{-- Members Table --}}
        <section class="panel panel--members">
            <div class="panel__header">
                <div class="panel__title-group">
                    <h2 class="panel__title">Rekod Ahli</h2>
                    <p class="panel__subtitle">Medan utama: Nama, No Matrik, Semester, SYER dan YURAN.</p>
                </div>
                <div class="panel__stats">
                    <div class="stat-chip">
                        <span class="stat-chip__label">Jumlah</span>
                        <strong class="stat-chip__value">{{ $ahli->total() }}</strong>
                    </div>
                </div>
            </div>

            <div class="panel__body panel__body--no-padding">
                {{-- Table Controls --}}
                <div class="table-controls">
                    <div class="search-wrapper">
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" class="search-input" placeholder="Cari ahli..." id="tableSearch" aria-label="Cari ahli">
                    </div>
                    <div class="filter-group">
                        <select class="filter-select" id="semesterFilter" aria-label="Tapis mengikut semester">
                            <option value="">Semua Semester</option>
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                            <option value="3">Semester 3</option>
                            <option value="4">Semester 4</option>
                            <option value="5">Semester 5</option>
                            <option value="6">Semester 6</option>
                        </select>
                        <button class="btn btn--ghost btn--sm" id="resetFilters">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 1 0 9-9m0 0v6m0-6h-6"/>
                            </svg>
                            Set Semula
                        </button>
                    </div>
                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table--members">
                        <thead>
                            <tr>
                                <th class="col-name">
                                    <span>Nama</span>
                                    <button class="sort-btn" data-sort="nama" aria-label="Susun mengikut nama">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6-6 6 6"/>
                                            <path d="M6 15l6 6 6-6"/>
                                        </svg>
                                    </button>
                                </th>
                                <th class="col-matrik">No Matrik</th>
                                <th class="col-semester">Semester</th>
                                <th class="col-syer">SYER</th>
                                <th class="col-yuran">YURAN</th>
                                <th class="col-actions">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody id="memberTableBody">
                            @forelse ($ahli as $member)
                                @php
                                    $semesterNumber = $member->semester ? preg_replace('/^sem\s+/i', '', $member->semester) : '';
                                @endphp
                                <tr class="member-row" 
                                    data-id="{{ $member->id }}" 
                                    data-name="{{ strtolower($member->nama) }}" 
                                    data-matrik="{{ $member->no_matrik }}" 
                                    data-semester="{{ $semesterNumber }}">
                                    <td class="col-name">
                                        <div class="member-info">
                                            <div class="member-avatar" style="background:{{ '#' . substr(md5($member->nama), 0, 6) }}">
                                                {{ strtoupper(substr($member->nama, 0, 2)) }}
                                            </div>
                                            <div>
                                                <strong>{{ $member->nama }}</strong>
                                                <span class="member-id">#{{ $member->no_matrik }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="col-matrik">{{ $member->no_matrik }}</td>
                                    <td class="col-semester">
                                        <span class="badge badge--semester semester-{{ $semesterNumber }}">
                                            {{ $member->semester ?: '-' }}
                                        </span>
                                    </td>
                                    <td class="col-syer">
                                        <span class="amount">RM {{ number_format((float) optional($member->saham)->syer, 2) }}</span>
                                    </td>
                                    <td class="col-yuran">
                                        <span class="amount">RM {{ number_format((float) optional($member->saham)->yuran, 2) }}</span>
                                    </td>
                                    <td class="col-actions">
                                        <button class="btn-action" title="Lihat detail" aria-label="Lihat detail ahli">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </button>
                                        <button class="btn-action" title="Edit ahli" aria-label="Edit ahli">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                        </button>
                                        <button class="btn-action btn-action--danger" title="Padam ahli" aria-label="Padam ahli">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                            </svg>
                                            <p>Tiada rekod ahli lagi.</p>
                                            <span>Import data ahli untuk bermula.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Table Footer --}}
                <div class="table-footer">
                    <div class="table-footer__info">
                        Menunjukkan <strong>{{ $ahli->firstItem() ?? 0 }}</strong> - <strong>{{ $ahli->lastItem() ?? 0 }}</strong>
                        daripada <strong>{{ $ahli->total() }}</strong> rekod
                    </div>
                    <div class="table-footer__pagination">
                        {{ $ahli->links() }}
                    </div>
                </div>
            </div>
        </section>

        {{-- Side Panel --}}
        <aside class="side-panel">
            {{-- Latest Import Report --}}
            <section class="panel panel--import-report">
                <div class="panel__header panel__header--compact">
                    <h2 class="panel__title">Laporan Import Terkini</h2>
                    @if($latestImport)
                        <span class="badge badge--info">Terbaru</span>
                    @endif
                </div>

                <div class="panel__body">
                    @if ($latestImport)
                        <div class="import-report">
                            <div class="import-report__file">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                <div>
                                    <strong>{{ $latestImport->filename }}</strong>
                                    <span>{{ $latestImport->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>

                            <div class="import-stats">
                                <div class="import-stat import-stat--success">
                                    <span class="import-stat__label">Berjaya</span>
                                    <strong class="import-stat__value">{{ $latestImport->imported_count }}</strong>
                                    <span class="import-stat__icon">OK</span>
                                </div>
                                <div class="import-stat import-stat--failed {{ $latestImport->failed_count > 0 ? 'import-stat--warning' : '' }}">
                                    <span class="import-stat__label">Gagal</span>
                                    <strong class="import-stat__value">{{ $latestImport->failed_count }}</strong>
                                    <span class="import-stat__icon">{{ $latestImport->failed_count > 0 ? '!' : 'OK' }}</span>
                                </div>
                            </div>

                            @if ($latestImport->errors && count($latestImport->errors) > 0)
                                <div class="import-errors">
                                    <div class="import-errors__header">
                                        <span>Ralat Import</span>
                                        <span class="import-errors__count">{{ count($latestImport->errors) }}</span>
                                    </div>
                                    <div class="import-errors__list">
                                        @foreach ($latestImport->errors as $error)
                                            <div class="import-error-item">
                                                <span class="import-error-item__row">
                                                    Baris {{ $error['row'] ?? '-' }}
                                                    @if ($error['no_matrik'] ?? false)
                                                        <span class="import-error-item__matrik">({{ $error['no_matrik'] }})</span>
                                                    @endif
                                                </span>
                                                <span class="import-error-item__message">{{ $error['message'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="empty-state empty-state--small">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <p>Belum ada import dijalankan.</p>
                            <span>Muat naik fail untuk mula import.</span>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Quick Actions --}}
            <section class="panel panel--quick-actions">
                <div class="panel__body">
                    <div class="quick-actions">
                        <a href="#" class="quick-action">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14"/>
                                <path d="M5 12h14"/>
                            </svg>
                            <span>Tambah Ahli Baru</span>
                        </a>
                        <a href="#" class="quick-action">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4v16h16"/>
                                <polyline points="20 10 12 18 8 14"/>
                            </svg>
                            <span>Laporan Bulanan</span>
                        </a>
                        <a href="#" class="quick-action">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            <span>Eksport Data</span>
                        </a>
                    </div>
                </div>
            </section>
        </aside>
    </div>
@endsection

@push('styles')
    <style>
        /* ===== CSS Variables ===== */
        :root {
            --color-primary: var(--primary);
            --color-primary-light: var(--primary-soft);
            --color-primary-dark: var(--primary-dark);
            --color-success: var(--success);
            --color-success-light: var(--success-soft);
            --color-danger: var(--danger);
            --color-danger-light: var(--danger-soft);
            --color-warning: var(--warning);
            --color-warning-light: var(--warning-soft);
            --color-info: var(--secondary);
            --color-info-light: var(--secondary-soft);
            --color-gray-50: #F8FAFC;
            --color-gray-100: #F1F5F9;
            --color-gray-200: #E2E8F0;
            --color-gray-300: #CBD5E1;
            --color-gray-400: #94A3B8;
            --color-gray-500: #64748B;
            --color-gray-600: #475569;
            --color-gray-700: #334155;
            --color-gray-800: #1E293B;
            --color-gray-900: #0F172A;
            --shadow-sm: 0 1px 2px rgba(16, 24, 40, 0.05);
            --shadow-md: 0 4px 12px rgba(16, 24, 40, 0.08);
            --shadow-lg: 0 8px 24px rgba(16, 24, 40, 0.12);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== Alerts ===== */
        .alert {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            border: 1px solid transparent;
            position: relative;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert--success {
            background: var(--color-success-light);
            border-color: #b8e6d8;
            color: #0b6e54;
        }

        .alert--danger {
            background: var(--color-danger-light);
            border-color: #fccdca;
            color: #a62b21;
        }

        .alert__icon {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
        }

        .alert strong {
            display: block;
            font-size: 14px;
        }

        .alert span {
            font-size: 14px;
        }

        .alert__close {
            margin-left: auto;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: inherit;
            opacity: 0.6;
            transition: opacity var(--transition);
            padding: 0 4px;
            line-height: 1;
        }

        .alert__close:hover {
            opacity: 1;
        }

        /* ===== Panels ===== */
        .panel {
            background: #ffffff;
            border-radius: var(--radius-lg);
            border: 1px solid var(--color-gray-200);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition);
            overflow: hidden;
        }

        .panel:hover {
            box-shadow: var(--shadow-md);
        }

        .panel__header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--color-gray-100);
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .panel__header--compact {
            padding: 16px 20px;
        }

        .panel__title-group {
            flex: 1;
        }

        .panel__title {
            font-size: 18px;
            font-weight: 700;
            color: var(--color-gray-900);
            margin: 0 0 4px;
            letter-spacing: -0.01em;
        }

        .panel__subtitle {
            font-size: 14px;
            color: var(--color-gray-500);
            margin: 0;
            font-weight: 500;
        }

        .panel__actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
            align-items: center;
        }

        .panel__stats {
            flex-shrink: 0;
        }

        .panel__body {
            padding: 20px 24px;
        }

        .panel__body--no-padding {
            padding: 0;
        }

        /* ===== Grid ===== */
        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.8fr) minmax(280px, 0.9fr);
            gap: 24px;
            align-items: start;
            margin-top: 24px;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        .side-panel {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ===== Buttons ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 14px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all var(--transition);
            text-decoration: none;
            white-space: nowrap;
            background: transparent;
        }

        .btn--primary {
            background: var(--color-primary);
            color: #fff;
        }

        .btn--primary:hover:not(:disabled) {
            background: var(--color-primary-dark);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .btn--primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .btn--ghost {
            color: var(--color-gray-600);
            border-color: var(--color-gray-200);
            background: transparent;
        }

        .btn--ghost:hover {
            background: var(--color-gray-50);
            border-color: var(--color-gray-300);
        }

        .btn--sm {
            padding: 6px 14px;
            font-size: 13px;
        }

        .btn svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* ===== File Drop Zone ===== */
        .file-drop-zone {
            position: relative;
            border: 2px dashed var(--color-gray-300);
            border-radius: var(--radius-md);
            padding: 32px 20px;
            text-align: center;
            transition: all var(--transition);
            background: var(--color-gray-50);
            cursor: pointer;
            min-height: 160px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .file-drop-zone:hover {
            border-color: var(--color-primary);
            background: var(--color-primary-light);
        }

        .file-drop-zone.dragover {
            border-color: var(--color-primary);
            background: var(--color-primary-light);
            transform: scale(1.01);
        }

        .file-drop-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .file-drop-zone__content {
            pointer-events: none;
        }

        .file-drop-zone__content svg {
            width: 40px;
            height: 40px;
            color: var(--color-gray-400);
            margin-bottom: 8px;
        }

        .file-drop-zone__title {
            display: block;
            font-weight: 600;
            color: var(--color-gray-700);
            font-size: 15px;
        }

        .file-drop-zone__subtitle {
            display: block;
            color: var(--color-gray-500);
            font-size: 14px;
            margin-top: 2px;
        }

        .file-drop-zone__info {
            display: flex;
            gap: 16px;
            margin-top: 10px;
            font-size: 13px;
            color: var(--color-gray-400);
        }

        .file-drop-zone__preview {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            gap: 16px;
            background: var(--color-gray-50);
            border-radius: var(--radius-md);
            padding: 20px;
        }

        .file-drop-zone__preview:not([hidden]) {
            display: flex;
        }

        .file-drop-zone__preview svg {
            width: 28px;
            height: 28px;
            color: var(--color-primary);
            flex-shrink: 0;
        }

        .file-drop-zone__preview strong {
            display: block;
            color: var(--color-gray-800);
            font-size: 14px;
        }

        .file-drop-zone__preview span {
            color: var(--color-gray-500);
            font-size: 13px;
        }

        .btn-remove-file {
            margin-left: auto;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--color-gray-400);
            transition: all var(--transition);
            padding: 0 4px;
            line-height: 1;
        }

        .btn-remove-file:hover {
            color: var(--color-danger);
        }

        /* ===== Import Form ===== */
        .import-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .import-form__actions {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .import-form__progress {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 160px;
        }

        .progress-bar {
            flex: 1;
            height: 6px;
            background: var(--color-gray-200);
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-bar__fill {
            height: 100%;
            background: var(--color-primary);
            border-radius: 999px;
            transition: width 0.3s ease;
        }

        .progress-text {
            font-size: 13px;
            color: var(--color-gray-500);
            white-space: nowrap;
        }

        /* ===== Table ===== */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .table thead {
            background: var(--color-gray-50);
            border-bottom: 1px solid var(--color-gray-200);
        }

        .table thead th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--color-gray-600);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            position: relative;
        }

        .table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--color-gray-100);
            color: var(--color-gray-700);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: var(--color-gray-50);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Column widths */
        .col-name { min-width: 200px; }
        .col-matrik { min-width: 130px; }
        .col-semester { min-width: 100px; }
        .col-syer { min-width: 100px; }
        .col-yuran { min-width: 100px; }
        .col-actions { min-width: 110px; text-align: right; }

        .col-name .sort-btn {
            display: inline-flex;
            background: none;
            border: none;
            padding: 2px 4px;
            cursor: pointer;
            color: var(--color-gray-400);
            transition: color var(--transition);
            margin-left: 4px;
            vertical-align: middle;
        }

        .col-name .sort-btn:hover {
            color: var(--color-gray-600);
        }

        .col-name .sort-btn svg {
            width: 14px;
            height: 14px;
        }

        /* Member info */
        .member-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .member-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            flex-shrink: 0;
            color: #fff;
            text-transform: uppercase;
        }

        .member-id {
            display: block;
            font-size: 12px;
            color: var(--color-gray-400);
            font-weight: 500;
        }

        /* Amounts */
        .amount {
            font-weight: 600;
            color: var(--color-gray-700);
        }

        /* Action buttons */
        .col-actions {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            color: var(--color-gray-400);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--transition);
        }

        .btn-action:hover {
            background: var(--color-gray-100);
            color: var(--color-gray-600);
        }

        .btn-action--danger:hover {
            background: var(--color-danger-light);
            color: var(--color-danger);
        }

        .btn-action svg {
            width: 18px;
            height: 18px;
        }

        /* Table Controls */
        .table-controls {
            padding: 16px 20px;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            border-bottom: 1px solid var(--color-gray-100);
        }

        .search-wrapper {
            position: relative;
            flex: 1;
            min-width: 180px;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--color-gray-400);
        }

        .search-input {
            width: 100%;
            padding: 8px 12px 8px 38px;
            border: 1px solid var(--color-gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            background: #fff;
            transition: all var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px var(--color-primary-light);
        }

        .filter-group {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 8px 36px 8px 12px;
            border: 1px solid var(--color-gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23667085' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 12px center;
            background-size: 12px;
            appearance: none;
            cursor: pointer;
            transition: all var(--transition);
            min-width: 140px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px var(--color-primary-light);
        }

        /* Table Footer */
        .table-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--color-gray-100);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .table-footer__info {
            font-size: 14px;
            color: var(--color-gray-500);
        }

        .table-footer__info strong {
            color: var(--color-gray-700);
        }

        .table-footer__pagination {
            display: flex;
            gap: 4px;
        }

        .table-footer__pagination .pagination {
            margin: 0;
        }

        /* ===== Badges ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.5;
            white-space: nowrap;
        }

        .badge--info {
            background: var(--color-info-light);
            color: var(--color-info);
        }

        .badge--semester {
            font-size: 12px;
            padding: 3px 10px;
        }

        .badge--semester.semester-1 { background: #e8f5e9; color: #2e7d32; }
        .badge--semester.semester-2 { background: #e3f2fd; color: #1565c0; }
        .badge--semester.semester-3 { background: #fff3e0; color: #e65100; }
        .badge--semester.semester-4 { background: #f3e5f5; color: #7b1fa2; }
        .badge--semester.semester-5 { background: #fce4ec; color: #c62828; }
        .badge--semester.semester-6 { background: #e0f7fa; color: #00695c; }

        /* ===== Stat Chip ===== */
        .stat-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 4px 14px;
            background: var(--color-primary-light);
            border-radius: 999px;
        }

        .stat-chip__label {
            font-size: 13px;
            color: var(--color-primary);
            font-weight: 500;
        }

        .stat-chip__value {
            font-size: 18px;
            color: var(--color-primary);
            font-weight: 700;
        }

        /* ===== Import Report ===== */
        .import-report {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .import-report__file {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--color-gray-100);
        }

        .import-report__file svg {
            width: 28px;
            height: 28px;
            color: var(--color-gray-400);
            flex-shrink: 0;
        }

        .import-report__file strong {
            display: block;
            color: var(--color-gray-800);
            font-size: 14px;
        }

        .import-report__file span {
            font-size: 13px;
            color: var(--color-gray-400);
        }

        .import-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .import-stat {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            text-align: center;
            position: relative;
            border: 1px solid var(--color-gray-200);
        }

        .import-stat--success {
            background: var(--color-success-light);
            border-color: #b8e6d8;
        }

        .import-stat--failed {
            background: var(--color-danger-light);
            border-color: #fccdca;
        }

        .import-stat--warning {
            background: var(--color-warning-light);
            border-color: #fde8c7;
        }

        .import-stat__label {
            display: block;
            font-size: 12px;
            color: var(--color-gray-500);
            font-weight: 500;
        }

        .import-stat__value {
            display: block;
            font-size: 22px;
            font-weight: 700;
            color: var(--color-gray-900);
            margin: 2px 0;
        }

        .import-stat__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(255,255,255,0.8);
            font-size: 12px;
        }

        /* Import Errors */
        .import-errors {
            border: 1px solid var(--color-gray-200);
            border-radius: var(--radius-sm);
            overflow: hidden;
        }

        .import-errors__header {
            padding: 10px 14px;
            background: var(--color-gray-50);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            font-size: 13px;
            color: var(--color-gray-600);
            border-bottom: 1px solid var(--color-gray-200);
        }

        .import-errors__count {
            background: var(--color-danger);
            color: #fff;
            padding: 1px 10px;
            border-radius: 999px;
            font-size: 12px;
        }

        .import-errors__list {
            max-height: 180px;
            overflow-y: auto;
            padding: 4px;
        }

        .import-error-item {
            padding: 8px 12px;
            border-bottom: 1px solid var(--color-gray-100);
            font-size: 13px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
        }

        .import-error-item:last-child {
            border-bottom: none;
        }

        .import-error-item__row {
            font-weight: 500;
            color: var(--color-gray-600);
            flex-shrink: 0;
        }

        .import-error-item__matrik {
            color: var(--color-gray-400);
            font-weight: 400;
        }

        .import-error-item__message {
            color: var(--color-danger);
            text-align: right;
            font-size: 12px;
        }

        /* ===== Quick Actions ===== */
        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .quick-action {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: var(--radius-sm);
            background: var(--color-gray-50);
            color: var(--color-gray-700);
            text-decoration: none;
            transition: all var(--transition);
            font-weight: 500;
            font-size: 14px;
        }

        .quick-action:hover {
            background: var(--color-primary-light);
            color: var(--color-primary);
            transform: translateX(4px);
        }

        .quick-action svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            color: var(--color-gray-400);
        }

        .quick-action:hover svg {
            color: var(--color-primary);
        }

        /* ===== Empty State ===== */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            text-align: center;
            color: var(--color-gray-400);
        }

        .empty-state svg {
            width: 40px;
            height: 40px;
            color: var(--color-gray-300);
            margin-bottom: 12px;
        }

        .empty-state p {
            font-weight: 600;
            color: var(--color-gray-600);
            margin: 0 0 4px;
        }

        .empty-state span {
            font-size: 14px;
        }

        .empty-state--small {
            padding: 32px 20px;
        }

        .empty-state--small svg {
            width: 32px;
            height: 32px;
        }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .panel__header {
                flex-direction: column;
                align-items: stretch;
            }

            .panel__actions {
                flex-wrap: wrap;
            }

            .table-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .search-wrapper {
                min-width: 0;
            }

            .filter-group {
                flex-wrap: wrap;
            }

            .filter-select {
                flex: 1;
                min-width: 120px;
            }

            .table-footer {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }

            .table-footer__pagination {
                justify-content: center;
            }

            .import-stats {
                grid-template-columns: 1fr;
            }

            .col-actions {
                gap: 2px;
            }

            .btn-action {
                width: 28px;
                height: 28px;
            }

            .file-drop-zone {
                padding: 24px 16px;
                min-height: 140px;
            }

            .file-drop-zone__info {
                flex-direction: column;
                gap: 4px;
            }

            .import-form__actions {
                flex-direction: column;
                align-items: stretch;
            }

            .import-form__progress {
                min-width: 0;
            }
        }

        @media (max-width: 480px) {
            .table thead th,
            .table tbody td {
                padding: 8px 12px;
                font-size: 13px;
            }

            .member-avatar {
                width: 28px;
                height: 28px;
                font-size: 10px;
            }

            .amount {
                font-size: 13px;
            }

            .stat-chip__value {
                font-size: 16px;
            }
        }

        /* ===== Accessibility ===== */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        .btn:focus-visible,
        .btn-action:focus-visible,
        .search-input:focus-visible,
        .filter-select:focus-visible,
        .sort-btn:focus-visible,
        .quick-action:focus-visible,
        .alert__close:focus-visible {
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
        }

        .file-drop-zone input[type="file"]:focus-visible + .file-drop-zone__content {
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
            border-radius: var(--radius-sm);
        }

        /* ===== Scrollbar Styling ===== */
        .import-errors__list::-webkit-scrollbar {
            width: 4px;
        }

        .import-errors__list::-webkit-scrollbar-track {
            background: var(--color-gray-100);
            border-radius: 999px;
        }

        .import-errors__list::-webkit-scrollbar-thumb {
            background: var(--color-gray-300);
            border-radius: 999px;
        }

        .import-errors__list::-webkit-scrollbar-thumb:hover {
            background: var(--color-gray-400);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== Close Alerts =====
            document.querySelectorAll('.alert__close').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const alert = this.closest('.alert');
                    alert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                });
            });

            // Auto-close alerts after 5 seconds
            document.querySelectorAll('.alert').forEach(function(alert) {
                setTimeout(function() {
                    if (document.body.contains(alert)) {
                        alert.style.transition = 'opacity 0.5s ease, transform 0.3s ease';
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-10px)';
                        setTimeout(function() {
                            if (document.body.contains(alert)) {
                                alert.remove();
                            }
                        }, 500);
                    }
                }, 5000);
            });

            // ===== File Upload with Drag & Drop =====
            const dropZone = document.getElementById('fileDropZone');
            const fileInput = document.getElementById('fileInput');
            const filePreview = document.getElementById('filePreview');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const removeFileBtn = document.getElementById('removeFile');
            const importBtn = document.getElementById('importBtn');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const uploadProgress = document.getElementById('uploadProgress');

            function formatFileSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / 1048576).toFixed(1) + ' MB';
            }

            function updateFilePreview(file) {
                if (file) {
                    fileName.textContent = file.name;
                    fileSize.textContent = formatFileSize(file.size);
                    filePreview.hidden = false;
                    dropZone.querySelector('.file-drop-zone__content').style.display = 'none';
                    importBtn.disabled = false;
                } else {
                    filePreview.hidden = true;
                    dropZone.querySelector('.file-drop-zone__content').style.display = 'block';
                    importBtn.disabled = true;
                }
            }

            // File input change
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    updateFilePreview(this.files[0]);
                } else {
                    updateFilePreview(null);
                }
            });

            // Remove file
            removeFileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                fileInput.value = '';
                updateFilePreview(null);
            });

            // Drag & Drop events
            ['dragenter', 'dragover'].forEach(function(eventName) {
                dropZone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    this.classList.add('dragover');
                });
            });

            ['dragleave', 'drop'].forEach(function(eventName) {
                dropZone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                });
            });

            dropZone.addEventListener('drop', function(e) {
                const files = e.dataTransfer.files;
                if (files && files[0]) {
                    const file = files[0];
                    const validExtensions = ['.csv', '.xlsx'];
                    const isValid = validExtensions.some(function(ext) {
                        return file.name.toLowerCase().endsWith(ext);
                    });
                    
                    if (isValid) {
                        fileInput.files = files;
                        updateFilePreview(file);
                    } else {
                        alert('Format fail tidak disokong. Sila gunakan .csv atau .xlsx');
                    }
                }
            });

            // ===== Form Submit with Progress =====
            const form = document.getElementById('importForm');
            form.addEventListener('submit', function(e) {
                const file = fileInput.files[0];
                if (!file) {
                    e.preventDefault();
                    alert('Sila pilih fail untuk diimport');
                    return;
                }

                // Show progress
                uploadProgress.hidden = false;
                importBtn.disabled = true;

                let progress = 0;
                const interval = setInterval(function() {
                    progress += Math.random() * 6 + 2;
                    if (progress > 95) progress = 95;
                    progressFill.style.width = progress + '%';
                    progressText.textContent = 'Memuat naik... ' + Math.round(progress) + '%';
                }, 200);

                // Note: Real progress tracking requires server-side support
                // The interval is for visual feedback only
            });

            // ===== Table Search Filter =====
            const searchInput = document.getElementById('tableSearch');
            const semesterFilter = document.getElementById('semesterFilter');
            const resetFilters = document.getElementById('resetFilters');
            const rows = document.querySelectorAll('.member-row');

            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const semesterValue = semesterFilter.value;

                rows.forEach(function(row) {
                    const name = row.dataset.name || '';
                    const matrik = row.dataset.matrik || '';
                    const semester = row.dataset.semester || '';

                    const matchesSearch = name.includes(searchTerm) || matrik.includes(searchTerm);
                    const matchesSemester = !semesterValue || semester === semesterValue;

                    row.style.display = (matchesSearch && matchesSemester) ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterTable);
            semesterFilter.addEventListener('change', filterTable);

            resetFilters.addEventListener('click', function() {
                searchInput.value = '';
                semesterFilter.value = '';
                filterTable();
            });

            // ===== Sort Functionality =====
            const sortBtn = document.querySelector('.sort-btn');
            let sortAsc = true;

            if (sortBtn) {
                sortBtn.addEventListener('click', function() {
                    sortAsc = !sortAsc;
                    const tbody = document.getElementById('memberTableBody');
                    const rowsArray = Array.from(tbody.querySelectorAll('.member-row'));

                    rowsArray.sort(function(a, b) {
                        const nameA = a.dataset.name || '';
                        const nameB = b.dataset.name || '';
                        return sortAsc ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
                    });

                    rowsArray.forEach(function(row) {
                        tbody.appendChild(row);
                    });

                    // Update sort icon
                    const svg = sortBtn.querySelector('svg');
                    svg.innerHTML = sortAsc ?
                        '<path d="M6 9l6-6 6 6"/><path d="M6 15l6 6 6-6"/>' :
                        '<path d="M6 15l6 6 6-6"/><path d="M6 9l6-6 6 6"/>';
                });
            }
        });
    </script>
@endpush
