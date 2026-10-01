@extends('layouts.app')
@section('title', 'Pembetulan Kehadiran')
@section('page-title', 'Pembetulan Kehadiran')
@section('page-subtitle', 'Semak dan putuskan permohonan pembetulan daripada pekerja.')
@section('content')
    @include('attendance.partials.styles')
    <div class="attendance-page"><header class="attendance-head"><div><h1>Pembetulan Kehadiran</h1><p>Semak permohonan pembetulan dan putuskan sama ada rekod perlu dikemas kini.</p></div><div class="attendance-head__meta"><span>Semakan</span><strong>Admin Kehadiran</strong></div></header>
        <section class="attendance-panel"><form class="attendance-toolbar" method="GET"><label>Status<select name="status"><option value="pending" @selected($status === 'pending')>Pending</option><option value="approved" @selected($status === 'approved')>Diluluskan</option><option value="rejected" @selected($status === 'rejected')>Ditolak</option></select></label><button class="attendance-primary" type="submit">Tapis</button></form><div class="attendance-table-wrap"><table class="attendance-table attendance-corrections-table"><thead><tr><th class="attendance-corrections-table__staff">Pekerja</th><th class="attendance-corrections-table__date">Tarikh</th><th class="attendance-corrections-table__type">Jenis</th><th class="attendance-corrections-table__time">Masa Dimohon</th><th class="attendance-corrections-table__reason">Sebab</th><th class="attendance-corrections-table__document">Dokumen</th><th class="attendance-corrections-table__status">Status</th><th class="attendance-corrections-table__action">Tindakan</th></tr></thead><tbody>@forelse($corrections as $correction)@php($correctionStaff = $correction->staff ?? $correction->attendance?->staff)<tr><td class="attendance-person attendance-corrections-table__staff-cell"><strong>{{ $correctionStaff?->nama ?? 'Rekod staf tidak ditemui' }}</strong><span>{{ $correctionStaff?->no_pekerja ?? 'ID staf lama: '.$correction->staff_id }}</span></td><td class="attendance-corrections-table__date-cell">{{ $correction->correction_date->format('d/m/Y') }}</td><td class="attendance-corrections-table__type-cell">{{ $correctionTypes[$correction->correction_type] ?? $correction->correction_type }}</td><td class="attendance-corrections-table__time-cell">{{ $correction->requested_time?->format('h:i A') ?? '-' }}</td><td class="attendance-corrections-table__reason-cell">{{ $correction->reason }}</td><td class="attendance-corrections-table__document-cell">@if($correction->supporting_document)<a class="attendance-secondary" href="{{ route('admin.attendance.corrections.document', $correction) }}">Buka</a>@else - @endif</td><td class="attendance-corrections-table__status-cell"><span class="attendance-badge attendance-badge--{{ $correction->status }}">{{ ucfirst($correction->status) }}</span></td><td class="attendance-corrections-table__action-cell">@if($correction->status === 'pending')<form method="POST" action="{{ route('admin.attendance.corrections.review', $correction) }}" class="attendance-inline attendance-correction-actions">@csrf @method('PUT')<input type="text" name="admin_remark" placeholder="Catatan admin"><div class="attendance-correction-actions__buttons"><button class="attendance-primary" name="decision" value="approved" type="submit">Lulus</button><button class="attendance-danger" name="decision" value="rejected" type="submit">Tolak</button></div></form>@else<span>{{ $correction->admin_remark ?? '-' }}</span>@endif</td></tr>@empty<tr><td colspan="8" class="attendance-empty">Tiada permohonan pembetulan untuk status ini.</td></tr>@endforelse</tbody></table></div>@if($corrections->hasPages())<div class="pagination">{{ $corrections->links() }}</div>@endif</section>
    </div>
@endsection

@push('styles')
<style>
    .attendance-corrections-table {
        min-width: 1180px;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0;
    }

    .attendance-corrections-table tbody tr {
        box-shadow: inset 0 -1px 0 var(--line);
    }

    .attendance-corrections-table tbody tr:last-child {
        box-shadow: none;
    }

    .attendance-corrections-table td {
        border-bottom: 0;
        vertical-align: middle;
        padding-top: 16px;
        padding-bottom: 16px;
    }

    .attendance-corrections-table__staff,
    .attendance-corrections-table__staff-cell { width: 190px; }
    .attendance-corrections-table__date { width: 110px; }
    .attendance-corrections-table__type { width: 150px; }
    .attendance-corrections-table__time { width: 116px; }
    .attendance-corrections-table__reason { width: 250px; }
    .attendance-corrections-table__document { width: 90px; }
    .attendance-corrections-table__status { width: 104px; }
    .attendance-corrections-table__action,
    .attendance-corrections-table__action-cell { width: 220px; }

    .attendance-corrections-table__date,
    .attendance-corrections-table__date-cell,
    .attendance-corrections-table__type,
    .attendance-corrections-table__type-cell,
    .attendance-corrections-table__time,
    .attendance-corrections-table__time-cell,
    .attendance-corrections-table__document,
    .attendance-corrections-table__document-cell,
    .attendance-corrections-table__status,
    .attendance-corrections-table__status-cell {
        text-align: center;
        white-space: nowrap;
    }

    .attendance-corrections-table__reason-cell {
        line-height: 1.45;
    }

    .attendance-corrections-table .attendance-person {
        gap: 6px;
    }

    .attendance-corrections-table .attendance-badge {
        margin-inline: auto;
        min-width: 84px;
    }

    .attendance-corrections-table__action-cell {
        padding-right: 14px;
    }

    .attendance-correction-actions {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
        align-items: stretch;
        width: 100%;
    }

    .attendance-correction-actions input {
        min-height: 38px;
        border-radius: 8px;
    }

    .attendance-correction-actions__buttons {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .attendance-correction-actions__buttons button {
        min-width: 72px;
        min-height: 36px;
        border-radius: 8px;
    }
</style>
@endpush
