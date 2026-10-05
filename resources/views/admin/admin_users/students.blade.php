@extends('layouts.app')

@section('title', 'Pengurusan Pelajar')
@section('page-title', 'Pengurusan Pelajar')
@section('page-subtitle', 'Kemaskini profil akaun pelajar. Kata laluan pelajar dijana secara automatik.')

@section('content')
    <section class="student-page-hero">
        <div>
            <h1>Pengurusan Pelajar</h1>
            <p>Kemaskini profil akaun pelajar. Kata laluan dijana secara automatik daripada No. Matrik.</p>
        </div>
        <div class="student-page-hero__meta">
            <span>Total {{ $students->total() }} pelajar</span>
        </div>
    </section>

    @if (session('status'))
        <div class="student-alert success" role="alert">
            <span>{{ session('status') }}</span>
            <button class="alert-close" type="button" aria-label="Close notification">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="student-alert danger" role="alert">
            <span>{{ $errors->first() }}</span>
            <button class="alert-close" type="button" aria-label="Close notification">&times;</button>
        </div>
    @endif

    <section class="panel student-import-panel">
        <div class="student-import-copy">
            <span class="student-import-kicker">IMPORT EXCEL</span>
            <h2>Muat Naik Senarai Student</h2>
            <p>Kolum diperlukan: Nama, No KP, No Matrik, Kelas dan Tarikh Masuk.</p>
        </div>
        <form class="student-import-form" method="POST" action="{{ route('admin.users.students.import') }}" enctype="multipart/form-data">
            @csrf
            <label class="student-import-file" for="studentImportFile">
                <input id="studentImportFile" name="file" type="file" accept=".csv,.txt,.xlsx" required>
                <span class="student-import-file__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                </span>
                <span>
                    <strong>Pilih fail Excel</strong>
                    <small>CSV atau XLSX, maksimum 10MB</small>
                </span>
            </label>
            <button class="student-import-button" type="submit">Upload Excel</button>
        </form>
        @if (! empty($latestImport))
            <div class="student-import-result">
                <span>Import terkini</span>
                <strong>{{ $latestImport->imported_count }} berjaya</strong>
                <strong class="{{ $latestImport->failed_count > 0 ? 'is-danger' : '' }}">{{ $latestImport->failed_count }} gagal</strong>
            </div>
            @if (! empty($latestImport->errors))
                <div class="student-import-errors" data-import-id="{{ $latestImport->id }}">
                    <div class="student-import-errors__head">
                        <strong>Ralat Import</strong>
                        <button class="student-import-errors__close" type="button" aria-label="Tutup ralat import">&times;</button>
                    </div>
                    @foreach ($latestImport->errors as $importError)
                        <div class="student-import-errors__item">
                            <strong>{{ isset($importError['row']) ? 'Baris '.$importError['row'] : 'Fail Excel' }}</strong>
                            <span>{{ $importError['message'] ?? 'Import gagal.' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </section>

    <section class="panel student-list-panel">
        {{-- Panel Header --}}
        <div class="student-list-head">
            <div>
                <h2>Senarai Student</h2>
                <p>Paparan lengkap mengikut maklumat yang tersedia dalam jadual student.</p>
            </div>
            <div class="student-list-actions">
                <span class="record-badge">
                    Total {{ $students->total() }} pelajar
                </span>
                {{-- Optional Add Button --}}
                <a href="{{ route('admin.users.create', ['type' => 'student']) }}" class="add-student-button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Tambah Student
                </a>
            </div>
        </div>

        <form class="student-search-bar" method="GET" action="{{ route('admin.users.students') }}">
            <div class="student-search-field">
                <label for="search">Nama / No Matrik / No KP</label>
                <input
                    id="search"
                    name="search"
                    type="search"
                    value="{{ $search ?? request('search') }}"
                    placeholder="Cari nama, no matrik atau no kp">
            </div>
            <div class="student-search-field student-search-field--small">
                <label for="program">Program</label>
                <select id="program" name="program">
                    <option value="">Semua program</option>
                    @foreach (($programOptions ?? []) as $programOption)
                        <option value="{{ $programOption }}" @selected(($filters['program'] ?? request('program')) === $programOption)>{{ $programOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="student-search-field student-search-field--small">
                <label for="kelas">Kelas</label>
                <select id="kelas" name="kelas">
                    <option value="">Semua kelas</option>
                    @foreach (($classOptions ?? []) as $classOption)
                        <option value="{{ $classOption }}" @selected(($filters['kelas'] ?? request('kelas')) === $classOption)>{{ $classOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="student-search-field student-search-field--small">
                <label for="semester">Semester</label>
                <select id="semester" name="semester">
                    <option value="">Semua semester</option>
                    @foreach (($semesterOptions ?? []) as $semesterOption)
                        <option value="{{ $semesterOption }}" @selected(($filters['semester'] ?? request('semester')) === $semesterOption)>{{ str_replace('Sem', 'Semester', $semesterOption) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="student-search-field student-search-field--date">
                <label for="tarikh_masuk">Tarikh Masuk</label>
                <input
                    id="tarikh_masuk"
                    name="tarikh_masuk"
                    type="date"
                    value="{{ $filters['tarikh_masuk'] ?? request('tarikh_masuk') }}">
            </div>
            <div class="student-search-actions">
                <button class="student-search-button" type="submit">Cari</button>
                @if (($search ?? request('search')) || ($filters['program'] ?? request('program')) || ($filters['kelas'] ?? request('kelas')) || ($filters['semester'] ?? request('semester')) || ($filters['tarikh_masuk'] ?? request('tarikh_masuk')))
                    <a class="student-reset-button" href="{{ route('admin.users.students') }}">Reset</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="student-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>No Matrik</th>
                        <th>No. KP</th>
                        <th>Program</th>
                        <th>Kelas</th>
                        <th>Tarikh Masuk</th>
                        <th>Semester</th>
                        <th class="actions-col">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        @php
                            $semesterLabel = $student->semester
                                ? preg_replace('/^sem\s+/i', 'Semester ', $student->semester)
                                : '-';
                        @endphp
                        <tr class="student-row" data-student-row="{{ $student->id_ahli }}">
                            <td>
                                <div class="user-cell">
                                    <div class="avatar">
                                        @if ($student->profile_image_path)
                                            <img src="{{ asset($student->profile_image_path) }}" alt="Gambar profil {{ $student->nama }}">
                                        @else
                                            {{ strtoupper(substr($student->nama, 0, 2)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="user-name">{{ $student->nama }}</div>
                                        <div class="user-sub">{{ $student->program ?? 'Pelajar' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><code class="badge-code">{{ $student->no_matrik }}</code></td>
                            <td><span class="mono">{{ $student->nric ? substr($student->nric, 0, 6).'****' : '-' }}</span></td>
                            <td>{{ $student->program ?? '-' }}</td>
                            <td>{{ $student->kelas ?? '-' }}</td>
                            <td>{{ $student->tarikh_daftar ? $student->tarikh_daftar->format('d/m/Y') : '-' }}</td>
                            <td>
                                <span class="status-badge is-info">{{ $semesterLabel }}</span>
                            </td>
                            <td>
                                <div class="student-action-buttons">
                                    <a class="student-edit-button" href="{{ route('admin.users.edit', ['type' => 'student', 'id' => $student->id_ahli]) }}">
                                        Kemaskini
                                    </a>
                                    <form class="student-delete-form" method="POST" action="{{ route('admin.users.destroy', ['type' => 'student', 'id' => $student->id_ahli]) }}" data-student-name="{{ $student->nama }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="student-delete-button" type="submit">Padam</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <strong>Tiada rekod student.</strong>
                                    <span>Klik Tambah Student untuk mendaftar akaun pelajar baru.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Optional Footer with Pagination (if needed) --}}
        @if(method_exists($students, 'links'))
            <div class="student-pagination">
                {{ $students->links() }}
            </div>
        @endif
    </section>

    {{-- Inline Styles for Alert Dismissible --}}
    <style>
        .alert-dismissible {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            background: var(--success-soft);
            border: 1px solid #abf0d5;
            border-radius: 12px;
            color: var(--success);
            margin-bottom: 24px;
            animation: slideDown 0.3s ease-out;
            position: relative;
        }
        .alert-danger {
            background: var(--danger-soft);
            border-color: #fecaca;
            color: var(--danger);
        }
        .alert-dismissible .alert-icon {
            flex-shrink: 0;
        }
        .alert-dismissible .alert-close {
            background: transparent;
            border: none;
            font-size: 22px;
            line-height: 1;
            color: var(--success);
            cursor: pointer;
            margin-left: auto;
            padding: 0 4px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .alert-dismissible .alert-close:hover {
            opacity: 1;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .student-import-panel {
            display: grid;
            grid-template-columns: minmax(260px, 1fr) minmax(360px, 1.2fr);
            gap: 18px;
            align-items: center;
            margin-bottom: 24px;
            padding: 24px 28px;
            border: 1px solid var(--line);
            border-radius: 22px !important;
            background: #fff;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .055) !important;
        }
        .student-import-copy {
            display: grid;
            gap: 6px;
        }
        .student-import-kicker {
            color: var(--secondary);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .05em;
        }
        .student-import-copy h2 {
            margin: 0;
            color: var(--text);
            font-size: 24px;
            line-height: 1.2;
            font-weight: 900;
        }
        .student-import-copy p {
            margin: 0;
            color: var(--muted-2);
            font-size: 14px;
            font-weight: 700;
        }
        .student-import-form {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }
        .student-import-file {
            display: flex;
            flex: 1 1 280px;
            min-height: 58px;
            align-items: center;
            gap: 12px;
            border: 1px dashed #b8d4ff;
            border-radius: 12px;
            background: var(--secondary-soft);
            padding: 10px 14px;
            color: var(--secondary);
            cursor: pointer;
        }
        .student-import-file input {
            width: 100%;
            min-width: 0;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }
        .student-import-file__icon {
            display: inline-flex;
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #fff;
            color: var(--secondary);
        }
        .student-import-file strong,
        .student-import-file small {
            display: block;
        }
        .student-import-file strong {
            color: var(--text);
            font-size: 14px;
            font-weight: 900;
        }
        .student-import-file small {
            margin-top: 2px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }
        .student-import-button {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--secondary);
            border-radius: 10px;
            background: var(--secondary);
            color: #fff;
            padding: 0 18px;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(30, 64, 175, .16);
        }
        .student-import-button:hover {
            background: var(--secondary-hover);
            border-color: var(--secondary-hover);
        }
        .student-import-result {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            border-top: 1px solid var(--line);
            padding-top: 14px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
        }
        .student-import-result strong {
            display: inline-flex;
            min-height: 28px;
            align-items: center;
            border-radius: 999px;
            background: var(--success-soft);
            color: var(--success);
            padding: 0 10px;
            font-size: 12px;
            font-weight: 900;
        }
        .student-import-result strong.is-danger {
            background: var(--danger-soft);
            color: var(--danger);
        }
        .student-import-errors {
            grid-column: 1 / -1;
            display: grid;
            gap: 8px;
            position: relative;
            border: 1px solid #fecaca;
            border-radius: 12px;
            background: var(--danger-soft);
            padding: 12px 14px;
        }
        .student-import-errors__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #fecaca;
            color: #991b1b;
            font-size: 13px;
            font-weight: 900;
        }
        .student-import-errors__close {
            display: inline-flex;
            width: 28px;
            height: 28px;
            align-items: center;
            justify-content: center;
            border: 1px solid #fecaca;
            border-radius: 999px;
            background: #fff;
            color: #991b1b;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
        }
        .student-import-errors__close:hover {
            background: #fee2e2;
        }
        .student-import-errors__item {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            color: var(--danger);
            font-size: 13px;
            font-weight: 800;
        }
        .student-import-errors__item strong {
            color: #991b1b;
        }

        /* Panel refinements */
        .panel {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        }
        .panel-pad {
            padding: 20px 24px;
        }
        .student-list-panel {
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 22px !important;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .055) !important;
        }
        .student-list-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            background: #fff;
            border-bottom: 1px solid var(--line);
            padding: 24px 28px;
        }
        .student-list-head h2 {
            margin: 0;
            color: var(--text);
            font-size: 24px;
            line-height: 1.2;
            font-weight: 900;
            letter-spacing: 0;
        }
        .student-list-head .brand-red {
            color: var(--primary);
        }
        .student-list-head .brand-blue {
            color: var(--secondary);
        }
        .student-list-head p {
            margin: 8px 0 0;
            color: var(--muted-2);
            font-size: 14px;
            font-weight: 700;
        }
        .student-list-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .record-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            border: 1px solid #bbf7d0;
            border-radius: 999px;
            background: var(--success-soft);
            color: var(--success);
            padding: 0 14px;
            font-size: 13px;
            font-weight: 900;
        }
        .record-badge::before {
            content: '';
            width: 8px;
            height: 8px;
            margin-right: 8px;
            border-radius: 999px;
            background: var(--success);
        }
        .add-student-button {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 12px;
            background: var(--secondary);
            color: #fff;
            padding: 0 16px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 900;
            box-shadow: 0 10px 22px rgba(30, 64, 175, .16);
        }
        .add-student-button:hover {
            background: var(--secondary-hover);
            color: #fff;
            box-shadow: 0 12px 26px rgba(30, 64, 175, .22);
        }
        .student-search-bar {
            display: grid;
            grid-template-columns: minmax(240px, 1.6fr) repeat(3, minmax(150px, .8fr)) minmax(160px, .85fr) auto;
            align-items: end;
            gap: 14px;
            padding: 18px 28px;
            border-bottom: 1px solid var(--line);
            background: var(--surface-soft);
            flex-wrap: wrap;
        }
        .student-search-field {
            display: grid;
            gap: 7px;
            min-width: 0;
        }
        .student-search-field label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        .student-search-field input,
        .student-search-field select {
            width: 100%;
            min-height: 42px;
            padding: 0 14px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            background: #fff;
            color: var(--text);
            font: inherit;
            outline: 0;
        }
        .student-search-field select {
            cursor: pointer;
        }
        .student-search-field input:focus,
        .student-search-field select:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(36, 83, 166, .12);
        }
        .student-search-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .search-button,
        .reset-button {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            padding: 0 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 900;
            text-decoration: none;
        }
        .search-button {
            border: 1px solid var(--secondary);
            background: var(--secondary);
            color: #fff;
            box-shadow: 0 10px 22px rgba(30, 64, 175, .16);
        }
        .search-button:hover {
            background: var(--secondary-hover);
            border-color: var(--secondary-hover);
        }
        .reset-button {
            border: 1px solid var(--line-strong);
            background: #fff;
            color: var(--text);
        }
        .reset-button:hover {
            background: var(--surface-soft);
            color: var(--text);
        }
        .student-action-buttons {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }
        .student-action-buttons form {
            margin: 0;
        }
        .student-edit-button,
        .student-delete-button {
            display: inline-flex;
            min-height: 38px;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 0 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            transition: .18s ease;
            cursor: pointer;
        }
        .student-edit-button {
            border: 1px solid var(--secondary);
            background: var(--secondary);
            color: #fff;
            box-shadow: 0 8px 18px rgba(30, 64, 175, .16);
        }
        .student-edit-button:hover {
            background: var(--secondary-hover);
            border-color: var(--secondary-hover);
            color: #fff;
        }
        .student-delete-button {
            border: 1px solid #fecaca;
            background: #fff1f2;
            color: #b91c1c;
        }
        .student-delete-button:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }
        .student-delete-button:disabled {
            cursor: wait;
            opacity: .65;
        }
        .student-row.is-removing {
            opacity: 0;
            transform: translateX(8px);
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .student-page-hero {
                align-items: flex-start;
                flex-direction: column;
                padding: 24px;
            }
            .panel-pad {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 12px;
            }
            .panel-pad > div:last-child {
                width: 100%;
                justify-content: space-between;
            }
            .student-list-head {
                align-items: flex-start;
                padding: 22px;
            }
            .student-import-panel {
                grid-template-columns: 1fr;
                padding: 22px;
            }
            .student-import-form,
            .student-import-button {
                width: 100%;
            }
            .student-search-bar {
                grid-template-columns: 1fr;
                padding: 16px 22px;
            }
            .student-search-field {
                min-width: 100%;
            }
            .student-search-actions {
                width: 100%;
            }
            .search-button,
            .reset-button {
                flex: 1 1 140px;
            }
            .student-list-actions,
            .add-student-button {
                width: 100%;
            }
            table {
                font-size: 13px;
            }
            th, td {
                padding: 10px 12px !important;
            }
            .student-action-buttons {
                width: 100%;
                justify-content: flex-end;
            }
            .student-edit-button,
            .student-delete-button {
                min-height: 36px;
                padding: 0 12px;
                font-size: 12px;
            }
        }
        .page-heading {
            display: none;
        }
        .student-page-hero {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
            padding: 34px 36px;
            border: 1px solid var(--line);
            border-left: 5px solid var(--secondary);
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .055);
        }
        .student-page-hero h1 {
            margin: 0;
            color: var(--text);
            font-size: 38px;
            line-height: 1.15;
            letter-spacing: 0;
            font-weight: 900;
        }
        .student-page-hero p {
            margin: 14px 0 0;
            color: var(--muted-2);
            font-size: 16px;
            font-weight: 800;
        }
        .student-page-hero__meta {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex: 0 0 auto;
        }
        .student-page-hero__meta span {
            display: inline-flex;
            min-height: 34px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: var(--secondary-soft);
            color: var(--secondary);
            padding: 0 14px;
            font-size: 13px;
            font-weight: 900;
        }
        .student-alert{
            position:fixed;
            top:20px;
            right:20px;
            z-index:1000;
            display:flex;
            max-width:min(420px, calc(100vw - 32px));
            align-items:center;
            gap:12px;
            padding:14px 20px;
            border-radius:14px;
            border:1px solid transparent;
            font-weight:800;
            box-shadow:0 18px 38px rgba(15,23,42,.16);
        }
        .student-alert.success{background:var(--success-soft);border-color:#bbf7d0;color:var(--success)}
        .student-alert.danger{background:var(--danger-soft);border-color:var(--danger-soft);color:var(--danger)}
        .alert-close{margin-left:auto;border:0;background:none;font-size:22px;cursor:pointer;color:inherit}
        .table-responsive{overflow-x:auto;padding:0}
        .student-table{width:100%;border-collapse:separate;border-spacing:0;min-width:1120px}
        .student-table th{
            padding:14px 16px;
            text-align:left;
            font-size:12px;
            font-weight:900;
            color:var(--muted);
            text-transform:uppercase;
            letter-spacing:.03em;
            border-bottom:1px solid var(--line);
            background:var(--surface-soft);
            white-space:nowrap;
        }
        .student-table td{
            padding:16px;
            border-bottom:1px solid var(--line);
            vertical-align:middle;
            font-size:14px;
            color:var(--text);
        }
        .student-table tbody tr:hover{background:#f8fafc}
        .user-cell{display:flex;align-items:center;gap:12px;min-width:220px}
        .avatar{
            width:48px;
            height:48px;
            border-radius:999px;
            display:flex;
            align-items:center;
            justify-content:center;
            color:var(--secondary);
            background:var(--secondary-soft);
            border:1px solid #dbeafe;
            font-size:13px;
            font-weight:900;
            flex:0 0 auto;
        }
        .avatar img { width: 100%; height: 100%; display: block; border-radius: inherit; object-fit: cover; }
        .user-name{font-weight:900;color:var(--text)}
        .user-sub{margin-top:3px;font-size:12px;color:var(--muted-2);font-weight:700}
        .mono{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono",monospace}
        .badge-code{display:inline-flex;white-space:nowrap;background:#f1f5f9;padding:5px 10px;border-radius:8px;color:#334155;font-weight:900}
        .status-badge{display:inline-flex;min-height:28px;align-items:center;border-radius:999px;padding:0 11px;font-size:12px;font-weight:900;white-space:nowrap}
        .status-badge.is-info{background:var(--secondary-soft);color:var(--secondary)}
        .student-action-buttons{display:flex;gap:8px;align-items:center;justify-content:flex-end;flex-wrap:nowrap}
        .student-action-buttons form{margin:0}
        .student-edit-button,.student-delete-button{
            display:inline-flex;
            width:76px;
            min-height:34px;
            align-items:center;
            justify-content:center;
            border-radius:10px;
            border:0;
            padding:0;
            text-decoration:none;
            font-weight:900;
            font-size:13px;
            cursor:pointer;
            box-shadow:none;
        }
        .student-edit-button{background:var(--secondary-soft);color:var(--secondary)}
        .student-edit-button:hover{background:var(--secondary);color:#fff}
        .student-delete-button{background:var(--danger-soft);color:var(--danger)}
        .student-delete-button:hover{background:var(--danger);color:#fff}
        .empty-state{display:grid;gap:6px;padding:40px 20px;text-align:center;color:var(--muted-2)}
        .empty-state strong{color:var(--text)}
        .student-pagination{
            padding:16px 24px;
            border-top:1px solid #D7E2EF;
            background:#F8F9FA;
        }

        .content:has(> .student-page-hero){
            background:#F4F7FB;
        }
        .content > .student-page-hero,
        .content > .student-list-panel,
        .content > .student-import-panel{
            max-width:none;
        }
        .content > .student-page-hero{
            position:relative;
            align-items:flex-start;
            margin-bottom:30px;
            padding:26px 28px!important;
            border:1px solid #D7E2EF!important;
            border-left:6px solid #0b1e36!important;
            border-radius:12px!important;
            background:#fff!important;
            box-shadow:0 12px 28px rgba(11,30,54,.08)!important;
        }
        .content > .student-page-hero::before{
            content:"";
            position:absolute;
            top:30px;
            left:28px;
            width:42px;
            height:3px;
            border-radius:999px;
            background:#ED1C2E;
        }
        .content > .student-page-hero h1{
            margin:20px 0 0!important;
            color:#071A33!important;
            font-size:26px!important;
            line-height:1.15!important;
            font-weight:700!important;
        }
        .content > .student-page-hero p{
            margin:8px 0 0!important;
            color:#263A54!important;
            font-size:14px!important;
            line-height:1.45!important;
            font-weight:600!important;
        }
        .content > .student-page-hero .student-page-hero__meta{padding-top:48px}
        .content > .student-page-hero .student-page-hero__meta span{
            min-height:32px;
            border-radius:999px;
            background:#EAF3FF;
            color:#0B5ED7;
            padding:0 14px;
            font-size:13px;
        }
        .content > .student-list-panel,
        .content > .student-import-panel{
            border:1px solid #D7E2EF!important;
            border-radius:12px!important;
            background:#fff!important;
            box-shadow:0 8px 20px rgba(8,47,89,.035)!important;
        }
        .content .student-list-head{
            padding:24px 28px;
            border-bottom:1px solid #D7E2EF;
            background:#fff;
        }
        .content .student-list-head h2{
            color:#082F59;
            font-size:24px;
            line-height:1.2;
            font-weight:900;
        }
        .content .student-list-head h2::before{
            content:"";
            display:block;
            width:34px;
            height:4px;
            margin-bottom:10px;
            border-radius:999px;
            background:#ED1C2E;
        }
        .content .student-list-head p{
            margin-top:6px;
            color:#31517D;
            font-size:15px;
            font-weight:650;
        }
        .content .record-badge{
            min-height:32px;
            border:1px solid #BBF7D0;
            background:#DCFCE7;
            color:#15803D;
            padding:0 14px;
            font-size:13px;
        }
        .content .record-badge::before{background:#16A34A}
        .content .add-student-button,
        .content .student-search-button,
        .content .student-reset-button{
            display:inline-flex;
            min-height:42px;
            align-items:center;
            justify-content:center;
            border-radius:6px;
            padding:0 18px;
            font-size:14px;
            font-weight:900;
            text-decoration:none;
            box-shadow:none;
        }
        .content .add-student-button,
        .content .student-search-button{
            border:1px solid #0B5ED7;
            background:#0B5ED7;
            color:#fff;
        }
        .content .student-reset-button{
            border:1px solid #C8D6E7;
            background:#fff;
            color:#102033;
        }
        .content .student-search-bar{
            display:grid;
            grid-template-columns:minmax(240px,1.6fr) repeat(3,minmax(150px,.8fr)) minmax(160px,.85fr) auto;
            gap:16px;
            margin:0;
            padding:18px 28px 20px;
            border-bottom:1px solid #D7E2EF;
            background:#FBFDFF;
        }
        .content .student-search-field label{
            color:#082F59;
            font-size:13px;
            font-weight:800;
            letter-spacing:0;
            text-transform:uppercase;
        }
        .content .student-search-field input,
        .content .student-search-field select{
            min-height:42px;
            border:1px solid #C8D6E7;
            border-radius:6px;
            color:#102033;
            font-size:14px;
            font-weight:650;
        }
        .content .student-table{
            border-collapse:collapse;
        }
        .content .student-table th{
            min-height:56px;
            padding:13px 16px;
            border-bottom:1px solid #D7E2EF;
            background:#F8F9FA;
            color:#082F59;
            font-size:12px;
            font-weight:900;
            letter-spacing:.04em;
        }
        .content .student-table td{
            padding:14px 16px;
            border-bottom:1px solid #DCE5F0;
            color:#082F59;
            font-size:13px;
        }
        .content .student-table tbody tr:hover{background:#F8FBFF}
        .content .user-cell{min-width:190px}
        .content .avatar{
            width:48px;
            height:48px;
            background:#EAF3FF;
            color:#0B5ED7;
            border-color:#CFE0F5;
            font-size:12px;
        }
        .content .user-name{
            color:#082F59;
            font-size:14px;
            font-weight:900;
        }
        .content .user-sub{
            color:#526987;
            font-size:12px;
        }
        .content .badge-code{
            min-height:26px;
            align-items:center;
            border-radius:6px;
            background:#F1F6FC;
            color:#082F59;
            padding:0 10px;
            font-size:12px;
            font-weight:750;
        }
        .content .status-badge{
            min-height:26px;
            padding:0 10px;
            font-size:12px;
        }
        .content .student-edit-button,
        .content .student-delete-button{
            width:76px;
            min-height:34px;
            border-radius:6px;
        }
        .content .student-edit-button{
            background:#E8F0FE;
            color:#1A73E8;
        }
        .content .student-delete-button{
            background:#FEF2F2;
            color:#B91C1C;
        }
        @media (max-width: 640px) {
            .student-page-hero {
                align-items: flex-start;
                flex-direction: column;
                padding: 24px;
            }
            .student-page-hero h1 {
                font-size: 30px;
            }
            .student-alert{top:12px;right:12px;left:12px;max-width:none}
            .content > .student-page-hero .student-page-hero__meta{padding-top:0}
            .content .student-search-bar{grid-template-columns:1fr}
            .student-list-actions,
            .add-student-button{width:100%}
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.student-alert').forEach((alert) => {
                const closeButton = alert.querySelector('.alert-close');
                const closeAlert = () => {
                    alert.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-8px)';
                    setTimeout(() => alert.remove(), 260);
                };

                closeButton?.addEventListener('click', closeAlert);
                setTimeout(closeAlert, 5000);
            });

            const importErrors = document.querySelector('.student-import-errors');
            const importErrorsClose = document.querySelector('.student-import-errors__close');
            const importId = importErrors?.dataset.importId;
            const dismissedKey = importId ? `coopbest-dismissed-import-errors-${importId}` : null;

            if (importErrors && dismissedKey && localStorage.getItem(dismissedKey) === '1') {
                importErrors.remove();

                return;
            }

            if (importErrors && importErrorsClose) {
                importErrorsClose.addEventListener('click', () => {
                    if (dismissedKey) {
                        localStorage.setItem(dismissedKey, '1');
                    }

                    importErrors.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                    importErrors.style.opacity = '0';
                    importErrors.style.transform = 'translateY(-6px)';
                    setTimeout(() => importErrors.remove(), 220);
                });
            }

            const decrementRecordCount = () => {
                document.querySelectorAll('.record-badge, .student-page-hero__meta span').forEach((badge) => {
                    const current = parseInt(badge.textContent, 10);

                    if (!Number.isNaN(current)) {
                        badge.textContent = `Total ${Math.max(current - 1, 0)} pelajar`;
                    }
                });
            };

            document.querySelectorAll('.student-delete-form').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const studentName = form.dataset.studentName || 'student ini';
                    if (!confirm(`Padam ${studentName}? Tindakan ini tidak boleh dibatalkan.`)) {
                        return;
                    }

                    const button = form.querySelector('.student-delete-button');
                    const row = form.closest('.student-row');
                    const originalText = button?.textContent || 'Delete';
                    const formData = new FormData(form);

                    if (button) {
                        button.disabled = true;
                        button.textContent = 'Padam...';
                    }

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: formData,
                        });
                        const result = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            throw new Error(result.message || 'Student tidak dapat dipadam.');
                        }

                        if (row) {
                            row.classList.add('is-removing');
                            setTimeout(() => row.remove(), 220);
                            decrementRecordCount();
                        }
                    } catch (error) {
                        alert(error.message || 'Student tidak dapat dipadam.');

                        if (button) {
                            button.disabled = false;
                            button.textContent = originalText;
                        }
                    }
                });
            });
        });
    </script>
@endsection
