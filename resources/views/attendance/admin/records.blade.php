@extends('layouts.app')
@section('title', 'Rekod Kehadiran')
@section('page-title', 'Rekod Kehadiran')
@section('page-subtitle', 'Semak semua rekod kehadiran Pekerja Koperasi.')
@section('content')
    @include('attendance.partials.styles')
    @php $duration = fn ($minutes) => intdiv((int) ($minutes ?? 0), 60).' jam '.((int) ($minutes ?? 0) % 60).' minit'; @endphp
    <div class="attendance-page"><header class="attendance-head"><div><span class="attendance-kicker">KEHADIRAN PEKERJA</span><h1>Rekod Kehadiran</h1><p>Gunakan penapis untuk mencari rekod check in dan check out dengan cepat.</p></div><div class="attendance-head__meta"><span>Modul</span><strong>Kehadiran</strong></div></header>
        <section class="attendance-panel"><form class="attendance-toolbar" method="GET"><label>Tarikh<input type="date" name="date" value="{{ $filters['date'] }}"></label><label>Bulan<input type="month" name="month" value="{{ $filters['month'] }}"></label><label>Pekerja<select name="staff_id"><option value="">Semua pekerja</option>@foreach($workers as $worker)<option value="{{ $worker->id_pekerja }}" @selected($filters['staff_id'] == $worker->id_pekerja)>{{ $worker->nama }} ({{ $worker->no_pekerja }})</option>@endforeach</select></label><label>Status<select name="status"><option value="">Semua status</option>@foreach($statuses as $key=>$label)<option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>@endforeach</select></label><button class="attendance-primary" type="submit">Tapis</button></form>
            <div class="attendance-table-wrap"><table class="attendance-table attendance-records-table"><thead><tr><th class="attendance-records-table__staff">Staff</th><th class="attendance-records-table__date">Tarikh</th><th class="attendance-records-table__time">Check In</th><th class="attendance-records-table__time">Check Out</th><th class="attendance-records-table__duration">Lewat</th><th class="attendance-records-table__duration">Keluar Awal</th><th class="attendance-records-table__duration">Jumlah Jam</th><th class="attendance-records-table__status">Status</th><th class="attendance-records-table__gps">GPS</th><th class="attendance-records-table__action"></th></tr></thead><tbody>@forelse($records as $record)<tr><td class="attendance-person attendance-records-table__staff-cell"><strong>{{ $record->staff?->nama }}</strong><span>{{ $record->staff?->no_pekerja }}</span></td><td class="attendance-records-table__date-cell">{{ $record->attendance_date->format('d/m/Y') }}</td><td class="attendance-records-table__time-cell">{{ $record->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</td><td class="attendance-records-table__time-cell">{{ $record->check_out_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</td><td class="attendance-records-table__duration-cell">{{ $record->check_in_time ? $duration($record->late_minutes) : '-' }}</td><td class="attendance-records-table__duration-cell">{{ $record->check_out_time ? $duration($record->early_leave_minutes) : '-' }}</td><td class="attendance-records-table__duration-cell">{{ $record->check_in_time ? $duration($record->working_minutes) : '-' }}</td><td><span class="attendance-badge attendance-badge--{{ $record->status === 'outside_area' ? 'missing_checkout' : $record->status }}">{{ $statuses[$record->status] ?? $record->status }}</span></td><td class="attendance-records-table__gps-cell">{{ $record->check_in_gps_verified ? 'Disahkan' : ($record->status === 'outside_area' ? 'Luar Kawasan' : 'Tidak') }}</td><td class="attendance-records-table__action-cell"><div class="attendance-actions"><a class="attendance-secondary" href="{{ route('admin.attendance.show', $record) }}" data-view-dialog-open data-dialog-title="Butiran Kehadiran" data-dialog-url="{{ route('admin.attendance.show', ['record' => $record, 'dialog' => 1]) }}">Lihat</a><form method="POST" action="{{ route('admin.attendance.destroy', $record) }}" onsubmit="return confirm('Padam rekod kehadiran ini?');">@csrf @method('DELETE')<button class="attendance-danger" type="submit">Delete</button></form></div></td></tr>@empty<tr><td colspan="10" class="attendance-empty">Tiada rekod ditemui.</td></tr>@endforelse</tbody></table></div>@if($records->hasPages())<div class="pagination">{{ $records->links() }}</div>@endif</section>
        <dialog class="view-dialog view-dialog--attendance" data-view-dialog aria-labelledby="attendanceViewDialogTitle"><div class="view-dialog__shell"><header class="view-dialog__head"><div><span>Paparan Rekod</span><h2 id="attendanceViewDialogTitle">Butiran Kehadiran</h2></div><button class="view-dialog__close" type="button" data-view-dialog-close aria-label="Tutup dialog">&times;</button></header><div class="view-dialog__body" data-view-dialog-body><iframe class="view-dialog__frame" data-view-dialog-frame title="Paparan butiran kehadiran" scrolling="no" tabindex="0"></iframe></div></div></dialog>
    </div>
@endsection

@push('styles')
<style>
    .attendance-records-table {
        min-width: 1180px;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }

    .attendance-records-table tbody tr {
        box-shadow: inset 0 -1px 0 var(--line);
    }

    .attendance-records-table tbody tr:last-child {
        box-shadow: none;
    }

    .attendance-records-table td {
        border-bottom: 0;
        vertical-align: middle;
        padding-top: 16px;
        padding-bottom: 16px;
    }

    .attendance-records-table__staff {
        width: 230px;
    }

    .attendance-records-table__staff-cell {
        min-width: 230px;
    }

    .attendance-records-table__date {
        width: 118px;
    }

    .attendance-records-table__date,
    .attendance-records-table__date-cell,
    .attendance-records-table__time,
    .attendance-records-table__time-cell,
    .attendance-records-table__duration-cell,
    .attendance-records-table__gps,
    .attendance-records-table__gps-cell {
        white-space: nowrap;
    }

    .attendance-records-table__date,
    .attendance-records-table__date-cell,
    .attendance-records-table__time,
    .attendance-records-table__time-cell,
    .attendance-records-table__duration,
    .attendance-records-table__duration-cell,
    .attendance-records-table__status,
    .attendance-records-table td:nth-child(8),
    .attendance-records-table__gps,
    .attendance-records-table__gps-cell {
        text-align: center;
    }

    .attendance-records-table__time {
        width: 112px;
    }

    .attendance-records-table__duration {
        width: 142px;
    }

    .attendance-records-table__status {
        width: 142px;
    }

    .attendance-records-table__gps {
        width: 104px;
    }

    .attendance-records-table__action,
    .attendance-records-table__action-cell {
        width: 160px;
    }

    .attendance-records-table .attendance-person {
        gap: 6px;
        padding-top: 2px;
    }

    .attendance-records-table .attendance-person strong {
        line-height: 1.35;
        word-break: normal;
        overflow-wrap: normal;
        text-wrap: balance;
    }

    .attendance-records-table .attendance-person span {
        margin-top: 0;
    }

    .attendance-records-table__action-cell {
        padding-right: 14px;
    }

    .attendance-records-table .attendance-actions {
        flex-wrap: nowrap;
        justify-content: flex-end;
        gap: 6px;
    }

    .attendance-records-table .attendance-actions form {
        display: inline-flex;
        width: auto;
    }

    .attendance-records-table .attendance-actions .attendance-secondary,
    .attendance-records-table .attendance-actions .attendance-danger {
        min-width: 68px;
        min-height: 34px;
        padding-inline: 10px;
        font-size: 12px;
    }

    .attendance-records-table .attendance-badge {
        margin-inline: auto;
        min-width: 104px;
    }

    .view-dialog {
        width: min(1120px, calc(100vw - 32px));
        max-width: 1120px;
        height: min(88vh, 860px);
        padding: 0;
        border: 0;
        border-radius: 18px;
        background: transparent;
        overflow: hidden;
        box-shadow: 0 28px 80px rgba(15, 23, 42, .28);
    }

    .view-dialog::backdrop {
        background: rgba(15, 23, 42, .45);
        backdrop-filter: blur(2px);
    }

    .view-dialog__shell {
        display: grid;
        grid-template-rows: auto minmax(0, 1fr);
        width: 100%;
        height: 100%;
        min-height: 0;
        background: #fff;
    }

    .view-dialog__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 22px;
        border-bottom: 1px solid var(--line);
        background: #fff;
    }

    .view-dialog__head span {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        padding: 0 8px;
        border-radius: 999px;
        background: var(--secondary-soft);
        color: var(--secondary);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .view-dialog__head h2 {
        margin: 8px 0 0;
        font-size: 22px;
        line-height: 1.2;
        color: var(--text);
    }

    .view-dialog__close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: #fff;
        color: var(--text);
        font-size: 24px;
        font-weight: 700;
    }

    .view-dialog__frame {
        width: 100%;
        height: 100%;
        min-height: 0;
        border: 0;
        background: #f8fafc;
        overflow: auto;
    }

    @media (max-width: 760px) {
        .view-dialog {
            width: calc(100vw - 16px);
            height: calc(100vh - 16px);
            border-radius: 14px;
        }

        .view-dialog__head {
            padding: 14px 16px;
        }

        .view-dialog__head h2 {
            font-size: 18px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const dialog = document.querySelector('[data-view-dialog]');
        const frame = dialog?.querySelector('[data-view-dialog-frame]');
        const body = dialog?.querySelector('[data-view-dialog-body]');
        const title = dialog?.querySelector('h2');

        if (!dialog || !frame || !body || !title) {
            return;
        }

        const closeDialog = () => {
            frame.removeAttribute('src');
            dialog.close();
        };

        const prepareFrameScroll = () => {
            try {
                const doc = frame.contentDocument;
                doc.documentElement.style.setProperty('height', 'auto', 'important');
                doc.documentElement.style.setProperty('overflow-y', 'auto', 'important');
                doc.body.style.setProperty('height', 'auto', 'important');
                doc.body.style.setProperty('overflow-y', 'auto', 'important');

                const frameHeight = Math.max(
                    doc.documentElement.scrollHeight,
                    doc.body.scrollHeight,
                    doc.querySelector('.content')?.scrollHeight || 0,
                    doc.querySelector('#main-content')?.scrollHeight || 0,
                    body.clientHeight
                );
                frame.style.setProperty('height', `${frameHeight}px`, 'important');

                doc.addEventListener('wheel', (event) => {
                    body.scrollBy({ top: event.deltaY, left: event.deltaX });
                    event.preventDefault();
                }, { passive: false });

                doc.addEventListener('keydown', (event) => {
                    const offsets = { ArrowDown: 48, ArrowUp: -48, PageDown: body.clientHeight * .8, PageUp: -body.clientHeight * .8, Home: -body.scrollTop, End: body.scrollHeight };
                    if (!(event.key in offsets)) return;
                    body.scrollBy({ top: offsets[event.key] });
                    event.preventDefault();
                });
            } catch (error) {
                // Same-origin pages can be adjusted; external pages keep browser defaults.
            }

            body.scrollTop = 0;
        };

        frame.addEventListener('load', prepareFrameScroll);

        document.querySelectorAll('[data-view-dialog-open]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                title.textContent = button.dataset.dialogTitle || 'Butiran';
                frame.src = button.dataset.dialogUrl || button.getAttribute('href');
                dialog.showModal();
                window.setTimeout(() => frame.focus(), 100);
            });
        });

        dialog.querySelector('[data-view-dialog-close]')?.addEventListener('click', closeDialog);
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                closeDialog();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && dialog.open) {
                closeDialog();
            }
        });
    })();
</script>
@endpush
