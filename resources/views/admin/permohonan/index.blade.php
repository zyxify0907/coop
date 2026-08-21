@extends('layouts.app')

@section('title', 'Senarai Permohonan')
@section('page-title', 'Senarai Permohonan')
@section('page-subtitle', 'Admin boleh semak, luluskan, tolak dan padam permohonan pelajar atau staff.')

@php
    $isStaffAudience = $selectedAudience === 'staff';
    $pageHeading = $isStaffAudience ? 'Senarai Permohonan Staff' : 'Senarai Permohonan Pelajar';
    $identityHeading = $isStaffAudience ? 'NO PEKERJA' : 'NO MATRIK';
@endphp

@section('content')
    <div class="applications-wrap">
        <section class="applications-hero">
            <div>
                <span class="hero-kicker">KOPERASI POLITEKNIK BESUT</span>
                <h1>Senarai Permohonan</h1>
                <p>Semak permohonan pelajar dan staff, urus kelulusan, catatan dan rekod yang masuk dalam satu paparan.</p>
            </div>

            <div class="hero-meta">
                <span>{{ now()->format('d M Y') }}</span>
                <strong>{{ $applications->total() }} rekod</strong>
            </div>
        </section>

        <section class="panel application-board">
            <div class="board-head">
                <div>
                    <h2>{{ $pageHeading }}</h2>
                    <p>Pilih paparan pemohon di bawah untuk semak rekod yang masuk.</p>
                </div>

                <div class="switch-group" role="tablist" aria-label="Jenis pemohon">
                    <a class="switch-button {{ $selectedAudience === 'pelajar' ? 'is-active' : '' }}" href="{{ route('admin.permohonan.index', array_merge(request()->query(), ['pemohon' => 'pelajar'])) }}">Pelajar</a>
                    <a class="switch-button {{ $selectedAudience === 'staff' ? 'is-active' : '' }}" href="{{ route('admin.permohonan.index', array_merge(request()->query(), ['pemohon' => 'staff'])) }}">Staff</a>
                </div>
            </div>

            <div class="request-summary">
                <div class="summary-card"><span>Jumlah Permohonan</span><strong>{{ $applications->total() }}</strong></div>
                <div class="summary-card"><span>Baru</span><strong>{{ $applications->getCollection()->where('status', 'baru')->count() }}</strong></div>
                <div class="summary-card"><span>Diluluskan</span><strong>{{ $applications->getCollection()->where('status', 'diluluskan')->count() }}</strong></div>
                <div class="summary-card"><span>Ditolak</span><strong>{{ $applications->getCollection()->where('status', 'ditolak')->count() }}</strong></div>
            </div>

            <form method="GET" action="{{ route('admin.permohonan.index') }}" class="filters-grid">
                <input type="hidden" name="pemohon" value="{{ $selectedAudience }}">
                <div class="field-inline">
                    <label for="jenis">Jenis Permohonan</label>
                    <select id="jenis" name="jenis">
                        <option value="">Semua jenis</option>
                        @foreach ($types as $key => $type)
                            <option value="{{ $key }}" @selected($selectedJenis === $key)>{{ $type['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-inline">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-inline field-inline--actions">
                    <button class="button" type="submit">Tapis</button>
                </div>
            </form>
        </section>

        <section class="panel request-table" style="overflow-x:auto">
        <div class="request-row request-row--head">
            <div>NAMA</div>
            <div>{{ $identityHeading }}</div>
            <div>NO. KP</div>
            <div>JENIS</div>
            <div>STATUS</div>
            <div>TARIKH</div>
            <div>TINDAKAN</div>
        </div>

        @forelse ($applications as $application)
            @php
                $data = $application->data_permohonan ?? [];
                $amountFields = ['amaun_tambahan', 'amaun_dipohon', 'syer_semasa', 'yuran_anggota', 'modal_saham', 'saham_dipohon', 'jumlah_dipohon'];
                $initials = collect(explode(' ', $application->nama_pemohon))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'ST';
                $noKp = $data['no_kad_pengenalan'] ?? $data['no_kp'] ?? $application->ahli?->nric ?? '-';
            @endphp

            <div class="request-item">
                <div class="request-row">
                    <div class="student-cell">
                        <span class="student-avatar">{{ $initials }}</span>
                        <div>
                            <strong>{{ $application->nama_pemohon }}</strong>
                            <span>{{ $application->email ?: 'Email tiada' }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="soft-code">{{ $application->no_matrik ?: '-' }}</span>
                    </div>

                    <div>
                        <span class="soft-code">{{ $noKp }}</span>
                    </div>

                    <div>
                        <span class="type-pill">{{ $types[$application->jenis]['label'] ?? ucfirst($application->jenis) }}</span>
                    </div>

                    <div>
                        <span class="status-pill status-pill--{{ $application->status }}">{{ str_replace('_', ' ', ucfirst($application->status)) }}</span>
                    </div>

                    <div class="date-cell">{{ optional($application->tarikh_permohonan)->format('d/m/Y') ?? '-' }}</div>

                    <div class="row-actions">
                        <a class="mini-button mini-button--view" href="{{ route('admin.permohonan.show', $application) }}">Lihat</a>
                        <form method="POST" action="{{ route('admin.permohonan.destroy', $application) }}" onsubmit="return confirm('Padam permohonan ini? Tindakan ini tidak boleh dibatalkan.');">
                            @csrf
                            @method('DELETE')
                            <button class="mini-button mini-button--delete" type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state" style="padding:28px">Tiada permohonan {{ $selectedAudience === 'staff' ? 'staff' : 'pelajar' }} untuk paparan ini.</div>
        @endforelse
        </section>

        <div class="panel-pad">{{ $applications->links() }}</div>
    </div>
@endsection

@push('styles')
    <style>
        .page-heading {
            display: none;
        }

        .applications-wrap {
            display: grid;
            gap: 22px;
        }

        .applications-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            min-height: 176px;
            padding: 34px 38px;
            border: 1px solid var(--line);
            border-left: 4px solid var(--secondary);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 18px 38px rgba(15, 23, 42, .06);
        }

        .hero-kicker {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 16px;
            border-radius: 999px;
            background: var(--secondary-soft);
            color: var(--secondary);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .04em;
        }

        .applications-hero h1 {
            margin: 22px 0 10px;
            color: #020617;
            font-size: clamp(30px, 3vw, 44px);
            line-height: 1.05;
            letter-spacing: 0;
        }

        .applications-hero p {
            max-width: 720px;
            margin: 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.6;
        }

        .hero-meta {
            display: grid;
            gap: 7px;
            justify-items: end;
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
        }

        .hero-meta strong {
            display: inline-flex;
            align-items: center;
            min-height: 32px;
            padding: 0 14px;
            border-radius: 999px;
            background: var(--success-soft);
            color: var(--success);
            font-size: 13px;
        }

        .application-board {
            overflow: hidden;
            background: #fff;
        }

        .board-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 26px 30px;
            border-bottom: 1px solid var(--line);
        }

        .board-head h2 {
            margin: 0;
            color: #020617;
            font-size: 24px;
            line-height: 1.2;
        }

        .board-head p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 15px;
        }

        .switch-group {
            display: inline-flex;
            gap: 6px;
            padding: 6px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
        }
        .switch-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 22px;
            border-radius: 10px;
            color: #334155;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
        }
        .switch-button.is-active {
            background: var(--secondary);
            color: #fff;
        }
        .request-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            padding: 24px 30px;
        }
        .summary-card {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 18px 20px;
            background: #fff;
            box-shadow: 0 14px 30px rgba(15, 23, 42, .04);
        }
        .summary-card span {
            display: block;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .summary-card strong {
            display: block;
            margin-top: 8px;
            color: #020617;
            font-size: 30px;
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            align-items: end;
            padding: 24px 30px;
            border-top: 1px solid var(--line);
            background: #f8fafc;
        }
        .field-inline {
            display: grid;
            gap: 8px;
        }
        .field-inline label {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }
        .field-inline select,
        .note-form textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 13px 14px;
            background: #fff;
            color: #020617;
            font: inherit;
        }
        .field-inline--actions {
            display: flex;
            justify-content: flex-start;
        }
        .request-table {
            background: #fff;
            box-shadow: 0 18px 38px rgba(15, 23, 42, .06);
        }
        .request-item {
            border-bottom: 1px solid #eef2f7;
        }
        .request-row {
            display: grid;
            grid-template-columns: minmax(260px, 1.3fr) minmax(120px, .5fr) minmax(130px, .55fr) minmax(180px, .8fr) minmax(120px, .5fr) minmax(100px, .4fr) minmax(160px, .6fr);
            gap: 18px;
            align-items: center;
            padding: 22px 26px;
        }
        .request-row--head {
            min-height: 56px;
            padding: 0 26px;
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .04em;
        }
        .student-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .student-avatar {
            width: 46px;
            height: 46px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: var(--secondary-soft);
            color: var(--secondary);
            border: 1px solid #dbeafe;
            font-size: 12px;
            font-weight: 900;
            flex: 0 0 auto;
        }
        .student-cell strong {
            display: block;
            font-size: 15px;
            color: #0f172a;
        }
        .student-cell span:not(.student-avatar) {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
        }
        .soft-code,
        .type-pill {
            display: inline-flex;
            align-items: center;
            min-height: 26px;
            border-radius: 8px;
            padding: 0 10px;
            background: #f8fafc;
            color: #0f172a;
            font-size: 12px;
            font-weight: 700;
        }
        .date-cell {
            color: #334155;
            font-size: 13px;
            font-weight: 700;
        }
        .row-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .mini-button {
            min-width: 84px;
            min-height: 34px;
            border: 0;
            border-radius: 10px;
            padding: 0 14px;
            font-size: 13px;
            font-weight: 800;
        }
        .mini-button--review {
            background: var(--secondary-soft);
            color: var(--secondary);
        }
        .mini-button--view {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--secondary-soft);
            color: var(--secondary);
        }
        .mini-button--view { text-decoration: none; }
        .mini-button--approve {
            background: #ecfdf5;
            color: #047857;
        }
        .mini-button--reject,
        .mini-button--delete {
            background: var(--danger-soft);
            color: var(--danger);
        }
        @media (max-width: 1200px) {
            .request-row,
            .request-row--head {
                grid-template-columns: minmax(230px, 1.1fr) minmax(110px, .5fr) minmax(120px, .5fr) minmax(150px, .7fr) minmax(110px, .5fr) minmax(90px, .4fr) minmax(140px, .6fr);
            }
        }
        @media (max-width: 900px) {
            .applications-hero,
            .board-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .applications-hero {
                padding: 28px 24px;
            }

            .hero-meta {
                justify-items: start;
            }

            .request-summary,
            .filters-grid {
                grid-template-columns: 1fr;
                padding: 20px;
            }
            .request-row--head {
                display: none;
            }
            .request-row {
                grid-template-columns: 1fr;
                gap: 10px;
                padding: 18px;
            }
            .row-actions {
                display: flex;
                flex-wrap: wrap;
            }
        }
    </style>
@endpush
