@php
    $role = $role ?? session('auth_role');
    $staffType = $staffType ?? ($user->staff_type ?? session('staff_type'));
    $isDialogMode = request()->boolean('dialog');
    $showAccountTools = ! $isDialogMode && in_array($role, ['admin', 'staff', 'ahli'], true);
    $showStudentNav = $showAccountTools && $role === 'ahli';
    $showStaffNav = $showAccountTools && $role === 'staff';
    $showPortalNav = $showStudentNav || $showStaffNav;
    $showSidebar = $showAccountTools && $role === 'admin';
    $displayName = $user->nama ?? 'CoopBest';
    $initials = collect(explode(' ', $displayName))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'CB';
    $roleLabel = $role === 'admin'
        ? 'Admin / Pengurus Sistem'
        : ($role === 'staff'
            ? match ($staffType) {
                'lecturer_member' => 'Pensyarah / Staf Akademik',
                'clothing_staff' => 'Staff Pengurus Baju',
                'share_staff' => 'Staff Pengurus Saham',
                'coop_manager' => 'Staff Pengurus Pekerja Koperasi',
                default => 'Pekerja Koperasi',
            }
            : 'Student / Ahli');
    $headerNotifications = collect();
    $unreadNotificationCount = 0;

    if ($showAccountTools && \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
        $headerNotifications = \App\Models\CooperativeNotification::query()
            ->where('recipient_role', $role)
            ->where('recipient_id', $user->getKey())
            ->latest()
            ->limit(5)
            ->get();
        $unreadNotificationCount = \App\Models\CooperativeNotification::query()
            ->where('recipient_role', $role)
            ->where('recipient_id', $user->getKey())
            ->whereNull('read_at')
            ->count();
    }

    $adminLinks = [
        ['section' => 'Home', 'label' => 'Laman Utama Admin', 'route' => 'auth.dashboard', 'active' => 'auth.dashboard', 'icon' => 'grid'],
        ['section' => 'Kehadiran Pekerja', 'label' => 'Ringkasan Kehadiran', 'route' => 'admin.attendance.dashboard', 'active' => 'admin.attendance.dashboard', 'icon' => 'chart'],
        ['section' => 'Kehadiran Pekerja', 'label' => 'Rekod Kehadiran', 'route' => 'admin.attendance.records', 'active' => 'admin.attendance.records*', 'icon' => 'clipboard'],
        ['section' => 'Kehadiran Pekerja', 'label' => 'Elaun Bulanan', 'route' => 'admin.allowances.index', 'active' => 'admin.allowances.*', 'icon' => 'cash'],
        ['section' => 'Kehadiran Pekerja', 'label' => 'Tetapan Attendance', 'route' => 'admin.attendance.settings', 'active' => 'admin.attendance.settings*', 'icon' => 'book'],
        ['section' => 'SAHAM', 'label' => 'Ringkasan Saham', 'route' => 'admin.dashboard.saham', 'active' => 'admin.dashboard.saham', 'icon' => 'chart'],
        ['section' => 'SAHAM', 'label' => 'Permohonan Student', 'route' => 'admin.permohonan.index', 'params' => ['pemohon' => 'pelajar'], 'active' => 'admin.permohonan.*', 'audience' => 'pelajar', 'icon' => 'clipboard'],
        ['section' => 'SAHAM', 'label' => 'Permohonan Staff', 'route' => 'admin.permohonan.index', 'params' => ['pemohon' => 'staff'], 'active' => 'admin.permohonan.*', 'audience' => 'staff', 'icon' => 'clipboard'],
        ['section' => 'SAHAM', 'label' => 'Anggota Student', 'route' => 'admin.anggota.students', 'active' => 'admin.anggota.students', 'icon' => 'users'],
        ['section' => 'SAHAM', 'label' => 'Anggota Staff', 'route' => 'admin.anggota.staff', 'active' => 'admin.anggota.staff', 'icon' => 'users'],
        ['section' => 'SAHAM', 'label' => 'Saham', 'route' => 'admin.saham.index', 'active' => 'admin.saham.*', 'icon' => 'cash'],
        ['section' => 'SAHAM', 'label' => 'Transaksi Saham', 'route' => 'koperasi.transactions.index', 'active' => 'koperasi.transactions.*', 'icon' => 'cash'],
        ['section' => 'Tempahan Baju', 'label' => 'Ringkasan Baju', 'route' => 'admin.dashboard.baju', 'active' => 'admin.dashboard.baju', 'icon' => 'chart'],
        ['section' => 'Tempahan Baju', 'label' => 'Senarai Tempahan', 'route' => 'admin.tempahan.index', 'active' => 'admin.tempahan.*', 'icon' => 'clipboard'],
        ['section' => 'Tempahan Baju', 'label' => 'Stok Baju', 'route' => 'admin.baju.index', 'active' => 'admin.baju.index', 'icon' => 'box'],
        ['section' => 'Pengguna', 'label' => 'Senarai Student', 'route' => 'admin.users.students', 'active' => 'admin.users.students', 'icon' => 'users'],
        ['section' => 'Pengguna', 'label' => 'Senarai Staff', 'route' => 'admin.users.staff', 'active' => 'admin.users.staff', 'icon' => 'users'],
        ['section' => 'Pengguna', 'label' => 'Senarai Pekerja Koperasi', 'route' => 'admin.users.coop-workers', 'active' => 'admin.users.coop-workers', 'icon' => 'users'],
        ['section' => 'Pengurusan', 'label' => 'Pengumuman', 'route' => 'admin.announcements.index', 'active' => 'admin.announcements.*', 'icon' => 'clipboard'],
    ];

    $lecturerLinks = [
        ['section' => 'Home', 'label' => 'Home', 'route' => 'lecturer-member.dashboard', 'active' => 'lecturer-member.dashboard', 'icon' => 'grid'],
        ['section' => 'SAHAM', 'label' => 'Dashboard Saham', 'route' => 'lecturer-member.dashboard.saham', 'active' => 'lecturer-member.dashboard.saham', 'icon' => 'chart'],
        ['section' => 'SAHAM', 'label' => 'Permohonan Anggota', 'route' => 'lecturer-member.permohonan.index', 'active' => 'lecturer-member.permohonan.*', 'icon' => 'clipboard'],
        ['section' => 'SAHAM', 'label' => 'Semak Permohonan', 'route' => 'student.permohonan.status', 'active' => 'student.permohonan.status', 'icon' => 'clipboard'],
        ['section' => 'SAHAM', 'label' => 'Senarai Transaksi', 'route' => 'koperasi.transactions.index', 'active' => 'koperasi.transactions.*', 'icon' => 'chart'],
    ];

    $coopStaffLinks = [
        ['section' => 'Home', 'label' => 'Home', 'route' => 'coop-staff.dashboard', 'active' => 'coop-staff.dashboard', 'icon' => 'grid'],
        ['section' => 'Kehadiran Pekerja', 'label' => 'Kehadiran Pekerja', 'route' => 'coop-staff.attendance.index', 'active' => 'coop-staff.attendance.index', 'icon' => 'clipboard'],
        ['section' => 'Kehadiran Pekerja', 'label' => 'Sejarah Kehadiran', 'route' => 'coop-staff.attendance.history', 'active' => 'coop-staff.attendance.history', 'icon' => 'book'],
    ];

    $clothingStaffLinks = [
        ['section' => 'Home', 'label' => 'Home', 'route' => 'clothing-staff.dashboard', 'active' => 'clothing-staff.dashboard', 'icon' => 'grid'],
        ['section' => 'SAHAM', 'label' => 'Dashboard Saham', 'route' => 'clothing-staff.dashboard.saham', 'active' => 'clothing-staff.dashboard.saham', 'icon' => 'chart'],
        ['section' => 'SAHAM', 'label' => 'Permohonan', 'route' => 'clothing-staff.permohonan.index', 'active' => 'clothing-staff.permohonan.*', 'icon' => 'clipboard'],
        ['section' => 'SAHAM', 'label' => 'Semak Permohonan', 'route' => 'student.permohonan.status', 'active' => 'student.permohonan.status', 'icon' => 'clipboard'],
        ['section' => 'SAHAM', 'label' => 'Senarai Transaksi', 'route' => 'koperasi.transactions.index', 'active' => 'koperasi.transactions.*', 'icon' => 'chart'],
        ['section' => 'Tempahan Baju', 'label' => 'Dashboard Baju', 'route' => 'clothing-staff.dashboard.baju', 'active' => 'clothing-staff.dashboard.baju', 'icon' => 'chart'],
        ['section' => 'Tempahan Baju', 'label' => 'Stok Baju', 'route' => 'clothing-staff.baju.index', 'active' => 'clothing-staff.baju.*', 'icon' => 'box'],
        ['section' => 'Tempahan Baju', 'label' => 'Senarai Tempahan', 'route' => 'clothing-staff.orders.index', 'active' => 'clothing-staff.orders.*', 'icon' => 'clipboard'],
        ['section' => 'Pengurusan', 'label' => 'Urus Pengumuman', 'route' => 'admin.announcements.index', 'active' => 'admin.announcements.*', 'icon' => 'clipboard'],
    ];

    $shareStaffLinks = [
        ['section' => 'Home', 'label' => 'Home', 'route' => 'share-staff.dashboard', 'active' => 'share-staff.dashboard', 'icon' => 'grid'],
        ['section' => 'Saham', 'label' => 'Saham', 'route' => 'share-staff.dashboard.saham', 'active' => ['share-staff.dashboard.saham', 'share-staff.shares', 'share-staff.permohonan.*', 'student.permohonan.status'], 'icon' => 'cash'],
        ['section' => 'SAHAM', 'label' => 'Permohonan Pelajar', 'route' => 'admin.permohonan.index', 'params' => ['pemohon' => 'pelajar'], 'active' => 'admin.permohonan.*', 'icon' => 'clipboard'],
        ['section' => 'SAHAM', 'label' => 'Permohonan Staff', 'route' => 'admin.permohonan.index', 'params' => ['pemohon' => 'staff'], 'active' => 'admin.permohonan.*', 'icon' => 'clipboard'],
        ['section' => 'SAHAM', 'label' => 'Rumusan Saham', 'route' => 'admin.saham.index', 'params' => ['kategori' => 'rumusan'], 'active' => 'admin.saham.*', 'icon' => 'cash'],
        ['section' => 'SAHAM', 'label' => 'Senarai Saham', 'route' => 'admin.saham.index', 'active' => 'admin.saham.*', 'icon' => 'cash'],
        ['section' => 'SAHAM', 'label' => 'Transaksi Saham', 'route' => 'koperasi.transactions.index', 'active' => 'koperasi.transactions.*', 'icon' => 'cash'],
        ['section' => 'Saham Sendiri', 'label' => 'Saham', 'route' => 'share-staff.shares', 'active' => ['share-staff.shares', 'share-staff.permohonan.*'], 'icon' => 'cash'],
    ];

    $shareStaffManagementDropdown = [
        'label' => 'Pengurusan Saham',
        'active' => ['admin.dashboard.saham', 'admin.permohonan.*', 'admin.saham.*', 'koperasi.transactions.*'],
        'children' => [
            ['label' => 'Dashboard Pengurusan Saham', 'route' => 'admin.dashboard.saham', 'active' => 'admin.dashboard.saham'],
            ['label' => 'Permohonan Anggota Pelajar', 'route' => 'admin.permohonan.index', 'params' => ['pemohon' => 'pelajar', 'jenis' => 'anggota'], 'active' => 'admin.permohonan.*'],
            ['label' => 'Permohonan Anggota Staff', 'route' => 'admin.permohonan.index', 'params' => ['pemohon' => 'staff', 'jenis' => 'anggota'], 'active' => 'admin.permohonan.*'],
            ['label' => 'Proses Tambah Saham', 'route' => 'admin.permohonan.index', 'params' => ['jenis' => 'saham'], 'active' => 'admin.permohonan.*'],
            ['label' => 'Pengeluaran / Berhenti / Pindah / Bersara', 'route' => 'admin.permohonan.index', 'params' => ['jenis' => 'berhenti'], 'active' => 'admin.permohonan.*'],
            ['label' => 'Rumusan Saham', 'route' => 'admin.saham.index', 'params' => ['kategori' => 'rumusan'], 'active' => 'admin.saham.*'],
            ['label' => 'Transaksi Saham', 'route' => 'koperasi.transactions.index', 'params' => ['scope' => 'all'], 'active' => 'koperasi.transactions.*'],
        ],
    ];

    $coopManagerLinks = [
        ['section' => 'Home', 'label' => 'Home', 'route' => 'coop-manager.dashboard', 'active' => 'coop-manager.dashboard', 'icon' => 'grid'],
        ['section' => 'Saham', 'label' => 'Saham', 'route' => 'coop-manager.dashboard.saham', 'active' => ['coop-manager.dashboard.saham', 'coop-manager.shares', 'coop-manager.permohonan.*', 'student.permohonan.status'], 'icon' => 'cash'],
        ['section' => 'Pekerja Koperasi', 'label' => 'Senarai Pekerja Koperasi', 'route' => 'admin.users.coop-workers', 'active' => 'admin.users.coop-workers', 'icon' => 'users'],
        ['section' => 'Kehadiran Pekerja', 'label' => 'Rekod Kehadiran', 'route' => 'admin.attendance.records', 'active' => 'admin.attendance.records*', 'icon' => 'clipboard'],
        ['section' => 'Kehadiran Pekerja', 'label' => 'Elaun Bulanan', 'route' => 'admin.allowances.index', 'active' => 'admin.allowances.*', 'icon' => 'cash'],
    ];

    $coopManagerManagementDropdown = [
        'label' => 'Pengurusan Koperasi',
        'active' => ['coop-manager.dashboard.koperasi', 'admin.attendance.dashboard', 'admin.users.coop-workers', 'admin.attendance.records*', 'admin.allowances.*'],
        'children' => [
            ['label' => 'Dashboard Pengurusan Koperasi', 'route' => 'coop-manager.dashboard.koperasi', 'active' => ['coop-manager.dashboard.koperasi', 'admin.attendance.dashboard']],
            ['label' => 'Senarai Pekerja Koperasi', 'route' => 'admin.users.coop-workers', 'active' => 'admin.users.coop-workers'],
            ['label' => 'Rekod Kehadiran', 'route' => 'admin.attendance.records', 'active' => 'admin.attendance.records*'],
            ['label' => 'Elaun Bulanan', 'route' => 'admin.allowances.index', 'active' => 'admin.allowances.*'],
        ],
    ];

    $studentLinks = [
        ['section' => 'Home', 'label' => 'Home', 'route' => 'auth.dashboard', 'active' => 'auth.dashboard', 'icon' => 'grid'],
        ['section' => 'Saham', 'label' => 'Saham', 'route' => 'student.dashboard.saham', 'active' => ['student.dashboard.saham', 'student.permohonan.*', 'koperasi.transactions.*'], 'icon' => 'cash'],
        ['section' => 'Tempahan', 'label' => 'Tempahan', 'route' => 'student.tempahan.index', 'active' => 'student.tempahan.*', 'icon' => 'box'],
    ];

    $portalNavLabel = $role === 'staff' ? 'Portal Staf' : 'Portal Pelajar';

    $links = match ($role) {
        'admin' => $adminLinks,
        'staff' => match ($staffType) {
            'lecturer_member' => $lecturerLinks,
            'clothing_staff' => $clothingStaffLinks,
            'share_staff' => $shareStaffLinks,
            'coop_manager' => $coopManagerLinks,
            default => $coopStaffLinks,
        },
        'ahli' => $studentLinks,
        default => [],
    };

    $staffTopLinks = match ($staffType) {
        'lecturer_member' => [
            $lecturerLinks[0],
            ['section' => 'Saham', 'label' => 'Saham', 'route' => 'lecturer-member.dashboard.saham', 'active' => ['lecturer-member.dashboard.saham', 'lecturer-member.permohonan.*', 'student.permohonan.status', 'koperasi.transactions.*'], 'icon' => 'cash'],
        ],
        'clothing_staff' => [
            $clothingStaffLinks[0],
            ['section' => 'Pengurusan', 'label' => 'Pengumuman', 'route' => 'admin.announcements.index', 'active' => 'admin.announcements.*', 'icon' => 'clipboard'],
            ['section' => 'Saham', 'label' => 'Saham', 'route' => 'clothing-staff.dashboard.saham', 'active' => ['clothing-staff.dashboard.saham', 'clothing-staff.permohonan.*', 'student.permohonan.status', 'koperasi.transactions.*'], 'icon' => 'cash'],
            [
                'section' => 'Tempahan',
                'label' => 'Tempahan',
                'active' => ['clothing-staff.dashboard.baju', 'clothing-staff.baju.*', 'clothing-staff.orders.*'],
                'icon' => 'box',
                'children' => [
                    ['label' => 'Dashboard Baju', 'route' => 'clothing-staff.dashboard.baju', 'active' => 'clothing-staff.dashboard.baju'],
                    ['label' => 'Stok Baju', 'route' => 'clothing-staff.baju.index', 'active' => 'clothing-staff.baju.*'],
                    ['label' => 'Senarai Tempahan', 'route' => 'clothing-staff.orders.index', 'active' => 'clothing-staff.orders.*'],
                ],
            ],
        ],
        'share_staff' => [
            $shareStaffLinks[0],
            ['section' => 'Pengurusan', 'label' => 'Pengumuman', 'route' => 'admin.announcements.index', 'active' => 'admin.announcements.*', 'icon' => 'clipboard'],
            $shareStaffLinks[1],
            $shareStaffManagementDropdown,
        ],
        'coop_manager' => [
            $coopManagerLinks[0],
            ['section' => 'Pengurusan', 'label' => 'Pengumuman', 'route' => 'admin.announcements.index', 'active' => 'admin.announcements.*', 'icon' => 'clipboard'],
            $coopManagerLinks[1],
            $coopManagerManagementDropdown,
        ],
        default => [
            $coopStaffLinks[0],
            ['section' => 'Kehadiran', 'label' => 'Kehadiran', 'route' => 'coop-staff.attendance.index', 'active' => 'coop-staff.attendance.index', 'icon' => 'clipboard'],
            ['section' => 'Sejarah', 'label' => 'Sejarah', 'route' => 'coop-staff.attendance.history', 'active' => 'coop-staff.attendance.history', 'icon' => 'book'],
        ],
    };
    $portalLinks = $role === 'staff' ? $staffTopLinks : $studentLinks;
    $portalHomeUrl = isset($portalLinks[0]['route'])
        ? route($portalLinks[0]['route'], $portalLinks[0]['params'] ?? [])
        : route('auth.dashboard');
    $profileRoute = match (true) {
        $role === 'admin' => 'admin.profile',
        $role === 'staff' && $staffType === 'lecturer_member' => 'lecturer-member.profile',
        $role === 'staff' && $staffType === 'clothing_staff' => 'clothing-staff.profile',
        $role === 'staff' && $staffType === 'share_staff' => 'share-staff.profile',
        $role === 'staff' && $staffType === 'coop_manager' => 'coop-manager.profile',
        $role === 'staff' => 'coop-staff.profile',
        $role === 'ahli' => 'student.profile',
        default => null,
    };
    $profileUrl = $profileRoute ? route($profileRoute) : '#';
    $isPortalLinkActive = function (array $link): bool {
        $activePatterns = (array) ($link['active'] ?? []);
        $active = $activePatterns !== [] && request()->routeIs(...$activePatterns);

        foreach (['pemohon', 'jenis', 'kategori'] as $queryKey) {
            if (isset($link['params'][$queryKey])) {
                $active = $active && request($queryKey) === $link['params'][$queryKey];
            }
        }

        return $active;
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $isDialogMode ? 'dialog-mode embedded-frame' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CoopBest')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Koperasi Politeknik Color Palette */
            --primary: #C62828;
            --primary-dark: #991B1B;
            --primary-soft: #FEF2F2;
            --secondary: #2453A6;
            --secondary-hover: #1E40AF;
            --secondary-soft: #EFF6FF;
            
            /* Neutrals & Surfaces */
            --bg: #F8FAFC;
            --surface: #FFFFFF;
            --surface-soft: #F8FAFC;
            --line: #E2E8F0;
            --line-strong: #CBD5E1;
            
            /* Typography Colors */
            --text: #0F172A;
            --ink: #14213D;
            --muted: #475569;
            --muted-2: #64748B;
            
            /* Functional Status Colors */
            --success: #16A34A;
            --success-soft: #DCFCE7;
            --danger: #B91C1C;
            --danger-soft: #FEE2E2;
            --warning: #D97706;
            --warning-soft: #FEF3C7;
            
            /* Modern Elevation & Geometry */
            --shadow-sm: 0 10px 28px rgba(15, 23, 42, 0.06);
            --shadow-md: 0 16px 36px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 24px 55px rgba(15, 23, 42, 0.12);
            --radius-sm: 8px;
            --radius: 10px;
            --radius-lg: 12px;
            --sidebar-w: 280px;

            /* Sidebar Palette */
            --sidebar-bg: #F4F7FB;
            --sidebar-text: #334155;
            --sidebar-icon: #64748B;
            --sidebar-active-bg: #E8F0FF;
            --sidebar-active: #1D4ED8;
            --sidebar-section: #64748B;
            --sidebar-border: #E2E8F0;
            
            /* Transitions */
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition: 200ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { box-sizing: border-box; }

        html, body {
            height: 100%;
            min-height: 100%;
        }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 15px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            overflow: hidden;
        }

        /* Custom Scrollbars */
        * {
            scrollbar-width: thin;
            scrollbar-color: var(--line-strong) transparent;
        }
        *::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        *::-webkit-scrollbar-track {
            background: transparent;
        }
        *::-webkit-scrollbar-thumb {
            background: var(--line-strong);
            border-radius: 999px;
        }
        *::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }

        a { color: var(--secondary); text-decoration: underline; text-underline-offset: 4px; font-weight: 700; }
        a:hover { color: var(--secondary-hover); }
        button, input, select { font: inherit; }
        button, label { cursor: pointer; }

        /* Skip Link for Accessibility */
        .skip-link {
            position: absolute;
            left: -9999px;
            top: 0;
            z-index: 100;
            background: var(--primary);
            color: #FFF;
            padding: 12px 18px;
            border-radius: 0 0 var(--radius-sm) 0;
            font-weight: 600;
        }
        .skip-link:focus { left: 0; }

        /* Focus States */
        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        select:focus-visible,
        label[tabindex]:focus-visible {
            outline: 2px solid var(--secondary);
            outline-offset: 2px;
        }

        /* App Layout Structure */
        .app-shell {
            height: 100vh;
            display: flex;
            background: var(--bg);
            overflow: hidden;
        }

        .sidebar-toggle,
        .sidebar-backdrop { display: none; }

        /* Sidebar Component */
        .sidebar {
            width: var(--sidebar-w);
            flex: 0 0 var(--sidebar-w);
            height: 100vh;
            margin: 0;
            background: var(--sidebar-bg);
            border: 0;
            border-right: 1px solid var(--sidebar-border);
            border-bottom: 0;
            border-radius: 0;
            box-shadow: none;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            overflow: hidden;
            z-index: 30;
            transition: transform var(--transition);
        }

        .sidebar-brand {
            height: 88px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            border-bottom: 1px solid var(--sidebar-border);
        }

        .brand-info {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .sidebar-close { display: none; }

        .brand-mark {
            width: 54px;
            height: 54px;
            border-radius: 0;
            display: grid;
            place-items: center;
            background: transparent;
            box-shadow: none;
        }

        .brand-mark img { width: 54px; height: 54px; object-fit: contain; display: block; }

        .brand-title {
            font-size: 20px; 
            font-weight: 800; 
            color: var(--text);
            letter-spacing: -0.02em; 
        }

        .brand-title__coop { color: #C62828; }
        .brand-title__best { color: #2453A6; }

        .brand-subtitle { 
            display: block;
            margin-top: 2px; 
            color: #64748B;
            font-size: 11px; 
            font-weight: 700; 
        }

        /* User Info Badge */
        .sidebar-user {
            margin: 16px 16px 8px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid var(--sidebar-border);
            border-radius: var(--radius-sm);
            background: #FFFFFF;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: #DBEAFE;
            color: #2453A6;
            font-weight: 800;
            font-size: 15px;
            flex: 0 0 auto;
            border: 1px solid #BFDBFE;
        }

        .sidebar-user strong { display: block; color: var(--text); font-size: 14px; font-weight: 700; }
        .sidebar-user > div > span { display: block; color: var(--muted-2); font-size: 12px; font-weight: 600; }

        /* Navigation Area */
        .nav {
            padding: 12px;
            flex: 1;
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .nav::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        .nav-label {
            padding: 12px 8px 6px;
            color: var(--sidebar-section);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .nav-link {
            min-height: 42px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 12px;
            margin: 4px 0;
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--sidebar-text);
            font-weight: 700;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: all var(--transition-fast);
        }

        .nav-link:hover {
            background: #EEF2F7;
            color: #1D4ED8;
        }

        /* Active Page Highlighted with Soft Blue */
        .nav-link.active {
            background: var(--sidebar-active-bg);
            color: var(--sidebar-active);
            border-left: 3px solid #1D4ED8;
        }

        .nav-link__label {
            display: block;
            min-width: max-content;
            white-space: nowrap;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            flex: 0 0 auto;
            color: var(--sidebar-icon);
            transition: color var(--transition-fast);
        }

        .nav-link:hover .nav-icon {
            color: #1D4ED8;
        }

        .nav-link.active .nav-icon {
            color: #1D4ED8;
        }

        /* Content Area Infrastructure */
        .main-wrap {
            flex: 1;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .topbar {
            min-height: 92px;
            background: #FFFFFF;
            border-top: 0;
            border-bottom: 1px solid #E2E8F0;
            box-shadow: 0 2px 8px rgba(15,23,42,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 0 32px;
            z-index: 10;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 48px;
            padding: 8px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: var(--surface);
            box-shadow: 0 14px 28px rgba(15,23,42,.08);
        }

        .topbar-actions::before {
            content: 'Akaun';
            display: inline-flex;
            align-items: center;
            min-height: 32px;
            padding: 0 10px;
            border-right: 1px solid var(--line);
            color: var(--muted-2);
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .icon-button {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-sm);
            border: 1px solid #E2E8F0;
            background: #FFFFFF;
            color: #2453A6;
            display: grid;
            place-items: center;
            transition: all var(--transition-fast);
        }

        .icon-button:hover {
            background: #EFF6FF;
            color: #1D4ED8;
            border-color: #BFDBFE;
        }

        .icon-button svg {
            width: 20px;
            height: 20px;
        }

        .profile-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 48px;
            padding: 4px 14px 4px 4px;
            border: 1px solid #E2E8F0;
            border-radius: 999px;
            background: #fff;
            box-shadow: none;
        }

        .profile-chip__text,
        .profile-chip__name {
            font-size: 13px;
            font-weight: 700;
            color: #0F172A;
            line-height: 1.2;
        }

        .profile-chip small {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--muted-2);
        }

        .profile-menu {
            position: relative;
        }

        .profile-menu > summary {
            list-style: none;
            cursor: pointer;
        }

        .profile-menu > summary::-webkit-details-marker {
            display: none;
        }

        .profile-menu[open] .profile-chip {
            background: var(--surface-soft);
            color: #1D4ED8;
        }

        .profile-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            z-index: 65;
            width: 230px;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            box-shadow: var(--shadow-lg);
        }

        .profile-dropdown__head {
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
        }

        .profile-dropdown__head strong {
            display: block;
            overflow: hidden;
            color: var(--text);
            font-size: 13px;
            font-weight: 800;
            line-height: 1.3;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .profile-dropdown__head span {
            display: block;
            margin-top: 3px;
            color: var(--muted-2);
            font-size: 11px;
            font-weight: 700;
        }

        .profile-dropdown__item {
            width: 100%;
            min-height: 42px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 14px;
            border: 0;
            background: #fff;
            color: var(--sidebar-text);
            font-size: 13px;
            font-weight: 750;
            text-align: left;
            text-decoration: none;
        }

        .profile-dropdown__item:hover {
            background: #EFF6FF;
            color: #1D4ED8;
        }

        .profile-dropdown__item svg {
            width: 17px;
            height: 17px;
            flex: 0 0 auto;
        }

        .profile-dropdown form {
            margin: 0;
            border-top: 1px solid var(--line);
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .notification-menu {
            position: relative;
        }

        .notification-menu > summary {
            list-style: none;
        }

        .notification-menu > summary::-webkit-details-marker {
            display: none;
        }

        .notification-trigger {
            position: relative;
        }

        .notification-trigger[aria-expanded='true'],
        .notification-menu[open] .notification-trigger {
            background: #EFF6FF;
            color: #1D4ED8;
        }

        .notification-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            display: grid;
            place-items: center;
            min-width: 17px;
            height: 17px;
            padding: 0 4px;
            border: 2px solid #fff;
            border-radius: 999px;
            background: var(--danger);
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            line-height: 1;
        }

        .notification-dropdown {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            z-index: 60;
            width: min(390px, calc(100vw - 28px));
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            box-shadow: var(--shadow-lg);
        }

        .notification-dropdown__head,
        .notification-dropdown__foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
        }

        .notification-dropdown__head {
            border-bottom: 1px solid var(--line);
        }

        .notification-dropdown__head strong {
            color: var(--text);
            font-size: 14px;
        }

        .notification-dropdown__head span {
            color: var(--muted-2);
            font-size: 12px;
            font-weight: 600;
        }

        .notification-dropdown__mark-all,
        .notification-dropdown__read {
            padding: 0;
            border: 0;
            background: transparent;
            color: var(--secondary);
            font-size: 12px;
            font-weight: 700;
        }

        .notification-dropdown__mark-all:hover,
        .notification-dropdown__read:hover {
            color: var(--secondary-hover);
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .notification-dropdown__list {
            max-height: min(390px, calc(100dvh - 185px));
            overflow-y: auto;
        }

        .notification-dropdown__item {
            padding: 13px 16px;
            border-bottom: 1px solid var(--line);
            background: #fff;
        }

        .notification-dropdown__item--unread {
            background: #F8FBFF;
            box-shadow: inset 3px 0 0 var(--secondary);
        }

        .notification-dropdown__link {
            display: block;
            color: inherit;
            text-decoration: none;
        }

        .notification-dropdown__link:hover .notification-dropdown__title {
            color: var(--secondary);
        }

        .notification-dropdown__title {
            display: block;
            overflow: hidden;
            color: var(--text);
            font-size: 13px;
            font-weight: 750;
            line-height: 1.35;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .notification-dropdown__message {
            display: -webkit-box;
            margin: 4px 0 0;
            overflow: hidden;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .notification-dropdown__meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 8px;
        }

        .notification-dropdown__date {
            color: var(--muted-2);
            font-size: 11px;
            font-weight: 600;
        }

        .notification-dropdown__empty {
            padding: 30px 20px;
            color: var(--muted);
            font-size: 13px;
            text-align: center;
        }

        .notification-dropdown__foot {
            justify-content: center;
            background: var(--surface-soft);
        }

        .notification-dropdown__foot a {
            font-size: 12px;
            text-decoration: none;
        }

        @media (max-width: 560px) {
            .notification-dropdown {
                position: fixed;
                top: 62px;
                right: 12px;
                left: 12px;
                width: auto;
            }

            .profile-dropdown {
                position: fixed;
                top: 62px;
                right: 12px;
            }
        }

        .topbar-logout {
            min-height: 48px;
            padding: 0 20px;
            background: #fff;
            border: 1.5px solid var(--line);
            border-radius: var(--radius-sm);
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
            transition: all var(--transition-fast);
        }

        .topbar-logout:hover {
            background: var(--danger-soft);
            color: var(--danger);
            border-color: var(--danger-soft);
        }

        .search-box {
            width: 320px;
            height: 40px;
            border: 1.5px solid var(--line);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 12px;
            background: var(--surface-soft);
            transition: border-color var(--transition-fast);
        }

        .search-box:focus-within {
            border-color: var(--secondary);
            background: var(--surface);
            box-shadow: 0 0 0 3px var(--secondary-soft);
        }

        .search-box input {
            border: 0;
            outline: 0;
            background: transparent;
            flex: 1;
            font-size: 14px;
            color: var(--text);
        }

        .content {
            padding: 32px;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            background: var(--bg);
        }

        .page-heading {
            margin-bottom: 24px;
        }

        .page-heading h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .page-heading p {
            margin: 4px 0 0;
            color: var(--muted-2);
            font-size: 14px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            background: var(--secondary-soft);
            border: 1px solid #BFDBFE;
            color: var(--secondary);
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 24px;
        }

        /* Dashboard Panel & Cards */
        .panel {
            background: var(--surface);
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition);
        }

        .panel:hover {
            border-color: rgba(226, 232, 240, .9);
            box-shadow: var(--shadow-sm);
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .metric {
            padding: 24px;
            border: 1px solid rgba(226, 232, 240, .95);
            border-radius: 22px;
            background: var(--surface);
            box-shadow: 0 18px 36px rgba(15, 23, 42, .07);
            transition: all var(--transition);
            position: relative;
            overflow: hidden;
        }

        .metric::before {
            display: none;
        }

        .metric:hover {
            border-color: rgba(226, 232, 240, .95);
            transform: none;
            box-shadow: 0 18px 36px rgba(15, 23, 42, .07);
        }

        .metric-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: var(--secondary);
            color: #FFF;
            margin-bottom: 16px;
            box-shadow: 0 4px 10px rgba(30, 64, 175, 0.25);
        }

        .metric strong {
            font-size: 28px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge.success { background: var(--success-soft); color: var(--success); }
        .badge.danger { background: var(--danger-soft); color: var(--danger); }
        .badge.warning { background: var(--warning-soft); color: var(--warning); }

        .detail-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: var(--radius-sm);
        }

        .detail-table th,
        .detail-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: middle;
        }

        .detail-table tr:last-child th,
        .detail-table tr:last-child td { border-bottom: 0; }
        .detail-table th { width: 160px; background: var(--surface-soft); color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .02em; }
        .detail-table td { color: var(--text); font-weight: 700; }

        .panel-section-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            padding: 18px 20px;
            border-bottom: 1px solid var(--line);
            background: var(--surface-soft);
        }

        .panel-section-head h2 { margin: 0; font-size: 20px; }
        .panel-section-head p { margin: 6px 0 0; color: var(--muted-2); font-size: 13px; }

        /* Buttons & Inputs inside Dashboard */
        input, select {
            width: 100%;
            border: 1.5px solid var(--line);
            border-radius: 8px;
            padding: 10px 12px;
            background: var(--surface);
            color: var(--text);
            font-size: 14px;
            transition: all var(--transition-fast);
        }

        input:focus, select:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px var(--secondary-soft);
            outline: none;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 20px;
            border-radius: 8px;
            background: var(--secondary);
            color: #FFF;
            font-weight: 700;
            font-size: 14px;
            line-height: 1;
            white-space: nowrap;
            text-align: center;
            flex-shrink: 0;
            border: 0;
            box-shadow: 0 4px 14px rgba(30, 64, 175, 0.22);
            transition: all var(--transition-fast);
        }

        .button:hover { 
            background: var(--secondary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.28);
        }

        .menu-button {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--line);
            display: grid;
            place-items: center;
            color: var(--text);
        }

        .menu-button svg {
            width: 20px;
            height: 20px;
        }

        /* Responsive Mobile Drawer Rules */
        .mobile-only { display: none; }

        @media (max-width: 1024px) {
            .mobile-only { display: grid; }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                margin: 0;
                border-radius: 0 12px 12px 0;
                transform: translateX(-100%);
            }

            .sidebar-close {
                display: grid;
                place-items: center;
                width: 32px;
                height: 32px;
                color: var(--muted-2);
            }

            .sidebar-close svg {
                width: 20px;
                height: 20px;
            }

            .sidebar-toggle:checked ~ .sidebar {
                transform: translateX(0);
            }

            .sidebar-toggle:checked ~ .sidebar-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.4);
                backdrop-filter: blur(2px);
                z-index: 20;
            }

            .sidebar-brand { height: 76px; padding: 0 18px; }
            .brand-mark { width: 46px; height: 46px; }
            .brand-mark img { width: 46px; height: 46px; }
            .brand-title { font-size: 18px; }
            .topbar { min-height: 76px; padding: 0 16px; }
            .icon-button,
            .profile-chip,
            .topbar-logout { min-height: 42px; }
            .icon-button { width: 42px; height: 42px; }
            .content { padding: 20px 16px; }
            .profile-chip__text { display: none; }
        }
    </style>
    @stack('styles')
    <style>
        /* CoopBest production design guardrails. Page-specific CSS inherits these rules. */
        body { background: var(--bg); font-size: 14px; line-height: 1.5; }
        a { color: var(--secondary); font-weight: 600; }
        a:hover { color: #1D4ED8; }

        .sidebar-brand { height: 82px; padding: 0 20px; }
        .brand-mark { width: 52px; height: 52px; border-radius: 0; box-shadow: none; background: transparent; }
        .brand-mark img { width: 52px; height: 52px; }
        .brand-title { font-size: 20px; letter-spacing: -.03em; }
        .brand-subtitle { font-size: 11px; font-weight: 600; }

        @media (max-width: 620px) {
            .sidebar-brand { height: 82px; }
            .brand-mark, .brand-mark img { width: 48px; height: 48px; }
        }
        .sidebar-user { margin: 14px 14px 10px; border-radius: 8px; box-shadow: none; }
        .nav { padding: 10px 8px; }
        .nav-label { padding: 18px 10px 6px; color: #64748B; font-size: 10px; letter-spacing: .08em; }
        .nav-link { min-height: 40px; margin: 2px 0; border-radius: 6px; font-size: 14px; }
        .nav-link:hover { background: #EEF2F7; color: #1D4ED8; }
        .nav-link:hover .nav-icon { color: #1D4ED8; }
        .nav-link.active { border-left-width: 3px; }
        .topbar { min-height: 72px; padding: 0 28px; box-shadow: none; }
        .topbar-actions { min-height: 40px; padding: 0; gap: 8px; border: 0; border-radius: 0; box-shadow: none; background: transparent; }
        .topbar-actions::before { display: none; }
        .icon-button { width: 38px; height: 38px; border: 0; border-radius: 6px; background: transparent; }
        .icon-button:hover { border: 0; background: #EFF6FF; color: #1D4ED8; }
        .profile-chip { min-height: 40px; padding: 2px 8px 2px 2px; border: 0; border-radius: 6px; box-shadow: none; }
        .profile-chip:hover { background: var(--surface-soft); }
        .topbar-logout { min-height: 38px; padding: 0 14px; border: 1px solid var(--line); border-radius: 6px; }

        .content { padding: 28px 32px 40px; background: var(--bg); }
        .content > * { max-width: 1440px; }
        .page-heading { margin-bottom: 28px; }
        .page-heading h2 { font-size: 28px; font-weight: 700; letter-spacing: -.025em; color: var(--text); }
        .page-heading p { margin-top: 6px; color: var(--muted); font-size: 14px; }
        .alert { border-radius: 7px; box-shadow: none; }

        .panel,
        .metric,
        .coop-panel,
        .chart-card,
        .student-profile-card,
        .staff-profile-panel,
        .action-panel,
        .admin-panel { border-color: var(--line) !important; border-radius: 10px !important; box-shadow: none !important; }
        .panel:hover,
        .metric:hover,
        .coop-panel:hover,
        .chart-card:hover,
        .student-profile-card:hover,
        .staff-profile-panel:hover,
        .action-panel:hover,
        .admin-panel:hover { border-color: var(--line) !important; box-shadow: none !important; transform: none !important; }

        .card-grid { gap: 16px; }
        .metric { padding: 18px; }
        .admin-metric { padding: 18px !important; }
        .admin-metric strong, .work-card strong { color: var(--text) !important; }
        .admin-metric small, .work-card small { color: var(--muted-2) !important; font-weight: 500 !important; }
        .work-card__count { min-width: 34px !important; min-height: 26px !important; padding: 0 8px !important; border-radius: 5px !important; font-size: 14px !important; font-weight: 600 !important; }
        .metric-icon { width: 38px; height: 38px; margin-bottom: 12px; border-radius: 7px; background: var(--secondary-soft); color: var(--secondary); box-shadow: none; }
        .metric strong { font-size: 24px; }
        .badge { min-height: 22px; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }

        .panel-section-head,
        .chart-head,
        .action-panel__head,
        .coop-panel-head { padding: 18px 20px !important; background: var(--surface-soft) !important; }
        .panel-section-head h2,
        .chart-head h2,
        .action-panel__head h2,
        .coop-panel-head h2 { font-size: 19px !important; font-weight: 700; }
        .panel-section-head p,
        .chart-head p,
        .action-panel__head p,
        .coop-panel-head p { color: var(--muted) !important; }

        input, select, textarea { border: 1px solid var(--line-strong); border-radius: 7px; min-height: 40px; box-shadow: none; }
        input:focus, select:focus, textarea:focus { border-color: var(--secondary); box-shadow: 0 0 0 3px rgba(36, 83, 166, .14); }
        .button { min-height: 40px; padding: 0 15px; border-radius: 7px; background: var(--secondary); box-shadow: none; font-weight: 600; }
        .button:hover { background: var(--secondary-hover); transform: none; box-shadow: none; }
        .button.primary, .button-danger, .danger-button { background: var(--primary); color: #fff; }
        .button.primary:hover, .button-danger:hover, .danger-button:hover { background: var(--primary-dark); }
        .button.soft, .button-secondary, .coop-button-soft { background: #fff; color: var(--secondary); border: 1px solid #BFDBFE; border-radius: 7px; box-shadow: none; }
        .coop-button-danger { border-radius: 7px; }

        .detail-table { border-radius: 8px; }
        .detail-table th { background: var(--surface-soft); color: var(--muted); font-size: 11px; font-weight: 600; }
        .detail-table td { font-weight: 600; }
        .table-wrap, .table-container, .coop-table-wrap { border: 1px solid var(--line); border-radius: 10px; background: #fff; }
        table:not(.detail-table) th, .coop-table th { background: var(--surface-soft); color: var(--muted); font-size: 11px; font-weight: 600; letter-spacing: .02em; }
        table:not(.detail-table) td, .coop-table td { border-color: var(--line); }
        table:not(.detail-table) tbody tr:hover { background: var(--surface-soft); }

        .template-hero,
        .student-hero,
        .coop-hero,
        .admin-hero,
        .applications-hero,
        .student-page-hero,
        .staff-page-hero,
        .create-user-hero,
        .edit-user-hero,
        .anggota-hero,
        .baju-hero,
        .admin-baju-hero,
        .baju-orders-hero,
        .baju-edit-hero,
        .withdrawal-hero,
        .share-add-hero,
        .application-detail-hero,
        .detail-hero,
        .staff-hero,
        .share-head,
        .attendance-head,
        .announcement-hero,
        .announcement-admin__head,
        .announcement-form__head,
        .notification-hero,
        .coop-header { margin-bottom: 24px !important; padding: 30px 34px !important; min-height: 150px !important; border: 1px solid #C9D8EA !important; border-left: 8px solid #082F59 !important; border-radius: 14px !important; background: #fff !important; box-shadow: 0 18px 42px rgba(15, 23, 42, .06) !important; }
        .template-kicker,
        .coop-kicker,
        .hero-kicker,
        .baju-edit-hero > div > span { min-height: 24px; padding: 0 8px; border-radius: 4px; background: var(--secondary-soft); color: var(--secondary); font-size: 10px; font-weight: 600; letter-spacing: .06em; }
        .template-hero h1, .template-hero h2, .student-hero h2, .coop-hero h1, .admin-hero h1,
        .applications-hero h1, .student-page-hero h1, .staff-page-hero h1, .create-user-hero h1, .edit-user-hero h1,
        .anggota-hero h1, .anggota-hero h2, .baju-hero h1, .admin-baju-hero h2, .baju-orders-hero h1, .baju-edit-hero h1, .withdrawal-hero h1, .share-add-hero h1,
        .application-detail-hero h1, .detail-hero h1, .staff-hero h1, .staff-hero h2, .share-head h1,
        .attendance-head h1, .announcement-hero h1, .announcement-admin__head h1, .announcement-form__head h1,
        .notification-hero h1, .coop-header h1 { font-size: 32px !important; line-height: 1.12 !important; font-weight: 900 !important; letter-spacing: 0 !important; color: #061B34 !important; }
        .template-hero p, .student-hero p, .coop-hero p, .admin-hero p,
        .applications-hero p, .student-page-hero p, .staff-page-hero p, .create-user-hero p, .edit-user-hero p,
        .anggota-hero p, .baju-hero p, .admin-baju-hero p, .baju-orders-hero p, .baju-edit-hero p, .withdrawal-hero p, .share-add-hero p,
        .application-detail-hero p, .detail-hero p, .staff-hero p, .share-head p,
        .attendance-head p, .announcement-hero p, .announcement-admin__head p, .announcement-form__head p,
        .notification-hero p, .coop-header p { color: #003B75 !important; font-weight: 800 !important; }
        .hero-meta, .template-hero__meta, .student-hero__meta, .staff-hero__meta, .admin-hero__meta,
        .applications-hero .hero-meta, .share-add-hero .hero-meta, .withdrawal-hero .hero-meta { font-weight: 600 !important; color: var(--muted) !important; }
        .hero-meta strong, .template-hero__meta strong, .student-hero__meta strong, .staff-hero__meta strong, .admin-hero__meta strong { font-size: 13px !important; color: var(--text) !important; }

        .link-button, .mini-button, .switch-button, .tab, .hero-back, .profile-button, .pagination a, .pagination span {
            display: inline-flex !important; align-items: center !important; justify-content: center !important; min-height: 36px !important; padding: 0 12px !important; border: 1px solid var(--line-strong) !important; border-radius: 7px !important; background: #fff !important; color: var(--secondary) !important; box-shadow: none !important; font-size: 13px !important; font-weight: 600 !important; line-height: 1 !important; white-space: nowrap !important; text-align: center !important; text-decoration: none !important; flex-shrink: 0 !important;
        }
        .link-button:hover, .mini-button:hover, .switch-button:hover, .tab:hover, .hero-back:hover, .profile-button:hover { background: var(--secondary-soft) !important; border-color: #BFDBFE !important; color: var(--secondary) !important; transform: none !important; }
        .switch-button.is-active, .tab.is-active, .link-button.primary, .mini-button--view { background: var(--secondary) !important; border-color: var(--secondary) !important; color: #fff !important; }
        .mini-button--delete, .link-button.danger { background: var(--danger-soft) !important; border-color: var(--danger-soft) !important; color: var(--danger) !important; }

        .coop-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            width: 100%;
            margin: 0;
            padding: 16px 20px;
            border-top: 1px solid var(--line);
            background: #fff;
        }
        .coop-pagination__summary {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }
        .coop-pagination__summary strong {
            color: var(--text);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        .coop-pagination__controls,
        .coop-pagination__pages {
            display: flex;
            align-items: center;
        }
        .coop-pagination__controls {
            gap: 8px;
        }
        .coop-pagination__pages {
            gap: 2px;
            padding: 3px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface-soft);
        }
        .coop-pagination__direction,
        .coop-pagination__page,
        .coop-pagination__ellipsis {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 34px !important;
            padding: 0 10px !important;
            border: 0 !important;
            border-radius: 6px !important;
            background: transparent !important;
            color: var(--text) !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            line-height: 1 !important;
            text-decoration: none !important;
            white-space: nowrap !important;
            transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease;
        }
        .coop-pagination__direction {
            min-width: 112px !important;
            border: 1px solid var(--line-strong) !important;
            background: #fff !important;
        }
        .coop-pagination__page { min-width: 34px !important; padding: 0 8px !important; }
        a.coop-pagination__direction:hover,
        a.coop-pagination__page:hover {
            background: var(--secondary-soft) !important;
            color: var(--secondary) !important;
        }
        a.coop-pagination__direction:hover { border-color: #93c5fd !important; }
        .coop-pagination__page.is-current {
            background: var(--secondary) !important;
            color: #fff !important;
        }
        .coop-pagination__direction.is-disabled {
            border-color: var(--line) !important;
            background: var(--surface-soft) !important;
            color: #94a3b8 !important;
            cursor: not-allowed;
        }
        .coop-pagination__ellipsis {
            min-width: 28px !important;
            padding: 0 4px !important;
            background: transparent !important;
            color: var(--muted) !important;
        }

        .filters-grid, .share-filter, .filter-bar, .table-toolbar { padding: 16px !important; border-bottom: 1px solid var(--line) !important; background: var(--surface-soft) !important; }
        .summary-card, .admin-metric, .work-card { border-radius: 10px !important; box-shadow: none !important; }
        .request-summary { gap: 12px !important; }
        .request-table, .applications-wrap .panel { border-radius: 10px !important; box-shadow: none !important; }

        .action-menu { gap: 12px; padding: 16px 20px 20px; }
        .action-menu__item, .operation-card, .work-card { min-height: auto; padding: 16px; border-color: var(--line); border-radius: 8px; box-shadow: none; }
        .action-menu__item:hover, .operation-card:hover, .work-card:hover { background: var(--surface-soft); border-color: var(--line); box-shadow: none; transform: none; }

        @media (max-width: 1024px) { .content { padding: 24px 20px 32px; } }
        @media (max-width: 720px) {
            .topbar { min-height: 64px; padding: 0 14px; }
            .content { padding: 20px 14px 28px; }
            .page-heading h2 { font-size: 24px; }
            .profile-chip__text { display: none; }
            .topbar-logout { padding: 0 10px; }
            .template-hero, .student-hero, .coop-hero { padding: 18px !important; }
        }

        /* Shared responsive guardrails. Pages keep their own layout, while these
           rules prevent desktop-width controls, media and tables from escaping it. */
        html, body { width: 100%; max-width: 100%; overflow-x: hidden; }
        .app-shell, .main-wrap, .content, #main-content, .content > * { min-width: 0; max-width: 100%; }
        img, picture, video, canvas { max-width: 100%; height: auto; }

        .table-wrap,
        .table-container,
        .table-responsive,
        .coop-table-wrap,
        .attendance-table-wrap,
        .request-table-wrap,
        .share-table-wrap,
        .transaction-table-wrap,
        .document-table-wrap {
            max-width: 100%;
            overflow-x: auto !important;
            overflow-y: hidden;
            overscroll-behavior-inline: contain;
            -webkit-overflow-scrolling: touch;
        }

        .table-wrap > table,
        .table-container > table,
        .table-responsive > table,
        .coop-table,
        .attendance-table,
        .transaction-table,
        .orders-table,
        .document-table {
            min-width: 100%;
        }

        .coop-actions,
        .attendance-actions,
        .attendance-inline,
        .list-actions,
        .form-actions,
        .action-buttons,
        .share-filter__actions {
            flex-wrap: wrap;
        }

        .list-actions form,
        .action-buttons form {
            flex: 0 0 auto;
        }

        .file-field,
        .upload-field,
        .image-upload-button { min-width: 0; }
        .file-field input[type="file"],
        .upload-field input[type="file"] { max-width: 100%; }

        @media (max-width: 1199px) {
            .content { padding: 24px; }
            .card-grid { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        }

        @media (max-width: 991px) {
            .content { padding: 22px 20px 32px; }
            .admin-metrics,
            .admin-work-grid,
            .attendance-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .dashboard-grid,
            .portal-columns,
            .attendance-section-grid,
            .profile-layout { grid-template-columns: 1fr !important; }
        }

        @media (max-width: 767px) {
            .topbar { min-height: 62px; padding: 0 14px; }
            .content { padding: 18px 14px 28px; }
            .page-heading { margin-bottom: 18px; }
            .page-heading h2 { font-size: 24px; }
            .profile-chip__text,
            .topbar-logout { display: none; }
            .sidebar { width: min(86vw, 320px); }
            .card-grid,
            .admin-metrics,
            .admin-work-grid,
            .attendance-metrics { grid-template-columns: 1fr !important; }
            .panel-section-head,
            .chart-head,
            .action-panel__head,
            .coop-panel-head { padding: 16px !important; }
            .template-hero,
            .student-hero,
            .coop-hero,
            .admin-hero,
            .applications-hero,
            .student-page-hero,
            .staff-page-hero,
            .create-user-hero,
            .edit-user-hero,
            .anggota-hero,
            .baju-hero,
            .admin-baju-hero,
            .baju-orders-hero,
            .baju-edit-hero,
            .withdrawal-hero,
            .share-add-hero,
            .application-detail-hero,
            .detail-hero,
            .staff-hero,
            .share-head,
            .attendance-head,
            .announcement-hero,
            .announcement-admin__head,
            .announcement-form__head,
            .notification-hero,
            .coop-header { min-height: 0 !important; padding: 22px !important; margin-bottom: 18px !important; }
            .template-hero h1, .template-hero h2, .student-hero h2, .coop-hero h1, .admin-hero h1,
            .applications-hero h1, .student-page-hero h1, .staff-page-hero h1, .create-user-hero h1, .edit-user-hero h1,
            .anggota-hero h1, .anggota-hero h2, .baju-hero h1, .admin-baju-hero h2, .baju-orders-hero h1, .baju-edit-hero h1, .withdrawal-hero h1, .share-add-hero h1,
            .application-detail-hero h1, .detail-hero h1, .staff-hero h1, .staff-hero h2, .share-head h1,
            .attendance-head h1, .announcement-hero h1, .announcement-admin__head h1, .announcement-form__head h1,
            .notification-hero h1, .coop-header h1 { font-size: 26px !important; }
            .table-wrap > table,
            .table-container > table,
            .table-responsive > table,
            .coop-table,
            .attendance-table,
            .transaction-table,
            .orders-table,
            .document-table { min-width: 720px; }
            .panel:has(> table:not(.detail-table)),
            .request-table:has(> table:not(.detail-table)),
            .order-panel:has(> table:not(.detail-table)) {
                overflow-x: auto !important;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
            }
            .panel > table:not(.detail-table),
            .request-table > table:not(.detail-table),
            .order-panel > table:not(.detail-table) { min-width: 720px; }
        }

        @media (max-width: 639px) {
            input, select, textarea, .button { min-height: 44px; }
            .filters-grid,
            .share-filter,
            .filter-bar,
            .table-toolbar,
            .form-grid,
            .application-grid,
            .section-block__grid,
            .edit-form-grid,
            .create-form-grid,
            .profile-grid,
            .settings-grid { grid-template-columns: 1fr !important; }
            .filters-grid,
            .share-filter,
            .filter-bar,
            .table-toolbar { gap: 12px !important; }
            .coop-actions > *,
            .attendance-actions > *,
            .form-actions > *,
            .share-filter__actions > * { flex: 1 1 100%; }
            .pagination,
            .pagination-wrap,
            .list-actions { flex-wrap: wrap; }
            .coop-pagination {
                align-items: stretch;
                flex-direction: column;
                gap: 12px;
                padding: 14px;
            }
            .coop-pagination__summary {
                text-align: center;
            }
            .coop-pagination__controls {
                justify-content: space-between;
                width: 100%;
            }
            .coop-pagination__pages {
                display: none;
            }
            .coop-pagination__direction {
                flex: 1 1 0;
                min-width: 0 !important;
            }
        }

        /* Responsive layout baseline: laptop first, constrained wide screens, then touch layouts. */
        :root { --content-max: 1440px; }

        .app-shell,
        .main-wrap,
        .content {
            min-width: 0;
        }

        .app-shell,
        .main-wrap {
            min-height: 100dvh;
        }

        .content {
            padding: 28px clamp(24px, 2.5vw, 44px) 44px;
        }

        .content > * {
            width: 100%;
            max-width: var(--content-max);
            margin-inline: auto;
        }

        .topbar {
            padding-inline: clamp(20px, 2.2vw, 36px);
        }

        .topbar-left,
        .topbar-actions,
        .profile-chip,
        .panel-section-head,
        .coop-panel-head,
        .chart-head,
        .action-panel__head {
            min-width: 0;
        }

        .topbar-actions {
            margin-left: auto;
            flex-shrink: 0;
        }

        .form-grid,
        .application-grid,
        .section-block__grid,
        .edit-form-grid,
        .create-form-grid,
        .profile-grid,
        .settings-grid,
        .filters-grid,
        .share-filter,
        .filter-bar,
        .table-toolbar {
            min-width: 0;
        }

        .card-grid > *,
        .admin-metrics > *,
        .admin-work-grid > *,
        .attendance-metrics > *,
        .dashboard-grid > *,
        .portal-columns > *,
        .profile-layout > * {
            min-width: 0;
        }

        .table-wrap,
        .table-container,
        .table-responsive,
        .coop-table-wrap,
        .attendance-table-wrap,
        .request-table-wrap,
        .share-table-wrap,
        .transaction-table-wrap,
        .document-table-wrap {
            scrollbar-gutter: stable;
        }

        @media (min-width: 1600px) {
            .content { padding-inline: 48px; }
            .content > * { max-width: 1480px; }
        }

        @media (max-width: 1199px) {
            .content { padding: 24px 24px 36px; }
        }

        @media (max-width: 1024px) {
            .content { padding: 22px 20px 32px; }
            .topbar { padding-inline: 20px; }
            .topbar-actions { gap: 6px; }
        }

        @media (max-width: 767px) {
            body { overflow: auto; }
            .app-shell,
            .main-wrap { height: auto; min-height: 100dvh; }
            .main-wrap { overflow: visible; }
            .content { overflow: visible; padding: 18px 14px 28px; }
            .topbar { min-height: 64px; padding-inline: 14px; gap: 8px; }
            .topbar-actions { gap: 2px; }
            .icon-button { width: 40px; height: 40px; }
            .profile-chip__text,
            .topbar-logout { display: none; }
            .page-heading { margin-bottom: 18px; }
            .page-heading h2 { font-size: 24px; line-height: 1.25; }
            .page-heading p { font-size: 13px; }
            .panel-section-head,
            .coop-panel-head,
            .chart-head,
            .action-panel__head { gap: 10px; }
            .panel-section-head h2,
            .coop-panel-head h2,
            .chart-head h2,
            .action-panel__head h2 { font-size: 18px !important; }
            .card-grid,
            .admin-metrics,
            .admin-work-grid,
            .attendance-metrics,
            .dashboard-grid,
            .portal-columns,
            .attendance-section-grid,
            .profile-layout,
            .form-grid,
            .application-grid,
            .section-block__grid,
            .edit-form-grid,
            .create-form-grid,
            .profile-grid,
            .settings-grid { grid-template-columns: minmax(0, 1fr) !important; }
            .filters-grid,
            .share-filter,
            .filter-bar,
            .table-toolbar { grid-template-columns: minmax(0, 1fr) !important; }
            .filters-grid > *,
            .share-filter > *,
            .filter-bar > *,
            .table-toolbar > * { min-width: 0 !important; width: 100%; }
            .form-actions,
            .action-buttons,
            .coop-actions,
            .attendance-actions,
            .share-filter__actions { gap: 8px; }
            .form-actions > .button,
            .action-buttons > .button,
            .coop-actions > .button,
            .attendance-actions > .button,
            .share-filter__actions > .button { min-height: 44px; }
            input,
            select,
            textarea,
            .button { max-width: 100%; }
        }

        @media (max-width: 479px) {
            .sidebar { width: min(88vw, 304px); }
            .content { padding-inline: 12px; }
            .topbar { padding-inline: 12px; }
            .panel-section-head,
            .coop-panel-head,
            .chart-head,
            .action-panel__head { padding: 14px !important; }
        }

        /* Laptop-first responsive hardening. The content container measures the
           usable space after the sidebar, so grids respond to real page width. */
        .app-shell,
        .main-wrap,
        .topbar,
        .content {
            width: 100%;
            min-width: 0;
        }

        .main-wrap {
            flex: 1 1 auto;
        }

        .content {
            container-type: inline-size;
            container-name: coop-content;
            overflow-x: clip;
            overscroll-behavior-inline: contain;
            scrollbar-gutter: auto;
        }

        @media (min-width: 768px) {
            html,
            body {
                height: 100%;
                overflow: hidden !important;
            }

            .app-shell,
            .main-wrap {
                height: 100dvh;
                max-height: 100dvh;
                overflow: hidden !important;
            }

            .content {
                flex: 1 1 auto;
                min-height: 0;
                overflow-y: auto !important;
                overflow-x: clip;
                scrollbar-gutter: auto;
            }
        }

        .content > *,
        .content :is(section, article, form, fieldset, header, footer, nav, div) {
            min-width: 0;
        }

        .content :is(input, select, textarea, button, .button, .link-button, .mini-button, .profile-button) {
            max-width: 100%;
        }

        .content :is(.metric-row, .hero-meta, .template-hero__meta, .student-hero__meta, .staff-hero__meta, .list-actions, .form-actions, .action-buttons) {
            min-width: 0;
        }

        .content :is(.metric-row, .hero-meta, .template-hero__meta, .student-hero__meta, .staff-hero__meta) {
            flex-wrap: wrap;
        }

        .content :is(.table-wrap, .table-container, .table-responsive, .coop-table-wrap, .attendance-table-wrap, .request-table-wrap, .share-table-wrap, .transaction-table-wrap, .document-table-wrap) {
            width: 100%;
            max-width: 100%;
        }

        @media (min-width: 1440px) {
            :root { --sidebar-w: 280px; }
            .content { padding-inline: clamp(32px, 3vw, 52px); }
            .content > * { max-width: 1480px; }
        }

        @media (min-width: 1920px) {
            .content { padding-inline: 56px; }
            .content > * { max-width: 1560px; }
        }

        @media (min-width: 1200px) and (max-width: 1439px) {
            :root { --sidebar-w: 280px; }
            .content { padding: 24px 28px 36px; }
            .topbar { padding-inline: 24px; }
        }

        /* 1024px-1199px has too little working width beside a fixed sidebar.
           Use the existing drawer navigation before forms and tables become cramped. */
        @media (max-width: 1199px) {
            .mobile-only { display: grid; }

            .sidebar {
                width: min(86vw, 320px);
                flex-basis: min(86vw, 320px);
                position: fixed;
                inset: 0 auto 0 0;
                height: 100vh;
                margin: 0;
                border-radius: 0 12px 12px 0;
                transform: translateX(-100%);
                z-index: 40;
                box-shadow: 12px 0 28px rgba(15, 23, 42, .12);
            }

            .sidebar-close {
                display: grid;
                place-items: center;
            }

            .sidebar-toggle:checked ~ .sidebar { transform: translateX(0); }
            .sidebar-toggle:checked ~ .sidebar-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, .36);
                z-index: 35;
            }

            .topbar { min-height: 68px; padding-inline: 20px; }
            .content { padding: 22px 20px 32px; }
        }

        /* These breakpoints use the content width, not the browser width. This
           keeps the same pages balanced on 1366px and 1280px laptops. */
        @container coop-content (max-width: 1080px) {
            .card-grid,
            .stats-grid,
            .admin-metrics,
            .admin-work-grid,
            .attendance-metrics,
            .portal-metrics,
            .clothing-metrics,
            .request-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .dashboard-grid,
            .portal-columns,
            .attendance-overview,
            .attendance-section-grid,
            .profile-layout,
            .staff-dashboard-grid {
                grid-template-columns: minmax(0, 1fr) !important;
            }

            .filter-bar,
            .attendance-toolbar,
            .share-filter__actions {
                flex-wrap: wrap;
            }

            .filter-bar .filter-field,
            .attendance-toolbar label {
                min-width: min(220px, 100%);
                flex: 1 1 220px;
            }
        }

        /* Keep operational tables usable on narrow laptops without forcing the
           entire page to scroll sideways. Phone layouts retain table scrolling. */
        @container coop-content (min-width: 768px) and (max-width: 1120px) {
            .table-wrap > table:not(.detail-table),
            .table-container > table:not(.detail-table),
            .table-responsive > table:not(.detail-table),
            .coop-table,
            .attendance-table,
            .orders-table,
            .student-orders-table,
            .staff-table,
            .transaction-table,
            .document-table,
            .share-table {
                width: 100% !important;
                min-width: 0 !important;
                table-layout: fixed;
            }

            .table-wrap > table:not(.detail-table) :is(th, td),
            .table-container > table:not(.detail-table) :is(th, td),
            .table-responsive > table:not(.detail-table) :is(th, td),
            .coop-table :is(th, td),
            .attendance-table :is(th, td),
            .orders-table :is(th, td),
            .student-orders-table :is(th, td),
            .staff-table :is(th, td),
            .transaction-table :is(th, td),
            .document-table :is(th, td),
            .share-table :is(th, td) {
                padding: 12px 10px !important;
                white-space: normal;
                overflow-wrap: anywhere;
            }

            .orders-table .list-actions {
                min-width: 0;
                gap: 6px;
            }

            .orders-table :is(.action-save, .action-delete) {
                min-width: 0;
                padding-inline: 10px;
            }

            .orders-table :is(.status-control, .date-control) {
                min-width: 0;
                max-width: 100%;
            }
        }

        @container coop-content (max-width: 720px) {
            .card-grid,
            .stats-grid,
            .admin-metrics,
            .admin-work-grid,
            .attendance-metrics,
            .portal-metrics,
            .clothing-metrics,
            .request-summary,
            .action-menu,
            .portal-actions {
                grid-template-columns: minmax(0, 1fr) !important;
            }

            .filter-bar,
            .attendance-toolbar,
            .panel-section-head,
            .coop-panel-head,
            .chart-head,
            .action-panel__head {
                align-items: stretch;
                flex-direction: column;
            }

            .filter-bar > :is(.filter-field, .button, .link-button),
            .attendance-toolbar > *,
            .form-actions > .button,
            .action-buttons > .button {
                width: 100%;
            }
        }

        html.dialog-mode,
        body.dialog-mode {
            background: #fff;
            height: auto !important;
            min-height: 100dvh !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
        }

        body.dialog-mode .skip-link,
        body.dialog-mode .topbar,
        body.dialog-mode .page-heading {
            display: none !important;
        }

        body.dialog-mode .app-shell,
        body.dialog-mode .main-wrap {
            display: block;
            width: 100%;
            min-height: 100dvh;
            height: auto;
            overflow: visible;
        }

        body.dialog-mode .content {
            display: block;
            width: 100%;
            min-height: 100dvh;
            padding: 0;
            overflow: visible;
            background: #fff;
        }

        body.dialog-mode .content > * {
            max-width: none;
        }

        html.embedded-frame,
        body.embedded-frame {
            background: #f8fafc;
            height: auto !important;
            min-height: 100dvh !important;
            overflow-y: auto !important;
        }

        body.embedded-frame .skip-link,
        body.embedded-frame .topbar,
        body.embedded-frame .page-heading,
        body.embedded-frame .sidebar,
        body.embedded-frame .sidebar-toggle,
        body.embedded-frame .sidebar-backdrop {
            display: none !important;
        }

        body.embedded-frame .app-shell,
        body.embedded-frame .main-wrap {
            display: block;
            width: 100%;
            min-height: auto;
            height: auto;
            overflow: visible;
        }

        body.embedded-frame .content {
            display: block;
            width: 100%;
            min-height: auto;
            padding: 22px 24px 28px !important;
            overflow: visible;
            background: #f8fafc;
        }

        body.embedded-frame .content > * {
            max-width: none;
        }

        body.student-navbar-page .topbar {
            min-height: 78px;
            padding-inline: clamp(18px, 3vw, 42px);
        }

        body.student-navbar-page .topbar-left {
            flex: 1 1 auto;
            min-width: 0;
        }

        body.student-navbar-page .student-topnav {
            width: 100%;
            min-width: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: clamp(22px, 3vw, 42px);
        }

        .student-topnav__brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--text);
            text-decoration: none;
            flex: 0 0 auto;
        }

        .student-topnav__brand img {
            width: 42px;
            height: 42px;
            object-fit: contain;
            flex: 0 0 auto;
        }

        .student-topnav__brand > span {
            display: grid;
            gap: 1px;
        }

        .student-topnav__brand strong {
            font-size: 20px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0;
        }

        .student-topnav__brand small {
            color: var(--muted-2);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .student-topnav__links {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
            overflow: visible;
            scrollbar-width: none;
        }

        .student-topnav__links::-webkit-scrollbar {
            display: none;
        }

        .student-topnav__links a {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            border-bottom: 3px solid transparent;
            color: #334155;
            text-decoration: none;
            font-size: 14px;
            font-weight: 900;
            white-space: nowrap;
        }

        .student-topnav__links a:hover,
        .student-topnav__links a:focus-visible,
        .student-topnav__links a.active {
            color: var(--secondary);
            border-bottom-color: var(--primary);
        }

        .student-topnav__dropdown {
            position: relative;
            flex: 0 0 auto;
        }

        .student-topnav__dropdown > summary {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 0 16px;
            border-bottom: 3px solid transparent;
            color: #334155;
            cursor: pointer;
            font-size: 14px;
            font-weight: 900;
            line-height: 1;
            list-style: none;
            white-space: nowrap;
        }

        .student-topnav__dropdown > summary::-webkit-details-marker {
            display: none;
        }

        .student-topnav__dropdown > summary::after {
            width: 7px;
            height: 7px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            content: "";
            transform: translateY(-2px) rotate(45deg);
            transition: transform .16s ease;
        }

        .student-topnav__dropdown[open] > summary::after {
            transform: translateY(2px) rotate(225deg);
        }

        .student-topnav__dropdown > summary:hover,
        .student-topnav__dropdown > summary:focus-visible,
        .student-topnav__dropdown > summary.active {
            color: var(--secondary);
            border-bottom-color: var(--primary);
            outline: none;
        }

        .student-topnav__dropdown-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            z-index: 70;
            width: min(320px, calc(100vw - 32px));
            padding: 8px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            background: #FFFFFF;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .16);
        }

        .student-topnav__links .student-topnav__dropdown-menu a {
            width: 100%;
            min-height: 38px;
            justify-content: flex-start;
            padding: 0 11px;
            border: 0;
            border-radius: 6px;
            color: #334155;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.25;
            white-space: normal;
        }

        .student-topnav__links .student-topnav__dropdown-menu a:hover,
        .student-topnav__links .student-topnav__dropdown-menu a:focus-visible,
        .student-topnav__links .student-topnav__dropdown-menu a.active {
            background: #EEF2F7;
            color: var(--secondary);
        }

        body.student-navbar-page .topbar-actions {
            flex: 0 0 auto;
        }

        @media (max-width: 760px) {
            body.student-navbar-page .topbar {
                min-height: 112px;
                align-items: stretch;
                flex-direction: column;
                justify-content: center;
                gap: 8px;
                padding-block: 10px;
            }

            body.student-navbar-page .student-topnav {
                align-items: flex-start;
                flex-direction: column;
                gap: 8px;
            }

            .student-topnav__links {
                width: 100%;
                flex-wrap: wrap;
            }

            .student-topnav__links a {
                min-height: 34px;
                padding-inline: 12px;
            }

            .student-topnav__dropdown {
                position: static;
            }

            .student-topnav__dropdown > summary {
                min-height: 34px;
                padding-inline: 12px;
            }

            .student-topnav__dropdown-menu {
                left: 12px;
                right: auto;
                width: min(320px, calc(100vw - 24px));
            }

            body.student-navbar-page .topbar-actions {
                align-self: flex-end;
            }
        }

        .view-dialog {
            position: fixed !important;
            inset: 0 !important;
            width: min(1080px, calc(100vw - 96px)) !important;
            max-width: 1080px !important;
            height: min(80dvh, 760px) !important;
            max-height: calc(100dvh - 96px) !important;
            margin: auto !important;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 12px !important;
            background: transparent !important;
            overflow: hidden !important;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .30) !important;
        }

        .view-dialog--attendance {
            width: min(1080px, calc(100vw - 96px)) !important;
            max-width: 1080px !important;
            height: min(80dvh, 760px) !important;
            max-height: calc(100dvh - 96px) !important;
        }

        .view-dialog::backdrop {
            background: rgba(15, 23, 42, .42) !important;
            backdrop-filter: none !important;
        }

        .view-dialog__shell {
            display: grid !important;
            grid-template-rows: auto minmax(0, 1fr) !important;
            width: 100% !important;
            height: 100% !important;
            min-height: 0 !important;
            background: #fff !important;
        }

        .view-dialog__head {
            min-height: 72px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 16px !important;
            padding: 14px 18px 14px 22px !important;
            border-bottom: 1px solid var(--line) !important;
            background: #fff !important;
        }

        .view-dialog__head span {
            display: none !important;
        }

        .view-dialog__head h2 {
            margin: 0 !important;
            color: var(--text) !important;
            font-size: 22px !important;
            line-height: 1.2 !important;
            font-weight: 800 !important;
            letter-spacing: 0 !important;
        }

        .view-dialog__close {
            width: 42px !important;
            height: 42px !important;
            min-height: 42px !important;
            padding: 0 !important;
            border: 1px solid var(--line) !important;
            border-radius: 8px !important;
            background: #fff !important;
            color: var(--text) !important;
            font-size: 26px !important;
            font-weight: 700 !important;
            line-height: 1 !important;
            box-shadow: none !important;
        }

        .view-dialog__close:hover {
            border-color: var(--danger-soft) !important;
            background: var(--danger-soft) !important;
            color: var(--danger) !important;
        }

        .view-dialog__frame {
            width: 100% !important;
            height: 100% !important;
            min-height: 0 !important;
            border: 0 !important;
            background: #f8fafc !important;
            display: block !important;
            overflow: hidden !important;
        }

        .view-dialog__body {
            min-height: 0 !important;
            overflow-y: scroll !important;
            overflow-x: hidden !important;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            background: #f8fafc !important;
        }

        html.dialog-mode,
        body.dialog-mode {
            background: #f8fafc;
            height: auto !important;
            min-height: 100dvh !important;
            overflow-y: auto !important;
        }

        body.dialog-mode .content {
            min-height: auto;
            padding: 18px 24px 22px !important;
            background: #f8fafc;
            overflow: visible !important;
        }

        body.dialog-mode .attendance-head,
        body.dialog-mode .application-detail-hero {
            display: none !important;
        }

        body.dialog-mode .attendance-page,
        body.dialog-mode .applications-wrap {
            gap: 20px !important;
        }

        body.dialog-mode .attendance-detail div {
            display: grid !important;
            align-content: center !important;
            min-height: 104px !important;
            padding: 18px 24px !important;
        }

        body.dialog-mode .attendance-detail span {
            font-size: 13px !important;
        }

        body.dialog-mode .attendance-detail strong {
            margin-top: 6px !important;
            font-size: 20px !important;
            line-height: 1.3 !important;
        }

        body.dialog-mode .attendance-panel__head {
            padding: 20px 24px !important;
        }

        body.dialog-mode .attendance-panel__head h2 {
            font-size: 20px !important;
        }

        body.dialog-mode .attendance-panel__head p {
            margin-top: 5px !important;
        }

        body.dialog-mode .attendance-empty {
            padding: 30px 24px !important;
        }

        body.dialog-mode .attendance-panel,
        body.dialog-mode .panel,
        body.dialog-mode .detail-summary-card,
        body.dialog-mode .detail-section-card,
        body.dialog-mode .application-documents,
        body.dialog-mode .decision-card,
        body.dialog-mode .profile-shell {
            border-radius: 8px !important;
            box-shadow: none !important;
        }

        body.dialog-mode .detail-summary-card,
        body.dialog-mode .detail-metrics,
        body.dialog-mode .profile-shell {
            margin-bottom: 16px !important;
        }

        body.dialog-mode .summary-profile,
        body.dialog-mode .profile-shell,
        body.dialog-mode .detail-section-card,
        body.dialog-mode .application-documents,
        body.dialog-mode .decision-card {
            padding: 18px 20px !important;
        }

        body.dialog-mode .summary-stat {
            padding: 16px 20px !important;
        }

        body.dialog-mode .summary-stat strong,
        body.dialog-mode .detail-metrics strong {
            font-size: 20px !important;
        }

        body.dialog-mode .detail-hero {
            margin-bottom: 16px !important;
            padding: 20px 24px !important;
        }

        body.dialog-mode .detail-metrics {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px !important;
        }

        body.dialog-mode .detail-metrics section {
            min-height: 92px !important;
            padding: 14px 16px !important;
        }

        @media (max-width: 760px) {
            .view-dialog {
                width: calc(100vw - 28px) !important;
                height: calc(100dvh - 28px) !important;
                max-height: calc(100dvh - 28px) !important;
                border-radius: 10px !important;
            }

            .view-dialog--attendance {
                width: calc(100vw - 28px) !important;
                height: calc(100dvh - 28px) !important;
                max-height: calc(100dvh - 28px) !important;
            }

            .view-dialog__head {
                min-height: 64px !important;
                padding: 12px 14px !important;
            }

            .view-dialog__head h2 {
                font-size: 18px !important;
            }

            body.dialog-mode .content {
                padding: 14px !important;
            }

            body.dialog-mode .summary-stats,
            body.dialog-mode .detail-metrics {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>
<body class="{{ trim(($isDialogMode ? 'dialog-mode embedded-frame' : '').' '.($showPortalNav ? 'student-navbar-page' : '')) }}">
    @include('components.flash-notification')
    <script>
        if (window.self !== window.top) {
            document.body.classList.add('embedded-frame');
        }
    </script>
    <a class="skip-link" href="#main-content">Skip to main content</a>
    <div class="app-shell">
        @if ($showSidebar)
            <input class="sidebar-toggle" id="sidebar-toggle" type="checkbox" aria-label="Open menu">
            <aside class="sidebar" aria-label="Main navigation">
                <div class="sidebar-brand">
                    <div class="brand-info">
                        <span class="brand-mark" aria-hidden="true">
                            <img src="{{ asset('images/koperasi-logo.svg?v=2') }}" alt="">
                        </span>
                        <div>
                            <span class="brand-title"><span class="brand-title__coop">Coop</span><span class="brand-title__best">Best</span></span>
                            <span class="brand-subtitle">Koperasi Politeknik Besut</span>
                        </div>
                    </div>
                    <label class="sidebar-close" for="sidebar-toggle" aria-label="Close menu" tabindex="0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </label>
                </div>

                <div class="sidebar-user">
                    <x-user-avatar :initials="$initials" />
                    <div>
                        <strong>{{ $displayName }}</strong>
                        <span>{{ $roleLabel }}</span>
                    </div>
                </div>

                <nav class="nav" aria-label="Primary">
                    @php
                        $currentSection = null;
                    @endphp
                    @foreach ($links as $link)
                        @php
                            $href = $link['route'] ? route($link['route'], $link['params'] ?? []) : '#';
                            $activePatterns = (array) ($link['active'] ?? []);
                            $active = request()->routeIs(...$activePatterns);
                            if (($link['route'] ?? null) === 'admin.permohonan.index' && isset($link['audience'])) {
                                $active = $active && request('pemohon', 'pelajar') === $link['audience'];
                            }
                            $active = $active
                                || ($link['route'] === 'admin.users.students' && request()->routeIs('admin.users.edit') && request()->route('type') === 'student')
                                || ($link['route'] === 'admin.users.staff' && request()->routeIs('admin.users.edit') && request()->route('type') === 'staff');
                        @endphp
                        @if (($link['section'] ?? 'Menu') !== $currentSection)
                            @php
                                $currentSection = $link['section'] ?? 'Menu';
                            @endphp
                            <div class="nav-label">{{ $currentSection }}</div>
                        @endif
                        <a class="nav-link {{ $active ? 'active' : '' }}" href="{{ $href }}" @if($active) aria-current="page" @endif>
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                @switch($link['icon'])
                                    @case('users')
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9.5" cy="7" r="3.5"/>
                                        <path d="M21 21v-2a3.5 3.5 0 0 0-3-3.46"/>
                                        <path d="M16.5 3.4a3.5 3.5 0 0 1 0 6.8"/>
                                        @break
                                    @case('shield')
                                        <path d="M12 22s7-3.5 7-9.5V5.5l-7-3-7 3v7C5 18.5 12 22 12 22Z"/>
                                        <path d="m9 12 2 2 4-4"/>
                                        @break
                                    @case('chart')
                                        <path d="M4 19V5"/>
                                        <path d="M4 19h16"/>
                                        <path d="M8 16v-5"/>
                                        <path d="M12 16V8"/>
                                        <path d="M16 16v-3"/>
                                        @break
                                    @case('book')
                                        <path d="M5 4.5A2.5 2.5 0 0 1 7.5 2H20v18H7.5A2.5 2.5 0 0 0 5 22V4.5Z"/>
                                        <path d="M8 6h8"/>
                                        <path d="M8 10h7"/>
                                        @break
                                    @case('clipboard')
                                        <rect x="8" y="2.5" width="8" height="4" rx="1"/>
                                        <path d="M16 4.5h2a2 2 0 0 1 2 2V20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6.5a2 2 0 0 1 2-2h2"/>
                                        <path d="M8 12h8"/>
                                        <path d="M8 16h6"/>
                                        @break
                                    @case('box')
                                        <path d="m21 8-9-5-9 5 9 5 9-5Z"/>
                                        <path d="M3 8v8l9 5 9-5V8"/>
                                        <path d="M12 13v8"/>
                                        @break
                                    @case('cash')
                                        <rect x="3" y="6.5" width="18" height="11" rx="2"/>
                                        <circle cx="12" cy="12" r="2"/>
                                        <path d="M7 12h.01"/>
                                        <path d="M17 12h.01"/>
                                        @break
                                    @default
                                        <rect x="4" y="4" width="6" height="6" rx="1"/>
                                        <rect x="14" y="4" width="6" height="6" rx="1"/>
                                        <rect x="14" y="14" width="6" height="6" rx="1"/>
                                        <rect x="4" y="14" width="6" height="6" rx="1"/>
                                @endswitch
                            </svg>
                            <span class="nav-link__label">{{ $link['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

            </aside>
            <label class="sidebar-backdrop" for="sidebar-toggle" aria-label="Close menu"></label>
        @endif

        <div class="main-wrap">
            <header class="topbar">
                <div class="topbar-left">
                    @if ($showSidebar)
                        <label class="menu-button mobile-only" for="sidebar-toggle" aria-label="Open menu" tabindex="0">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                        </label>
                    @endif
                    @if ($showPortalNav)
                        <div class="student-topnav" aria-label="Navigasi portal">
                            <a class="student-topnav__brand" href="{{ $portalHomeUrl }}">
                                <img src="{{ asset('images/koperasi-logo.svg?v=2') }}" alt="Logo Koperasi">
                                <span>
                                    <strong><span class="brand-title__coop">Coop</span><span class="brand-title__best">Best</span></strong>
                                    <small>{{ $portalNavLabel }}</small>
                                </span>
                            </a>
                            <nav class="student-topnav__links" aria-label="Menu utama">
                                @foreach ($portalLinks as $link)
                                    @php
                                        $hasChildren = isset($link['children']) && is_array($link['children']);
                                    @endphp
                                    @if ($hasChildren)
                                        @php
                                            $dropdownActive = request()->routeIs(...((array) ($link['active'] ?? [])))
                                                || collect($link['children'])->contains(fn ($child) => $isPortalLinkActive($child));
                                        @endphp
                                        <details class="student-topnav__dropdown">
                                            <summary class="{{ $dropdownActive ? 'active' : '' }}" @if($dropdownActive) aria-current="page" @endif>{{ $link['label'] }}</summary>
                                            <div class="student-topnav__dropdown-menu">
                                                @foreach ($link['children'] as $child)
                                                    @php
                                                        $href = $child['route'] ? route($child['route'], $child['params'] ?? []) : '#';
                                                        $active = $isPortalLinkActive($child);
                                                    @endphp
                                                    <a class="{{ $active ? 'active' : '' }}" href="{{ $href }}" @if($active) aria-current="page" @endif>{{ $child['label'] }}</a>
                                                @endforeach
                                            </div>
                                        </details>
                                    @else
                                        @php
                                            $href = $link['route'] ? route($link['route'], $link['params'] ?? []) : '#';
                                            $active = $isPortalLinkActive($link);
                                        @endphp
                                        <a class="{{ $active ? 'active' : '' }}" href="{{ $href }}" @if($active) aria-current="page" @endif>{{ $link['label'] }}</a>
                                    @endif
                                @endforeach
                            </nav>
                        </div>
                    @endif
                </div>
                <div class="topbar-actions">
                    @yield('page-actions')
                    @if ($showAccountTools)
                        <details class="notification-menu">
                            <summary class="icon-button notification-trigger" aria-label="Notifikasi" title="Notifikasi">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 6.5-3 7-3 9h18c0-2-3-2.5-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                                @if ($unreadNotificationCount > 0)
                                    <span class="notification-badge" aria-hidden="true">{{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}</span>
                                    <span class="sr-only">{{ $unreadNotificationCount }} notifikasi belum dibaca</span>
                                @endif
                            </summary>
                            <section class="notification-dropdown" aria-label="Senarai notifikasi">
                                <header class="notification-dropdown__head">
                                    <div>
                                        <strong>Notifikasi</strong>
                                        <span>{{ $unreadNotificationCount > 0 ? $unreadNotificationCount.' belum dibaca' : 'Semua telah dibaca' }}</span>
                                    </div>
                                    @if ($unreadNotificationCount > 0)
                                        <form method="POST" action="{{ route('notifications.read') }}">
                                            @csrf
                                            <button class="notification-dropdown__mark-all" type="submit">Tandakan semua dibaca</button>
                                        </form>
                                    @endif
                                </header>
                                <div class="notification-dropdown__list">
                                    @forelse ($headerNotifications as $notification)
                                        <article class="notification-dropdown__item {{ $notification->read_at ? '' : 'notification-dropdown__item--unread' }}">
                                            @if ($notification->link)
                                                <a class="notification-dropdown__link" href="{{ $notification->link }}">
                                                    <span class="notification-dropdown__title">{{ $notification->title }}</span>
                                                    <p class="notification-dropdown__message">{{ $notification->message ?? 'Tiada makluman tambahan.' }}</p>
                                                </a>
                                            @else
                                                <span class="notification-dropdown__title">{{ $notification->title }}</span>
                                                <p class="notification-dropdown__message">{{ $notification->message ?? 'Tiada makluman tambahan.' }}</p>
                                            @endif
                                            <div class="notification-dropdown__meta">
                                                <span class="notification-dropdown__date">{{ $notification->created_at?->format('d/m/Y') }}</span>
                                                @if (! $notification->read_at)
                                                    <form method="POST" action="{{ route('notifications.read') }}">
                                                        @csrf
                                                        <input type="hidden" name="notification_id" value="{{ $notification->getKey() }}">
                                                        <button class="notification-dropdown__read" type="submit">Tandakan dibaca</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </article>
                                    @empty
                                        <p class="notification-dropdown__empty">Tiada notifikasi buat masa ini.</p>
                                    @endforelse
                                </div>
                                <footer class="notification-dropdown__foot">
                                    <a href="{{ route('notifications.index') }}">Lihat semua notifikasi</a>
                                </footer>
                            </section>
                        </details>
                        <details class="profile-menu">
                            <summary class="profile-chip" aria-label="Menu profil" title="Menu profil">
                                <x-user-avatar :initials="$initials" />
                                <span class="profile-chip__text"><span class="profile-chip__name">{{ $displayName }}</span><small>{{ $roleLabel }}</small></span>
                            </summary>
                            <section class="profile-dropdown" aria-label="Menu profil">
                                <header class="profile-dropdown__head">
                                    <strong>{{ $displayName }}</strong>
                                    <span>{{ $roleLabel }}</span>
                                </header>
                                <a class="profile-dropdown__item" href="{{ $profileUrl }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="3.5"/><path d="M21 21v-2a3.5 3.5 0 0 0-3-3.46"/><path d="M16.5 3.4a3.5 3.5 0 0 1 0 6.8"/></svg>
                                    Profil Saya
                                </a>
                                <a class="profile-dropdown__item" href="{{ $profileUrl }}#kata-laluan">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/><path d="M12 15v2"/></svg>
                                    Tukar Kata Laluan
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="profile-dropdown__item" type="submit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                                        Log Keluar
                                    </button>
                                </form>
                            </section>
                        </details>
                    @endif
                </div>
            </header>

            <main class="content" id="main-content" tabindex="-1">
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
    @if ($showAccountTools)
        <script>
            document.querySelectorAll('.notification-menu, .profile-menu, .student-topnav__dropdown').forEach(function (menu) {
                menu.addEventListener('toggle', function () {
                    if (!menu.open) {
                        return;
                    }

                    document.querySelectorAll('.notification-menu[open], .profile-menu[open], .student-topnav__dropdown[open]').forEach(function (otherMenu) {
                        if (otherMenu !== menu) {
                            otherMenu.open = false;
                        }
                    });
                });
            });

            document.addEventListener('click', function (event) {
                document.querySelectorAll('.notification-menu[open], .profile-menu[open], .student-topnav__dropdown[open]').forEach(function (menu) {
                    if (!menu.contains(event.target)) {
                        menu.open = false;
                    }
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    document.querySelectorAll('.notification-menu[open], .profile-menu[open], .student-topnav__dropdown[open]').forEach(function (menu) {
                        menu.open = false;
                    });
                }
            });

            @if ($showSidebar)
                document.querySelectorAll('.nav-link').forEach(function (link) {
                    link.addEventListener('click', function () {
                        const sidebarToggle = document.getElementById('sidebar-toggle');

                        if (sidebarToggle && window.matchMedia('(max-width: 1024px)').matches) {
                            sidebarToggle.checked = false;
                        }
                    });
                });
            @endif
        </script>
    @endif
</body>
</html>
