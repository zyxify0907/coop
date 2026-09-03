@extends('layouts.app')
@section('title', 'Pembetulan Kehadiran')
@section('page-title', 'Pembetulan Kehadiran')
@section('page-subtitle', 'Hantar permohonan jika rekod Check In atau Check Out tidak tepat.')
@section('content')
    @include('attendance.partials.styles')
    <div class="attendance-page">
        <header class="attendance-head"><div><h1>Pembetulan Kehadiran</h1><p>Permohonan akan disemak dan diputuskan oleh admin.</p></div></header>
        <div class="attendance-section-grid">
            <section class="attendance-panel"><header class="attendance-panel__head"><div><h2>Permohonan Baharu</h2><p>Lampirkan bukti jika berkaitan.</p></div></header><form class="attendance-form" method="POST" action="{{ route('coop-staff.attendance.corrections.store') }}" enctype="multipart/form-data">@csrf
                <div class="attendance-form__grid"><label>Rekod Kehadiran (jika ada)<select name="attendance_id"><option value="">Tiada rekod / pilih kemudian</option>@foreach($records as $record)<option value="{{ $record->id }}">{{ $record->attendance_date->format('d/m/Y') }} - {{ $record->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? 'Tiada Check In' }}</option>@endforeach</select></label><label>Tarikh Pembetulan<input type="date" name="correction_date" max="{{ now()->timezone('Asia/Kuala_Lumpur')->toDateString() }}" value="{{ old('correction_date') }}" required></label><label>Jenis Pembetulan<select name="correction_type" required><option value="">Pilih jenis</option>@foreach($correctionTypes as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label><label>Masa Dimohon<input type="time" name="requested_time" value="{{ old('requested_time') }}"></label></div>
                <label>Pilih Sebab Tidak Hadir<select name="absence_reason"><option value="">Pilih sebab (jika berkaitan)</option>@foreach($absenceReasons as $key => $label)<option value="{{ $key }}" @selected(old('absence_reason') === $key)>{{ $label }}</option>@endforeach</select></label><label>Sebab / Catatan<textarea name="reason" required placeholder="Nyatakan isu kehadiran dan maklumat tambahan jika perlu.">{{ old('reason') }}</textarea></label><label>Dokumen Sokongan (PDF, JPG, PNG)<input type="file" name="supporting_document" accept=".pdf,.jpg,.jpeg,.png"></label><div class="attendance-form__footer"><button class="attendance-primary" type="submit">Hantar Permohonan</button></div>
            </form></section>
            <section class="attendance-panel"><header class="attendance-panel__head"><div><h2>Status Permohonan</h2><p>Rekod pembetulan yang telah dihantar.</p></div></header><div class="attendance-list">@forelse($corrections as $correction)<div class="attendance-list__item"><div><strong>{{ $correctionTypes[$correction->correction_type] ?? $correction->correction_type }}</strong><span>{{ $correction->correction_date->format('d/m/Y') }} &middot; {{ $correction->reason }}</span></div><span class="attendance-badge attendance-badge--{{ $correction->status }}">{{ ucfirst($correction->status) }}</span></div>@empty<div class="attendance-empty">Belum ada permohonan pembetulan.</div>@endforelse</div>@if($corrections->hasPages())<div class="pagination">{{ $corrections->links() }}</div>@endif</section>
        </div>
    </div>
@endsection
