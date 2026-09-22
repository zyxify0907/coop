@extends('layouts.app')

@section('title', $heroTitle ?? 'Manage Staff')
@section('page-title', $heroTitle ?? 'Manage Staff')
@section('page-subtitle', $heroSubtitle ?? 'Kemaskini maklumat staff yang sedia ada dalam sistem.')

@php
    $showWorkerFields = $showWorkerFields ?? false;
    $emptyColspan = $showWorkerFields ? 9 : 7;
@endphp

@section('content')
    <section class="staff-page-hero">
        <div>
            <h1>{{ $heroTitle ?? 'Manage Staff' }}</h1>
            <p>{{ $heroSubtitle ?? 'Kemaskini maklumat staff yang sedia ada dalam sistem.' }}</p>
        </div>
        <div class="staff-page-hero__meta">
            <span>Total {{ $staff->total() }} {{ $showWorkerFields ? 'pekerja' : 'staff' }}</span>
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

    @if (! $showWorkerFields && ! empty($importRoute))
        <section class="panel staff-import-panel">
            <div class="staff-import-copy">
                <span class="staff-import-kicker">IMPORT EXCEL</span>
                <h2>Muat Naik Senarai Staff</h2>
                <p>Kolum diperlukan: Nama dan No KP sahaja. Tarikh Masuk akan digunakan jika ada dalam fail.</p>
            </div>
            <form class="staff-import-form" method="POST" action="{{ $importRoute }}" enctype="multipart/form-data">
                @csrf
                <label class="staff-import-file" for="staffImportFile">
                    <input id="staffImportFile" name="file" type="file" accept=".csv,.txt,.xlsx" required>
                    <span class="staff-import-file__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                    </span>
                    <span>
                        <strong>Pilih fail Excel</strong>
                        <small>CSV atau XLSX, maksimum 10MB</small>
                    </span>
                </label>
                <button class="staff-import-button" type="submit">Upload Excel</button>
            </form>
        </section>
    @endif

    <section class="panel staff-list-panel">
        <div class="staff-list-head">
            <div>
                <h2>{{ $listTitle ?? 'Senarai Staff' }}</h2>
                <p>{{ $listSubtitle ?? 'Paparan lengkap mengikut maklumat yang tersedia dalam jadual staff.' }}</p>
            </div>
            <div class="staff-list-actions">
                <span class="record-badge">Total {{ $staff->total() }} {{ $showWorkerFields ? 'pekerja' : 'staff' }}</span>
                <a href="{{ $addButtonRoute ?? route('admin.users.create', ['type' => 'staff']) }}" class="add-staff-button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    {{ $addButtonLabel ?? 'Tambah Staff' }}
                </a>
            </div>
        </div>

        <form class="staff-search-bar" method="GET" action="{{ $listRoute ?? route('admin.users.staff') }}">
            <div class="staff-search-field">
                <label for="search">{{ $searchLabel ?? 'Cari Staff' }}</label>
                <input
                    id="search"
                    name="search"
                    type="search"
                    value="{{ $search ?? request('search') }}"
                    placeholder="{{ $searchPlaceholder ?? 'Nama, no pekerja, No. KP atau jenis staff' }}">
            </div>
            @if (! $showWorkerFields)
                <div class="staff-search-field staff-search-field--small">
                    <label for="staff_type">Jenis Staff</label>
                    <select id="staff_type" name="staff_type">
                        <option value="">Semua jenis</option>
                        @foreach (($staffTypeOptions ?? []) as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['staff_type'] ?? request('staff_type')) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="staff-search-actions">
                <button class="staff-search-button" type="submit">Cari</button>
                @if (($search ?? request('search')) || collect($filters ?? [])->filter()->isNotEmpty())
                    <a class="staff-reset-button" href="{{ $listRoute ?? route('admin.users.staff') }}">Reset</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        @if ($showWorkerFields)
                            <th>No Pekerja</th>
                        @endif
                        <th>No. KP</th>
                        <th>Jenis Staff</th>
                        <th>Email</th>
                        <th>No Telefon</th>
                        <th>Tarikh Masuk</th>
                        @if ($showWorkerFields)
                            <th>Status</th>
                        @endif
                        <th class="actions-col">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $member)
                        @php
                            $displayStaffType = in_array($member->nric, $activeAdminNrics ?? [], true)
                                ? 'Admin Pengurusan Sistem'
                                : $member->staff_type_label;
                        @endphp
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="avatar">
                                        {{ strtoupper(substr($member->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="user-name">{{ $member->nama }}</div>
                                        <div class="user-sub">{{ $displayStaffType }}</div>
                                    </div>
                                </div>
                            </td>
                            @if ($showWorkerFields)
                                <td class="no-pekerja-cell"><code class="badge-code">{{ $member->no_pekerja }}</code></td>
                            @endif
                            <td><span class="mono">{{ $member->nric ?? '-' }}</span></td>
                            <td>{{ $displayStaffType }}</td>
                            <td>{{ $member->email ?? '-' }}</td>
                            <td>{{ $member->no_tel ?? '-' }}</td>
                            <td>{{ optional($member->tarikh_mula)->format('d/m/Y') ?? '-' }}</td>
                            @if ($showWorkerFields)
                                <td>
                                    <span class="status-badge {{ $member->status_aktif ? 'is-active' : 'is-inactive' }}">
                                        {{ $member->status_aktif ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                            @endif
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
                            <td colspan="{{ $emptyColspan }}">
                                <div class="empty-state">
                                    <strong>{{ $emptyTitle ?? 'Tiada rekod staff.' }}</strong>
                                    <span>{{ $emptySubtitle ?? 'Klik Tambah Staff untuk daftar rekod baru.' }}</span>
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
    .staff-alert{
        position:fixed;
        top:20px;
        right:20px;
        z-index:1000;
        display:flex;
        max-width:min(420px, calc(100vw - 32px));
        align-items:center;
        gap:12px;
        padding:14px 20px;
        border-radius:14px;
        border:1px solid transparent;
        font-weight:800;
        box-shadow:0 18px 38px rgba(15,23,42,.16);
    }
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
    .staff-import-panel{
        display:grid;
        grid-template-columns:minmax(0,1fr) auto;
        gap:18px;
        align-items:center;
        margin-bottom:24px;
        padding:24px 28px;
        border:1px solid var(--line);
        border-radius:22px!important;
        background:#fff;
        box-shadow:0 14px 34px rgba(15,23,42,.055)!important;
    }
    .staff-import-copy{
        display:grid;
        gap:6px;
    }
    .staff-import-kicker{
        color:var(--secondary);
        font-size:12px;
        font-weight:900;
        letter-spacing:.04em;
    }
    .staff-import-copy h2{
        margin:0;
        color:var(--text);
        font-size:22px;
        font-weight:900;
    }
    .staff-import-copy p{
        margin:0;
        color:var(--muted-2);
        font-weight:800;
    }
    .staff-import-form{
        display:flex;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
        justify-content:flex-end;
    }
    .staff-import-file{
        display:flex;
        align-items:center;
        gap:12px;
        min-height:56px;
        padding:10px 14px;
        border:1px dashed var(--line-strong);
        border-radius:14px;
        background:var(--surface-soft);
        cursor:pointer;
    }
    .staff-import-file input{
        position:absolute;
        width:1px;
        height:1px;
        overflow:hidden;
        clip:rect(0,0,0,0);
    }
    .staff-import-file__icon{
        display:grid;
        width:36px;
        height:36px;
        place-items:center;
        border-radius:10px;
        background:var(--secondary-soft);
        color:var(--secondary);
    }
    .staff-import-file strong,
    .staff-import-file small{
        display:block;
    }
    .staff-import-file strong{
        color:var(--text);
        font-size:13px;
        font-weight:900;
    }
    .staff-import-file small{
        margin-top:2px;
        color:var(--muted-2);
        font-size:12px;
        font-weight:700;
    }
    .staff-import-button{
        min-height:46px;
        border:1px solid var(--secondary);
        border-radius:12px;
        background:var(--secondary);
        color:#fff;
        padding:0 18px;
        font-weight:900;
        cursor:pointer;
        box-shadow:0 10px 22px rgba(30,64,175,.16);
    }
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
    .staff-search-field--small{
        flex:0 1 210px;
        min-width:190px;
    }
    .staff-search-field label{
        color:var(--muted);
        font-size:12px;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.03em;
    }
    .staff-search-field input,
    .staff-search-field select{
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
    .staff-search-field select{
        cursor:pointer;
    }
    .staff-search-field input:focus,
    .staff-search-field select:focus{
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
    .staff-table{width:100%;border-collapse:separate;border-spacing:0;min-width:{{ $showWorkerFields ? '1040px' : '860px' }}}
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

    .content:has(> .staff-page-hero){
        background:#F4F7FB;
    }
    .content > .staff-page-hero,
    .content > .staff-list-panel,
    .content > .staff-import-panel{
        max-width:none;
    }
    .content > .staff-page-hero{
        position:relative;
        align-items:flex-start;
        margin-bottom:30px;
        padding:26px 28px!important;
        border:1px solid #D7E2EF!important;
        border-left:6px solid #0b1e36!important;
        border-radius:12px!important;
        background:#fff!important;
        box-shadow:0 12px 28px rgba(11,30,54,.08)!important;
    }
    .content > .staff-page-hero::before{
        content:"";
        position:absolute;
        top:30px;
        left:28px;
        width:42px;
        height:3px;
        border-radius:999px;
        background:#ED1C2E;
    }
    .content > .staff-page-hero h1{
        margin:20px 0 0!important;
        color:#071A33!important;
        font-size:26px!important;
        line-height:1.15!important;
        font-weight:700!important;
    }
    .content > .staff-page-hero p{
        margin:8px 0 0!important;
        color:#263A54!important;
        font-size:14px!important;
        line-height:1.45!important;
        font-weight:600!important;
    }
    .content > .staff-page-hero .staff-page-hero__meta{
        padding-top:48px;
    }
    .content > .staff-page-hero .staff-page-hero__meta span{
        min-height:32px;
        border-radius:999px;
        background:#EAF3FF;
        color:#0B5ED7;
        padding:0 14px;
        font-size:13px;
    }
    .content > .staff-list-panel,
    .content > .staff-import-panel{
        border:1px solid #D7E2EF!important;
        border-radius:12px!important;
        background:#fff!important;
        box-shadow:0 8px 20px rgba(8,47,89,.035)!important;
    }
    .content .staff-list-head{
        padding:24px 28px;
        border-bottom:1px solid #D7E2EF;
        background:#fff;
    }
    .content .staff-list-head h2{
        color:#082F59;
        font-size:24px;
        line-height:1.2;
        font-weight:900;
    }
    .content .staff-list-head h2::before{
        content:"";
        display:block;
        width:34px;
        height:4px;
        margin-bottom:10px;
        border-radius:999px;
        background:#ED1C2E;
    }
    .content .staff-list-head p{
        margin-top:6px;
        color:#31517D;
        font-size:15px;
        font-weight:650;
    }
    .content .record-badge{
        min-height:32px;
        border:1px solid #BBF7D0;
        background:#DCFCE7;
        color:#15803D;
        padding:0 14px;
        font-size:13px;
    }
    .content .record-badge::before{
        background:#16A34A;
    }
    .content .add-staff-button,
    .content .staff-search-button,
    .content .staff-reset-button{
        min-height:42px;
        border-radius:6px;
        font-size:14px;
        box-shadow:none;
    }
    .content .add-staff-button,
    .content .staff-search-button{
        border:1px solid #0B5ED7;
        background:#0B5ED7;
        color:#fff;
    }
    .content .staff-search-bar{
        gap:16px;
        margin:0;
        padding:18px 28px 20px;
        border-bottom:1px solid #D7E2EF;
        background:#FBFDFF;
    }
    .content .staff-search-field label{
        color:#082F59;
        font-size:13px;
        font-weight:800;
        letter-spacing:0;
    }
    .content .staff-search-field input,
    .content .staff-search-field select{
        min-height:42px;
        border:1px solid #C8D6E7;
        border-radius:6px;
        color:#102033;
        font-size:14px;
        font-weight:650;
    }
    .content .staff-table{
        border-collapse:collapse;
    }
    .content .staff-table th{
        min-height:56px;
        padding:13px 16px;
        border-bottom:1px solid #D7E2EF;
        background:#F8F9FA;
        color:#082F59;
        font-size:12px;
        font-weight:900;
        letter-spacing:.04em;
    }
    .content .staff-table td{
        padding:14px 16px;
        border-bottom:1px solid #DCE5F0;
        color:#082F59;
        font-size:13px;
    }
    .content .staff-table tbody tr:hover{
        background:#F8FBFF;
    }
    .content .user-cell{
        min-width:190px;
    }
    .content .avatar{
        width:36px;
        height:36px;
        background:#EAF3FF;
        color:#0B5ED7;
        border-color:#CFE0F5;
        font-size:12px;
    }
    .content .user-name{
        color:#082F59;
        font-size:14px;
        font-weight:900;
    }
    .content .user-sub{
        color:#526987;
        font-size:12px;
    }
    .content .badge-code{
        min-height:26px;
        align-items:center;
        border-radius:6px;
        background:#F1F6FC;
        color:#082F59;
        padding:0 10px;
        font-size:12px;
        font-weight:750;
    }
    .content .status-badge{
        min-height:26px;
        padding:0 10px;
        font-size:12px;
    }
    .content .edit-button,
    .content .delete-button{
        width:76px;
        min-height:34px;
        border-radius:6px;
    }
    .content .edit-button{
        background:#E8F0FE;
        color:#1A73E8;
    }
    .content .delete-button{
        background:#FEF2F2;
        color:#B91C1C;
    }

    @media (max-width:640px){
        .staff-page-hero{align-items:flex-start;flex-direction:column;padding:24px}
        .staff-page-hero h1{font-size:30px}
        .content > .staff-page-hero .staff-page-hero__meta{padding-top:0}
        .staff-alert{top:12px;right:12px;left:12px;max-width:none}
        .staff-import-panel{grid-template-columns:1fr;padding:22px}
        .staff-import-form{justify-content:flex-start}
        .staff-import-file,.staff-import-button{width:100%}
        .staff-list-head{align-items:flex-start;padding:22px}
        .staff-search-bar{padding:16px 22px}
        .staff-search-field{min-width:100%}
        .staff-list-actions,.add-staff-button{width:100%}
        .table-responsive{overflow-x:auto}
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.staff-alert').forEach((alert) => {
            const closeButton = alert.querySelector('.alert-close');
            const closeAlert = () => {
                alert.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-8px)';
                setTimeout(() => alert.remove(), 260);
            };

            closeButton?.addEventListener('click', closeAlert);
            setTimeout(closeAlert, 5000);
        });
    });
</script>
@endpush
