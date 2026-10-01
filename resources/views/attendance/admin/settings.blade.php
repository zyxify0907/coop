@extends('layouts.app')
@section('title', 'Tetapan Kehadiran')
@section('page-title', 'Tetapan Kehadiran')
@section('page-subtitle', 'Tetapkan waktu kerja, hari bekerja dan lokasi GPS yang dibenarkan.')
@section('content')
    @include('attendance.partials.styles')
    @push('styles')
        <style>
            .attendance-form input[type="checkbox"] {
                width: 18px;
                min-width: 18px;
                height: 18px;
                min-height: 18px;
                margin: 0;
                padding: 0;
                border: 0;
                box-shadow: none;
                accent-color: var(--secondary);
            }
            .attendance-days { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
            .attendance-day {
                display: inline-flex !important;
                align-items: center;
                gap: 7px;
                min-height: 38px;
                padding: 0 10px;
                border: 1px solid var(--line);
                border-radius: 7px;
                background: #fff;
                color: var(--text) !important;
                font-size: 13px !important;
                font-weight: 600 !important;
            }
            .attendance-toggle {
                display: flex !important;
                align-items: center;
                gap: 12px;
                width: fit-content;
                max-width: 100%;
                margin-top: 4px;
                padding: 12px 14px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: var(--surface-soft);
                color: var(--text) !important;
                cursor: pointer;
            }
            .attendance-toggle > input {
                position: absolute;
                width: 1px !important;
                min-width: 1px !important;
                height: 1px !important;
                min-height: 1px !important;
                opacity: 0;
                pointer-events: none;
            }
            .attendance-toggle__track {
                position: relative;
                display: inline-flex;
                width: 42px;
                min-width: 42px;
                height: 24px;
                padding: 3px;
                border-radius: 999px;
                background: #CBD5E1;
                transition: background .18s ease;
            }
            .attendance-toggle__track::after {
                width: 18px;
                height: 18px;
                border-radius: 50%;
                background: #fff;
                box-shadow: 0 1px 2px rgba(15, 23, 42, .2);
                content: '';
                transition: transform .18s ease;
            }
            .attendance-toggle > input:checked + .attendance-toggle__track { background: var(--secondary); }
            .attendance-toggle > input:checked + .attendance-toggle__track::after { transform: translateX(18px); }
            .attendance-toggle > input:focus-visible + .attendance-toggle__track { box-shadow: 0 0 0 3px rgba(36, 83, 166, .18); }
            .attendance-toggle__text { display: grid; gap: 2px; }
            .attendance-toggle__text strong { font-size: 14px; }
            .attendance-toggle__text small { color: var(--muted); font-size: 12px; }
            @media (max-width: 640px) {
                .attendance-toggle { width: 100%; }
                .attendance-days { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .attendance-day { min-width: 0; }
            }
        </style>
    @endpush
    @php $days = [1 => 'Isnin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Khamis', 5 => 'Jumaat', 6 => 'Sabtu', 7 => 'Ahad']; $workingDays = old('working_days', $setting->working_days ?: [1,2,3,4,5]); @endphp
    <div class="attendance-page"><header class="attendance-head"><div><h1>Tetapan Kehadiran</h1><p>Tetapkan lokasi, radius, waktu kerja, rehat dan polisi elaun kehadiran.</p></div></header>
        <section class="attendance-panel"><form class="attendance-form" method="POST" action="{{ route('admin.attendance.settings.update') }}">@csrf @method('PUT')<div class="attendance-form__grid"><label>Waktu Mula Kerja<input type="time" name="work_start_time" value="{{ old('work_start_time', \Carbon\Carbon::parse($setting->work_start_time)->format('H:i')) }}" required></label><label>Waktu Tamat Kerja<input type="time" name="work_end_time" value="{{ old('work_end_time', \Carbon\Carbon::parse($setting->work_end_time)->format('H:i')) }}" required></label><label>Waktu Mula Rehat<input type="time" name="break_start_time" value="{{ old('break_start_time', \Carbon\Carbon::parse($setting->break_start_time ?? '13:00:00')->format('H:i')) }}" required></label><label>Waktu Tamat Rehat<input type="time" name="break_end_time" value="{{ old('break_end_time', \Carbon\Carbon::parse($setting->break_end_time ?? '14:00:00')->format('H:i')) }}" required></label><label>Grace Period (minit)<input type="number" name="grace_period_minutes" min="0" max="180" value="{{ old('grace_period_minutes', $setting->grace_period_minutes) }}" required></label><label>Full Day Minutes<input type="number" name="full_day_minutes" min="60" max="720" value="{{ old('full_day_minutes', $setting->full_day_minutes ?? 480) }}" required></label><label>Half Day Multiplier<input type="number" name="half_day_rate_multiplier" min="0" max="1" step="0.01" value="{{ old('half_day_rate_multiplier', $setting->half_day_rate_multiplier ?? '0.50') }}" required></label><label>Check Out Cutoff<input type="time" name="checkout_cutoff_time" value="{{ old('checkout_cutoff_time', \Carbon\Carbon::parse($setting->checkout_cutoff_time)->format('H:i')) }}" required></label><label>Nama Lokasi<input name="location_name" value="{{ old('location_name', $setting->location_name) }}" required></label><label>Radius Dibenarkan (meter)<input type="number" name="allowed_radius_meter" min="20" max="5000" value="{{ old('allowed_radius_meter', $setting->allowed_radius_meter) }}" required></label><label>Latitude<input name="latitude" type="number" step="0.00000001" value="{{ old('latitude', $setting->latitude) }}" placeholder="Contoh: 5.74800000"></label><label>Longitude<input name="longitude" type="number" step="0.00000001" value="{{ old('longitude', $setting->longitude) }}" placeholder="Contoh: 102.49800000"></label></div><div><span class="attendance-label">Hari Bekerja</span><div class="attendance-days">@foreach($days as $number=>$label)<label class="attendance-day"><input type="checkbox" name="working_days[]" value="{{ $number }}" @checked(in_array($number, array_map('intval', $workingDays), true))>{{ $label }}</label>@endforeach</div></div><label class="attendance-toggle"><input type="checkbox" name="status" value="1" @checked(old('status', $setting->status))><span class="attendance-toggle__track" aria-hidden="true"></span><span class="attendance-toggle__text"><strong>Aktifkan GPS Kehadiran</strong><small>Semak lokasi ketika check-in dan check-out.</small></span></label><div class="attendance-form__footer"><button class="attendance-primary" type="submit">Simpan Tetapan</button></div></form></section>
    </div>
@endsection
