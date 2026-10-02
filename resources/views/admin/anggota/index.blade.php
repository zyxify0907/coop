@extends('layouts.app')

@php
    $isStaffList = ($memberType ?? 'student') === 'staff';
    $pageTitle = $isStaffList ? 'Senarai Anggota Staff' : 'Senarai Anggota Student';
    $shareRoute = $isStaffList
        ? route('admin.saham.index', ['kategori' => 'staff'])
        : route('admin.saham.index', ['kategori' => 'pelajar']);
@endphp

@section('title', $pageTitle)
@section('page-title', $pageTitle)
@section('page-subtitle', 'Senarai anggota koperasi yang telah diluluskan oleh admin.')

@php
    $totalMembers = $members->total();
    $currentItems = $members->count();
    $pageLabel = $members->total() ? $members->firstItem().'-'.$members->lastItem() : '0-0';
    $latestApprovedDate = $latestApproved ? \Carbon\Carbon::parse($latestApproved)->format('d/m/Y') : '-';
@endphp

@section('content')
    <section class="anggota-hero">
        <div>
            <h2>{{ $pageTitle }}</h2>
            <p>Rekod anggota {{ $isStaffList ? 'staff' : 'student' }} yang telah diluluskan oleh admin dan sedia untuk dirujuk.</p>
        </div>
        <div class="anggota-hero__meta">
            <span class="badge success">Diluluskan</span>
            <strong>Terakhir: {{ $latestApprovedDate }}</strong>
        </div>
    </section>

    <div class="anggota-stats">
        <section class="panel anggota-stat">
            <span class="anggota-stat__label">Jumlah Rekod</span>
            <strong>{{ $totalMembers }}</strong>
            <small>Anggota {{ $isStaffList ? 'staff' : 'student' }} dalam senarai ini.</small>
        </section>
        <section class="panel anggota-stat">
            <span class="anggota-stat__label">Paparan Halaman</span>
            <strong>{{ $currentItems }}</strong>
            <small>{{ $pageLabel }} daripada {{ $totalMembers }} rekod.</small>
        </section>
        <section class="panel anggota-stat">
            <span class="anggota-stat__label">Status Data</span>
            <strong>{{ $totalMembers ? 'Tersusun' : 'Kosong' }}</strong>
            <small>Semua rekod yang dipaparkan adalah status diluluskan.</small>
        </section>
    </div>

    <section class="panel anggota-panel">
        <div class="anggota-panel__head">
            <div>
                <h2>{{ $pageTitle }}</h2>
                <p>{{ $totalMembers }} anggota {{ $isStaffList ? 'staff' : 'student' }} direkodkan di sini.</p>
            </div>
            <a class="saham-button" href="{{ $shareRoute }}">{{ $isStaffList ? 'Lihat Saham Staff' : 'Lihat Saham Student' }}</a>
        </div>

        <form class="anggota-search" method="GET" action="{{ route($isStaffList ? 'admin.anggota.staff' : 'admin.anggota.students') }}">
            <label for="search">Cari {{ $isStaffList ? 'Anggota Staff' : 'Anggota Student' }}</label>
            <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ $isStaffList ? 'Nama, no anggota staff, no KP atau jenis staff' : 'Nama, no anggota, no matrik, no KP atau program' }}">
            <button type="submit">Cari</button>
            @if (($filters['search'] ?? '') !== '')
                <a href="{{ route($isStaffList ? 'admin.anggota.staff' : 'admin.anggota.students') }}">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="anggota-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>{{ $isStaffList ? 'No Anggota Staff' : 'No Anggota Pelajar' }}</th>
                        @unless ($isStaffList)
                            <th>No Matrik</th>
                        @endunless
                        <th>No KP</th>
                        <th>{{ $isStaffList ? 'Jenis Staff' : 'Program' }}</th>
                        <th>Tarikh Daftar Ahli</th>
                        <th>Jumlah Saham</th>
                        <th>Status Permohonan</th>
                        <th>Status Akaun</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        @php
                            $data = $member->data_permohonan ?? [];
                            $memberName = $member->nama_pemohon ?: 'Tanpa Nama';
                            $initials = collect(explode(' ', $memberName))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: ($isStaffList ? 'AS' : 'AG');
                            $profile = $isStaffList ? $staffMembers->get($member->no_matrik) : $member->ahli;
                            $memberSaham = $isStaffList ? optional($profile)->sahamStaff : optional($profile)->saham;
                            $memberNumber = $isStaffList ? ($profile->no_anggota ?? $data['no_anggota'] ?? '-') : ($profile->no_anggota ?? '-');
                            $typeLabel = $isStaffList
                                ? (in_array($profile?->nric, $activeAdminNrics ?? [], true) ? 'Admin Pengurusan Sistem' : ($profile->staff_type_label ?? '-'))
                                : ($data['program_pengajian'] ?? $profile->program ?? '-');
                            $registeredDate = $isStaffList
                                ? (optional(optional($profile)->tarikh_mula)->format('d/m/Y') ?? (optional($member->tarikh_keputusan)->format('d/m/Y') ?? '-'))
                                : (optional(optional($profile)->tarikh_daftar)->format('d/m/Y') ?? (optional($member->tarikh_keputusan)->format('d/m/Y') ?? '-'));
                            $totalShare = (float) optional($memberSaham)->syer + (float) optional($memberSaham)->tambahan_saham;
                        @endphp
                        <tr>
                            <td>
                                <div class="member-cell">
                                    <span class="member-avatar">{{ $initials }}</span>
                                    <div>
                                        <strong>{{ $memberName }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td><span class="table-chip">{{ $memberNumber }}</span></td>
                            @unless ($isStaffList)
                                <td><span class="table-chip">{{ $member->no_matrik }}</span></td>
                            @endunless
                            <td>{{ $profile->nric ?? $data['no_kad_pengenalan'] ?? '-' }}</td>
                            <td>{{ $typeLabel }}</td>
                            <td>{{ $registeredDate }}</td>
                            <td><span class="money">{{ 'RM '.number_format($totalShare, 2) }}</span></td>
                            <td><span class="status-pill status-pill--diluluskan">Diluluskan</span></td>
                            <td>
                                <span class="status-pill {{ ($profile->status_aktif ?? false) ? 'status-pill--diluluskan' : 'status-pill--ditolak' }}">
                                    {{ ($profile->status_aktif ?? false) ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a class="action-link" href="{{ route('admin.anggota.show', $member) }}">Lihat Profil</a>
                                    <form method="POST" action="{{ route('admin.anggota.destroy', $member) }}" onsubmit="return confirm('Padam anggota ini? Rekod saham dan nombor anggota akan dibuang.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="action-delete" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isStaffList ? 9 : 10 }}">
                                <div class="empty-state">
                                    <strong>Belum ada anggota {{ $isStaffList ? 'staff' : 'student' }} yang diluluskan.</strong>
                                    <span>Rekod akan muncul di sini selepas permohonan anggota diluluskan oleh admin.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($members->hasPages())
            <div class="pagination-wrap">
                {{ $members->links() }}
            </div>
        @endif
        <dialog class="view-dialog" data-view-dialog aria-labelledby="memberViewDialogTitle">
            <div class="view-dialog__shell">
                <header class="view-dialog__head">
                    <div>
                        <span>Paparan Rekod</span>
                        <h2 id="memberViewDialogTitle">Profil Anggota</h2>
                    </div>
                    <button class="view-dialog__close" type="button" data-view-dialog-close aria-label="Tutup dialog">&times;</button>
                </header>
                <div class="view-dialog__body" data-view-dialog-body>
                    <iframe class="view-dialog__frame" data-view-dialog-frame title="Paparan profil anggota" scrolling="no" tabindex="0"></iframe>
                </div>
            </div>
        </dialog>
    </section>
@endsection

@push('styles')
<style>
    .content:has(> .anggota-hero){
        background:#F4F7FB;
    }

    .content > .anggota-hero{
        position:relative;
        display:flex;
        justify-content:space-between;
        align-items:flex-end;
        gap:18px;
        margin-bottom:16px !important;
        padding:24px 28px 22px 32px !important;
        border:1px solid #D7E2EF !important;
        border-left:6px solid #082F59 !important;
        border-radius:10px !important;
        background:#fff !important;
        background-image:none !important;
        box-shadow:0 6px 16px rgba(8,47,89,.03) !important;
        overflow:hidden;
    }
    .content > .anggota-hero::before{
        content:"";
        position:absolute;
        top:21px;
        left:32px;
        width:42px;
        height:3px;
        border-radius:999px;
        background:#ED1C2E;
    }
    .content > .anggota-hero h2{
        margin:18px 0 0 !important;
        font-size:30px !important;
        line-height:1.15 !important;
        color:#082F59 !important;
        font-weight:900 !important;
        letter-spacing:0 !important;
    }
    .content > .anggota-hero p{
        margin:8px 0 0 !important;
        color:#496487 !important;
        font-weight:800 !important;
        line-height:1.45 !important;
    }
    .anggota-hero__meta{
        display:grid;
        gap:7px;
        justify-items:end;
    }
    .anggota-hero__meta strong{
        font-size:14px;
        color:#496487;
        font-weight:900;
    }
    .anggota-hero .badge.success{
        border:1px solid #B8EFCB;
        background:#DDF8E7;
        color:#128A4A;
        font-weight:900;
    }
    .anggota-stats{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:14px;
        margin-bottom:16px;
    }
    .anggota-stat{
        position:relative;
        padding:18px 20px 16px 24px;
        border:1px solid #D7E2EF;
        border-radius:10px;
        background:#fff;
        display:grid;
        gap:7px;
        overflow:hidden;
        box-shadow:0 6px 16px rgba(8,47,89,.025);
    }
    .anggota-stat::before{
        content:"";
        position:absolute;
        inset:0 auto 0 0;
        width:5px;
        background:#082F59;
    }
    .anggota-stat:nth-child(2)::before{
        background:#0B5ED7;
    }
    .anggota-stat:nth-child(3)::before{
        background:#12A466;
    }
    .anggota-stat__label{
        font-size:12px;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.06em;
        color:#496487;
    }
    .anggota-stat strong{
        font-size:26px;
        line-height:1;
        color:#082F59;
        font-weight:900;
        letter-spacing:0;
    }
    .anggota-stat small{
        color:#496487;
        font-weight:700;
        line-height:1.5;
    }
    .anggota-panel{
        overflow:hidden;
        border:1px solid #D7E2EF;
        border-radius:10px;
        background:#fff;
        box-shadow:0 7px 18px rgba(8,47,89,.035);
    }
    .anggota-panel__head{
        position:relative;
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:14px;
        padding:24px 24px 18px;
        border-bottom:1px solid #D7E2EF;
        background:#fff !important;
        background-image:none !important;
    }
    .anggota-panel__head::before{
        content:"";
        position:absolute;
        top:16px;
        left:24px;
        width:40px;
        height:3px;
        border-radius:999px;
        background:#ED1C2E;
    }
    .anggota-panel__head h2{
        margin:12px 0 0;
        font-size:21px;
        line-height:1.2;
        color:#082F59;
        font-weight:900;
        letter-spacing:0;
    }
    .anggota-panel__head p{
        margin:5px 0 0;
        color:#496487;
        font-weight:700;
    }
    .anggota-search{
        display:flex;
        align-items:center;
        gap:9px;
        padding:10px 24px;
        border-bottom:1px solid #D7E2EF;
        background:#FBFDFF;
        flex-wrap:wrap;
    }
    .anggota-search label{
        display:inline-flex;
        align-items:center;
        min-height:34px;
        color:#496487;
        font-size:12px;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.06em;
        white-space:nowrap;
    }
    .anggota-search input{
        width:min(520px, 68vw);
        min-height:34px;
        border:1px solid #D7E2EF;
        border-radius:7px;
        padding:0 12px;
        background:#fff;
        color:#082F59;
        font:inherit;
        font-weight:700;
        box-shadow:inset 0 1px 0 rgba(8,47,89,.02);
    }
    .anggota-search input::placeholder{
        color:#6C82A1;
    }
    .anggota-search button,
    .anggota-search a{
        display:inline-flex;
        min-height:34px;
        align-items:center;
        justify-content:center;
        border-radius:7px;
        padding:0 14px;
        font-size:13px;
        font-weight:900;
        text-decoration:none;
    }
    .anggota-search button{
        border:1px solid #0B5ED7;
        background:#0B5ED7;
        color:#fff;
        box-shadow:0 8px 16px rgba(11,94,215,.16);
    }
    .anggota-search a{
        border:1px solid #D7E2EF;
        background:#fff;
        color:#082F59;
    }
    .saham-button{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:36px;
        padding:0 16px;
        border-radius:7px;
        font-size:13px;
        border:1px solid #0B5ED7;
        background:#0B5ED7;
        color:#fff;
        font-weight:900;
        text-decoration:none;
        white-space:nowrap;
        box-shadow:0 6px 14px rgba(11,94,215,.14);
        transition:background var(--transition-fast), box-shadow var(--transition-fast);
    }
    .saham-button:hover{
        background:#094FB7;
        color:#fff;
        box-shadow:0 10px 22px rgba(11,94,215,.18);
    }
    .table-wrap{
        overflow:auto;
        padding:0;
        background:#fff;
    }
    .anggota-table{
        width:100%;
        min-width:1180px;
        border-collapse:collapse;
        border-spacing:0;
    }
    .anggota-table thead th{
        padding:11px 16px;
        border-top:1px solid #D7E2EF;
        border-bottom:1px solid #D7E2EF;
        background:#F8FBFF;
        color:#496487;
        font-size:12px;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.06em;
        text-align:left;
    }
    .anggota-table tbody td{
        padding:10px 16px;
        border-top:0;
        border-bottom:1px solid #D7E2EF;
        background:#fff;
        vertical-align:middle;
        color:#082F59;
        font-weight:800;
        font-size:14px;
    }
    .anggota-table tbody td:first-child{
        border-left:0;
    }
    .anggota-table tbody td:last-child{
        border-right:0;
        min-width:116px;
    }
    .anggota-table tbody tr:hover td{
        background:#F8FBFF;
    }
    .member-cell{
        display:flex;
        align-items:center;
        gap:9px;
        min-width:0;
    }
    .member-avatar{
        width:36px;
        height:36px;
        border-radius:999px;
        display:grid;
        place-items:center;
        background:#EAF3FF;
        color:#0B5ED7;
        border:1px solid #CFE0F5;
        font-weight:900;
        font-size:12px;
        flex:0 0 auto;
    }
    .member-cell strong{
        display:block;
        font-size:14px;
        color:#082F59;
        font-weight:900;
    }
    .member-cell span{
        display:block;
        margin-top:2px;
        color:#496487;
        font-size:12px;
    }
    .table-chip,
    .money{
        display:inline-flex;
        align-items:center;
        gap:6px;
        min-height:28px;
        padding:0 10px;
        border-radius:999px;
        background:#F8FBFF;
        border:1px solid #D7E2EF;
        color:#082F59;
        font-weight:900;
        font-size:13px;
        white-space:nowrap;
    }
    .table-chip small{
        color:#496487;
        font-size:10px;
        font-weight:900;
        text-transform:uppercase;
    }
    .status-pill{
        display:inline-flex;
        align-items:center;
        min-height:28px;
        padding:0 10px;
        border-radius:999px;
        font-size:13px;
        font-weight:900;
        white-space:nowrap;
    }
    .status-pill--diluluskan{
        border:1px solid #B8EFCB;
        background:#DDF8E7;
        color:#128A4A;
    }
    .status-pill--ditolak{
        border:1px solid #F6C7C7;
        background:#FDECEC;
        color:#C4162A;
    }
    .action-link{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:34px;
        padding:0 12px;
        border-radius:7px;
        border:1px solid #CFE0F5;
        background:#EAF3FF;
        color:#0B5ED7;
        font-weight:900;
        font-size:13px;
        text-decoration:none;
        transition:background var(--transition-fast), box-shadow var(--transition-fast), transform var(--transition-fast);
    }
    .action-link:hover{
        background:#DBEAFF;
        box-shadow:0 8px 18px rgba(11,94,215,.10);
        transform:translateY(-1px);
    }
    .action-group{
        display:grid;
        align-items:start;
        gap:6px;
        justify-items:start;
    }
    .action-group form{
        margin:0;
    }
    .action-delete{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:98px;
        min-height:34px;
        padding:0 12px;
        border-radius:7px;
        border:1px solid #F6C7C7;
        background:#FDECEC;
        color:#C4162A;
        font:inherit;
        font-weight:900;
        cursor:pointer;
        font-size:13px;
        transition:background var(--transition-fast), color var(--transition-fast), box-shadow var(--transition-fast), transform var(--transition-fast);
    }
    .action-link{
        width:98px;
    }
    .action-delete:hover{
        background:#ED1C2E;
        color:#fff;
        box-shadow:0 8px 18px rgba(237,28,46,.14);
        transform:translateY(-1px);
    }
    .empty-state{
        padding:30px 18px;
        text-align:center;
        color:#496487;
        display:grid;
        gap:6px;
        justify-items:center;
    }
    .empty-state strong{
        color:#082F59;
        font-size:15px;
    }
    .pagination-wrap{
        display:flex;
        justify-content:center;
        padding:18px 24px 22px;
    }
    @media (max-width: 1000px){
        .anggota-hero{
            align-items:flex-start;
            flex-direction:column;
        }
        .anggota-hero__meta{
            justify-items:start;
        }
        .anggota-stats{
            grid-template-columns:1fr;
        }
    }

    .view-dialog{
        width:min(1280px,calc(100vw - 32px));
        max-width:1280px;
        height:min(90vh,920px);
        padding:0;
        border:0;
        border-radius:18px;
        background:transparent;
        overflow:hidden;
        box-shadow:0 28px 80px rgba(15,23,42,.28);
    }
    .view-dialog::backdrop{
        background:rgba(15,23,42,.45);
        backdrop-filter:blur(2px);
    }
    .view-dialog__shell{
        display:grid;
        grid-template-rows:auto minmax(0,1fr);
        width:100%;
        height:100%;
        min-height:0;
        background:#fff;
    }
    .view-dialog__head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:16px;
        padding:18px 22px;
        border-bottom:1px solid var(--line);
        background:#fff;
    }
    .view-dialog__head span{
        display:inline-flex;
        align-items:center;
        min-height:24px;
        padding:0 8px;
        border-radius:999px;
        background:var(--secondary-soft);
        color:var(--secondary);
        font-size:11px;
        font-weight:800;
        letter-spacing:.04em;
        text-transform:uppercase;
    }
    .view-dialog__head h2{
        margin:8px 0 0;
        font-size:22px;
        line-height:1.2;
        color:var(--text);
    }
    .view-dialog__close{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:42px;
        height:42px;
        border:1px solid var(--line);
        border-radius:10px;
        background:#fff;
        color:var(--text);
        font-size:24px;
        font-weight:700;
    }
    .view-dialog__frame{
        width:100%;
        height:100%;
        min-height:0;
        border:0;
        background:#f8fafc;
        overflow:auto;
    }
    @media (max-width: 760px){
        .view-dialog{
            width:calc(100vw - 16px);
            height:calc(100vh - 16px);
            border-radius:14px;
        }
        .view-dialog__head{
            padding:14px 16px;
        }
        .view-dialog__head h2{
            font-size:18px;
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
