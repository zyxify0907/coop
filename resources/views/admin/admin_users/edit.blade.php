@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit '.($type === 'student' ? 'Student' : 'Staff'))
@section('page-subtitle', 'Admin boleh kemaskini profil dan password.')

@php
    $isStudent = $type === 'student';
    $isCoopWorkerProfile = ! $isStudent && old('staff_type', $profile->staff_type) === 'coop_staff';
    $backRoute = $isStudent
        ? route('admin.users.students')
        : ($profile->staff_type === 'coop_staff' ? route('admin.users.coop-workers') : route('admin.users.staff'));
    $programOptions = ['JTMK', 'JRKV'];
    $semesterOptions = ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'];
    $classOptionsBySemester = collect(range(1, 6))->mapWithKeys(fn ($semester) => [
        'Sem '.$semester => ['DIT'.$semester.'A', 'DIT'.$semester.'B', 'DDC'.$semester.'A', 'DBF'.$semester.'A'],
    ])->all();
@endphp

@section('content')
    <section class="edit-user-hero">
        <div>
            <span class="hero-kicker">{{ $isStudent ? 'STUDENT / AHLI' : 'STAFF KOPERASI' }}</span>
            <h1>Edit <span class="brand-red">{{ $isStudent ? 'Student' : 'Staff' }}</span></h1>
            <p>Kemaskini profil, maklumat akademik dan password pengguna tanpa ubah role login.</p>
        </div>
        <a class="hero-back" href="{{ $backRoute }}">Kembali</a>
    </section>

    @if ($errors->any())
        <div class="edit-alert">{{ $errors->first() }}</div>
    @endif

    <div class="edit-user-layout">
        <section class="edit-form-panel">
            <div class="form-panel-head">
                <div>
                    <h2>Maklumat Profil</h2>
                    <p>Semak dan kemaskini maklumat yang diperlukan sahaja.</p>
                </div>
                <span class="form-badge">{{ $isStudent ? 'Student' : 'Staff' }}</span>
            </div>

            <form method="POST" action="{{ route('admin.users.update', ['type' => $type, 'id' => $profile->getKey()]) }}">
                @csrf
                @method('PUT')

                <div class="edit-form-grid">
                    <div class="field">
                        <label for="nama">Nama</label>
                        <input id="nama" name="nama" value="{{ old('nama', $profile->nama) }}" required>
                        @error('nama')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label for="nric">No. KP</label>
                        <input id="nric" name="nric" value="{{ old('nric', $profile->nric) }}">
                        @error('nric')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label for="no_tel">No Telefon</label>
                        <input id="no_tel" name="no_tel" value="{{ old('no_tel', $profile->no_tel) }}">
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $profile->email) }}">
                        @error('email')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    @if ($isStudent)
                        <div class="field">
                            <label for="semester">Semester</label>
                            <select id="semester" name="semester">
                                <option value="">Pilih semester</option>
                                @foreach ($semesterOptions as $semester)
                                    <option value="{{ $semester }}" @selected(old('semester', $profile->semester) === $semester)>{{ str_replace('Sem', 'Semester', $semester) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="program">Program</label>
                            <select id="program" name="program">
                                <option value="">Pilih program</option>
                                @foreach ($programOptions as $program)
                                    <option value="{{ $program }}" @selected(old('program', $profile->program) === $program)>{{ $program }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="kelas">Kelas</label>
                            <select id="kelas" name="kelas" data-selected="{{ old('kelas', $profile->kelas) }}">
                                <option value="">Pilih semester dahulu</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="tarikh_daftar">Tarikh Daftar</label>
                            <input id="tarikh_daftar" name="tarikh_daftar" type="date" value="{{ old('tarikh_daftar', optional($profile->tarikh_daftar)->format('Y-m-d')) }}">
                            @error('tarikh_daftar')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                    @else
                        @if ($isCoopWorkerProfile)
                            <div class="field">
                                <label for="no_pekerja">No Pekerja</label>
                                <input id="no_pekerja" name="no_pekerja" value="{{ old('no_pekerja', $profile->no_pekerja) }}" placeholder="PBT-001" required>
                                @error('no_pekerja')<div class="field-error">{{ $message }}</div>@enderror
                            </div>
                        @else
                            <input type="hidden" name="no_pekerja" value="{{ old('no_pekerja', $profile->no_pekerja) }}">
                        @endif

                        <div class="field">
                            <label for="staff_type">Jenis Staff</label>
                            <select id="staff_type" name="staff_type" required>
                                <option value="lecturer_member" @selected(old('staff_type', $profile->staff_type) === 'lecturer_member')>Pensyarah / Staf Akademik</option>
                                <option value="coop_staff" @selected(old('staff_type', $profile->staff_type) === 'coop_staff')>Pekerja Koperasi</option>
                                <option value="clothing_staff" @selected(old('staff_type', $profile->staff_type) === 'clothing_staff')>Staff Pengurusan Baju</option>
                            </select>
                            @error('staff_type')<div class="field-error">{{ $message }}</div>@enderror
                        </div>

                        @if ($isCoopWorkerProfile)
                            <div class="field">
                                <label for="kadar_elaun">Kadar Elaun</label>
                                <input id="kadar_elaun" name="kadar_elaun" type="number" min="0" step="0.01" value="{{ old('kadar_elaun', $profile->kadar_elaun) }}">
                                @error('kadar_elaun')<div class="field-error">{{ $message }}</div>@enderror
                            </div>
                        @endif

                        <div class="field">
                            <label for="tarikh_mula">Tarikh Mula Kerja</label>
                            <input id="tarikh_mula" name="tarikh_mula" type="date" value="{{ old('tarikh_mula', optional($profile->tarikh_mula)->format('Y-m-d')) }}">
                            @error('tarikh_mula')<div class="field-error">{{ $message }}</div>@enderror
                        </div>

                        @if ($isCoopWorkerProfile)
                            <div class="field">
                                <label for="status_aktif">Status Pekerja</label>
                                <select id="status_aktif" name="status_aktif">
                                    <option value="1" @selected(old('status_aktif', $profile->status_aktif ? '1' : '0') == '1')>Aktif</option>
                                    <option value="0" @selected(old('status_aktif', $profile->status_aktif ? '1' : '0') == '0')>Tidak Aktif</option>
                                </select>
                            </div>
                        @endif
                    @endif

                    <div class="field">
                        <label for="password">Password Baru</label>
                        <input id="password" name="password" type="password" placeholder="Biarkan kosong jika tidak mahu ubah">
                        @error('password')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="edit-actions">
                    <button class="save-button" type="submit">Simpan Perubahan</button>
                    <a class="cancel-button" href="{{ $backRoute }}">Batal</a>
                </div>
            </form>
        </section>
    </div>
@endsection

@push('styles')
<style>
    .page-heading{display:none}
    .edit-user-hero{
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
    .edit-user-hero h1{margin:16px 0 0;color:var(--text);font-size:38px;line-height:1.15;font-weight:900;letter-spacing:0}
    .edit-user-hero p{margin:12px 0 0;color:var(--muted-2);font-weight:800}
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
    .edit-alert{margin-bottom:18px;border:1px solid var(--danger-soft);border-radius:14px;background:var(--danger-soft);color:var(--danger);padding:14px 16px;font-weight:800}
    .edit-user-layout{display:block}
    .edit-form-panel{
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
    .edit-form-panel form{padding:28px}
    .edit-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
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
    .field-error{color:var(--danger);font-size:13px;font-weight:800}
    .edit-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:24px;padding-top:22px;border-top:1px solid var(--line)}
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
    @media (max-width: 640px){
        .edit-user-hero{align-items:flex-start;flex-direction:column;padding:24px}
        .edit-user-hero h1{font-size:30px}
        .edit-form-grid{grid-template-columns:1fr}
        .form-panel-head{flex-direction:column;padding:22px}
        .edit-form-panel form{padding:22px}
        .save-button,.cancel-button{width:100%}
    }
</style>
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
