@extends('layouts.app')

@section('title', 'Transaksi Saham')
@section('page-title', 'Transaksi Saham')
@section('page-subtitle', 'Sejarah keluar masuk saham koperasi.')

@section('content')
    @include('components.coop-page-style')

    @php
        $typeLabels = [
            'OPENING_BALANCE' => 'Baki Awal',
            'SHARE_ADDITION' => 'Tambah Saham',
            'SHARE_WITHDRAWAL' => 'Pengeluaran Saham',
            'MANUAL_CREATE' => 'Rekod Manual Baru',
            'MANUAL_UPDATE' => 'Kemaskini Manual',
            'MANUAL_ADJUSTMENT' => 'Pelarasan Manual',
        ];

        $directionLabels = [
            'CREDIT' => 'Masuk',
            'DEBIT' => 'Keluar',
        ];
        $shareDashboardRoute = match (true) {
            ($role ?? null) === 'ahli' => 'student.dashboard.saham',
            ($role ?? null) === 'staff' && ($user->staff_type ?? null) === 'lecturer_member' => 'lecturer-member.dashboard.saham',
            ($role ?? null) === 'staff' && ($user->staff_type ?? null) === 'clothing_staff' => 'clothing-staff.dashboard.saham',
            ($role ?? null) === 'staff' && ($user->staff_type ?? null) === 'share_staff' => 'share-staff.dashboard.saham',
            ($role ?? null) === 'staff' && ($user->staff_type ?? null) === 'coop_manager' => 'coop-manager.dashboard.saham',
            default => null,
        };
        $showAllTransactions = ($canViewAllTransactions ?? false) && ! ($showOwnTransactions ?? false);
    @endphp

    <div class="coop-wrap transaction-page">
        <section class="coop-hero transaction-hero">
            <div>
                <h1>Transaksi Saham</h1>
                <p>{{ $showAllTransactions ? 'Cari transaksi saham semua pelajar dan staff dalam satu tempat.' : 'Setiap kali saham bertambah atau berkurang, sistem simpan rekod di sini supaya baki boleh disemak semula.' }}</p>
            </div>
            <div class="transaction-hero__side">
                @if ($shareDashboardRoute)
                    <a class="student-share-backlink" href="{{ route($shareDashboardRoute) }}">Dashboard Saham</a>
                @endif
            </div>
        </section>

        <section class="panel transaction-panel">
            <div class="transaction-panel__head">
                <div>
                    <h2>Senarai Transaksi</h2>
                    <p>{{ $showAllTransactions ? 'Cari nama ahli, no matrik, no pekerja, no anggota staff atau IC untuk semak transaksi.' : 'Cari nama ahli, no matrik, no anggota staff atau IC untuk semak senarai transaksi saham.' }}</p>
                </div>
            </div>

            <form class="transaction-filter" method="GET" action="{{ route('koperasi.transactions.index') }}">
                @if ($canViewAllTransactions ?? false)
                    <input type="hidden" name="scope" value="{{ $filters['scope'] ?? 'all' }}">
                    <div class="field">
                        <label for="member_type">Kategori Ahli</label>
                        <select id="member_type" name="member_type">
                            <option value="">Semua pelajar & staff</option>
                            <option value="student" @selected(($filters['member_type'] ?? '') === 'student')>Pelajar sahaja</option>
                            <option value="staff" @selected(($filters['member_type'] ?? '') === 'staff')>Staff sahaja</option>
                        </select>
                    </div>
                @endif
                <div class="field">
                    <label for="search">Cari Ahli</label>
                    <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, no matrik, no pekerja, no anggota staff atau IC">
                </div>
                <div class="field">
                    <label for="type">Jenis Transaksi</label>
                    <select id="type" name="type">
                        <option value="">Semua jenis</option>
                        @foreach ($typeLabels as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['type'] ?? '') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="direction">Arah</label>
                    <select id="direction" name="direction">
                        <option value="">Semua arah</option>
                        <option value="CREDIT" @selected(($filters['direction'] ?? '') === 'CREDIT')>Masuk</option>
                        <option value="DEBIT" @selected(($filters['direction'] ?? '') === 'DEBIT')>Keluar</option>
                    </select>
                </div>
                <div class="transaction-filter__actions">
                    <button class="button" type="submit">Cari</button>
                    <a class="link-button secondary" href="{{ route('koperasi.transactions.index', ($filters['scope'] ?? '') === 'mine' ? ['scope' => 'mine'] : []) }}">Reset</a>
                </div>
            </form>

            <div class="transaction-table-wrap">
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>Tarikh</th>
                            <th>Ahli</th>
                            <th>Jenis Transaksi</th>
                            <th>Masuk / Keluar</th>
                            <th>Amaun</th>
                            <th>Baki Selepas</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            @php
                                $member = $transaction->member_type === 'staff' ? $transaction->staff : $transaction->student;
                                $memberName = $member->nama ?? 'Ahli tidak dijumpai';
                                $memberNumber = $transaction->member_type === 'staff'
                                    ? ($member->no_anggota ?? $member->no_pekerja ?? 'Staff #'.$transaction->member_id)
                                    : ($member->no_matrik ?? 'Pelajar #'.$transaction->member_id);
                                $memberMeta = $transaction->member_type === 'staff' ? 'Staff' : 'Pelajar';
                                $isCredit = $transaction->direction === 'CREDIT';
                            @endphp
                            <tr>
                                <td>{{ optional($transaction->transacted_at)->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <div class="member-cell">
                                        <span class="member-avatar">{{ strtoupper(substr($memberName, 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $memberName }}</strong>
                                            <span>{{ $memberNumber }} &middot; {{ $memberMeta }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="type-pill">{{ $typeLabels[$transaction->transaction_type] ?? str_replace('_', ' ', $transaction->transaction_type) }}</span></td>
                                <td>
                                    <span class="direction-pill {{ $isCredit ? 'is-credit' : 'is-debit' }}">
                                        {{ $directionLabels[$transaction->direction] ?? $transaction->direction }}
                                    </span>
                                </td>
                                <td class="money {{ $isCredit ? 'money--credit' : 'money--debit' }}">
                                    {{ $isCredit ? '+' : '-' }} RM {{ number_format((float) $transaction->amount, 2) }}
                                </td>
                                <td class="money"><strong>RM {{ number_format((float) $transaction->balance_after, 2) }}</strong></td>
                                <td class="note-cell">{{ $transaction->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">Tiada transaksi saham untuk paparan ini.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $transactions->links() }}
    </div>
@endsection

@push('styles')
    <style>
        .transaction-page {
            gap: 22px;
        }

        .student-share-backlink {
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 20px;
            border: 1px solid #D7E3F5;
            border-radius: 10px;
            background: #fff;
            color: var(--secondary);
            font-size: 15px;
            font-weight: 900;
            text-decoration: none;
            white-space: nowrap;
        }

        .student-share-backlink::before {
            content: '\2190';
            margin-right: 8px;
            color: var(--primary);
            font-weight: 900;
        }

        .student-share-backlink:hover {
            border-color: #AFC7EA;
            background: #F8FBFF;
        }

        .transaction-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .transaction-hero__side {
            display: grid;
            justify-items: end;
            gap: 10px;
        }

        .transaction-panel {
            overflow: hidden;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 18px 38px rgba(15, 23, 42, .06);
        }

        .transaction-panel__head {
            padding: 24px 28px;
            border-bottom: 1px solid var(--line);
        }

        .transaction-panel__head h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
        }

        .transaction-panel__head p {
            margin: 8px 0 0;
            color: var(--muted);
            font-weight: 700;
        }

        .transaction-filter {
            display: grid;
            grid-template-columns: minmax(260px, 1.4fr) repeat(2, minmax(170px, .75fr)) auto;
            gap: 12px;
            padding: 18px;
            border-bottom: 1px solid var(--line);
            background: #fbfdff;
            align-items: end;
        }

        .transaction-filter .field label {
            display: block;
            margin-bottom: 7px;
            color: #475569;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .transaction-filter input,
        .transaction-filter select {
            width: 100%;
            min-height: 44px;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 10px 12px;
            background: #fff;
            font: inherit;
        }

        .transaction-filter__actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .transaction-table-wrap {
            overflow-x: auto;
        }

        .transaction-table {
            width: 100%;
            min-width: 1060px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .transaction-table th {
            padding: 15px 18px;
            background: #f8fafc;
            color: var(--muted);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .03em;
            text-align: left;
            text-transform: uppercase;
        }

        .transaction-table td {
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }

        .member-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .member-avatar {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border: 1px solid #bfdbfe;
            background: var(--secondary-soft);
            color: var(--secondary);
            font-weight: 900;
        }

        .member-cell strong,
        .member-cell span {
            display: block;
        }

        .member-cell span {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .type-pill,
        .direction-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }

        .type-pill {
            background: var(--secondary-soft);
            color: var(--secondary);
        }

        .direction-pill.is-credit {
            background: var(--success-soft);
            color: var(--success);
        }

        .direction-pill.is-debit {
            background: var(--danger-soft);
            color: var(--danger);
        }

        .money {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            white-space: nowrap;
        }

        .money--credit {
            color: var(--success);
            font-weight: 900;
        }

        .money--debit {
            color: var(--danger);
            font-weight: 900;
        }

        .note-cell {
            color: var(--muted);
            line-height: 1.5;
            min-width: 220px;
        }

        @media (max-width: 900px) {
            .transaction-hero {
                align-items: flex-start;
                flex-direction: column;
            }

            .transaction-hero__side {
                width: 100%;
                justify-items: stretch;
            }

            .transaction-filter {
                grid-template-columns: 1fr;
            }

            .transaction-filter__actions {
                align-items: stretch;
                flex-direction: column;
            }

            .transaction-filter__actions .button,
            .transaction-filter__actions .link-button {
                width: 100%;
            }
        }

        .content:has(> .transaction-page) {
            background: #F4F7FB;
        }

        .content > .transaction-page {
            width: 100%;
            max-width: none;
            margin: 0;
            gap: 20px;
        }

        .content .transaction-page .transaction-hero {
            position: relative;
            min-height: 0 !important;
            padding: 26px 30px !important;
            border: 1px solid #D7E2EF !important;
            border-left: 6px solid #082F59 !important;
            border-radius: 10px !important;
            background: #fff !important;
            background-image: none !important;
            box-shadow: 0 7px 18px rgba(8, 47, 89, .035) !important;
        }

        .content .transaction-page .transaction-hero::before {
            content: "";
            position: absolute;
            top: 24px;
            left: 30px;
            width: 42px;
            height: 3px;
            border-radius: 999px;
            background: #ED1C2E;
        }

        .content .transaction-page .coop-kicker {
            margin-top: 15px;
            min-height: 24px;
            padding: 0 10px;
            border-radius: 5px;
            background: #EAF3FF;
            color: #0B5ED7;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .04em;
        }

        .content .transaction-page .transaction-hero h1 {
            margin: 12px 0 0 !important;
            color: #082F59 !important;
            font-size: 32px !important;
            line-height: 1.15 !important;
            font-weight: 900 !important;
        }

        .content .transaction-page .transaction-hero p {
            margin: 9px 0 0 !important;
            color: #496487 !important;
            font-size: 15px !important;
            font-weight: 750 !important;
            line-height: 1.45 !important;
        }

        .content .transaction-page .student-share-backlink {
            min-height: 40px;
            padding: 0 17px;
            border: 1px solid #B9CBE4;
            border-radius: 7px;
            background: #fff;
            color: #0B5ED7;
            font-size: 13px;
            box-shadow: none;
        }

        .content .transaction-page .student-share-backlink::before {
            color: #082F59;
        }

        .content .transaction-page .transaction-panel {
            border: 1px solid #D7E2EF !important;
            border-radius: 10px !important;
            background: #fff !important;
            box-shadow: 0 7px 18px rgba(8, 47, 89, .035) !important;
        }

        .content .transaction-page .transaction-panel__head {
            position: relative;
            padding: 25px 26px 20px;
            border-bottom: 1px solid #D7E2EF;
            background: #fff;
        }

        .content .transaction-page .transaction-panel__head::before {
            content: "";
            display: block;
            width: 42px;
            height: 3px;
            margin-bottom: 11px;
            border-radius: 999px;
            background: #ED1C2E;
        }

        .content .transaction-page .transaction-panel__head h2 {
            color: #082F59;
            font-size: 24px;
            line-height: 1.2;
            font-weight: 900;
        }

        .content .transaction-page .transaction-panel__head p {
            margin-top: 7px;
            color: #496487;
            font-size: 14px;
            font-weight: 750;
        }

        .content .transaction-page .transaction-filter {
            display: flex;
            align-items: end;
            gap: 10px;
            padding: 13px 26px 15px;
            border-bottom: 1px solid #D7E2EF;
            background: #FBFDFF;
        }

        .content .transaction-page .transaction-filter .field {
            min-width: 0;
            flex: 1 1 0;
        }

        .content .transaction-page .transaction-filter .field:first-of-type {
            flex-basis: 230px;
        }

        .content .transaction-page .transaction-filter .field:nth-of-type(2) {
            flex-basis: 270px;
        }

        .content .transaction-page .transaction-filter__actions {
            flex: 0 0 auto;
            gap: 8px;
        }

        .content .transaction-page .transaction-filter .field label {
            margin-bottom: 6px;
            color: #496487;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .04em;
        }

        .content .transaction-page .transaction-filter input,
        .content .transaction-page .transaction-filter select {
            min-height: 38px;
            border: 1px solid #D7E2EF;
            border-radius: 7px;
            background: #fff;
            color: #082F59;
            font-size: 13px;
            font-weight: 700;
        }

        .content .transaction-page .button,
        .content .transaction-page .link-button {
            min-height: 38px !important;
            border-radius: 7px !important;
            padding: 0 16px !important;
            font-size: 12px !important;
            font-weight: 900 !important;
        }

        .content .transaction-page .button {
            border-color: #0B5ED7 !important;
            background: #0B5ED7 !important;
            color: #fff !important;
            box-shadow: 0 6px 14px rgba(11, 94, 215, .14) !important;
        }

        .content .transaction-page .link-button {
            border: 1px solid #B9CBE4 !important;
            background: #fff !important;
            color: #0B5ED7 !important;
        }

        .content .transaction-page .transaction-table-wrap {
            width: calc(100% - 32px);
            margin: 0 16px 16px;
            overflow-x: auto;
            border: 1px solid #D7E2EF;
            border-radius: 8px;
            background: #fff;
        }

        .content .transaction-page .transaction-table {
            width: 100%;
            min-width: 1060px;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .content .transaction-page .transaction-table th:nth-child(1),
        .content .transaction-page .transaction-table td:nth-child(1) {
            width: 9%;
        }

        .content .transaction-page .transaction-table th:nth-child(2),
        .content .transaction-page .transaction-table td:nth-child(2) {
            width: 22%;
        }

        .content .transaction-page .transaction-table th:nth-child(3),
        .content .transaction-page .transaction-table td:nth-child(3) {
            width: 15%;
        }

        .content .transaction-page .transaction-table th:nth-child(4),
        .content .transaction-page .transaction-table td:nth-child(4) {
            width: 14%;
        }

        .content .transaction-page .transaction-table th:nth-child(5),
        .content .transaction-page .transaction-table td:nth-child(5),
        .content .transaction-page .transaction-table th:nth-child(6),
        .content .transaction-page .transaction-table td:nth-child(6) {
            width: 14%;
        }

        .content .transaction-page .transaction-table th:nth-child(7),
        .content .transaction-page .transaction-table td:nth-child(7) {
            width: 20%;
        }

        .content .transaction-page .transaction-table th {
            padding: 9px 12px;
            border-bottom: 1px solid #D7E2EF;
            background: #F8FBFF;
            color: #496487;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .03em;
        }

        .content .transaction-page .transaction-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #D7E2EF;
            color: #082F59;
            font-size: 12px;
            font-weight: 750;
        }

        .content .transaction-page .transaction-table tbody tr:hover td {
            background: #F8FBFF;
        }

        .content .transaction-page .member-cell {
            gap: 8px;
        }

        .content .transaction-page .member-avatar {
            width: 28px;
            height: 28px;
            border: 1px solid #CFE0F5;
            background: #EAF3FF;
            color: #0B5ED7;
            font-size: 11px;
        }

        .content .transaction-page .member-cell strong {
            color: #082F59;
            font-size: 12px;
            font-weight: 900;
        }

        .content .transaction-page .member-cell span {
            color: #496487;
            font-size: 11px;
            font-weight: 700;
        }

        .content .transaction-page .type-pill,
        .content .transaction-page .direction-pill {
            min-height: 24px;
            padding: 0 9px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 900;
        }

        .content .transaction-page .type-pill {
            border: 1px solid #CFE0F5;
            background: #EAF3FF;
            color: #0B5ED7;
        }

        .content .transaction-page .direction-pill.is-credit {
            border: 1px solid #B8EFCB;
            background: #DDF8E7;
            color: #128A4A;
        }

        .content .transaction-page .direction-pill.is-debit {
            border: 1px solid #F6C7C7;
            background: #FDECEC;
            color: #C4162A;
        }

        .content .transaction-page .money {
            color: #082F59;
            font-family: inherit;
            font-weight: 900;
        }

        .content .transaction-page .money--credit {
            color: #128A4A;
        }

        .content .transaction-page .money--debit {
            color: #C4162A;
        }

        .content .transaction-page .note-cell {
            color: #496487;
            line-height: 1.35;
        }

        @media (max-width: 900px) {
            .content > .transaction-page {
                width: 100%;
            }

            .content .transaction-page .transaction-filter {
                display: grid;
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush
