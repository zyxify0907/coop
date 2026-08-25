@extends('layouts.app')

@section('title', 'Permohonan Digital')
@section('page-title', 'Permohonan Digital')
@section('page-subtitle', 'Hantar borang digital dan semak status permohonan anda.')

@php
    $typeConfig = $types[$activeType];
    $currentData = old();
    $isStaffApplicant = ($role ?? 'ahli') === 'staff';
    $portalLabel = $portalLabel ?? 'Student Portal';
    $identityLabel = $identityLabel ?? 'No Matrik';
    $identityValue = $identityValue ?? $user->no_matrik;
    $memberNumber = $isStaffApplicant ? ($identityValue !== 'Belum dijana' ? $identityValue : null) : ($user->no_anggota ?? null);
    $programOptions = ['JTMK', 'JRKV'];
    $kelasOptions = collect(range(1, 6))
        ->flatMap(fn ($semester) => ['DIT'.$semester.'A', 'DIT'.$semester.'B', 'DDC'.$semester.'A', 'DBF'.$semester.'A'])
        ->all();
    $profileProgramLocked = ! $isStaffApplicant && filled($user->program) && in_array($user->program, $programOptions, true);
    $profileClassLocked = ! $isStaffApplicant && filled($user->kelas) && in_array($user->kelas, $kelasOptions, true);
    $currentShare = $currentShare ?? (float) optional($user->saham)->syer;
    $portalRoutes = $portalRoutes ?? [
        'dashboard' => 'auth.dashboard',
        'permohonan_index' => 'student.permohonan.index',
        'permohonan_store' => 'student.permohonan.store',
        'profile' => 'student.profile',
    ];
@endphp

