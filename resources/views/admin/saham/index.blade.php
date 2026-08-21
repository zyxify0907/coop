@extends('layouts.app')

@section('title', 'Saham')
@section('page-title', 'Saham')
@section('page-subtitle', 'Rekod saham pelajar, staff dan rumusan keseluruhan.')

@section('content')
<style>
    :root {
        --line: #e2e8f0;
        --soft: #f8fafc;
        --text: #0f172a;
        --muted: #64748b;
        --blue: var(--secondary);
        --red: var(--danger);
        --green: #16a34a;
    }

    .page-heading {
        display: none;
    }

    .share-wrap {
        max-width: none;
        margin: 0 auto;
        padding: 0 0 48px;
    }

    .share-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        margin-bottom: 24px;
        padding: 34px 36px;
        border: 1px solid var(--line);
        border-left: 5px solid var(--secondary);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .055);
        flex-wrap: wrap;
    }

    .share-head h1 {
        margin: 0;
        color: var(--text);
        font-size: 38px;
        line-height: 1.15;
        font-weight: 900;
        letter-spacing: 0;
    }

    .share-head p {
        margin: 14px 0 0;
        color: var(--muted-2);
        font-size: 16px;
        font-weight: 800;
    }

    .tabs {
        display: inline-flex;
        gap: 8px;
        padding: 6px;
        border: 1px solid var(--line);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
    }

    .tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 0 16px;
        border-radius: 10px;
        color: #334155;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .tab.is-active {
        background: var(--blue);
        color: #fff;
        box-shadow: 0 10px 22px rgba(30, 64, 175, .16);
    }

    .head-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .alert {
        padding: 13px 15px;
        border: 1px solid var(--danger-soft);
        border-radius: 14px;
        background: var(--danger-soft);
        color: var(--primary-dark);
        margin-bottom: 16px;
    }

    .panel {
        border: 1px solid var(--line);
        border-radius: 22px;
        background: #fff;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .055);
    }

    .panel-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 24px 28px;
        border-bottom: 1px solid var(--line);
        background: #fff;
        flex-wrap: wrap;
    }

    .panel-head h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 900;
        color: var(--text);
    }

    .panel-head span {
        color: var(--muted);
        display: block;
        margin-top: 8px;
        font-size: 14px;
        font-weight: 700;
    }

    .share-filter {
        display: grid;
        grid-template-columns: 240px 168px 210px 210px 210px auto;
        column-gap: 8px;
        row-gap: 10px;
        padding: 16px 18px;
        border-bottom: 1px solid var(--line);
        background: #fbfdff;
        align-items: end;
        justify-content: start;
    }

    .share-filter .field {
        grid-column: auto;
    }

    .share-filter.share-filter--staff {
        grid-template-columns: 240px 168px 210px 230px auto;
    }

    .share-filter .field.search-compact {
        width: 240px;
        max-width: 240px;
    }

    .share-filter .field.date-compact {
        width: 168px;
        max-width: 168px;
    }

    .share-filter input,
    .share-filter select {
        min-height: 44px;
        border-radius: 12px;
        background: #fff;
    }

    .share-filter__actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 12px;
        padding: 18px;
        align-items: end;
    }

    .field {
        grid-column: span 12;
    }

    .field label {
        display: block;
        margin-bottom: 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #475569;
    }

    .field input,
    .field select {
        width: 100%;
        min-height: 42px;
        padding: 9px 11px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: #fff;
        color: var(--text);
        font-size: 14px;
    }

    .button,
    .link-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-height: 40px;
        padding: 0 14px;
        border-radius: 12px;
        border: 1px solid transparent;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .button {
        background: var(--blue);
        color: #fff;
        box-shadow: 0 10px 22px rgba(30, 64, 175, .14);
    }

    .link-button {
        background: var(--secondary-soft);
        border-color: var(--secondary-soft);
        color: var(--secondary);
    }

    .link-button.danger {
        border-color: var(--danger-soft);
        color: var(--red);
        background: var(--danger-soft);
    }

    .link-button.export-button {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: #15803d;
    }

    .link-button.print-button {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: var(--secondary);
    }

    .profile-button {
        background: var(--secondary-soft);
        border-color: #bfdbfe;
        color: var(--secondary);
        box-shadow: none;
    }

    .profile-button:hover {
        background: var(--secondary);
        border-color: var(--secondary);
        color: #fff;
    }

    .table-wrap {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        border-top: 1px solid var(--line);
        background: #fff;
        box-shadow: inset -18px 0 20px -24px rgba(15, 23, 42, .35);
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 1180px;
    }

    th {
        padding: 14px 16px;
        border-bottom: 1px solid var(--line);
        background: var(--soft);
        color: var(--muted);
        text-align: left;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    th:last-child,
    td:last-child {
        width: 210px;
        min-width: 210px;
        max-width: 210px;
        text-align: center !important;
    }

    td {
        padding: 16px;
        border-bottom: 1px solid var(--line);
        color: var(--text);
        vertical-align: middle;
        font-size: 14px;
    }

    tbody tr:hover td {
        background: #f8fafc;
    }

    .name {
        font-weight: 900;
    }

    .sub {
        display: block;
        margin-top: 3px;
        color: var(--muted);
        font-size: 12px;
    }

    .amount {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        white-space: nowrap;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 108px;
        min-height: 32px;
        padding: 0 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-badge.active {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.inactive {
        background: #fee2e2;
        color: #b91c1c;
    }

    .actions-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        min-width: 0;
    }

    .staff-share-table .actions-cell {
        gap: 12px;
    }

    .staff-share-table {
        min-width: 1260px;
        table-layout: fixed;
    }

    .staff-share-table th {
        white-space: normal;
        line-height: 1.25;
        padding: 13px 10px;
        letter-spacing: .02em;
    }

    .staff-share-table td {
        padding: 15px 10px;
    }

    .staff-share-table th:nth-child(1),
    .staff-share-table td:nth-child(1) { width: 54px; }
    .staff-share-table th:nth-child(2),
    .staff-share-table td:nth-child(2) { width: 180px; }
    .staff-share-table th:nth-child(3),
    .staff-share-table td:nth-child(3) { width: 128px; }
    .staff-share-table th:nth-child(4),
    .staff-share-table td:nth-child(4) { width: 118px; }
    .staff-share-table th:nth-child(5),
    .staff-share-table td:nth-child(5) { width: 130px; }
    .staff-share-table th:nth-child(6),
    .staff-share-table td:nth-child(6) { width: 125px; }
    .staff-share-table th:nth-child(7),
    .staff-share-table td:nth-child(7),
    .staff-share-table th:nth-child(8),
    .staff-share-table td:nth-child(8),
    .staff-share-table th:nth-child(9),
    .staff-share-table td:nth-child(9),
    .staff-share-table th:nth-child(10),
    .staff-share-table td:nth-child(10) { width: 120px; }
    .staff-share-table th:nth-child(11),
    .staff-share-table td:nth-child(11) { width: 140px; text-align:center; }
    .staff-share-table th:nth-child(12),
    .staff-share-table td:nth-child(12) {
        width: 320px;
        min-width: 320px;
        max-width: 320px;
        text-align:center!important;
    }

    .student-share-table {
        min-width: 1500px;
        table-layout: fixed;
    }

    .student-share-table th {
        white-space: normal;
        line-height: 1.25;
        padding: 13px 10px;
        letter-spacing: .02em;
    }

    .student-share-table td {
        padding: 15px 10px;
    }

    .student-share-table th:nth-child(1),
    .student-share-table td:nth-child(1) { width: 54px; }
    .student-share-table th:nth-child(2),
    .student-share-table td:nth-child(2) { width: 170px; }
    .student-share-table th:nth-child(3),
    .student-share-table td:nth-child(3) { width: 120px; }
    .student-share-table th:nth-child(4),
    .student-share-table td:nth-child(4) { width: 120px; }
    .student-share-table th:nth-child(5),
    .student-share-table td:nth-child(5) { width: 105px; }
    .student-share-table th:nth-child(6),
    .student-share-table td:nth-child(6) { width: 90px; }
    .student-share-table th:nth-child(7),
    .student-share-table td:nth-child(7) { width: 125px; }
    .student-share-table th:nth-child(8),
    .student-share-table td:nth-child(8),
    .student-share-table th:nth-child(9),
    .student-share-table td:nth-child(9),
    .student-share-table th:nth-child(10),
    .student-share-table td:nth-child(10),
    .student-share-table th:nth-child(11),
    .student-share-table td:nth-child(11),
    .student-share-table th:nth-child(12),
    .student-share-table td:nth-child(12) { width: 118px; }
    .student-share-table th:nth-child(13),
    .student-share-table td:nth-child(13) { width: 130px; text-align:center; }
    .student-share-table th:nth-child(14),
    .student-share-table td:nth-child(14) {
        width: 280px;
        min-width: 280px;
        max-width: 280px;
        text-align:center!important;
    }

    .actions-cell form,
    .actions-cell > * {
        margin: 0;
    }

    .actions-cell form {
        display: flex;
        flex: 0 0 auto;
    }

    .actions-cell .button,
    .actions-cell .link-button,
    .actions-cell .profile-button {
        min-width: 96px;
        padding: 0 14px !important;
        white-space: nowrap;
    }

    .summary-table {
        max-width: 760px;
    }

    .summary-table tfoot td {
        background: #f8fafc;
        font-weight: 800;
    }

    .empty {
        padding: 36px 18px;
        text-align: center;
        color: var(--muted);
        font-weight: 700;
    }

    .pagination {
        padding: 18px 24px;
        display: flex;
        justify-content: center;
    }

    .print-report {
        display: none;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            background: #fff !important;
            font-family: Arial, Helvetica, sans-serif;
        }

        .panel,
        .share-wrap {
            overflow: visible !important;
        }

        body * {
            visibility: hidden !important;
        }

        .print-report,
        .print-report * {
            visibility: visible !important;
        }

        .print-report {
            display: block !important;
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            padding: 0;
            color: #111827;
            background: #fff;
            font-family: Arial, sans-serif;
        }

        .print-report__title {
            margin: 0;
            padding: 0 0 6px;
            border-bottom: 1px solid #111827;
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .print-report__meta {
            margin: 8px 0 10px;
            font-size: 9px;
        }

        .print-table {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed;
            border-collapse: collapse;
            border-spacing: 0;
            font-size: 8.3px;
            background: #fff;
        }

        .print-table thead {
            display: table-header-group;
        }

        .print-table tfoot {
            display: table-footer-group;
        }

        .print-table tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .print-table th,
        .print-table td {
            padding: 4px 4px;
            border: 1px solid #9ca3af;
            background: #fff !important;
            color: #111827 !important;
            vertical-align: middle;
            line-height: 1.2;
            word-break: normal;
            overflow-wrap: normal;
        }

        .print-table th {
            padding: 5px 4px;
            background: #f8fafc !important;
            font-size: 7.6px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            white-space: nowrap !important;
        }

        .print-table td:nth-child(1),
        .print-table td:nth-child(3),
        .print-table td:nth-child(4),
        .print-table td:nth-child(5),
        .print-table td:nth-child(6),
        .print-table td:nth-child(7),
        .print-table td:nth-child(13) {
            text-align: center;
            white-space: nowrap !important;
        }

        .print-table td:nth-child(8),
        .print-table td:nth-child(9),
        .print-table td:nth-child(10),
        .print-table td:nth-child(11),
        .print-table td:nth-child(12) {
            text-align: right;
            white-space: nowrap !important;
            overflow-wrap: normal;
        }

        .print-table--staff td:nth-child(7),
        .print-table--staff td:nth-child(8),
        .print-table--staff td:nth-child(9),
        .print-table--staff td:nth-child(10) {
            text-align: right;
            white-space: nowrap !important;
            overflow-wrap: normal;
        }

        .print-table--staff td:nth-child(11) {
            text-align: center;
            white-space: nowrap !important;
        }

        .print-report__name {
            display: block;
            font-weight: 700;
        }

        .print-report__email {
            display: block;
            margin-top: 2px;
            color: #475569 !important;
            font-size: 7.5px;
        }
    }

    @media (min-width: 860px) {
        .field.col-4 { grid-column: span 4; }
        .field.col-2 { grid-column: span 2; }
        .field.actions { grid-column: span 2; }
    }

    @media (max-width: 1180px) {
        .share-filter {
            grid-template-columns: repeat(3, minmax(180px, 1fr));
        }

        .share-filter__actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 720px) {
        .share-wrap {
            padding: 0 0 36px;
        }

        .share-head {
            align-items: flex-start;
            flex-direction: column;
            padding: 24px;
        }

        .share-head h1 {
            font-size: 30px;
        }

        .share-filter {
            grid-template-columns: 1fr;
        }

        .share-filter .field.search-compact,
        .share-filter .field.date-compact {
            max-width: none;
        }

        .share-filter__actions {
            align-items: stretch;
            flex-direction: column;
        }

        .share-filter__actions .button,
        .share-filter__actions .link-button {
            width: 100%;
        }

        .tabs {
            width: 100%;
            overflow-x: auto;
        }

        .tab {
            flex: 1 0 auto;
        }
    }

    /* Saham has many financial columns. Keep the table wide inside its own
       scroller instead of squeezing the action column on small laptops. */
    .student-share-table th:nth-child(14),
    .student-share-table td:nth-child(14),
    .staff-share-table th:nth-child(12),
    .staff-share-table td:nth-child(12) {
        width: 344px;
        min-width: 344px;
        max-width: 344px;
    }

    .student-share-table .actions-cell,
    .staff-share-table .actions-cell {
        flex-wrap: nowrap;
        gap: 8px;
    }

    .student-share-table .actions-cell .button,
    .student-share-table .actions-cell .link-button,
    .student-share-table .actions-cell .profile-button,
    .staff-share-table .actions-cell .button,
    .staff-share-table .actions-cell .link-button,
    .staff-share-table .actions-cell .profile-button {
        min-width: 0;
        padding-inline: 12px !important;
    }

    @container coop-content (max-width: 1180px) {
        .share-filter,
        .share-filter.share-filter--staff {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .share-filter .field.search-compact,
        .share-filter .field.date-compact {
            width: auto;
            max-width: none;
        }

        .share-filter__actions {
            grid-column: 1 / -1;
            min-width: 0;
            justify-self: start;
            flex-wrap: wrap;
        }

        .table-wrap > .student-share-table {
            width: max-content !important;
            min-width: 1564px !important;
            table-layout: fixed !important;
        }

        .table-wrap > .staff-share-table {
            width: max-content !important;
            min-width: 1344px !important;
            table-layout: fixed !important;
        }
    }

    @container coop-content (max-width: 720px) {
        .share-filter,
        .share-filter.share-filter--staff {
            grid-template-columns: minmax(0, 1fr);
            padding: 14px;
        }

        .share-filter__actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            gap: 8px;
        }

        .share-filter__actions .button,
        .share-filter__actions .link-button {
            width: 100%;
            min-width: 0;
            padding-inline: 10px;
        }
    }
</style>

@php
    $money = fn ($value) => 'RM ' . number_format((float) $value, 2);
    $studentStatusLabel = function ($record) use ($inactiveReasons) {
        if ($record->ahli->status_aktif ?? false) {
            return 'Aktif';
        }

        return $inactiveReasons[$record->id_ahli] ?? 'Pindah / Berhenti';
    };
    $staffStatusLabel = function ($staff) use ($inactiveStaffReasons) {
        if ($staff->status_aktif) {
            return 'Aktif';
        }

        return $inactiveStaffReasons[$staff->id_pekerja] ?? 'Pindah / Berhenti';
    };
@endphp

<div class="share-wrap">
    <div class="share-head">
        <div>
            <h1>Saham</h1>
            <p>Layout digital berdasarkan fail saham pelajar, saham staff dan rumusan saham.</p>
        </div>

        <div class="head-actions">
            @if ($category === 'rumusan')
                <a class="link-button" href="{{ route('admin.saham.export.csv', ['kategori' => $category]) }}">CSV</a>
            @endif
            <nav class="tabs" aria-label="Kategori saham">
                <a class="tab {{ $category === 'pelajar' ? 'is-active' : '' }}" href="{{ route('admin.saham.index', ['kategori' => 'pelajar']) }}">Pelajar</a>
                <a class="tab {{ $category === 'staff' ? 'is-active' : '' }}" href="{{ route('admin.saham.index', ['kategori' => 'staff']) }}">Staff</a>
                <a class="tab {{ $category === 'rumusan' ? 'is-active' : '' }}" href="{{ route('admin.saham.index', ['kategori' => 'rumusan']) }}">Rumusan Saham</a>
            </nav>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    @if ($category === 'pelajar')
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2>Saham Pelajar</h2>
                    <span>{{ $records->total() }} rekod pelajar. Tambah saham dibuat melalui page permohonan.</span>
                </div>
            </div>

            <form class="share-filter" method="GET" action="{{ route('admin.saham.index') }}">
                <input type="hidden" name="kategori" value="pelajar">
                <div class="field search-compact">
                    <label for="student_search">Cari Pelajar</label>
                    <input id="student_search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, no matrik, IC atau email">
                </div>
                <div class="field date-compact">
                    <label for="student_tarikh">Tarikh Daftar</label>
                    <input id="student_tarikh" name="tarikh" type="date" value="{{ $filters['tarikh'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="student_status">Status</label>
                    <select id="student_status" name="status">
                        <option value="">Semua status</option>
                        <option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif</option>
                        <option value="tidak_aktif" @selected(($filters['status'] ?? '') === 'tidak_aktif')>Tidak Aktif</option>
                    </select>
                </div>
                <div class="field">
                    <label for="student_program">Program</label>
                    <select id="student_program" name="program">
                        <option value="">Semua program</option>
                        @foreach ($studentPrograms as $programOption)
                            <option value="{{ $programOption }}" @selected(($filters['program'] ?? '') === $programOption)>{{ $programOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="student_kelas">Kelas</label>
                    <select id="student_kelas" name="kelas">
                        <option value="">Semua kelas</option>
                        @foreach ($studentClasses as $classOption)
                            <option value="{{ $classOption }}" @selected(($filters['kelas'] ?? '') === $classOption)>{{ $classOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="share-filter__actions">
                    <button class="button" type="submit">Cari</button>
                    <a class="link-button export-button" href="{{ route('admin.saham.export.csv', array_merge(request()->query(), ['kategori' => 'pelajar'])) }}">Export CSV</a>
                    <button class="link-button print-button" type="button" onclick="window.print()">Print</button>
                    <a class="link-button secondary" href="{{ route('admin.saham.index', ['kategori' => 'pelajar']) }}">Reset</a>
                </div>
            </form>

            <div class="table-wrap">
                <table class="student-share-table">
                    <thead>
                        <tr>
                            <th>Bil</th>
                            <th>Nama</th>
                            <th>No KP</th>
                            <th>No Matrik</th>
                            <th>Program</th>
                            <th>Kelas</th>
                            <th>Tarikh Daftar Ahli</th>
                            <th>Yuran Ahli</th>
                            <th>Saham Semasa</th>
                            <th>Tambahan Saham</th>
                            <th>Jumlah Saham</th>
                            <th>Status Pelajar</th>
                            <th style="text-align:center;">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            <tr>
                                <td>{{ $records->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="name">{{ $record->ahli->nama ?? '-' }}</span>
                                    <span class="sub">{{ $record->ahli->email ?? '-' }}</span>
                                </td>
                                <td>{{ $record->ahli->nric ?? '-' }}</td>
                                <td>{{ $record->ahli->no_matrik ?? '-' }}</td>
                                <td>{{ $record->ahli->program ?? '-' }}</td>
                                <td>{{ $record->ahli->kelas ?? '-' }}</td>
                                <td>{{ optional($record->ahli->tarikh_daftar)->format('d/m/Y') ?? '-' }}</td>
                                <td><span class="amount">{{ $money($record->yuran) }}</span></td>
                                <td><span class="amount">{{ $money($record->syer) }}</span></td>
                                <td><span class="amount">{{ $money($record->tambahan_saham) }}</span></td>
                                <td><span class="amount">{{ $money((float) $record->syer + (float) $record->tambahan_saham) }}</span></td>
                                <td>
                                    <span class="status-badge {{ ($record->ahli->status_aktif ?? false) ? 'active' : 'inactive' }}">
                                        {{ $studentStatusLabel($record) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        @if (isset($memberProfiles[$record->id_ahli]))
                                            <a class="button profile-button" href="{{ route('admin.anggota.show', $memberProfiles[$record->id_ahli]) }}">Lihat Profil</a>
                                        @else
                                            <span class="link-button" aria-disabled="true">Profil Tiada</span>
                                        @endif
                                        <form method="POST" action="{{ route('admin.saham.destroy', $record) }}" onsubmit="return confirm('Padam rekod saham ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="link-button danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13"><div class="empty">Tiada rekod saham pelajar.</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($records->hasPages())
                <div class="pagination">{{ $records->links() }}</div>
            @endif

            <section class="print-report" aria-hidden="true">
                <h1 class="print-report__title">SENARAI SAHAM PELAJAR</h1>
                <p class="print-report__meta">Tarikh Cetakan: {{ now()->format('d/m/Y') }}</p>
                <table class="print-table">
                    <colgroup>
                        <col style="width: 3%;">
                        <col style="width: 12%;">
                        <col style="width: 9%;">
                        <col style="width: 10%;">
                        <col style="width: 6%;">
                        <col style="width: 5%;">
                        <col style="width: 9%;">
                        <col style="width: 8%;">
                        <col style="width: 9%;">
                        <col style="width: 8%;">
                        <col style="width: 7%;">
                        <col style="width: 7%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Bil</th>
                            <th>Nama</th>
                            <th>No KP</th>
                            <th>No Matrik</th>
                            <th>Program</th>
                            <th>Kelas</th>
                            <th>Tarikh<br>Daftar</th>
                            <th>Yuran</th>
                            <th>Saham<br>Semasa</th>
                            <th>Tambahan<br>Saham</th>
                            <th>Jumlah<br>Saham</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($printStudentRecords as $printRecord)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="print-report__name">{{ $printRecord->ahli->nama ?? '-' }}</span>
                                    <span class="print-report__email">{{ $printRecord->ahli->email ?? '-' }}</span>
                                </td>
                                <td>{{ $printRecord->ahli->nric ?? '-' }}</td>
                                <td>{{ $printRecord->ahli->no_matrik ?? '-' }}</td>
                                <td>{{ $printRecord->ahli->program ?? '-' }}</td>
                                <td>{{ $printRecord->ahli->kelas ?? '-' }}</td>
                                <td>{{ optional($printRecord->ahli->tarikh_daftar)->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $money($printRecord->yuran) }}</td>
                                <td>{{ $money($printRecord->syer) }}</td>
                                <td>{{ $money($printRecord->tambahan_saham) }}</td>
                                <td>{{ $money((float) $printRecord->syer + (float) $printRecord->tambahan_saham) }}</td>
                                <td>{{ $studentStatusLabel($printRecord) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12">Tiada rekod saham pelajar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </section>
    @elseif ($category === 'staff')
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2>Saham Staff</h2>
                    <span>{{ $staffMembers->total() }} rekod staff dipaparkan dengan susunan yang sama seperti pelajar.</span>
                </div>
            </div>

            <form class="share-filter share-filter--staff" method="GET" action="{{ route('admin.saham.index') }}">
                <input type="hidden" name="kategori" value="staff">
                <div class="field search-compact">
                    <label for="staff_search">Cari Staff</label>
                    <input id="staff_search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, no pekerja, IC atau email">
                </div>
                <div class="field date-compact">
                    <label for="staff_tarikh">Tarikh Mula</label>
                    <input id="staff_tarikh" name="tarikh" type="date" value="{{ $filters['tarikh'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="staff_status">Status</label>
                    <select id="staff_status" name="status">
                        <option value="">Semua status</option>
                        <option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif</option>
                        <option value="tidak_aktif" @selected(($filters['status'] ?? '') === 'tidak_aktif')>Tidak Aktif</option>
                    </select>
                </div>
                <div class="field">
                    <label for="staff_type">Jenis Staff</label>
                    <select id="staff_type" name="staff_type">
                        <option value="">Semua jenis staff</option>
                        @foreach ($staffTypes as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['staff_type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="share-filter__actions">
                    <button class="button" type="submit">Cari</button>
                    <a class="link-button export-button" href="{{ route('admin.saham.export.csv', array_merge(request()->query(), ['kategori' => 'staff'])) }}">Export CSV</a>
                    <button class="link-button print-button" type="button" onclick="window.print()">Print</button>
                    <a class="link-button secondary" href="{{ route('admin.saham.index', ['kategori' => 'staff']) }}">Reset</a>
                </div>
            </form>

            <div class="table-wrap">
                <table class="staff-share-table">
                    <thead>
                        <tr>
                            <th>Bil</th>
                            <th>Nama</th>
                            <th>No KP</th>
                            <th>No Pekerja</th>
                            <th>Jenis Staff</th>
                            <th>Tarikh Mula Kerja</th>
                            <th>Saham Semasa</th>
                            <th>Tambahan Saham</th>
                            <th>Jumlah Saham</th>
                            <th>Status Staff</th>
                            <th style="text-align:right;">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($staffMembers as $staff)
                            @php
                                $staffShare = $staff->sahamStaff;
                                $staffTotalShare = (float) ($staffShare->syer ?? 0) + (float) ($staffShare->tambahan_saham ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $staffMembers->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="name">{{ $staff->nama }}</span>
                                    <span class="sub">{{ $staff->email ?? '-' }}</span>
                                </td>
                                <td>{{ $staff->nric ?? '-' }}</td>
                                <td>{{ $staff->no_pekerja ?? '-' }}</td>
                                <td>{{ $staff->staff_type_label }}</td>
                                <td>{{ optional($staff->tarikh_mula)->format('d/m/Y') ?? '-' }}</td>
                                <td><span class="amount">{{ $money($staffShare->syer ?? 0) }}</span></td>
                                <td><span class="amount">{{ $money($staffShare->tambahan_saham ?? 0) }}</span></td>
                                <td><span class="amount">{{ $money($staffTotalShare) }}</span></td>
                                <td>
                                    <span class="status-badge {{ $staff->status_aktif ? 'active' : 'inactive' }}">
                                        {{ $staffStatusLabel($staff) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a class="button profile-button" href="{{ route('admin.users.edit', ['type' => 'staff', 'id' => $staff->id_pekerja]) }}">Lihat Profil</a>
                                        <form method="POST" action="{{ route('admin.users.destroy', ['type' => 'staff', 'id' => $staff->id_pekerja]) }}" onsubmit="return confirm('Padam rekod staff ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="link-button danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12"><div class="empty">Tiada rekod staff.</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($staffMembers->hasPages())
                <div class="pagination">{{ $staffMembers->links() }}</div>
            @endif

            <section class="print-report" aria-hidden="true">
                <h1 class="print-report__title">SENARAI SAHAM STAFF</h1>
                <p class="print-report__meta">Tarikh Cetakan: {{ now()->format('d/m/Y') }}</p>
                <table class="print-table print-table--staff">
                    <colgroup>
                        <col style="width: 4%;">
                        <col style="width: 15%;">
                        <col style="width: 10%;">
                        <col style="width: 10%;">
                        <col style="width: 13%;">
                        <col style="width: 10%;">
                        <col style="width: 8%;">
                        <col style="width: 9%;">
                        <col style="width: 8%;">
                        <col style="width: 7%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Bil</th>
                            <th>Nama</th>
                            <th>No KP</th>
                            <th>No Pekerja</th>
                            <th>Jenis Staff</th>
                            <th>Tarikh<br>Mula Kerja</th>
                            <th>Saham<br>Semasa</th>
                            <th>Tambahan<br>Saham</th>
                            <th>Jumlah<br>Saham</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($printStaffRecords as $printStaff)
                            @php
                                $printStaffShare = $printStaff->sahamStaff;
                                $printStaffTotalShare = (float) ($printStaffShare->syer ?? 0) + (float) ($printStaffShare->tambahan_saham ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="print-report__name">{{ $printStaff->nama ?? '-' }}</span>
                                    <span class="print-report__email">{{ $printStaff->email ?? '-' }}</span>
                                </td>
                                <td>{{ $printStaff->nric ?? '-' }}</td>
                                <td>{{ $printStaff->no_pekerja ?? '-' }}</td>
                                <td>{{ $printStaff->staff_type_label }}</td>
                                <td>{{ optional($printStaff->tarikh_mula)->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $money($printStaffShare->syer ?? 0) }}</td>
                                <td>{{ $money($printStaffShare->tambahan_saham ?? 0) }}</td>
                                <td>{{ $money($printStaffTotalShare) }}</td>
                                <td>{{ $staffStatusLabel($printStaff) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">Tiada rekod saham staff.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </section>
    @else
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2>Jumlah Saham Koperasi Politeknik Besut</h2>
                    <span>Rumusan digital berdasarkan rekod sistem.</span>
                </div>
            </div>

            <div class="table-wrap">
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Perkara</th>
                            <th>Orang</th>
                            <th>Jumlah Saham</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Jumlah Saham Pelajar</td>
                            <td>{{ $summary['student_count'] }}</td>
                            <td><span class="amount">{{ $money($summary['student_total']) }}</span></td>
                        </tr>
                        <tr>
                            <td>Jumlah Saham Staff</td>
                            <td>{{ $summary['staff_count'] }}</td>
                            <td><span class="amount">{{ $money($summary['staff_total']) }}</span></td>
                        </tr>
                        <tr>
                            <td>Pelajar Pindah / Berhenti</td>
                            <td>{{ $summary['stopped_student_count'] }}</td>
                            <td><span class="amount">{{ $money($summary['stopped_total']) }}</span></td>
                        </tr>
                        <tr>
                            <td>Staff Pindah / Berhenti</td>
                            <td>{{ $summary['stopped_staff_count'] }}</td>
                            <td><span class="amount">{{ $money($summary['stopped_staff_total']) }}</span></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Jumlah Saham Anggota Koperasi Polibesut</td>
                            <td>{{ $summary['grand_count'] }}</td>
                            <td><span class="amount">{{ $money($summary['grand_total']) }}</span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    @endif
</div>

@endsection