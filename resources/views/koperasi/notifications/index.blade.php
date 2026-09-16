@extends('layouts.app')

@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')
@section('page-subtitle', 'Makluman sistem koperasi.')

@push('styles')
    <style>
        .content:has(> .notification-page) { background: #f4f6f9; }
        .notification-page { width: 100%; max-width: 1600px; margin: 0 auto; gap: 18px; }
        .notification-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            padding: 26px 28px;
            border: 1px solid #d7e2ef;
            border-left: 6px solid #0b1e36;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 12px 28px rgba(11, 30, 54, .08);
        }
        .notification-accent { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .notification-accent__bar { width: 24px; height: 3px; border-radius: 999px; background: #d91c1c; }
        .notification-accent__tag { color: #0d6efd; font-size: 11px; font-weight: 900; letter-spacing: .5px; text-transform: uppercase; }
        .notification-hero h1 { margin: 0; color: #071a33; font-size: 26px; line-height: 1.15; font-weight: 700; letter-spacing: 0; }
        .notification-hero p { margin: 8px 0 0; color: #263a54; font-size: 14px; font-weight: 600; line-height: 1.45; }
        .notification-hero .button { min-height: 40px; white-space: nowrap; }
        .notification-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .notification-summary__item {
            min-height: 74px;
            padding: 14px 18px;
            border: 1px solid #d7e2ef;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(11, 30, 54, .08);
        }
        .notification-summary__item span { display: block; color: #0b1e36; font-size: 11px; font-weight: 900; letter-spacing: .03em; text-transform: uppercase; }
        .notification-summary__item strong { display: block; margin-top: 4px; color: #0b1e36; font-size: 26px; line-height: 1; font-weight: 900; }
        .notification-summary__item--unread strong { color: #f59e0b; }
        .notifications-card { overflow: hidden; border: 1px solid #d7e2ef; border-radius: 12px; background: #fff; box-shadow: 0 10px 28px rgba(11, 30, 54, .07); }
        .notifications-panel__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 24px; border-bottom: 1px solid #d7e2ef; }
        .notifications-panel__title { display: flex; align-items: center; gap: 10px; margin-bottom: 4px; }
        .notifications-panel__head h2 { margin: 0; color: #071a33; font-size: 22px; line-height: 1.2; font-weight: 800; letter-spacing: 0; }
        .notifications-panel__head p { margin: 0; color: #263a54; font-size: 13px; font-weight: 600; line-height: 1.45; }
        .notifications-filter { display: grid; grid-template-columns: minmax(220px, 1.7fr) minmax(150px, .8fr) minmax(150px, .8fr) auto; gap: 12px; align-items: end; padding: 16px 24px; border-bottom: 1px solid #d7e2ef; background: #fbfdff; }
        .notifications-filter label { display: grid; gap: 6px; color: #0b1e36; font-size: 12px; font-weight: 800; }
        .notifications-filter input, .notifications-filter select { width: 100%; min-height: 39px; padding: 8px 10px; border: 1px solid #c8d6e7; border-radius: 6px; background: #fff; color: #102033; font: inherit; font-size: 13px; font-weight: 600; }
        .notifications-filter input:focus, .notifications-filter select:focus { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13, 110, 253, .12); outline: 0; }
        .notifications-filter__actions { display: flex; gap: 8px; }
        .notifications-filter__actions .button, .notifications-filter__actions .link-button { min-height: 39px; border-radius: 6px; white-space: nowrap; font-weight: 800; }
        .notifications-filter__actions .button { background: #0b4fb3; }
        .notification-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .notification-actions form { margin: 0; }
        .notification-actions .coop-button-soft { min-height: 30px; padding: 0 12px; border-radius: 6px; background: #e8f0fe; color: #1a73e8; font-size: 12px; font-weight: 850; }
        .notification-actions .coop-button-soft:hover { background: #dbe8ff; color: #1558b0; }
        .notification-empty { padding: 34px 20px; color: #526987; text-align: center; }
        .notifications-card .coop-table { min-width: 960px; }
        .notifications-card .coop-table th { background: #f8f9fa; color: #0b1e36; border-bottom: 1px solid #d7e2ef; font-size: 11px; font-weight: 900; letter-spacing: .03em; }
        .notifications-card .coop-table td { border-bottom: 1px solid #dce5f0; color: #0b1e36; font-size: 13px; font-weight: 650; }
        .notifications-card .coop-table td:first-child strong { color: #0b1e36; font-weight: 850; }
        .notifications-card .coop-table tr:last-child td { border-bottom: 0; }
        .notifications-card .coop-pill.success { background: #e6f4ea; color: #137333; font-size: 11px; font-weight: 900; }
        .notifications-card .coop-pill.warning { background: #fef3c7; color: #b45309; font-size: 11px; font-weight: 900; }
        .notification-page nav[role="navigation"], .notification-page .pagination { margin-top: 4px; }
        @media (max-width: 900px) { .notifications-filter { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 640px) {
            .notification-summary { grid-template-columns: 1fr; }
            .notification-hero { align-items: stretch; flex-direction: column; padding: 20px; }
            .notifications-panel__head { align-items: flex-start; flex-direction: column; }
            .notifications-filter { grid-template-columns: 1fr; padding: 16px; }
            .notifications-filter__actions { display: grid; grid-template-columns: 1fr 1fr; }
            .notifications-filter__actions .button, .notifications-filter__actions .link-button { display: inline-flex; align-items: center; justify-content: center; }
        }
    </style>
@endpush

@section('content')
    @include('components.coop-page-style')

    <div class="coop-wrap notification-page">
        <section class="notification-hero">
            <div>
                <div class="notification-accent">
                    <span class="notification-accent__bar"></span>
                    <span class="notification-accent__tag">Notifikasi</span>
                </div>
                <h1>Notifikasi</h1>
                <p>Semak makluman permohonan, bayaran dan transaksi koperasi.</p>
            </div>
            @if ($summary['unread'] > 0)
                <form method="POST" action="{{ route('notifications.read') }}">
                    @csrf
                    <button class="button" type="submit">Tandakan Semua Dibaca</button>
                </form>
            @endif
        </section>

        <section class="notification-summary" aria-label="Ringkasan notifikasi">
            <div class="notification-summary__item">
                <span>Jumlah Notifikasi</span>
                <strong>{{ $summary['total'] }}</strong>
            </div>
            <div class="notification-summary__item notification-summary__item--unread">
                <span>Belum Dibaca</span>
                <strong>{{ $summary['unread'] }}</strong>
            </div>
            <div class="notification-summary__item">
                <span>Telah Dibaca</span>
                <strong>{{ $summary['read'] }}</strong>
            </div>
        </section>

        <section class="notifications-card">
            <div class="notifications-panel__head">
                <div>
                    <div class="notifications-panel__title">
                        <span class="notification-accent__bar"></span>
                        <h2>Senarai Notifikasi</h2>
                    </div>
                    <p>{{ $notifications->total() }} rekod ditemui berdasarkan carian dan penapis semasa.</p>
                </div>
            </div>

            <form class="notifications-filter" method="GET" action="{{ route('notifications.index') }}">
                <label for="notification-search">
                    Cari Notifikasi
                    <input id="notification-search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tajuk atau mesej notifikasi">
                </label>
                <label for="notification-status">
                    Status
                    <select id="notification-status" name="status">
                        <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Semua status</option>
                        <option value="unread" @selected(($filters['status'] ?? '') === 'unread')>Belum Dibaca</option>
                        <option value="read" @selected(($filters['status'] ?? '') === 'read')>Dibaca</option>
                    </select>
                </label>
                <label for="notification-period">
                    Tempoh
                    <select id="notification-period" name="period">
                        <option value="all" @selected(($filters['period'] ?? 'all') === 'all')>Semua tarikh</option>
                        <option value="today" @selected(($filters['period'] ?? '') === 'today')>Hari ini</option>
                        <option value="week" @selected(($filters['period'] ?? '') === 'week')>Minggu ini</option>
                        <option value="month" @selected(($filters['period'] ?? '') === 'month')>Bulan ini</option>
                    </select>
                </label>
                <div class="notifications-filter__actions">
                    <button class="button" type="submit">Cari</button>
                    <a class="link-button secondary" href="{{ route('notifications.index') }}">Reset</a>
                </div>
            </form>

            <div class="coop-table-wrap">
                <table class="coop-table">
                    <thead>
                        <tr>
                            <th>Tajuk</th>
                            <th>Mesej</th>
                            <th>Status</th>
                            <th>Tarikh</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notifications as $notification)
                            <tr>
                                <td><strong>{{ $notification->title }}</strong></td>
                                <td>{{ $notification->message ?? '-' }}</td>
                                <td><span class="coop-pill {{ $notification->read_at ? 'success' : 'warning' }}">{{ $notification->read_at ? 'Dibaca' : 'Belum Dibaca' }}</span></td>
                                <td>{{ $notification->created_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="notification-actions">
                                        @if ($notification->link)
                                            <a class="coop-button-soft" href="{{ $notification->link }}">Lihat</a>
                                        @endif
                                        @if (! $notification->read_at)
                                            <form method="POST" action="{{ route('notifications.read') }}">
                                                @csrf
                                                <input type="hidden" name="notification_id" value="{{ $notification->getKey() }}">
                                                <button class="coop-button-soft" type="submit">Tandakan dibaca</button>
                                            </form>
                                        @endif
                                        @if (! $notification->link && $notification->read_at)
                                            <span>-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="notification-empty">Tiada notifikasi yang sepadan dengan carian ini.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $notifications->links() }}
    </div>
@endsection