@section('content')
    <section class="student-hero">
        <div>
            <h2>Borang Permohonan Digital</h2>
            <p>{{ $typeConfig['description'] }}</p>
        </div>
        <div class="student-actions">
            <a class="link-button secondary" href="{{ route($portalRoutes['dashboard']) }}">Dashboard</a>
            @if ($role === 'ahli')
                <a class="link-button secondary" href="{{ route($portalRoutes['profile']) }}">Profil</a>
            @endif
        </div>
    </section>

    @if ($errors->any())
        <div class="alert danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <section class="panel panel-pad application-type-panel">
        <div class="application-type-head">
            <h2 style="margin:0">Jenis Permohonan</h2>
            <p style="margin:6px 0 0;color:var(--muted)">Pilih jenis borang yang anda mahu isi.</p>
        </div>
        <nav class="application-menu" aria-label="Jenis permohonan">
            @foreach ($types as $key => $type)
                <a
                    href="{{ route($portalRoutes['permohonan_index'], ['jenis' => $key]) }}"
                    class="application-menu__item {{ $activeType === $key ? 'active' : '' }}"
                >
                    <strong>{{ $type['label'] }}</strong>
                    <span>{{ $type['description'] }}</span>
                </a>
            @endforeach
        </nav>
    </section>

    <div class="application-shell">
        <section class="panel panel-pad application-form">
            <div class="application-form__header">
                <div>
                    <h2 style="margin:0">{{ $typeConfig['label'] }}</h2>
                    <p style="margin:6px 0 0;color:var(--muted)">Maklumat asas diambil daripada profil semasa.</p>
                </div>
                <span class="badge success">{{ $portalLabel }}</span>
            </div>

            <form method="POST" action="{{ route($portalRoutes['permohonan_store'], ['jenis' => $activeType]) }}" class="application-grid" enctype="multipart/form-data">
                @csrf

                @if ($activeType === 'anggota')
                    <div class="section-block field-block--full">
                        <div class="section-block__head">
                            <span class="section-block__tag">A</span>
                            <div>
                                <h3>Maklumat Pemohon</h3>
                                <p>Butiran asas pemohon seperti dalam borang permohonan anggota.</p>
                            </div>
                        </div>
                        <div class="section-block__grid">
                            <div class="field-block">
                                <label>Nama</label>
                                <input type="text" value="{{ $user->nama }}" disabled>
                            </div>

                            @unless ($isStaffApplicant)
                                <div class="field-block">
                                    <label>{{ $identityLabel }}</label>
                                    <input type="text" value="{{ $identityValue }}" disabled>
                                </div>
                            @endunless

                            <div class="field-block">
                                <label>No. KP</label>
                                <input type="text" value="{{ $user->nric ?? '-' }}" disabled>
                            </div>

                            <div class="field-block">
                                <label for="email">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="nama@email.com" @readonly(filled($user->email))>
                            </div>

                            <div class="field-block">
                                <label for="no_tel">No Telefon</label>
                                <input id="no_tel" name="no_tel" type="text" value="{{ old('no_tel', $user->no_tel) }}" placeholder="01X-XXXXXXX" @readonly(filled($user->no_tel))>
                            </div>

                            <div class="field-block">
                                <label for="tarikh_lahir">Tarikh Lahir</label>
                                <input id="tarikh_lahir" name="tarikh_lahir" type="date" value="{{ old('tarikh_lahir') }}" required>
                            </div>

                            <div class="field-block">
                                <label for="jantina">Jantina</label>
                                <select id="jantina" name="jantina" required>
                                    <option value="">Pilih jantina</option>
                                    @foreach (['Lelaki', 'Perempuan'] as $option)
                                        <option value="{{ $option }}" @selected(old('jantina') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="field-block">
                                <label for="pekerjaan_pelajar">Pekerjaan / Pelajar</label>
                                <input id="pekerjaan_pelajar" name="pekerjaan_pelajar" type="text" value="{{ old('pekerjaan_pelajar', $isStaffApplicant ? $user->staff_type_label : 'Pelajar') }}" required readonly>
                            </div>

                            <div class="field-block">
                                <label for="bangsa">Bangsa</label>
                                <input id="bangsa" name="bangsa" type="text" value="{{ old('bangsa') }}" required>
                            </div>

                            <div class="field-block">
                                <label for="agama">Agama</label>
                                <input id="agama" name="agama" type="text" value="{{ old('agama') }}" required>
                            </div>

                            <div class="field-block">
                                <label for="taraf_perkahwinan">Taraf Perkahwinan</label>
                                <input id="taraf_perkahwinan" name="taraf_perkahwinan" type="text" value="{{ old('taraf_perkahwinan') }}" required>
                            </div>

                            <div class="field-block field-block--full">
                                <label for="alamat">Alamat</label>
                                <textarea id="alamat" name="alamat" rows="3" required>{{ old('alamat') }}</textarea>
                            </div>

                            <div class="field-block">
                                <label for="no_tel_rumah">No Telefon Rumah</label>
                                <input id="no_tel_rumah" name="no_tel_rumah" type="text" value="{{ old('no_tel_rumah') }}">
                            </div>

                            @if ($isStaffApplicant)
                                <div class="field-block">
                                    <label>Jenis Staff</label>
                                    <input type="text" value="{{ $user->staff_type_label }}" disabled>
                                </div>

                                <div class="field-block">
                                    <label>Tarikh Mula Kerja</label>
                                    <input type="text" value="{{ optional($user->tarikh_mula)->format('d/m/Y') ?? '-' }}" disabled>
                                </div>
                            @else
                                <div class="field-block">
                                    <label for="program_pengajian">Program Pengajian</label>
                                    @if ($profileProgramLocked)
                                        <input type="hidden" name="program_pengajian" value="{{ $user->program }}">
                                        <select id="program_pengajian" disabled>
                                            <option value="{{ $user->program }}">{{ $user->program }}</option>
                                        </select>
                                    @else
                                        <select id="program_pengajian" name="program_pengajian" required>
                                            @foreach ($programOptions as $programOption)
                                                <option value="{{ $programOption }}" @selected(old('program_pengajian', $user->program) === $programOption)>{{ $programOption }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>

                                <div class="field-block">
                                    <label>Semester Semasa</label>
                                    <input type="text" value="{{ $user->semester ?? '-' }}" disabled>
                                </div>

                                <div class="field-block">
                                    <label for="kelas">Kelas</label>
                                    @if ($profileClassLocked)
                                        <input type="hidden" name="kelas" value="{{ $user->kelas }}">
                                        <select id="kelas" disabled>
                                            <option value="{{ $user->kelas }}">{{ $user->kelas }}</option>
                                        </select>
                                    @else
                                        <select id="kelas" name="kelas" required>
                                            @foreach ($kelasOptions as $kelasOption)
                                                <option value="{{ $kelasOption }}" @selected(old('kelas', $user->kelas ?? 'DIT1A') === $kelasOption)>{{ $kelasOption }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif ($activeType === 'saham')
                    <div class="section-block field-block--full">
                        <div class="section-block__head">
                            <span class="section-block__tag">I</span>
                            <div>
                                <h3>Maklumat Anggota</h3>
                                <p>Butiran anggota seperti Borang Penambahan Saham.</p>
                            </div>
                        </div>
                        <div class="section-block__grid">
                            <div class="field-block">
                                <label>Nama</label>
                                <input type="text" value="{{ $user->nama }}" disabled>
                            </div>

                            @unless ($isStaffApplicant)
                                <div class="field-block">
                                    <label>{{ $identityLabel }}</label>
                                    <input type="text" value="{{ $identityValue }}" disabled>
                                </div>
                            @endunless

                            <div class="field-block">
                                <label>No Anggota</label>
                                <input type="text" value="{{ $memberNumber ?: 'Belum dijana' }}" disabled>
                            </div>

                            <div class="field-block">
                                <label for="no_kp">No. KP</label>
                                <input id="no_kp" name="no_kp" type="text" value="{{ old('no_kp', $user->nric) }}" required @readonly(filled($user->nric))>
                            </div>

                            <div class="field-block">
                                <label for="email">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="nama@email.com" @readonly(filled($user->email))>
                            </div>

                            <div class="field-block">
                                <label for="no_tel">No Telefon</label>
                                <input id="no_tel" name="no_tel" type="text" value="{{ old('no_tel', $user->no_tel) }}" placeholder="01X-XXXXXXX" @readonly(filled($user->no_tel))>
                            </div>
                        </div>
                    </div>
                @elseif ($activeType === 'berhenti')
                    <div class="section-block field-block--full">
                        <div class="section-block__head">
                            <span class="section-block__tag">I</span>
                            <div>
                                <h3>Maklumat Anggota / Pemohon</h3>
                                <p>Butiran pemohon untuk pengeluaran saham atau berhenti.</p>
                            </div>
                        </div>
                        <div class="section-block__grid">
                            <div class="field-block">
                                <label>Nama</label>
                                <input type="text" value="{{ $user->nama }}" disabled>
                            </div>

                            @unless ($isStaffApplicant)
                                <div class="field-block">
                                    <label>{{ $identityLabel }}</label>
                                    <input type="text" value="{{ $identityValue }}" disabled>
                                </div>
                            @endunless

                            <div class="field-block">
                                <label for="no_anggota">No Anggota</label>
                                <input id="no_anggota" name="no_anggota" type="text" value="{{ old('no_anggota', $memberNumber) }}" readonly>
                            </div>

                            <div class="field-block">
                                <label for="no_kp">No K/P</label>
                                <input id="no_kp" name="no_kp" type="text" value="{{ old('no_kp', $user->nric) }}" required @readonly(filled($user->nric))>
                            </div>

                            <div class="field-block">
                                <label for="email">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="nama@email.com" @readonly(filled($user->email))>
                            </div>

                            <div class="field-block">
                                <label for="no_tel">No Telefon</label>
                                <input id="no_tel" name="no_tel" type="text" value="{{ old('no_tel', $user->no_tel) }}" placeholder="01X-XXXXXXX" @readonly(filled($user->no_tel))>
                            </div>

                        </div>
                    </div>
                @else
                    <div class="field-block">
                        <label>Nama</label>
                        <input type="text" value="{{ $user->nama }}" disabled>
                    </div>

                    <div class="field-block">
                        <label>{{ $identityLabel }}</label>
                        <input type="text" value="{{ $identityValue }}" disabled>
                    </div>

                    <div class="field-block">
                        <label>No. KP</label>
                        <input type="text" value="{{ $user->nric ?? '-' }}" disabled>
                    </div>

                    <div class="field-block">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="nama@email.com" @readonly(filled($user->email))>
                    </div>

                    <div class="field-block">
                        <label for="no_tel">No Telefon</label>
                        <input id="no_tel" name="no_tel" type="text" value="{{ old('no_tel', $user->no_tel) }}" placeholder="01X-XXXXXXX" @readonly(filled($user->no_tel))>
                    </div>
                @endif

                @if ($activeType === 'anggota')
                    <div class="section-block field-block--full">
                        <div class="section-block__head">
                            <span class="section-block__tag">B</span>
                            <div>
                                <h3>Akuan dan Ikrar Pemohonan Keanggotaan</h3>
                                <p>Pemohon mengesahkan maklumat yang dihantar adalah benar dan bersetuju mematuhi peraturan koperasi.</p>
                            </div>
                        </div>
                        <div class="section-block__grid">
                            <div class="field-block">
                                <label for="yuran_anggota">Yuran Menjadi Anggota</label>
                                <div class="money-input">
                                    <span>RM</span>
                                    <input id="yuran_anggota" name="yuran_anggota" type="number" min="10" step="0.01" value="{{ old('yuran_anggota', '10.00') }}" required readonly>
                                </div>
                            </div>

                            <div class="field-block">
                                <label for="modal_saham">Modal Saham Minimum</label>
                                <div class="money-input">
                                    <span>RM</span>
                                    <input id="modal_saham" name="modal_saham" type="number" min="10" step="0.01" value="{{ old('modal_saham', '10.00') }}" required readonly>
                                </div>
                            </div>

                            <div class="field-block">
                                <label for="salinan_ic">Salinan IC</label>
                                <input id="salinan_ic" name="salinan_ic" type="file" accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="field-hint">PDF, JPG atau PNG. Maksimum 5 MB.</small>
                                @error('salinan_ic')<div class="field-error">{{ $message }}</div>@enderror
                            </div>

                            <div class="field-block">
                                <label for="slip_bayaran">Slip / Bukti Bayaran</label>
                                <input id="slip_bayaran" name="slip_bayaran" type="file" accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="field-hint">Muat naik bukti bayaran yuran anggota dan modal saham. Maksimum 5 MB.</small>
                                @error('slip_bayaran')<div class="field-error">{{ $message }}</div>@enderror
                            </div>

                            <label class="consent-box field-block--full">
                                <input type="checkbox" name="setuju_saham_tidak_dituntut" value="1" required @checked(old('setuju_saham_tidak_dituntut'))>
                                <span>Saya bersetuju untuk menyumbangkan kesemua saham sekiranya ia tidak dituntut dalam masa 3 bulan selepas berhenti, bertukar atau tamat pengajian kepada pihak Koperasi Politeknik Besut Berhad.</span>
                            </label>

                            <label class="consent-box field-block--full">
                                <input type="checkbox" name="akuan_pemohon" value="1" required @checked(old('akuan_pemohon'))>
                                <span>Saya mengaku bahawa segala maklumat yang saya berikan adalah benar.</span>
                            </label>
                        </div>
                    </div>

                    <div class="section-block field-block--full">
                        <div class="section-block__head">
                            <span class="section-block__tag">C</span>
                            <div>
                                <h3>Pelantikan Penama / Wasi</h3>
                                <p>Maklumat penama atau wasi seperti Borang KOPBB 102.</p>
                            </div>
                        </div>
                        <div class="section-block__grid">
                            <div class="field-block field-block--full">
                                <h4 class="subsection-title">Maklumat Penama / Wasi</h4>
                            </div>

                            <div class="field-block">
                                <label for="penama_nama">Nama Penama / Wasi</label>
                                <input id="penama_nama" name="penama_nama" type="text" value="{{ old('penama_nama') }}" required>
                            </div>

                            <div class="field-block">
                                <label for="penama_nric">No MyKad Penama / Wasi</label>
                                <input id="penama_nric" name="penama_nric" type="text" value="{{ old('penama_nric') }}" required>
                            </div>

                            <div class="field-block">
                                <label for="penama_hubungan">Hubungan</label>
                                <input id="penama_hubungan" name="penama_hubungan" type="text" value="{{ old('penama_hubungan') }}" required>
                            </div>

                            <div class="field-block">
                                <label for="penama_no_tel">No Telefon</label>
                                <input id="penama_no_tel" name="penama_no_tel" type="text" value="{{ old('penama_no_tel') }}" required>
                            </div>

                            <div class="field-block field-block--full">
                                <label for="penama_alamat">Alamat Penama / Wasi</label>
                                <textarea id="penama_alamat" name="penama_alamat" rows="3" required>{{ old('penama_alamat') }}</textarea>
                            </div>

                            <div class="field-block">
                                <label for="penama_poskod">Poskod</label>
                                <input id="penama_poskod" name="penama_poskod" type="text" value="{{ old('penama_poskod') }}" required>
                            </div>

                            <div class="field-block">
                                <label for="penama_peratus">Peratus Pembahagian</label>
                                <input id="penama_peratus" name="penama_peratus" type="number" min="0" max="100" step="0.01" value="{{ old('penama_peratus') }}" placeholder="Untuk bukan Islam sahaja">
                            </div>

                            <div class="field-block field-block--full">
                                <h4 class="subsection-title">Penama 2</h4>
                            </div>

                            <div class="field-block">
                                <label for="penama2_nama">Nama Penama 2</label>
                                <input id="penama2_nama" name="penama2_nama" type="text" value="{{ old('penama2_nama') }}">
                            </div>

                            <div class="field-block">
                                <label for="penama2_nric">No MyKad Penama 2</label>
                                <input id="penama2_nric" name="penama2_nric" type="text" value="{{ old('penama2_nric') }}">
                            </div>

                            <div class="field-block">
                                <label for="penama2_hubungan">Hubungan Penama 2</label>
                                <input id="penama2_hubungan" name="penama2_hubungan" type="text" value="{{ old('penama2_hubungan') }}">
                            </div>

                            <div class="field-block">
                                <label for="penama2_no_tel">No Telefon Penama 2</label>
                                <input id="penama2_no_tel" name="penama2_no_tel" type="text" value="{{ old('penama2_no_tel') }}">
                            </div>

                            <div class="field-block field-block--full">
                                <label for="penama2_alamat">Alamat Penama 2</label>
                                <textarea id="penama2_alamat" name="penama2_alamat" rows="3">{{ old('penama2_alamat') }}</textarea>
                            </div>

                            <div class="field-block">
                                <label for="penama2_poskod">Poskod Penama 2</label>
                                <input id="penama2_poskod" name="penama2_poskod" type="text" value="{{ old('penama2_poskod') }}">
                            </div>

                            <div class="field-block">
                                <label for="penama2_peratus">Peratus Pembahagian Penama 2</label>
                                <input id="penama2_peratus" name="penama2_peratus" type="number" min="0" max="100" step="0.01" value="{{ old('penama2_peratus') }}" placeholder="Untuk bukan Islam sahaja">
                            </div>

                            <div class="field-block field-block--full">
                                <h4 class="subsection-title">Perakuan Saksi</h4>
                            </div>

                            <div class="field-block">
                                <label for="nama_waris">Nama Waris / Rujukan</label>
                                <input id="nama_waris" name="nama_waris" type="text" value="{{ old('nama_waris') }}" required>
                            </div>

                            <div class="field-block">
                                <label for="telefon_waris">Telefon Waris / Rujukan</label>
                                <input id="telefon_waris" name="telefon_waris" type="text" value="{{ old('telefon_waris') }}" required>
                            </div>

                            <div class="field-block">
                                <label for="hubungan_waris">Hubungan Waris / Rujukan</label>
                                <input id="hubungan_waris" name="hubungan_waris" type="text" value="{{ old('hubungan_waris') }}" required>
                            </div>

                            <div class="field-block">
                                <label for="saksi_nama">Nama Saksi</label>
                                <input id="saksi_nama" name="saksi_nama" type="text" value="{{ old('saksi_nama') }}" required>
                            </div>

                            <div class="field-block">
                                <label for="saksi_nric">No Kad Pengenalan Saksi</label>
                                <input id="saksi_nric" name="saksi_nric" type="text" value="{{ old('saksi_nric') }}" required>
                            </div>

                            <div class="field-block">
                                <label for="saksi_tarikh">Tarikh Saksi</label>
                                <input id="saksi_tarikh" name="saksi_tarikh" type="date" value="{{ old('saksi_tarikh', now()->toDateString()) }}" required>
                            </div>
                        </div>
                    </div>
                @elseif ($activeType === 'saham')
                    <div class="section-block field-block--full">
                        <div class="section-block__head">
                            <span class="section-block__tag">II</span>
                            <div>
                                <h3>Penambahan Saham</h3>
                                <p>Nyatakan jumlah saham tambahan yang ingin dimohon.</p>
                            </div>
                        </div>
                        <div class="section-block__grid">
                            <div class="field-block">
                                <label for="amaun_tambahan">Saya ingin menambah saham saya sebanyak</label>
                                <div class="money-input">
                                    <span>RM</span>
                                    <input id="amaun_tambahan" name="amaun_tambahan" type="number" min="1" step="0.01" value="{{ old('amaun_tambahan') }}" placeholder="0.00" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-block field-block--full">
                        <div class="section-block__head">
                            <span class="section-block__tag">III</span>
                            <div>
                                <h3>Dokumen Sokongan</h3>
                                <p>Tandakan dokumen yang perlu dimuat naik selepas permohonan dihantar.</p>
                            </div>
                        </div>
                        <div class="section-block__grid">
                            <div class="field-block field-block--full">
                                <label for="slip_bayaran">Slip / Bukti Bayaran</label>
                                <input id="slip_bayaran" name="slip_bayaran" type="file" accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="field-hint">Muat naik bukti bayaran. PDF, JPG atau PNG, maksimum 5 MB.</small>
                                @error('slip_bayaran')<div class="field-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="section-block field-block--full">
                        <div class="section-block__head">
                            <span class="section-block__tag">IV</span>
                            <div>
                                <h3>Pengakuan Pemohon</h3>
                                <p>Pemohon mengesahkan maklumat dan dokumen lampiran adalah benar.</p>
                            </div>
                        </div>
                        <div class="section-block__grid">
                            <div class="field-block">
                                <label for="tarikh_pengakuan">Tarikh</label>
                                <input id="tarikh_pengakuan" name="tarikh_pengakuan" type="date" value="{{ old('tarikh_pengakuan', now()->toDateString()) }}" required>
                            </div>

                            <label class="consent-box field-block--full">
                                <input type="checkbox" name="akuan_saham" value="1" required @checked(old('akuan_saham'))>
                                <span>Saya mengaku bahawa segala maklumat-maklumat di atas dan dokumen-dokumen yang dilampirkan adalah benar dan bersetuju mengikut segala syarat yang ditetapkan oleh pihak Koperasi.</span>
                            </label>
                        </div>
                    </div>
                @else
                    <div class="section-block field-block--full">
                        <div class="section-block__head">
                            <span class="section-block__tag">II</span>
                            <div>
                                <h3>Sebab Permohonan</h3>
                                <p>Tandakan sebab permohonan seperti di dalam borang PDF.</p>
                            </div>
                        </div>
                        <div class="checkbox-grid">
                            @foreach ($isStaffApplicant ? ['Berhenti Keahlian', 'Berpindah', 'Bersara', 'Lain-lain'] : ['Berhenti Keahlian', 'Berpindah', 'Bersara', 'Tamat Pengajian', 'Lain-lain'] as $option)
                                <label class="choice-box">
                                    <input type="checkbox" name="jenis_permohonan[]" value="{{ $option }}" data-withdrawal-type @checked(in_array($option, old('jenis_permohonan', []), true))>
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="field-block" style="margin-top:18px">
                            <label for="lain_lain_sebab">Lain-lain Sebab</label>
                            <textarea id="lain_lain_sebab" name="lain_lain_sebab" rows="3">{{ old('lain_lain_sebab') }}</textarea>
                        </div>
                    </div>

                    <div class="section-block field-block--full">
                        <div class="section-block__head">
                            <span class="section-block__tag">III</span>
                            <div>
                                <h3>Dokumen Sokongan</h3>
                                <p>Muat naik dokumen yang berkaitan mengikut sebab permohonan anda.</p>
                            </div>
                        </div>
                        <div class="document-checklist">
                            <div class="document-group">
                                <strong>Dokumen sokongan</strong>
                                <div class="section-block__grid">
                                    <div class="field-block">
                                        <label for="salinan_ic">Salinan IC</label>
                                        <input id="salinan_ic" name="salinan_ic" type="file" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <small class="field-hint">PDF, JPG atau PNG. Maksimum 5 MB.</small>
                                        @error('salinan_ic')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="field-block">
                                        <label for="surat_sokongan">Salinan surat pindah / berhenti / kad persaraan</label>
                                        <input id="surat_sokongan" name="surat_sokongan" type="file" accept=".pdf,.jpg,.jpeg,.png">
                                        <small class="field-hint">Wajib untuk berhenti, pindah, bersara atau tamat pengajian. Maksimum 5 MB.</small>
                                        @error('surat_sokongan')<div class="field-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-block field-block--full">
                        <div class="section-block__head">
                            <span class="section-block__tag">IV</span>
                            <div>
                                <h3>Pengakuan Pemohon</h3>
                                <p>Pemohon mengesahkan pengeluaran penuh baki saham semasa.</p>
                            </div>
                        </div>
                        <div class="section-block__grid">
                            <div class="field-block">
                                <label>Syer Semasa</label>
                                <input type="text" value="RM {{ number_format((float) $currentShare, 2) }}" disabled>
                            </div>

                            <div class="field-block">
                                <label for="tarikh_pengakuan">Tarikh</label>
                                <input id="tarikh_pengakuan" name="tarikh_pengakuan" type="date" value="{{ old('tarikh_pengakuan', now()->toDateString()) }}" required>
                            </div>

                            <label class="consent-box field-block--full">
                                <input type="checkbox" name="akuan_pengeluaran" value="1" required @checked(old('akuan_pengeluaran'))>
                                <span>Saya mengaku bahawa semua maklumat adalah benar dan bersetuju baki saham semasa dikeluarkan sepenuhnya.</span>
                            </label>
                        </div>
                    </div>

                    <div class="section-block field-block--full">
                        <div class="section-block__head">
                            <span class="section-block__tag">VI</span>
                            <div>
                                <h3>Penerimaan Anggota</h3>
                                <p>Pilih kaedah bayaran untuk penerimaan baki saham.</p>
                            </div>
                        </div>
                        <div class="section-block__grid">
                            <div class="field-block">
                                <label for="kaedah_terima_bayaran">Bayaran Diterima Melalui</label>
                                <select id="kaedah_terima_bayaran" name="kaedah_terima_bayaran" required>
                                    <option value="">Pilih kaedah</option>
                                    @foreach (['Bayaran Atas Talian', 'Tunai'] as $option)
                                        <option value="{{ $option }}" @selected(old('kaedah_terima_bayaran') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="field-block">
                                <label for="nama_bank">Nama Bank</label>
                                <input id="nama_bank" name="nama_bank" type="text" value="{{ old('nama_bank') }}">
                            </div>

                            <div class="field-block field-block--full">
                                <label for="no_akaun_bank">No Akaun Bank</label>
                                <input id="no_akaun_bank" name="no_akaun_bank" type="text" value="{{ old('no_akaun_bank') }}">
                            </div>
                        </div>
                    </div>
                @endif

                <div class="field-block field-block--full">
                    <label for="catatan_pelajar">Catatan Tambahan</label>
                    <textarea id="catatan_pelajar" name="catatan_pelajar" rows="3">{{ old('catatan_pelajar') }}</textarea>
                </div>

                <div class="field-block field-block--full">
                    <button class="button" type="submit">Hantar Permohonan</button>
                </div>
            </form>
        </section>
    </div>

@endsection

@push('styles')
    <style>
        .application-type-panel {
            margin-bottom: 24px;
        }
        .application-type-head {
            margin-bottom: 18px;
        }
        .application-menu {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }
        .application-menu__item {
            display: block;
            min-height: 118px;
            padding: 16px 18px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            text-decoration: none;
            transition: border-color var(--transition-fast), background var(--transition-fast), box-shadow var(--transition-fast), transform var(--transition-fast);
        }
        .application-menu__item strong {
            display: block;
            margin-bottom: 6px;
            font-size: 15px;
            color: var(--text);
        }
        .application-menu__item span {
            display: block;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }
        .application-menu__item:hover {
            border-color: #bfd3ff;
            background: #f8fbff;
            transform: translateY(-1px);
        }
        .application-menu__item.active {
            border-color: var(--secondary);
            background: var(--secondary-soft);
            box-shadow: inset 3px 0 0 var(--secondary);
        }
        .application-shell {
            display: block;
        }
        .application-form__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }
        .application-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }
        .field-block {
            display: grid;
            gap: 8px;
        }
        .field-block--full {
            grid-column: 1 / -1;
        }
        .field-block label {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }
        .field-block input,
        .field-block select,
        .field-block textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 11px 12px;
            background: #fff;
            color: var(--text);
        }
        .field-block textarea {
            resize: vertical;
        }
        .field-block input:disabled,
        .field-block select:disabled,
        .field-block input[readonly],
        .field-block textarea[readonly] {
            background: #f8fafc;
            color: #475569;
        }
        .field-block input[readonly],
        .field-block textarea[readonly] {
            cursor: default;
        }
        .money-input {
            display: flex;
            align-items: center;
            min-height: 46px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            overflow: hidden;
        }
        .money-input span {
            align-self: stretch;
            display: grid;
            place-items: center;
            min-width: 58px;
            padding: 0 14px;
            border-right: 1px solid var(--line);
            background: #f8fafc;
            color: #2453a6;
            font-weight: 800;
        }
        .money-input input {
            min-width: 0;
            border: 0;
            border-radius: 0;
        }
        .money-input input:focus {
            outline: 0;
            box-shadow: none;
        }
        .section-block {
            border: 1px solid #dbe7ff;
            border-radius: 16px;
            padding: 18px;
            background: #fbfdff;
        }
        .section-block__head {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 18px;
        }
        .section-block__tag {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: var(--primary);
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            flex: 0 0 auto;
        }
        .section-block__head h3 {
            margin: 0;
            font-size: 17px;
        }
        .section-block__head p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 13px;
        }
        .section-block__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }
        .subsection-title {
            margin: 2px 0 0;
            padding-top: 4px;
            color: #16345c;
            font-size: 15px;
        }
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
        .choice-box {
            min-height: 58px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border: 1px solid #dbe7ff;
            border-radius: 12px;
            background: #fff;
            color: #334155;
            font-weight: 700;
        }
        .choice-box small {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
            line-height: 1.35;
        }
        .choice-box input {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
        }
        .consent-box {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 14px 16px;
            border: 1px solid #dbe7ff;
            border-radius: 12px;
            background: #fff;
            color: #334155;
            font-size: 14px;
            line-height: 1.5;
        }
        .consent-box input {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            flex: 0 0 auto;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            text-transform: capitalize;
        }
        .status-pill--baru {
            background: var(--secondary-soft);
            color: var(--secondary);
        }
        .status-pill--dalam_semakan {
            background: var(--warning-soft);
            color: var(--warning);
        }
        .status-pill--diluluskan {
            background: var(--success-soft);
            color: var(--success);
        }
        .status-pill--ditolak {
            background: var(--danger-soft);
            color: var(--danger);
        }
        @media (max-width: 900px) {
            .application-menu {
                grid-template-columns: 1fr;
            }
            .application-menu__item {
                min-height: 0;
            }
            .application-grid {
                grid-template-columns: 1fr;
            }
            .section-block__grid {
                grid-template-columns: 1fr;
            }
            .checkbox-grid {
                grid-template-columns: 1fr;
            }
        }
        .student-hero{margin-bottom:32px!important;border:1px solid var(--line)!important;border-left:5px solid var(--secondary)!important;border-radius:18px!important;background:#fff!important;color:var(--text)!important;box-shadow:var(--shadow-sm);padding:28px!important}
        .student-hero h2{color:var(--text)!important;font-size:32px!important;letter-spacing:0!important}
        .student-hero p{color:var(--muted-2)!important;font-weight:800!important}
        .student-hero .link-button{min-height:44px!important;padding:0 18px!important;border-radius:12px!important;background:var(--secondary)!important;border:1px solid var(--secondary)!important;color:#fff!important;box-shadow:none!important;text-decoration:none!important}
        .student-hero .link-button.secondary{background:var(--secondary-soft)!important;color:var(--secondary)!important;border-color:var(--secondary-soft)!important}
        .student-hero .link-button:hover{background:var(--secondary-hover)!important;color:#fff!important;transform:translateY(-1px)}
        .application-type-panel{
            border-radius:0!important;
            overflow:hidden!important;
            padding:24px!important;
            background:#fff!important;
            border:1px solid var(--line)!important;
            box-shadow:var(--shadow-sm)!important
        }
        .application-form{border-radius:26px!important;overflow:hidden!important;padding:24px!important;background:#fff!important;border:1px solid var(--line)!important;box-shadow:var(--shadow-sm)!important}
        .application-type-head,
        .application-form__header{
            margin:0 0 22px!important;
            padding:0 0 18px!important;
            border-bottom:1px solid var(--line)!important;
            background:#fff!important
        }
        .application-menu{gap:16px!important}
        .application-menu__item{border-radius:20px!important;background:#fff!important;box-shadow:var(--shadow-sm)!important}
        .application-menu__item.active{border-color:var(--secondary)!important;background:var(--secondary-soft)!important;color:var(--secondary)!important}
        .application-shell{gap:28px!important}
        .application-type-panel,
        .application-form{width:100%!important;max-width:none!important}
        .application-menu{grid-template-columns:repeat(3,minmax(240px,1fr))!important;width:100%!important}
        .application-grid{grid-template-columns:repeat(2,minmax(280px,1fr))!important;width:100%!important}
        .field-block--full{grid-column:1 / -1!important}
        .section-block{width:100%!important;padding:20px!important;background:#fff!important}
        .section-block__grid{grid-template-columns:repeat(2,minmax(280px,1fr))!important}
        .section-block__head{
            margin:0 0 18px!important;
            padding:0!important
        }
        @media (max-width: 900px){
            .application-menu,
            .application-grid,
            .section-block__grid{grid-template-columns:1fr!important}
        }
        .section-block{border-radius:22px!important;box-shadow:var(--shadow-sm)!important}
        .section-block__head{background:transparent!important;border-bottom:1px solid var(--line)!important}
        .section-block__tag{background:var(--secondary)!important;color:#fff!important}
        .choice-box,.consent-box{border-color:var(--line)!important;border-radius:16px!important}
        .document-checklist{display:grid;gap:14px}
        .document-group{padding:14px;border:1px solid var(--line);border-radius:18px;background:#fbfdff}
        .document-group.is-hidden{display:none!important}
        .document-group>strong{display:block;margin-bottom:12px;color:var(--secondary);font-size:12px;font-weight:900;letter-spacing:.04em;text-transform:uppercase}
        .document-group .checkbox-grid{grid-template-columns:repeat(2,minmax(220px,1fr));gap:10px}
        .document-option.is-hidden{display:none!important}
        @media (max-width: 900px){.document-group .checkbox-grid{grid-template-columns:1fr}}
        .status-pill--baru{background:var(--secondary-soft)!important;color:var(--secondary)!important}
        .status-pill--dalam_semakan{background:var(--warning-soft)!important;color:var(--warning)!important}
        .status-pill--diluluskan{background:var(--success-soft)!important;color:var(--success)!important}
        .status-pill--ditolak{background:var(--danger-soft)!important;color:var(--danger)!important}
        .application-shell,
        .application-menu,
        .application-grid,
        .section-block__grid{min-width:0!important}
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const typeInputs = Array.from(document.querySelectorAll('[data-withdrawal-type]'));
            const documentOptions = Array.from(document.querySelectorAll('[data-document-types]'));

            if (!typeInputs.length || !documentOptions.length) {
                return;
            }

            const syncDocumentOptions = () => {
                const selectedTypes = typeInputs
                    .filter((input) => input.checked)
                    .map((input) => input.value);

                documentOptions.forEach((option) => {
                    const allowedTypes = option.dataset.documentTypes.split('|');
                    const shouldShow = selectedTypes.some((type) => allowedTypes.includes(type));
                    const checkbox = option.querySelector('input[type="checkbox"]');
                    const fileInput = option.querySelector('input[type="file"]');

                    option.classList.toggle('is-hidden', !shouldShow);

                    if (!shouldShow && checkbox) {
                        checkbox.checked = false;
                    }

                    if (fileInput) {
                        fileInput.required = shouldShow;
                    }
                });

                documentOptions
                    .map((option) => option.closest('.document-group'))
                    .filter((group, index, groups) => group && groups.indexOf(group) === index)
                    .forEach((group) => {
                        const visibleOptions = Array.from(group.querySelectorAll('.document-option:not(.is-hidden)'));
                        group.classList.toggle('is-hidden', visibleOptions.length === 0);
                    });
            };

            typeInputs.forEach((input) => input.addEventListener('change', syncDocumentOptions));
            syncDocumentOptions();
        });
    </script>
@endpush
