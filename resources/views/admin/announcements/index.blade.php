@extends('layouts.app')

@section('title', 'Urus Announcement')
@section('page-title', 'Urus Announcement')
@section('page-subtitle', 'Tambah, edit dan padam pengumuman rasmi.')

@section('content')
    <div class="announcement-admin">
        <section class="announcement-admin__head">
            <div>
                <span>ANNOUNCEMENT</span>
                <h1>Urus Announcement</h1>
                <p>Announcement ini dipaparkan di page khas, bukan di dashboard.</p>
            </div>
            <a class="announcement-button" href="{{ route('admin.announcements.create') }}">Tambah Announcement</a>
        </section>

        <section class="announcement-table-panel">
            <div class="table-responsive">
                <table class="announcement-table">
                    <thead>
                        <tr>
                            <th>Tajuk</th>
                            <th>Kategori</th>
                            <th>Sasaran</th>
                            <th>Tarikh</th>
                            <th>Status</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($announcements as $announcement)
                            <tr>
                                <td>
                                    <strong>{{ $announcement->title }}</strong>
                                    <span>{{ \Illuminate\Support\Str::limit($announcement->body, 90) }}</span>
                                </td>
                                <td>{{ $announcement->category }}</td>
                                <td>{{ $announcement->audience_label }}</td>
                                <td>{{ $announcement->starts_at?->format('d/m/Y') ?? '-' }} hingga {{ $announcement->ends_at?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <span class="announcement-status {{ $announcement->is_active ? 'active' : 'inactive' }}">{{ $announcement->status_label }}</span>
                                </td>
                                <td>
                                    <div class="announcement-actions">
                                        <a href="{{ route('admin.announcements.edit', $announcement) }}">Edit</a>
                                        <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Padam announcement ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="announcement-empty-cell">Tiada announcement lagi.</td></tr>
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
    .announcement-admin{display:grid;gap:18px}
    .announcement-admin__head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;padding:28px 30px;border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:0 14px 34px rgba(15,23,42,.055)}
    .announcement-admin__head span{color:var(--secondary);font-size:12px;font-weight:900;letter-spacing:.08em}
    .announcement-admin__head h1{margin:6px 0 0;color:var(--text);font-size:30px;line-height:1.15;font-weight:900}
    .announcement-admin__head p{margin:8px 0 0;color:var(--muted);font-weight:700}
    .announcement-button,.announcement-actions a{display:inline-flex;align-items:center;justify-content:center;min-height:42px;border:1px solid #BFD7FF;border-radius:8px;background:var(--secondary);color:#fff;padding:0 16px;text-decoration:none;font-weight:900}
    .announcement-table-panel{overflow:hidden;border:1px solid var(--line);border-radius:16px;background:#fff}
    .announcement-table{width:100%;border-collapse:collapse}
    .announcement-table th{background:#F8FAFC;color:#475569;font-size:12px;font-weight:900;letter-spacing:.04em;text-align:left;text-transform:uppercase}
    .announcement-table th,.announcement-table td{padding:16px 18px;border-bottom:1px solid var(--line);vertical-align:middle}
    .announcement-table td strong{display:block;color:var(--text);font-weight:900}
    .announcement-table td span{display:block;color:var(--muted-2);font-size:13px;font-weight:700}
    .announcement-status{display:inline-flex;align-items:center;justify-content:center;min-width:100px;border-radius:999px;padding:7px 12px;font-size:13px;font-weight:900}
    .announcement-status.active{background:var(--success-soft);color:var(--success)}
    .announcement-status.inactive{background:var(--danger-soft);color:var(--danger)}
    .announcement-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .announcement-actions a{background:#fff;color:var(--secondary)}
    .announcement-actions button{min-height:42px;border:0;border-radius:8px;background:var(--danger-soft);color:var(--danger);padding:0 16px;font-weight:900}
    .announcement-empty-cell{text-align:center;color:var(--muted);font-weight:800}
    @media (max-width:720px){.announcement-admin__head{align-items:stretch;flex-direction:column}.announcement-button{width:100%}}
</style>
@endpush
