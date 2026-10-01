@extends('layouts.app')

@section('title', 'Tambah Pengguna')
@section('page-title', 'Tambah '.($type === 'student' ? 'Pelajar' : 'Staf'))
@section('page-subtitle', $type === 'student' ? 'Daftar akaun pelajar untuk log masuk menggunakan No. Matrik. Bukan anggota koperasi sehingga permohonan diluluskan.' : 'Daftar staf baharu untuk log masuk menggunakan No. KP.')

@php
    $isStudent = $type === 'student';
    $restrictToCoopWorkers = $restrictToCoopWorkers ?? false;
    $lockToCoopWorker = ! $isStudent && ($restrictToCoopWorkers || request('staff_type') === 'coop_staff');
    $isCoopWorkerCreate = ! $isStudent && ($lockToCoopWorker || old('staff_type', request('staff_type')) === 'coop_staff');
    $backRoute = $isStudent ? route('admin.users.students') : ($isCoopWorkerCreate ? route('admin.users.coop-workers') : route('admin.users.staff'));
    $storeRoute = $isStudent ? route('admin.users.students.store') : route('admin.users.staff.store');
    $programOptions = ['JTMK', 'JRKV'];
    $semesterOptions = ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'];
    $selectedStaffType = $lockToCoopWorker ? 'coop_staff' : old('staff_type', request('staff_type'));
    $classOptionsBySemester = collect(range(1, 6))->mapWithKeys(fn ($semester) => [
        'Sem '.$semester => ['DIT'.$semester.'A', 'DIT'.$semester.'B', 'DDC'.$semester.'A', 'DBF'.$semester.'A'],
    ])->all();
@endphp

