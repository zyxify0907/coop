@extends('layouts.app')

@section('title', 'Log Audit')
@section('page-title', 'Log Audit')
@section('page-subtitle', 'Rekod tindakan penting dalam sistem koperasi.')

@section('content')
    @include('components.coop-page-style')

    <div class="coop-wrap">
        <section class="coop-header">
            <div>
                <span class="coop-kicker">KAWALAN SISTEM</span>
                <h1>Log Audit</h1>
                <p>Jejak tindakan cipta, kemaskini, kelulusan, muat naik, muat turun, padam dan proses kewangan koperasi.</p>
            </div>
        </section>

        <section class="panel coop-panel">
            <div class="coop-panel-head">
                <div>
                    <h2>Rekod Aktiviti</h2>
                    <p>Senarai aktiviti terbaru disusun mengikut masa.</p>
                </div>
            </div>
            <div class="coop-table-wrap">
                <table class="coop-table">
                    <thead>
                        <tr>
                            <th>Masa</th>
                            <th>Pelaku</th>
                            <th>Modul</th>
                            <th>Tindakan</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                                <td><span class="coop-pill">{{ strtoupper($log->actor_role) }} #{{ $log->actor_id }}</span></td>
                                <td>{{ ucfirst($log->module) }}</td>
                                <td><strong>{{ ucfirst($log->action) }}</strong></td>
                                <td>{{ $log->description ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">Tiada audit log lagi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $logs->links() }}
    </div>
@endsection
