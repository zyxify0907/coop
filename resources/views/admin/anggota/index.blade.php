@extends('layouts.app')

@section('title', 'Senarai Ahli')
@section('page-title', 'Anggota')
@section('page-subtitle', 'Senarai anggota koperasi yang telah diluluskan oleh admin.')

@php
    $totalMembers = $members->total();
    $currentItems = $members->count();
    $pageLabel = $members->total() ? $members->firstItem().'-'.$members->lastItem() : '0-0';
    $latestApproved = optional($members->first()?->tarikh_keputusan)->format('d/m/Y') ?? '-';
@endphp

@section('content')
    <section class="anggota-hero">
        <div>
            <h2>Anggota Koperasi</h2>
            <p>Rekod anggota yang telah diluluskan oleh admin dan sedia untuk dirujuk.</p>
        </div>
        <div class="anggota-hero__meta">
            <span class="badge success">Diluluskan</span>
            <strong>Terakhir: {{ $latestApproved }}</strong>
        </div>
    </section>

    <div class="anggota-stats">
        <section class="panel anggota-stat">
            <span class="anggota-stat__label">Jumlah Rekod</span>
            <strong>{{ $totalMembers }}</strong>
            <small>Anggota koperasi yang aktif dalam senarai ini.</small>
        </section>
        <section class="panel anggota-stat">
            <span class="anggota-stat__label">Paparan Halaman</span>
            <strong>{{ $pageLabel }}</strong>
            <small>{{ $currentItems }} rekod dipaparkan pada halaman semasa.</small>
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
                <h2>Senarai Anggota Diluluskan</h2>
                <p>{{ $totalMembers }} anggota koperasi direkodkan di sini.</p>
            </div>
            <a class="saham-button" href="{{ route('admin.saham.index', ['kategori' => 'pelajar']) }}">Lihat Saham</a>
        </div>

        <div class="table-wrap">
            <table class="anggota-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>No Anggota</th>
                        <th>No Matrik</th>
                        <th>No KP</th>
                        <th>Program</th>
                        <th>Tarikh Daftar Ahli</th>
                        <th>Saham</th>
                        <th>Status Permohonan</th>
                        <th>Status Pelajar</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        @php
                            $data = $member->data_permohonan ?? [];
                            $memberName = $member->nama_pemohon ?: 'Tanpa Nama';
                            $initials = collect(explode(' ', $memberName))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'AG';
                            $memberProfile = $member->ahli;
                            $memberSaham = optional($memberProfile)->saham;
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
                            <td><span class="table-chip">{{ $memberProfile->no_anggota ?? '-' }}</span></td>
                            <td><span class="table-chip">{{ $member->no_matrik }}</span></td>
                            <td>{{ $data['penama_nric'] ?? $memberProfile->nric ?? '-' }}</td>
                            <td>{{ $data['program_pengajian'] ?? $memberProfile->program ?? '-' }}</td>
                            <td>{{ optional(optional($memberProfile)->tarikh_daftar)->format('d/m/Y') ?? (optional($member->tarikh_keputusan)->format('d/m/Y') ?? '-') }}</td>
                            <td><span class="money">{{ 'RM '.number_format((float) optional($memberSaham)->syer, 2) }}</span></td>
                            <td><span class="status-pill status-pill--diluluskan">Diluluskan</span></td>
                            <td>
                                <span class="status-pill {{ ($memberProfile->status_aktif ?? false) ? 'status-pill--diluluskan' : 'status-pill--ditolak' }}">
                                    {{ ($memberProfile->status_aktif ?? false) ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                <a class="action-link" href="{{ route('admin.anggota.show', $member) }}">Lihat Profil</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <strong>Belum ada anggota yang diluluskan.</strong>
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
        min-height:32px;
        padding:0 12px;
        border-radius:999px;
        background:var(--surface-soft);
        border:1px solid var(--line);
        color:var(--text);
        font-weight:800;
        white-space:nowrap;
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
