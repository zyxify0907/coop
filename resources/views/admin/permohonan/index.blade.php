@extends('layouts.app')

@section('title', 'Senarai Permohonan')
@section('page-title', 'Senarai Permohonan')
@section('page-subtitle', 'Admin boleh semak, luluskan, tolak dan padam permohonan pelajar atau staff.')

@php
    $isStaffAudience = $selectedAudience === 'staff';
    $pageHeading = $isStaffAudience ? 'Senarai Permohonan Staff' : 'Senarai Permohonan Pelajar';
    $identityHeading = $isStaffAudience ? 'NO ANGGOTA' : 'NO MATRIK';
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
                <strong>{{ $summary['total'] ?? $applications->total() }} rekod</strong>
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
                <div class="summary-card"><span>Jumlah Permohonan</span><strong>{{ $summary['total'] ?? $applications->total() }}</strong></div>
                <div class="summary-card"><span>Baru</span><strong>{{ $summary['baru'] ?? 0 }}</strong></div>
                <div class="summary-card"><span>Diluluskan</span><strong>{{ $summary['diluluskan'] ?? 0 }}</strong></div>
                <div class="summary-card"><span>Ditolak</span><strong>{{ $summary['ditolak'] ?? 0 }}</strong></div>
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
                $identityValue = $isStaffAudience ? ($data['no_anggota'] ?? 'Belum dijana') : ($application->no_matrik ?: '-');
            @endphp

            <div class="request-item">
                <div class="request-row">
                    <div class="student-cell">
                        <span class="student-avatar">{{ $initials }}</span>
                        <div>
                            <strong>{{ $application->nama_pemohon }}</strong>
                        </div>
                    </div>

                    <div>
                        <span class="soft-code">{{ $identityValue }}</span>
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
                        @if ($role === 'admin' || ($role === 'staff' && ($user->staff_type ?? null) === \App\Models\Pekerja::SHARE_MANAGER_STAFF_TYPE))
                            <form method="POST" action="{{ route('admin.permohonan.destroy', $application) }}" onsubmit="return confirm('Padam permohonan ini? Tindakan ini tidak boleh dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                <button class="mini-button mini-button--delete" type="submit">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state" style="padding:28px">Tiada permohonan {{ $selectedAudience === 'staff' ? 'staff' : 'pelajar' }} untuk paparan ini.</div>
        @endforelse
        </section>

        <div class="panel-pad">{{ $applications->links() }}</div>

        <dialog class="view-dialog" data-view-dialog aria-labelledby="viewDialogTitle">
            <div class="view-dialog__shell">
                <header class="view-dialog__head">
                    <div>
                        <span>Paparan Rekod</span>
                        <h2 id="viewDialogTitle">Butiran Permohonan</h2>
                    </div>
                    <button class="view-dialog__close" type="button" data-view-dialog-close aria-label="Tutup dialog">&times;</button>
                </header>
                <div class="view-dialog__body" data-view-dialog-body>
                    <iframe class="view-dialog__frame" data-view-dialog-frame title="Paparan butiran rekod" scrolling="no" tabindex="0"></iframe>
                </div>
            </div>
        </dialog>
    </div>
@endsection

