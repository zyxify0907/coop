@extends('layouts.app')
@section('title', 'Sejarah Kehadiran')
@section('page-title', 'Sejarah Kehadiran')
@section('page-subtitle', 'Rekod kehadiran peribadi anda sahaja.')
@section('content')
    @include('attendance.partials.styles')
    @php
        $labels = $statuses + ['missing_checkout' => 'Tiada Check Out'];
        $duration = fn ($minutes) => intdiv((int) ($minutes ?? 0), 60).' jam '.((int) ($minutes ?? 0) % 60).' minit';
    @endphp
    <div class="attendance-page">
        <header class="attendance-head"><div><h1>Sejarah Kehadiran</h1><p>Semak rekod Check In, Check Out dan jam bekerja anda.</p></div></header>
        <section class="attendance-panel">
            <form class="attendance-toolbar" method="GET"><label>Bulan<input type="number" name="month" min="1" max="12" value="{{ $filters['month'] }}"></label><label>Tahun<input type="number" name="year" min="2020" max="2100" value="{{ $filters['year'] }}"></label><label>Status<select name="status"><option value="">Semua status</option>@foreach($statuses as $key => $label)<option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>@endforeach</select></label><button class="attendance-primary" type="submit">Tapis</button></form>
            <div class="attendance-table-wrap"><table class="attendance-table"><thead><tr><th>Tarikh</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Lewat</th><th>Keluar Awal</th><th>Waktu Bekerja</th><th>Isu Kehadiran</th></tr></thead><tbody>
                @forelse($records as $record)@php($issueType = match ($record->status) {'missing_checkout' => 'missing_check_out', 'late' => 'wrong_check_in_time', 'early_leave' => 'wrong_check_out_time', 'outside_area' => 'other', default => 'other'})<tr><td><strong>{{ $record->attendance_date->format('d/m/Y') }}</strong></td><td>{{ $record->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</td><td>{{ $record->check_out_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</td><td><span class="attendance-badge attendance-badge--{{ $record->status === 'outside_area' ? 'missing_checkout' : $record->status }}">{{ $labels[$record->status] ?? $record->status }}</span></td><td>{{ $record->check_in_time ? $duration($record->late_minutes) : '-' }}</td><td>{{ $record->check_out_time ? $duration($record->early_leave_minutes) : '-' }}</td><td>{{ $record->check_in_time ? $duration($record->working_minutes) : '-' }}</td><td><button class="attendance-secondary attendance-issue-button" type="button" data-attendance-modal-open data-record-id="{{ $record->id }}" data-record-date="{{ $record->attendance_date->toDateString() }}" data-display-date="{{ $record->attendance_date->format('d/m/Y') }}" data-check-in="{{ $record->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}" data-check-out="{{ $record->check_out_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}" data-working-time="{{ $record->check_in_time ? $duration($record->working_minutes) : '-' }}" data-status-label="{{ $labels[$record->status] ?? $record->status }}" data-issue-type="{{ $issueType }}">Mohon Kelulusan</button></td></tr>
                @empty<tr><td colspan="8" class="attendance-empty">Tiada rekod kehadiran untuk pilihan ini.</td></tr>@endforelse
            </tbody></table></div>
            @if($records->hasPages())<div class="pagination">{{ $records->links() }}</div>@endif
        </section>

        <dialog class="attendance-modal" id="attendanceIssueModal">
            <form class="attendance-form attendance-modal__form" method="POST" action="{{ route('coop-staff.attendance.corrections.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="attendance-modal__head">
                    <div>
                        <h2>Permohonan Kelulusan Isu Kehadiran</h2>
                        <p>Hantar isu rekod kehadiran ini untuk semakan dan kelulusan admin.</p>
                    </div>
                    <button class="attendance-modal__close" type="button" data-attendance-modal-close aria-label="Tutup modal">×</button>
                </div>

                <input type="hidden" name="attendance_id" id="modalAttendanceId">
                <input type="hidden" name="correction_type" id="modalCorrectionType">

                <div class="attendance-issue-summary">
                    <div><span>Nama</span><strong>{{ $user->nama }}</strong></div>
                    <div><span>Tarikh</span><strong id="modalDisplayDate">-</strong></div>
                    <div><span>Masuk</span><strong id="modalCheckIn">-</strong></div>
                    <div><span>Keluar</span><strong id="modalCheckOut">-</strong></div>
                    <div><span>Jam Kerja</span><strong id="modalWorkingTime">-</strong></div>
                    <div><span>Isu</span><strong id="modalIssueLabel">-</strong></div>
                </div>

                <div class="attendance-form__grid">
                    <label class="attendance-modal__field">Tarikh Isu
                        <input id="modalCorrectionDate" type="date" name="correction_date" max="{{ now()->timezone('Asia/Kuala_Lumpur')->toDateString() }}" required>
                    </label>
                    <label class="attendance-modal__field">Masa Dimohon
                        <input type="time" name="requested_time">
                    </label>
                    <label class="attendance-modal__field">Pilih Sebab Tidak Hadir
                        <select name="absence_reason">
                            <option value="">Pilih sebab (jika berkaitan)</option>
                            @foreach($absenceReasons as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="attendance-modal__field attendance-file-field">Dokumen Sokongan
                        <input id="attendanceSupportFile" type="file" name="supporting_document" accept=".pdf,.jpg,.jpeg,.png">
                        <span class="attendance-file-box">
                            <span class="attendance-file-icon">+</span>
                            <span><strong id="attendanceSupportFileName">Pilih dokumen</strong><small>PDF, JPG atau PNG</small></span>
                        </span>
                    </label>
                </div>

                <label class="attendance-modal__field attendance-modal__field--full">Sebab / Catatan
                    <textarea name="reason" required placeholder="Nyatakan isu kehadiran dan maklumat tambahan jika perlu."></textarea>
                </label>

                <div class="attendance-form__footer">
                    <button class="attendance-secondary" type="button" data-attendance-modal-close>Batal</button>
                    <button class="attendance-primary" type="submit">Hantar Permohonan</button>
                </div>
            </form>
        </dialog>
    </div>
@endsection

@push('styles')
    <style>
        .attendance-issue-button{min-height:34px;padding-inline:12px;white-space:nowrap}
        .attendance-modal{width:min(760px,calc(100vw - 32px));max-height:calc(100dvh - 32px);padding:0;border:0;border-radius:12px;background:#fff;box-shadow:0 28px 80px rgba(15,23,42,.28);overflow:hidden}
        .attendance-modal::backdrop{background:rgba(15,23,42,.48)}
        .attendance-modal__form{display:flex;flex-direction:column;max-height:calc(100dvh - 32px);padding:0;overflow-y:scroll;overflow-x:hidden;overscroll-behavior:contain;scrollbar-gutter:stable}
        .attendance-modal__head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:24px 26px 18px;border-bottom:1px solid var(--line);background:linear-gradient(135deg,#ffffff 0%,#f8fbff 100%)}
        .attendance-modal__head h2{margin:0;color:var(--text);font-size:22px;line-height:1.2;font-weight:900;letter-spacing:0}
        .attendance-modal__head p{margin:7px 0 0;color:var(--muted);font-size:13px;line-height:1.45}
        .attendance-modal__close{display:grid;width:38px;height:38px;place-items:center;border:1px solid var(--line);border-radius:10px;background:#fff;color:var(--text);font-size:22px;font-weight:900;line-height:1;box-shadow:0 8px 18px rgba(15,23,42,.06)}
        .attendance-modal__close:hover{background:var(--danger-soft);border-color:var(--danger-soft);color:var(--danger)}
        .attendance-modal .attendance-form__grid{padding:24px 26px 0;gap:18px;grid-template-columns:repeat(2,minmax(0,1fr))}
        .attendance-issue-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;padding:18px 26px 0}
        .attendance-issue-summary div{display:grid;gap:4px;min-height:70px;align-content:center;padding:12px 14px;border:1px solid var(--line);border-radius:10px;background:#f8fafc}
        .attendance-issue-summary span{color:var(--muted);font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.04em}
        .attendance-issue-summary strong{color:var(--text);font-size:14px;font-weight:900;overflow-wrap:anywhere}
        .attendance-modal__field{gap:8px;color:var(--text);font-size:13px;font-weight:900}
        .attendance-modal__field input,
        .attendance-modal__field select,
        .attendance-modal__field textarea{border-radius:10px;border-color:#cbd5e1;background:#fff;font-weight:700}
        .attendance-modal__field input:focus,
        .attendance-modal__field select:focus,
        .attendance-modal__field textarea:focus{border-color:var(--secondary);box-shadow:0 0 0 4px rgba(36,83,166,.12)}
        .attendance-modal__field--full{padding:18px 26px 0}
        .attendance-modal__field textarea{min-height:112px}
        .attendance-file-field input{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)}
        .attendance-file-box{display:flex;align-items:center;gap:12px;min-height:50px;padding:10px 12px;border:1px dashed #cbd5e1;border-radius:10px;background:#f8fafc;color:var(--text)}
        .attendance-file-icon{display:grid;width:30px;height:30px;place-items:center;border-radius:8px;background:var(--secondary-soft);color:var(--secondary);font-size:20px;font-weight:900}
        .attendance-file-box strong{display:block;font-size:13px;line-height:1.2}
        .attendance-file-box small{display:block;margin-top:2px;color:var(--muted);font-size:12px;font-weight:700}
        .attendance-modal .attendance-form__footer{position:sticky;bottom:0;justify-content:flex-end;border-top:1px solid var(--line);padding:18px 26px 22px;margin-top:18px;background:#fff}
        .attendance-modal .attendance-form__footer button{min-width:118px;border-radius:9px}
        @media(max-width:680px){.attendance-issue-summary,.attendance-modal .attendance-form__grid{grid-template-columns:1fr}.attendance-modal .attendance-form__footer{align-items:stretch}.attendance-modal .attendance-form__footer button{width:100%}}
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('attendanceIssueModal');
            const recordInput = document.getElementById('modalAttendanceId');
            const dateInput = document.getElementById('modalCorrectionDate');
            const typeInput = document.getElementById('modalCorrectionType');
            const displayDate = document.getElementById('modalDisplayDate');
            const checkIn = document.getElementById('modalCheckIn');
            const checkOut = document.getElementById('modalCheckOut');
            const workingTime = document.getElementById('modalWorkingTime');
            const issueLabel = document.getElementById('modalIssueLabel');
            const fileInput = document.getElementById('attendanceSupportFile');
            const fileName = document.getElementById('attendanceSupportFileName');

            document.querySelectorAll('[data-attendance-modal-open]').forEach((button) => {
                button.addEventListener('click', () => {
                    recordInput.value = button.dataset.recordId || '';
                    dateInput.value = button.dataset.recordDate || '';
                    typeInput.value = button.dataset.issueType || '';
                    displayDate.textContent = button.dataset.displayDate || '-';
                    checkIn.textContent = button.dataset.checkIn || '-';
                    checkOut.textContent = button.dataset.checkOut || '-';
                    workingTime.textContent = button.dataset.workingTime || '-';
                    issueLabel.textContent = button.dataset.statusLabel || '-';

                    if (typeof modal.showModal === 'function') {
                        modal.showModal();
                    } else {
                        modal.setAttribute('open', 'open');
                    }
                });
            });

            document.querySelectorAll('[data-attendance-modal-close]').forEach((button) => {
                button.addEventListener('click', () => modal.close());
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.close();
                }
            });

            fileInput?.addEventListener('change', () => {
                fileName.textContent = fileInput.files?.[0]?.name || 'Pilih dokumen';
            });
        });
    </script>
@endpush
