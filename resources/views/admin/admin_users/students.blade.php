@extends('layouts.app')

@section('title', 'Manage Students')
@section('page-title', 'Manage Student')
@section('page-subtitle', 'Kemaskini profil akaun student. Password student dijana automatik.')

@section('content')
    <section class="student-page-hero">
        <div>
            <h1>Manage Student</h1>
            <p>Kemaskini profil akaun student. Password dijana automatik daripada No Matrik.</p>
        </div>
        <div class="student-page-hero__meta">
            <span>Total {{ $students->total() }} pelajar</span>
        </div>
    </section>

    {{-- Status Message with Auto-Dismiss --}}
    @if (session('status'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <div class="alert-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <span>{{ session('status') }}</span>
            <button class="alert-close" type="button" aria-label="Close notification">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <div class="alert-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
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
                <h2><span class="brand-red">Student</span> <span class="brand-blue">Login</span></h2>
                <p>Senarai student yang boleh login menggunakan No. Matrik atau No. KP dan password automatik.</p>
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
                <button class="search-button" type="submit">Cari</button>
                @if (($search ?? request('search')) || ($filters['program'] ?? request('program')) || ($filters['kelas'] ?? request('kelas')) || ($filters['semester'] ?? request('semester')) || ($filters['tarikh_masuk'] ?? request('tarikh_masuk')))
                    <a class="reset-button" href="{{ route('admin.users.students') }}">Reset</a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div style="overflow-x: auto; padding: 0 4px;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 14px; min-width: 980px;">
                <thead style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0;">
                    <tr>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 500; color: #475569; letter-spacing: 0.02em; text-transform: uppercase; font-size: 12px;">Nama</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 500; color: #475569; letter-spacing: 0.02em; text-transform: uppercase; font-size: 12px;">No Matrik</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 500; color: #475569; letter-spacing: 0.02em; text-transform: uppercase; font-size: 12px;">No. KP</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 500; color: #475569; letter-spacing: 0.02em; text-transform: uppercase; font-size: 12px;">Program</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 500; color: #475569; letter-spacing: 0.02em; text-transform: uppercase; font-size: 12px;">Kelas</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 500; color: #475569; letter-spacing: 0.02em; text-transform: uppercase; font-size: 12px;">Tarikh Masuk</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 500; color: #475569; letter-spacing: 0.02em; text-transform: uppercase; font-size: 12px; min-width: 118px;">Semester</th>
                        <th style="padding: 12px 16px; text-align: right; font-weight: 500; color: #475569; letter-spacing: 0.02em; text-transform: uppercase; font-size: 12px; width: 120px;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        @php
                            $semesterLabel = $student->semester
                                ? preg_replace('/^sem\s+/i', 'Semester ', $student->semester)
                                : '-';
                        @endphp
                        <tr class="student-row" data-student-row="{{ $student->id_ahli }}" style="border-bottom: 1px solid #E2E8F0; transition: background 0.15s, opacity 0.2s ease, transform 0.2s ease; background: white;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                            <td style="padding: 14px 16px; color: #0F172A; font-weight: 500;">{{ $student->nama }}</td>
                            <td style="padding: 14px 16px; color: #475569;">
                                <code style="background: #F1F5F9; padding: 2px 8px; border-radius: 4px; font-size: 13px; color: #334155;">{{ $student->no_matrik }}</code>
                            </td>
                            <td style="padding: 14px 16px; color: #475569; font-family: monospace; letter-spacing: 0.5px;">{{ $student->nric ? substr($student->nric, 0, 6).'****' : '-' }}</td>
                            <td style="padding: 14px 16px; color: #475569;">{{ $student->program ?? '-' }}</td>
                            <td style="padding: 14px 16px; color: #475569;">{{ $student->kelas ?? '-' }}</td>
                            <td style="padding: 14px 16px; color: #475569;">{{ $student->tarikh_daftar ? $student->tarikh_daftar->format('d/m/Y') : '-' }}</td>
                            <td style="padding: 14px 16px; color: #475569; min-width: 118px;">
                                <span style="display: inline-flex; align-items: center; white-space: nowrap; background: #eff8ff; color: #175cd3; padding: 2px 10px; border-radius: 100px; font-size: 12px; font-weight: 500;">{{ $semesterLabel }}</span>
                            </td>
                            <td style="padding: 14px 16px; text-align: right;">
                                <div class="student-action-buttons">
                                    <a class="student-edit-button" href="{{ route('admin.users.edit', ['type' => 'student', 'id' => $student->id_ahli]) }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                        Edit
                                    </a>
                                    <form class="student-delete-form" method="POST" action="{{ route('admin.users.destroy', ['type' => 'student', 'id' => $student->id_ahli]) }}" data-student-name="{{ $student->nama }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="student-delete-button" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding: 48px 16px; text-align: center; color: #64748B; background: white;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    <span style="font-size: 15px;">Tiada rekod student.</span>
                                    <span style="font-size: 13px; color: #94A3B8;">Klik "Tambah Student" untuk mendaftar akaun pelajar baru.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Optional Footer with Pagination (if needed) --}}
        @if(method_exists($students, 'links'))
            <div style="padding: 16px 24px; border-top: 1px solid #E2E8F0; background: #F8FAFC; border-radius: 0 0 16px 16px;">
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
        @media (max-width: 640px) {
            .student-page-hero {
                align-items: flex-start;
                flex-direction: column;
                padding: 24px;
            }
            .student-page-hero h1 {
                font-size: 30px;
            }
        }
    </style>

    {{-- Auto-dismiss alert after 5 seconds --}}
    @if (session('status'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const alert = document.querySelector('.alert-dismissible');
                if (alert) {
                    setTimeout(() => {
                        alert.style.transition = 'opacity 0.5s, transform 0.5s';
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-10px)';
                        setTimeout(() => alert.remove(), 500);
                    }, 5000);

                    const closeBtn = alert.querySelector('.alert-close');
                    if (closeBtn) {
                        closeBtn.addEventListener('click', () => {
                            alert.style.opacity = '0';
                            alert.style.transform = 'translateY(-10px)';
                            setTimeout(() => alert.remove(), 500);
                        });
                    }
                }
            });
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                        badge.textContent = `${Math.max(current - 1, 0)} rekod`;
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