@push('styles')
    <style>
        .page-heading {
            display: none;
        }

        .applications-wrap {
            display: grid;
            gap: 20px;
            color: #082F59;
        }

        .content:has(> .applications-wrap) {
            background: #F4F7FB;
        }

        .content .applications-wrap .applications-hero {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            min-height: 150px !important;
            margin: 0 0 24px !important;
            padding: 30px 34px 30px 46px !important;
            border: 1px solid #D6E2F0 !important;
            border-left: 8px solid #082F59 !important;
            border-radius: 14px !important;
            background: #fff !important;
            box-shadow: 0 10px 28px rgba(8, 47, 89, .06) !important;
            box-sizing: border-box;
            overflow: hidden;
        }

        .content .applications-wrap .applications-hero::before {
            content: "";
            position: absolute;
            top: 24px;
            left: 46px;
            width: 52px;
            height: 4px;
            border-radius: 999px;
            background: #ED1C2E;
        }

        .content .applications-wrap .applications-hero .hero-kicker {
            display: none;
        }

        .content .applications-wrap .applications-hero h1 {
            margin: 16px 0 0 !important;
            color: #082F59 !important;
            font-size: 32px !important;
            line-height: 1.12 !important;
            letter-spacing: 0;
            font-weight: 900 !important;
        }

        .content .applications-wrap .applications-hero p {
            max-width: 720px;
            margin: 8px 0 0 !important;
            color: #496487 !important;
            font-size: 15px;
            line-height: 1.45;
            font-weight: 700 !important;
        }

        .hero-meta {
            display: grid;
            gap: 7px;
            justify-items: end;
            padding-top: 40px;
            color: #082F59;
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
            background: #DCFCE7;
            color: #15803D;
            font-size: 13px;
            font-weight: 900;
        }

        .application-board {
            overflow: hidden;
            border: 1px solid #D7E2EF;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(8, 47, 89, .035);
        }

        .board-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 24px 28px;
            border-bottom: 1px solid #D7E2EF;
        }

        .board-head h2::before {
            content: "";
            display: block;
            width: 34px;
            height: 4px;
            margin-bottom: 10px;
            border-radius: 999px;
            background: #ED1C2E;
        }

        .board-head h2 {
            margin: 0;
            color: #082F59;
            font-size: 24px;
            line-height: 1.2;
            font-weight: 900;
        }

        .board-head p {
            margin: 6px 0 0;
            color: #31517D;
            font-size: 15px;
            font-weight: 650;
        }

        .switch-group {
            display: inline-flex;
            gap: 10px;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: #fff;
        }
        .switch-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 22px;
            border: 1px solid #9EC5FF !important;
            border-radius: 6px !important;
            color: #0B5ED7 !important;
            background: #fff !important;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
        }
        .switch-button.is-active {
            border-color: #0B5ED7 !important;
            background: #0B5ED7 !important;
            color: #fff !important;
        }
        .request-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            padding: 20px 28px;
        }
        .summary-card {
            position: relative;
            overflow: hidden;
            border: 1px solid #D7E2EF;
            border-left: 6px solid #082F59;
            border-radius: 8px;
            padding: 16px 18px;
            background: #fff;
            box-shadow: 0 8px 18px rgba(8, 47, 89, .03);
        }
        .summary-card:nth-child(2) { border-left-color: #0B5ED7; }
        .summary-card:nth-child(3) { border-left-color: #16A34A; }
        .summary-card:nth-child(4) { border-left-color: #ED1C2E; }
        .summary-card span {
            display: block;
            color: #082F59;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .summary-card strong {
            display: block;
            margin-top: 7px;
            color: #082F59;
            font-size: 30px;
            line-height: 1;
            font-weight: 900;
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            align-items: end;
            margin: 2px 28px 20px !important;
            padding: 18px 20px !important;
            border: 1px solid #D7E2EF !important;
            border-radius: 10px;
            background: #FBFDFF !important;
        }
        .field-inline {
            display: grid;
            gap: 8px;
        }
        .field-inline label {
            font-size: 13px;
            font-weight: 800;
            color: #082F59;
        }
        .field-inline select,
        .note-form textarea {
            width: 100%;
            min-height: 42px;
            border: 1px solid #C8D6E7;
            border-radius: 6px;
            padding: 10px 12px;
            background: #fff;
            color: #102033;
            font: inherit;
            font-size: 14px;
            font-weight: 650;
        }
        .field-inline--actions {
            display: flex;
            justify-content: flex-start;
        }
        .field-inline--actions .button {
            background: #0B5ED7 !important;
            color: #fff !important;
            border-radius: 6px !important;
            box-shadow: none !important;
        }
        .request-table {
            overflow: hidden;
            border: 1px solid #D7E2EF;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(8, 47, 89, .035);
        }
        .request-item {
            border-bottom: 1px solid #DCE5F0;
        }
        .request-row {
            display: grid;
            grid-template-columns: minmax(260px, 1.3fr) minmax(120px, .5fr) minmax(130px, .55fr) minmax(180px, .8fr) minmax(120px, .5fr) minmax(100px, .4fr) minmax(160px, .6fr);
            gap: 18px;
            align-items: center;
            padding: 16px 26px;
        }
        .request-row--head {
            min-height: 56px;
            padding: 0 26px;
            background: #F8F9FA;
            color: #082F59;
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
            display: none;
        }
        .student-cell strong {
            display: block;
            font-size: 15px;
            color: #082F59;
            font-weight: 900;
        }
        .student-cell span:not(.student-avatar) {
            display: block;
            margin-top: 4px;
            color: #526987;
            font-size: 12px;
        }
        .soft-code,
        .type-pill {
            display: inline-flex;
            align-items: center;
            min-height: 26px;
            border-radius: 6px;
            padding: 0 10px;
            background: #F1F6FC;
            color: #082F59;
            font-size: 12px;
            font-weight: 750;
        }
        .date-cell {
            color: #31517D;
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
            border-radius: 6px;
            padding: 0 14px;
            font-size: 13px;
            font-weight: 800;
        }
        .mini-button--review {
            background: #E8F0FE;
            color: #1A73E8;
        }
        .mini-button--view {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #0B5ED7 !important;
            border-color: #0B5ED7 !important;
            color: #fff !important;
        }
        .mini-button--view { text-decoration: none; }
        .mini-button--approve {
            background: #ecfdf5;
            color: #047857;
        }
        .mini-button--reject,
        .mini-button--delete {
            background: #FEF2F2 !important;
            border-color: #FECACA !important;
            color: #B91C1C !important;
        }

        .status-pill {
            border-radius: 999px;
            font-weight: 900;
        }

        .view-dialog {
            width: min(1280px, calc(100vw - 32px));
            max-width: 1280px;
            height: min(90vh, 920px);
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

            .content .applications-wrap .applications-hero {
                min-height: 0 !important;
                padding: 24px 22px 24px 32px !important;
            }

            .content .applications-wrap .applications-hero::before {
                top: 20px;
                left: 32px;
                width: 44px;
                height: 3px;
            }

            .content .applications-wrap .applications-hero h1 {
                font-size: 26px !important;
            }

            .hero-meta {
                justify-items: start;
                padding-top: 0;
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
