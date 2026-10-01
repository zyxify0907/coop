@extends('layouts.app')

@section('title', 'Urus Pengumuman')
@section('page-title', 'Urus Pengumuman')
@section('page-subtitle', 'Tambah, kemaskini dan padam pengumuman rasmi.')

@section('content')
    <div class="announcement-admin">
        <section class="announcement-admin__head">
            <div>
                <h1>Urus Pengumuman</h1>
                <p>Pengumuman ini dipaparkan di halaman khas, bukan di ringkasan utama.</p>
            </div>
            <div class="announcement-admin__meta">
                <span>Jumlah {{ $announcements->total() }} pengumuman</span>
            </div>
        </section>

        <section class="announcement-table-panel">
            <div class="announcement-list-head">
                <div>
                    <h2>Senarai Pengumuman</h2>
                    <p>Urus pengumuman rasmi mengikut kategori, sasaran dan status aktif.</p>
                </div>
                <div class="announcement-list-actions">
                    <span class="record-badge">Total {{ $announcements->total() }} announcement</span>
                    <a class="announcement-button" href="{{ route('admin.announcements.create') }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Announcement
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="announcement-table">
                    <thead>
                        <tr>
                            <th>Tajuk</th>
                            <th>Kategori</th>
                            <th>Sasaran</th>
                            <th>Diumumkan Oleh</th>
                            <th>Tarikh</th>
                            <th>Status</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($announcements as $announcement)
                            <tr>
                                <td>
                                    <div class="announcement-title-cell">
                                        <div class="announcement-icon">{{ strtoupper(substr($announcement->title, 0, 2)) }}</div>
                                        <div>
                                            <strong>{{ $announcement->title }}</strong>
                                            <span>{{ \Illuminate\Support\Str::limit($announcement->body, 90) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="badge-code">{{ $announcement->category }}</code></td>
                                <td>{{ $announcement->audience_label }}</td>
                                <td>{{ $announcement->announcer_name }}</td>
                                <td>{{ $announcement->starts_at?->format('d/m/Y') ?? '-' }} hingga {{ $announcement->ends_at?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <span class="announcement-status {{ $announcement->is_active ? 'active' : 'inactive' }}">{{ $announcement->status_label }}</span>
                                </td>
                                <td>
                                    <div class="announcement-actions">
                                        <a class="edit-button" href="{{ route('admin.announcements.edit', $announcement) }}">Kemaskini</a>
                                        <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Padam pengumuman ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="delete-button" type="submit">Padam</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <strong>Tiada announcement lagi.</strong>
                                        <span>Klik Tambah Announcement untuk cipta pengumuman baru.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($announcements->hasPages())
            <div class="pagination">{{ $announcements->links() }}</div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .page-heading{display:none}
    .announcement-admin{display:grid;gap:24px}
    .announcement-admin__head{
        position:relative;
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:18px;
        padding:26px 28px;
        border:1px solid #D7E2EF;
        border-left:6px solid #0b1e36;
        border-radius:12px;
        background:#fff;
        box-shadow:0 12px 28px rgba(11,30,54,.08);
    }
    .announcement-admin__head::before{content:"";position:absolute;top:30px;left:28px;width:42px;height:3px;border-radius:999px;background:#ED1C2E}
    .announcement-admin__head h1{margin:20px 0 0;color:#071A33;font-size:26px;line-height:1.15;font-weight:700}
    .announcement-admin__head p{margin:8px 0 0;color:#263A54;font-size:14px;line-height:1.45;font-weight:600}
    .announcement-admin__meta{padding-top:48px}
    .announcement-admin__meta span,.record-badge{display:inline-flex;min-height:32px;align-items:center;justify-content:center;border-radius:999px;background:#EAF3FF;color:#0B5ED7;padding:0 14px;font-size:13px;font-weight:900}
    .record-badge{border:1px solid #BBF7D0;background:#DCFCE7;color:#15803D}
    .record-badge::before{content:'';width:8px;height:8px;margin-right:8px;border-radius:999px;background:#16A34A}
    .announcement-table-panel{overflow:hidden;border:1px solid #D7E2EF;border-radius:12px;background:#fff;box-shadow:0 8px 20px rgba(8,47,89,.035)}
    .announcement-list-head{display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;padding:24px 28px;border-bottom:1px solid #D7E2EF;background:#fff}
    .announcement-list-head h2{margin:0;color:#082F59;font-size:24px;line-height:1.2;font-weight:900}
    .announcement-list-head h2::before{content:"";display:block;width:34px;height:4px;margin-bottom:10px;border-radius:999px;background:#ED1C2E}
    .announcement-list-head p{margin:6px 0 0;color:#31517D;font-size:15px;font-weight:650}
    .announcement-list-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
    .announcement-button{display:inline-flex;min-height:42px;align-items:center;justify-content:center;gap:8px;border:1px solid #0B5ED7;border-radius:6px;background:#0B5ED7;color:#fff;padding:0 16px;text-decoration:none;font-size:14px;font-weight:900}
    .table-responsive{overflow-x:auto;padding:0}
    .announcement-table{width:100%;min-width:980px;border-collapse:collapse}
    .announcement-table th{padding:13px 16px;border-bottom:1px solid #D7E2EF;background:#F8F9FA;color:#082F59;font-size:12px;font-weight:900;letter-spacing:.04em;text-align:left;text-transform:uppercase;white-space:nowrap}
    .announcement-table td{padding:14px 16px;border-bottom:1px solid #DCE5F0;color:#082F59;font-size:13px;vertical-align:middle}
    .announcement-table tbody tr:hover{background:#F8FBFF}
    .announcement-title-cell{display:flex;align-items:center;gap:12px;min-width:260px}
    .announcement-icon{display:flex;width:36px;height:36px;flex:0 0 auto;align-items:center;justify-content:center;border:1px solid #CFE0F5;border-radius:999px;background:#EAF3FF;color:#0B5ED7;font-size:12px;font-weight:900}
    .announcement-table td strong{display:block;color:#082F59;font-size:14px;font-weight:900}
    .announcement-table td span{display:block;margin-top:3px;color:#526987;font-size:12px;font-weight:700}
    .badge-code{display:inline-flex;min-height:26px;align-items:center;white-space:nowrap;border-radius:6px;background:#F1F6FC;color:#082F59;padding:0 10px;font-size:12px;font-weight:750}
    .announcement-status{display:inline-flex;min-width:100px;min-height:26px;align-items:center;justify-content:center;border-radius:999px;padding:0 10px;font-size:12px;font-weight:900}
    .announcement-status.active{background:var(--success-soft);color:var(--success)}
    .announcement-status.inactive{background:var(--danger-soft);color:var(--danger)}
    .announcement-actions{display:flex;gap:8px;align-items:center;justify-content:flex-end;flex-wrap:nowrap}
    .announcement-actions form{margin:0}
    .edit-button,.delete-button{display:inline-flex;width:76px;min-height:34px;align-items:center;justify-content:center;border:0;border-radius:6px;text-decoration:none;font-weight:900;font-size:13px;cursor:pointer}
    .edit-button{background:#E8F0FE;color:#1A73E8}
    .delete-button{background:#FEF2F2;color:#B91C1C}
    .empty-state{display:grid;gap:6px;padding:40px 20px;text-align:center;color:var(--muted-2)}
    .empty-state strong{color:var(--text)}
    .content:has(> .announcement-admin){background:#F4F7FB}
    .content > .announcement-admin{max-width:none}
    @media (max-width:720px){
        .announcement-admin__head{align-items:stretch;flex-direction:column}
        .announcement-admin__meta{padding-top:0}
        .announcement-list-head{align-items:flex-start;padding:22px}
        .announcement-list-actions,.announcement-button{width:100%}
    }
</style>
@endpush
