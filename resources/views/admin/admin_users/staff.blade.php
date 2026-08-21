@extends('layouts.app')

@section('title', 'Manage Staff')
@section('page-title', 'Manage Staff')
@section('page-subtitle', 'Kemaskini maklumat staff yang sedia ada dalam sistem.')

@section('content')
    <section class="staff-page-hero">
        <div>
            <h1>Manage <span class="brand-red">Staff</span></h1>
            <p>Kemaskini maklumat staff yang sedia ada dalam sistem.</p>
        </div>
        <div class="staff-page-hero__meta">
            <span>{{ $staff->count() }} rekod</span>
        </div>
    </section>

    @if (session('status'))
        <div class="staff-alert success" role="alert">
            <span>{{ session('status') }}</span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="staff-alert danger" role="alert">
            <span>{{ $errors->first() }}</span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    <section class="panel staff-list-panel">
        <div class="staff-list-head">
            <div>
                <h2>Senarai <span class="brand-red">Staff</span></h2>
                <p>Paparan lengkap mengikut maklumat yang tersedia dalam jadual staff.</p>
            </div>
            <div class="staff-list-actions">
                <span class="record-badge">{{ $staff->count() }} rekod</span>
                <a href="{{ route('admin.users.create', ['type' => 'staff']) }}" class="add-staff-button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Tambah Staff
                </a>
            </div>
        </div>

        <form class="staff-search-bar" method="GET" action="{{ route('admin.users.staff') }}">
            <div class="staff-search-field">
                <label for="search">Cari Staff</label>
                <input
                    id="search"
                    name="search"
                    type="search"
                    value="{{ $search ?? request('search') }}"
                    placeholder="Nama, no pekerja, No. KP atau jenis staff">
            </div>
            <div class="staff-search-actions">
                <button class="staff-search-button" type="submit">Cari</button>
                @if (($search ?? request('search')))
                    <a class="staff-reset-button" href="{{ route('admin.users.staff') }}">Reset</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>No Pekerja</th>
                        <th>No. KP</th>
                        <th>Jenis Staff</th>
                        <th>No Telefon</th>
                        <th>Email</th>
                        <th>Tarikh Mula</th>
                        <th>Elaun</th>
                        <th>Status</th>
                        <th class="actions-col">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $member)
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="avatar">
                                        {{ strtoupper(substr($member->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="user-name">{{ $member->nama }}</div>
                                        <div class="user-sub">{{ $member->staff_type_label }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="no-pekerja-cell"><code class="badge-code">{{ $member->no_pekerja }}</code></td>
                            <td><span class="mono">{{ $member->nric ?? '-' }}</span></td>
                            <td>{{ $member->staff_type_label }}</td>
                            <td>{{ $member->no_tel ?? '-' }}</td>
                            <td>{{ $member->email ?? '-' }}</td>
                            <td>{{ optional($member->tarikh_mula)->format('d/m/Y') ?? '-' }}</td>
                            <td>RM {{ number_format((float) $member->kadar_elaun, 2) }}</td>
                            <td>
                                <span class="status-badge {{ $member->status_aktif ? 'is-active' : 'is-inactive' }}">
                                    {{ $member->status_aktif ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a class="edit-button" href="{{ route('admin.users.edit', ['type' => 'staff', 'id' => $member->id_pekerja]) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.users.destroy', ['type' => 'staff', 'id' => $member->id_pekerja]) }}" onsubmit="return confirm('Padam staff ini? Tindakan ini tidak boleh dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="delete-button" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <strong>Tiada rekod staff.</strong>
                                    <span>Klik Tambah Staff untuk daftar rekod baru.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .page-heading{display:none}
    .staff-page-hero{
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
    .staff-page-hero h1{
        margin:0;
        color:var(--text);
        font-size:38px;
        line-height:1.15;
        letter-spacing:0;
        font-weight:900;
    }
    .staff-page-hero p{
        margin:14px 0 0;
        color:var(--muted-2);
        font-size:16px;
        font-weight:800;
    }
    .brand-red{color:var(--primary)}
    .staff-page-hero__meta{display:flex;align-items:center;justify-content:flex-end;flex:0 0 auto}
    .staff-page-hero__meta span{
        display:inline-flex;
        min-height:34px;
        align-items:center;
        justify-content:center;
        border-radius:999px;
        background:var(--secondary-soft);
        color:var(--secondary);
        padding:0 14px;
        font-size:13px;
        font-weight:900;
    }
    .staff-alert{display:flex;align-items:center;gap:12px;padding:14px 20px;border-radius:14px;margin-bottom:20px;border:1px solid transparent;font-weight:800}
    .staff-alert.success{background:var(--success-soft);border-color:#bbf7d0;color:var(--success)}
    .staff-alert.danger{background:var(--danger-soft);border-color:var(--danger-soft);color:var(--danger)}
    .alert-close{margin-left:auto;border:0;background:none;font-size:22px;cursor:pointer;color:inherit}
    .staff-list-panel{
        overflow:hidden;
        border:1px solid var(--line);
        border-radius:22px!important;
        background:#fff;
        box-shadow:0 14px 34px rgba(15,23,42,.055)!important;
    }
    .staff-list-head{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:20px;
        flex-wrap:wrap;
        background:#fff;
        border-bottom:1px solid var(--line);
        padding:24px 28px;
    }
    .staff-list-head h2{
        margin:0;
        color:var(--text);
        font-size:24px;
        line-height:1.2;
        font-weight:900;
        letter-spacing:0;
    }
    .staff-list-head p{
        margin:8px 0 0;
        color:var(--muted-2);
        font-size:14px;
        font-weight:700;
    }
    .staff-list-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
    .record-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:38px;
        border:1px solid #bbf7d0;
        border-radius:999px;
        background:var(--success-soft);
        color:var(--success);
        padding:0 14px;
        font-size:13px;
        font-weight:900;
    }
    .record-badge::before{
        content:'';
        width:8px;
        height:8px;
        margin-right:8px;
        border-radius:999px;
        background:var(--success);
    }
    .add-staff-button{
        display:inline-flex;
        min-height:40px;
        align-items:center;
        justify-content:center;
        gap:8px;
        border-radius:12px;
        background:var(--secondary);
        color:#fff;
        padding:0 16px;
        text-decoration:none;
        font-size:14px;
        font-weight:900;
        box-shadow:0 10px 22px rgba(30,64,175,.16);
    }
    .add-staff-button:hover{background:var(--secondary-hover);color:#fff;box-shadow:0 12px 26px rgba(30,64,175,.22)}
    .staff-search-bar{
        display:flex;
        align-items:end;
        gap:14px;
        padding:18px 28px;
        border-bottom:1px solid var(--line);
        background:var(--surface-soft);
        flex-wrap:wrap;
    }
    .staff-search-field{
        display:grid;
        gap:7px;
        flex:1 1 320px;
        min-width:260px;
    }
    .staff-search-field label{
        color:var(--muted);
        font-size:12px;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.03em;
    }
    .staff-search-field input{
        width:100%;
        min-height:42px;
        padding:0 14px;
        border:1px solid var(--line-strong);
        border-radius:10px;
        background:#fff;
        color:var(--text);
        font:inherit;
        outline:0;
    }
    .staff-search-field input:focus{
        border-color:var(--secondary);
        box-shadow:0 0 0 3px rgba(36,83,166,.12);
    }
    .staff-search-actions{
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }
    .staff-search-button,
    .staff-reset-button{
        display:inline-flex;
        min-height:42px;
        align-items:center;
        justify-content:center;
        padding:0 18px;
        border-radius:10px;
        font-size:14px;
        font-weight:900;
        text-decoration:none;
    }
    .staff-search-button{
        border:1px solid var(--secondary);
        background:var(--secondary);
        color:#fff;
        box-shadow:0 10px 22px rgba(30,64,175,.16);
    }
    .staff-search-button:hover{
        background:var(--secondary-hover);
        border-color:var(--secondary-hover);
    }
    .staff-reset-button{
        border:1px solid var(--line-strong);
        background:#fff;
        color:var(--text);
    }
    .staff-reset-button:hover{
        background:var(--surface-soft);
        color:var(--text);
    }
    .table-responsive{overflow-x:auto;padding:0}
    .staff-table{width:100%;border-collapse:separate;border-spacing:0;min-width:1080px}
    .staff-table th{
        padding:14px 16px;
        text-align:left;
        font-size:12px;
        font-weight:900;
        color:var(--muted);
        text-transform:uppercase;
        letter-spacing:.03em;
        border-bottom:1px solid var(--line);
        background:var(--surface-soft);
        white-space:nowrap;
    }
    .staff-table td{
        padding:16px;
        border-bottom:1px solid var(--line);
        vertical-align:middle;
        font-size:14px;
        color:var(--text);
    }
    .staff-table tbody tr:hover{background:#f8fafc}
    .user-cell{display:flex;align-items:center;gap:12px;min-width:220px}
    .avatar{
        width:42px;
        height:42px;
        border-radius:999px;
        display:flex;
        align-items:center;
        justify-content:center;
        color:var(--secondary);
        background:var(--secondary-soft);
        border:1px solid #dbeafe;
        font-size:13px;
        font-weight:900;
        flex:0 0 auto;
    }
    .user-name{font-weight:900;color:var(--text)}
    .user-sub{margin-top:3px;font-size:12px;color:var(--muted-2);font-weight:700}
    .mono{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono",monospace}
    .no-pekerja-cell{white-space:nowrap}
    .badge-code{display:inline-flex;white-space:nowrap;background:#f1f5f9;padding:5px 10px;border-radius:8px;color:#334155;font-weight:900}
    .status-badge{display:inline-flex;min-height:28px;align-items:center;border-radius:999px;padding:0 11px;font-size:12px;font-weight:900}
    .status-badge.is-active{background:var(--success-soft);color:var(--success)}
    .status-badge.is-inactive{background:var(--danger-soft);color:var(--danger)}
    .action-buttons{display:flex;gap:8px;align-items:center;justify-content:flex-end;flex-wrap:nowrap}
    .action-buttons form{margin:0}
    .edit-button,.delete-button{
        display:inline-flex;
        width:76px;
        min-height:34px;
        align-items:center;
        justify-content:center;
        border-radius:10px;
        border:0;
        text-decoration:none;
        font-weight:900;
        font-size:13px;
        cursor:pointer;
    }
    .edit-button{background:var(--secondary-soft);color:var(--secondary)}
    .edit-button:hover{background:var(--secondary);color:#fff}
    .delete-button{background:var(--danger-soft);color:var(--danger)}
    .delete-button:hover{background:var(--danger);color:#fff}
    .empty-state{display:grid;gap:6px;padding:40px 20px;text-align:center;color:var(--muted-2)}
    .empty-state strong{color:var(--text)}
    @media (max-width:640px){
        .staff-page-hero{align-items:flex-start;flex-direction:column;padding:24px}
        .staff-page-hero h1{font-size:30px}
        .staff-list-head{align-items:flex-start;padding:22px}
        .staff-search-bar{padding:16px 22px}
        .staff-search-field{min-width:100%}
        .staff-list-actions,.add-staff-button{width:100%}
        .table-responsive{overflow-x:auto}
    }
</style>
@endpush
