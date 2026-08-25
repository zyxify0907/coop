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
            <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ $isStaffList ? 'Nama, no anggota, no pekerja, no KP, jenis staff atau email' : 'Nama, no anggota, no matrik, no KP atau program' }}">
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
                        <th>{{ $isStaffList ? 'No Anggota Staff' : 'No Anggota' }}</th>
                        @unless ($isStaffList)
                            <th>Identiti</th>
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
                            $identityLabel = $isStaffList ? 'No Pekerja' : 'No Matrik';
                            $typeLabel = $isStaffList ? ($profile->staff_type_label ?? '-') : ($data['program_pengajian'] ?? $profile->program ?? '-');
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
                                        <span>{{ $member->email ?: 'Email tiada' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="table-chip">{{ $memberNumber }}</span></td>
                            @unless ($isStaffList)
                                <td><span class="table-chip">{{ $member->no_matrik }}<small>{{ $identityLabel }}</small></span></td>
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
                                <a class="action-link" href="{{ route('admin.anggota.show', $member) }}">Lihat Profil</a>
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
    </section>
@endsection

@push('styles')
<style>
    .anggota-hero{
        display:flex;
        justify-content:space-between;
        align-items:flex-end;
        gap:20px;
        margin-bottom:24px;
        padding:28px;
        border:1px solid var(--line);
        border-left:5px solid var(--secondary);
        border-radius:18px;
        background:#fff;
        box-shadow:var(--shadow-sm);
    }
    .anggota-hero h2{
        margin:0;
        font-size:32px;
        color:var(--text);
    }
    .anggota-hero p{
        margin:10px 0 0;
        color:var(--muted-2);
        font-weight:800;
    }
    .anggota-hero__meta{
        display:grid;
        gap:8px;
        justify-items:end;
    }
    .anggota-hero__meta strong{
        font-size:14px;
        color:var(--muted-2);
    }
    .anggota-stats{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:20px;
        margin-bottom:24px;
    }
    .anggota-stat{
        padding:22px 24px;
        border-radius:18px;
        display:grid;
        gap:8px;
    }
    .anggota-stat__label{
        font-size:12px;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.04em;
        color:var(--muted-2);
    }
    .anggota-stat strong{
        font-size:28px;
        line-height:1;
        color:var(--text);
    }
    .anggota-stat small{
        color:var(--muted-2);
        font-weight:700;
        line-height:1.5;
    }
    .anggota-panel{
        overflow:hidden;
        border-radius:22px;
        background:#fff;
    }
    .anggota-panel__head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:16px;
        padding:22px 24px;
        border-bottom:1px solid var(--line);
        background:var(--surface-soft);
    }
    .anggota-panel__head h2{
        margin:0;
        font-size:22px;
    }
    .anggota-panel__head p{
        margin:6px 0 0;
        color:var(--muted-2);
    }
    .anggota-search{
        display:flex;
        align-items:end;
        gap:12px;
        padding:16px 24px;
        border-bottom:1px solid var(--line);
        background:#fff;
        flex-wrap:wrap;
    }
    .anggota-search label{
        display:grid;
        gap:6px;
        color:var(--muted);
        font-size:12px;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.04em;
    }
    .anggota-search input{
        width:min(460px, 70vw);
        min-height:42px;
        border:1px solid var(--line-strong);
        border-radius:10px;
        padding:0 13px;
        color:var(--text);
        font:inherit;
        font-weight:700;
    }
    .anggota-search button,
    .anggota-search a{
        display:inline-flex;
        min-height:42px;
        align-items:center;
        justify-content:center;
        border-radius:10px;
        padding:0 16px;
        font-size:14px;
        font-weight:900;
        text-decoration:none;
    }
    .anggota-search button{
        border:1px solid var(--secondary);
        background:var(--secondary);
        color:#fff;
    }
    .anggota-search a{
        border:1px solid var(--line-strong);
        background:#fff;
        color:var(--text);
    }
    .saham-button{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:42px;
        padding:0 18px;
        border-radius:12px;
        border:1px solid var(--secondary);
        background:var(--secondary);
        color:#fff;
        font-weight:900;
        text-decoration:none;
        white-space:nowrap;
        box-shadow:0 8px 18px rgba(30,64,175,.14);
        transition:background var(--transition-fast), box-shadow var(--transition-fast);
    }
    .saham-button:hover{
        background:var(--secondary-hover);
        color:#fff;
        box-shadow:0 10px 22px rgba(30,64,175,.16);
    }
    .table-wrap{
        overflow:auto;
    }
    .anggota-table{
        width:100%;
        min-width:1180px;
        border-collapse:separate;
        border-spacing:0;
    }
    .anggota-table thead th{
        padding:15px 18px;
        border-bottom:1px solid var(--line);
        background:#f8fafc;
        color:#475569;
        font-size:12px;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.04em;
        text-align:left;
    }
    .anggota-table tbody td{
        padding:18px;
        border-bottom:1px solid var(--line);
        vertical-align:middle;
        color:var(--text);
    }
    .anggota-table tbody tr:hover{
        background:#f8fbff;
    }
    .member-cell{
        display:flex;
        align-items:center;
        gap:12px;
        min-width:0;
    }
    .member-avatar{
        width:44px;
        height:44px;
        border-radius:999px;
        display:grid;
        place-items:center;
        background:var(--secondary-soft);
        color:var(--secondary);
        border:1px solid #dbeafe;
        font-weight:900;
        font-size:13px;
        flex:0 0 auto;
    }
    .member-cell strong{
        display:block;
        font-size:14px;
        color:var(--text);
    }
    .member-cell span{
        display:block;
        margin-top:3px;
        color:var(--muted-2);
        font-size:12px;
    }
    .table-chip,
    .money{
        display:inline-flex;
        align-items:center;
        gap:6px;
        min-height:32px;
        padding:0 12px;
        border-radius:999px;
        background:var(--surface-soft);
        border:1px solid var(--line);
        color:var(--text);
        font-weight:800;
        white-space:nowrap;
    }
    .table-chip small{
        color:var(--muted-2);
        font-size:10px;
        font-weight:900;
        text-transform:uppercase;
    }
    .status-pill{
        display:inline-flex;
        align-items:center;
        min-height:32px;
        padding:0 12px;
        border-radius:999px;
        font-size:12px;
        font-weight:900;
        white-space:nowrap;
    }
    .status-pill--diluluskan{
        background:var(--success-soft);
        color:var(--success);
    }
    .status-pill--ditolak{
        background:var(--danger-soft);
        color:var(--danger);
    }
    .action-link{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:38px;
        padding:0 14px;
        border-radius:10px;
        border:1px solid #dbeafe;
        background:var(--secondary-soft);
        color:var(--secondary);
        font-weight:900;
        text-decoration:none;
        transition:background var(--transition-fast), box-shadow var(--transition-fast), transform var(--transition-fast);
    }
    .action-link:hover{
        background:#dbeafe;
        box-shadow:0 8px 18px rgba(30,64,175,.10);
        transform:translateY(-1px);
    }
    .empty-state{
        padding:30px 18px;
        text-align:center;
        color:var(--muted-2);
        display:grid;
        gap:6px;
        justify-items:center;
    }
    .empty-state strong{
        color:var(--text);
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
</style>
@endpush