@section('content')
    <section class="create-user-hero">
        <div>
            <span class="hero-kicker">{{ $isStudent ? 'AKAUN PELAJAR' : 'STAF KOPERASI' }}</span>
            <h1>Tambah <span class="brand-red">{{ $isStudent ? 'Pelajar' : 'Staf' }}</span></h1>
            <p>{{ $isStudent ? 'Daftar akaun pelajar untuk log masuk. Status anggota koperasi hanya aktif selepas permohonan anggota diluluskan pentadbir.' : 'Daftar staf baharu untuk log masuk menggunakan No. KP.' }}</p>
        </div>
        <a class="hero-back" href="{{ $backRoute }}">Kembali</a>
    </section>

    @if ($errors->any())
        <div class="create-alert">{{ $errors->first() }}</div>
    @endif

    <section class="create-form-panel">
        <div class="form-panel-head">
            <div>
                <h2>Maklumat Profil</h2>
                <p>{{ $isStudent ? 'Isi maklumat asas untuk akaun login student sahaja.' : 'Isi maklumat asas pengguna untuk akaun login sistem.' }}</p>
            </div>
            <span class="form-badge">{{ $isStudent ? 'Student' : 'Staff' }}</span>
        </div>

        <form method="POST" action="{{ $storeRoute }}">
            @csrf

            <div class="create-form-grid">
                @if ($isStudent)
                    <div class="field">
                        <label for="no_matrik">No Matrik</label>
                        <input id="no_matrik" name="no_matrik" value="{{ old('no_matrik') }}" required>
                    </div>
                @endif

                <div class="field">
                    <label for="nama">Nama</label>
                    <input id="nama" name="nama" value="{{ old('nama') }}" required>
                </div>

                <div class="field">
                    <label for="nric">No. KP</label>
                    <input id="nric" name="nric" value="{{ old('nric') }}" required>
                </div>

                <div class="field">
                    <label for="profile_email_display">Email</label>
                    <input id="profile_email_display" name="email_display" type="text" inputmode="email" value="{{ old('email') }}" autocomplete="new-password" data-email-display>
                    <input id="email" name="email" type="hidden" value="{{ old('email') }}" data-email-hidden>
                </div>

                <div class="field">
                    <label for="no_tel">No Telefon</label>
                    <input id="no_tel" name="no_tel" value="{{ old('no_tel') }}">
                </div>

                @if ($isStudent)
                    <div class="field">
                        <label for="semester">Semester</label>
                        <select id="semester" name="semester">
                            <option value="">Pilih semester</option>
                            @foreach ($semesterOptions as $semester)
                                <option value="{{ $semester }}" @selected(old('semester') === $semester)>{{ str_replace('Sem', 'Semester', $semester) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="program">Program</label>
                        <select id="program" name="program">
                            <option value="">Pilih program</option>
                            @foreach ($programOptions as $program)
                                <option value="{{ $program }}" @selected(old('program') === $program)>{{ $program }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="kelas">Kelas</label>
                        <select id="kelas" name="kelas" data-selected="{{ old('kelas') }}">
                            <option value="">Pilih semester dahulu</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="tarikh_daftar">Tarikh Daftar</label>
                        <input id="tarikh_daftar" name="tarikh_daftar" type="date" value="{{ old('tarikh_daftar') }}">
                    </div>
                @else
                    <div class="field">
                        <label for="staff_type">Jenis Staff</label>
                        <select id="staff_type" name="staff_type" required data-staff-type-select @disabled($lockToCoopWorker)>
                            @if (! $lockToCoopWorker)
                                <option value="">Pilih jenis staff</option>
                                <option value="lecturer_member" @selected($selectedStaffType === 'lecturer_member')>Pensyarah / Staf Akademik</option>
                                @if ($selectedStaffType === 'coop_staff')
                                    <option value="coop_staff" @selected($selectedStaffType === 'coop_staff')>Pekerja Koperasi</option>
                                @endif
                                <option value="clothing_staff" @selected($selectedStaffType === 'clothing_staff')>Staff Pengurus Baju</option>
                                <option value="share_staff" @selected($selectedStaffType === 'share_staff')>Staff Pengurus Saham</option>
                                <option value="coop_manager" @selected($selectedStaffType === 'coop_manager')>Staff Pengurus Pekerja Koperasi</option>
                            @else
                                <option value="coop_staff" selected>Pekerja Koperasi</option>
                            @endif
                        </select>
                        @if ($lockToCoopWorker)
                            <input type="hidden" name="staff_type" value="coop_staff">
                        @endif
                    </div>
                    <div class="field" data-no-pekerja-field @if($selectedStaffType !== 'coop_staff') hidden @endif>
                        <label for="no_pekerja">No Pekerja</label>
                        <input id="no_pekerja" name="no_pekerja" value="{{ old('no_pekerja') }}" placeholder="PBT-001" @if($selectedStaffType === 'coop_staff') required @else disabled @endif>
                    </div>
                    @if ($isCoopWorkerCreate)
                        <div class="field">
                            <label for="kadar_elaun">Kadar Elaun</label>
                            <input id="kadar_elaun" name="kadar_elaun" type="number" min="0" step="0.01" value="{{ old('kadar_elaun', 40) }}">
                        </div>
                    @endif
                @endif

                <div class="field field-section-heading">
                    <h3>Tetapan Password</h3>
                    <p>{{ $isStudent ? 'Password student dijana automatik oleh sistem.' : 'Isi password jika mahu tetapkan sendiri. Jika kosong, sistem guna staff12345.' }}</p>
                </div>

                @if ($isStudent)
                    <div class="field auto-password-note">
                        <label>Password</label>
                        <div class="auto-password-box">
                            <strong>Auto Generate</strong>
                            <span>No Matrik + @123</span>
                        </div>
                    </div>
                @else
                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" placeholder="Kosongkan untuk staff12345">
                    </div>
                @endif
            </div>

            <div class="create-actions">
                <button class="save-button" type="submit">Tambah {{ $isStudent ? 'Student' : 'Staff' }}</button>
                <a class="cancel-button" href="{{ $backRoute }}">Batal</a>
            </div>
        </form>
    </section>
@endsection

@push('styles')
<style>
    .page-heading{display:none}
    .create-user-hero{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:18px;
        margin-bottom:24px;
        padding:34px 36px;
        border:1px solid var(--line);
        border-left:5px solid var(--secondary);
        border-radius:22px;
        background:#fff;
        box-shadow:0 14px 34px rgba(15,23,42,.055);
    }
    .hero-kicker{
        display:inline-flex;
        min-height:30px;
        align-items:center;
        border-radius:999px;
        background:var(--secondary-soft);
        color:var(--secondary);
        padding:0 12px;
        font-size:12px;
        font-weight:900;
        letter-spacing:.04em;
    }
    .create-user-hero h1{margin:16px 0 0;color:var(--text);font-size:38px;line-height:1.15;font-weight:900;letter-spacing:0}
    .create-user-hero p{margin:12px 0 0;color:var(--muted-2);font-weight:800}
    .brand-red{color:var(--primary)}
    .brand-blue{color:var(--secondary)}
    .hero-back{
        display:inline-flex;
        min-height:42px;
        align-items:center;
        justify-content:center;
        border-radius:12px;
        background:var(--secondary-soft);
        color:var(--secondary);
        padding:0 16px;
        text-decoration:none;
        font-weight:900;
        white-space:nowrap;
    }
    .hero-back:hover{background:var(--secondary);color:#fff}
    .create-alert{margin-bottom:18px;border:1px solid var(--danger-soft);border-radius:14px;background:var(--danger-soft);color:var(--danger);padding:14px 16px;font-weight:800}
    .create-form-panel{
        border:1px solid var(--line);
        border-radius:22px;
        background:#fff;
        box-shadow:0 14px 34px rgba(15,23,42,.055);
        overflow:hidden;
    }
    .form-panel-head{display:flex;justify-content:space-between;gap:14px;align-items:flex-start;padding:24px 28px;border-bottom:1px solid var(--line);background:#fff}
    .form-panel-head h2{margin:0;color:var(--text);font-size:24px;font-weight:900}
    .form-panel-head p{margin:8px 0 0;color:var(--muted-2);font-weight:700}
    .form-badge{display:inline-flex;min-height:34px;align-items:center;border-radius:999px;background:var(--success-soft);color:var(--success);padding:0 13px;font-size:12px;font-weight:900}
    .create-form-panel form{padding:28px}
    .create-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
    .field{display:grid;gap:8px}
    .field label{color:var(--muted);font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.03em}
    .field input,.field select{
        width:100%;
        min-height:46px;
        border:1px solid var(--line);
        border-radius:12px;
        background:#fff;
        color:var(--text);
        padding:0 14px;
        font:inherit;
        font-weight:700;
    }
    .field input:focus,.field select:focus{outline:0;border-color:var(--secondary);box-shadow:0 0 0 4px rgba(30,64,175,.10)}
    .field-section-heading{
        grid-column:1 / -1;
        gap:6px;
        margin-top:4px;
        padding-top:18px;
        border-top:1px solid var(--line);
    }
    .field-section-heading h3{
        margin:0;
        color:var(--text);
        font-size:18px;
        font-weight:900;
    }
    .field-section-heading p{
        margin:0;
        color:var(--muted-2);
        font-size:13px;
        font-weight:700;
    }
    .auto-password-box{
        display:flex;
        min-height:46px;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        border:1px solid var(--line);
        border-radius:12px;
        background:var(--secondary-soft);
        color:var(--secondary);
        padding:0 14px;
        font-weight:900;
    }
    .auto-password-box span{color:var(--muted);font-size:13px;font-weight:800}
    .create-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:24px;padding-top:22px;border-top:1px solid var(--line)}
    .save-button,.cancel-button{
        display:inline-flex;
        min-height:44px;
        align-items:center;
        justify-content:center;
        border-radius:12px;
        padding:0 18px;
        border:0;
        text-decoration:none;
        font-weight:900;
        cursor:pointer;
    }
    .save-button{background:var(--secondary);color:#fff;box-shadow:0 10px 22px rgba(30,64,175,.16)}
    .save-button:hover{background:var(--secondary-hover)}
    .cancel-button{background:var(--secondary-soft);color:var(--secondary)}
    .cancel-button:hover{background:#dbeafe;color:var(--secondary)}

    .content:has(> .create-user-hero){
        background:#F4F7FB;
    }
    .content > .create-user-hero,
    .content > .create-form-panel{
        max-width:none;
    }
    .content > .create-user-hero{
        position:relative;
        align-items:flex-start;
        margin-bottom:30px;
        padding:26px 28px!important;
        border:1px solid #D7E2EF!important;
        border-left:6px solid #0B1E36!important;
        border-radius:12px!important;
        background:#fff!important;
        box-shadow:0 12px 28px rgba(11,30,54,.08)!important;
    }
    .content > .create-user-hero::before{
        content:"";
        position:absolute;
        top:30px;
        left:28px;
        width:42px;
        height:3px;
        border-radius:999px;
        background:#ED1C2E;
    }
    .content > .create-user-hero .hero-kicker{
        margin-top:16px;
        min-height:20px;
        padding:0;
        border-radius:0;
        background:transparent;
        color:#0D6EFD;
        font-size:11px;
        letter-spacing:.05em;
    }
    .content > .create-user-hero h1{
        margin:10px 0 0!important;
        color:#071A33!important;
        font-size:26px!important;
        line-height:1.15!important;
        font-weight:700!important;
    }
    .content > .create-user-hero p{
        margin:8px 0 0!important;
        color:#263A54!important;
        font-size:14px!important;
        line-height:1.45!important;
        font-weight:600!important;
    }
    .content > .create-user-hero .hero-back{
        margin-top:48px;
        min-height:36px;
        border:1px solid #B9CBE4;
        border-radius:6px;
        background:#fff;
        color:#0B5ED7;
        box-shadow:none;
    }
    .content > .create-form-panel{
        border:1px solid #D7E2EF!important;
        border-radius:12px!important;
        background:#fff!important;
        box-shadow:0 8px 20px rgba(8,47,89,.035)!important;
    }
    .content .form-panel-head{
        padding:24px 28px;
        border-bottom:1px solid #D7E2EF;
        background:#fff;
    }
    .content .form-panel-head h2{
        color:#082F59;
        font-size:24px;
        line-height:1.2;
        font-weight:900;
    }
    .content .form-panel-head h2::before{
        content:"";
        display:block;
        width:34px;
        height:4px;
        margin-bottom:10px;
        border-radius:999px;
        background:#ED1C2E;
    }
    .content .form-panel-head p{
        margin-top:6px;
        color:#31517D;
        font-size:15px;
        font-weight:650;
    }
    .content .form-badge{
        min-height:32px;
        border-radius:999px;
        background:#DCFCE7;
        color:#15803D;
        padding:0 14px;
        font-size:13px;
    }
    .content .create-form-panel form{
        padding:28px;
    }
    .content .field label{
        color:#082F59;
        font-size:13px;
        font-weight:800;
        letter-spacing:0;
    }
    .content .field input,
    .content .field select{
        min-height:42px;
        border:1px solid #C8D6E7;
        border-radius:6px;
        color:#102033;
        font-size:14px;
        font-weight:650;
    }
    .content .field-section-heading{
        border-top-color:#D7E2EF;
    }
    .content .field-section-heading h3{
        color:#082F59;
        font-size:18px;
    }
    .content .field-section-heading p{
        color:#31517D;
        font-size:13px;
        font-weight:650;
    }
    .content .save-button,
    .content .cancel-button{
        min-height:42px;
        border-radius:6px;
        font-size:14px;
        box-shadow:none;
    }
    .content .save-button{
        background:#0B5ED7;
        color:#fff;
    }
    .content .cancel-button{
        background:#E8F0FE;
        color:#1A73E8;
    }

    @media (max-width: 640px){
        .create-user-hero{align-items:flex-start;flex-direction:column;padding:24px}
        .create-user-hero h1{font-size:30px}
        .content > .create-user-hero .hero-back{margin-top:0}
        .create-form-grid{grid-template-columns:1fr}
        .form-panel-head{flex-direction:column;padding:22px}
        .create-form-panel form{padding:22px}
        .save-button,.cancel-button{width:100%}
    }
</style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const display = document.querySelector('[data-email-display]');
            const hidden = document.querySelector('[data-email-hidden]');
            const form = display?.closest('form');
            const isEmail = (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim());
            const syncEmail = () => {
                const value = display.value.trim();
                hidden.value = value === '' || isEmail(value) ? value : '';
            };

            if (!display || !hidden || !form) {
                return;
            }

            setTimeout(() => {
                if (display.value.trim() !== '' && !isEmail(display.value)) {
                    display.value = '';
                }

                syncEmail();
            }, 250);

            display.addEventListener('input', syncEmail);
            form.addEventListener('submit', syncEmail);
        });
    </script>
@endpush

@if ($isStudent)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const semesterSelect = document.getElementById('semester');
                const classSelect = document.getElementById('kelas');
                const programSelect = document.getElementById('program');
                const classesBySemester = @json($classOptionsBySemester);

                if (!semesterSelect || !classSelect) {
                    return;
                }

                const academicFromClass = (className) => {
                    const match = String(className || '').match(/^(DIT|DDC|DBF)([1-6])[A-Z]$/);

                    if (!match) {
                        return null;
                    }

                    return {
                        semester: `Sem ${match[2]}`,
                        program: match[1] === 'DIT' ? 'JTMK' : 'JRKV',
                    };
                };

                const renderClassOptions = () => {
                    const selectedClass = classSelect.dataset.selected || classSelect.value;
                    const selectedAcademic = academicFromClass(selectedClass);

                    if (!semesterSelect.value && selectedAcademic) {
                        semesterSelect.value = selectedAcademic.semester;
                    }

                    const classes = classesBySemester[semesterSelect.value] || Object.values(classesBySemester).flat();

                    classSelect.innerHTML = '';
                    classSelect.append(new Option('Pilih kelas', ''));
                    classSelect.disabled = false;

                    classes.forEach((className) => {
                        const option = new Option(className, className);
                        option.selected = selectedClass === className;
                        classSelect.append(option);
                    });

                    classSelect.dataset.selected = '';
                    syncAcademicFields();
                };

                const syncAcademicFields = () => {
                    const academic = academicFromClass(classSelect.value);

                    if (!academic) {
                        return;
                    }

                    semesterSelect.value = academic.semester;

                    if (programSelect) {
                        programSelect.value = academic.program;
                    }
                };

                semesterSelect.addEventListener('change', renderClassOptions);
                classSelect.addEventListener('change', syncAcademicFields);
                renderClassOptions();
            });
        </script>
    @endpush
@endif

@if (! $isStudent)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const staffTypeSelect = document.querySelector('[data-staff-type-select]');
                const noPekerjaField = document.querySelector('[data-no-pekerja-field]');
                const noPekerjaInput = document.getElementById('no_pekerja');

                if (!staffTypeSelect || !noPekerjaField || !noPekerjaInput) {
                    return;
                }

                const syncNoPekerjaField = () => {
                    const showNoPekerja = staffTypeSelect.value === 'coop_staff';

                    noPekerjaField.hidden = !showNoPekerja;
                    noPekerjaInput.required = showNoPekerja;
                    noPekerjaInput.disabled = !showNoPekerja;
                };

                staffTypeSelect.addEventListener('change', syncNoPekerjaField);
                syncNoPekerjaField();
            });
        </script>
    @endpush
@endif
