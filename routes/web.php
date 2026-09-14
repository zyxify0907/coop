<?php

use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\AhliController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CooperativeController;
use App\Http\Controllers\CooperativeEventController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\StaffPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/forgot-password', [AuthController::class, 'resetForgottenPassword'])->name('password.forgot.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('auth.dashboard');
Route::get('/dashboard/chart-data', [AuthController::class, 'dashboardChartData'])->name('auth.dashboard.chart-data');
Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
Route::get('/admin/dashboard', [AuthController::class, 'adminDashboard'])->name('admin.dashboard');
Route::get('/admin/profile', [AdminProfileController::class, 'profile'])->name('admin.profile');
Route::patch('/admin/profile/password', [AdminProfileController::class, 'updatePassword'])->name('admin.profile.password');
Route::get('/student/dashboard-saham', [AuthController::class, 'studentShareDashboard'])->name('student.dashboard.saham');
Route::get('/student/profile', [AuthController::class, 'studentProfile'])->name('student.profile');
Route::patch('/student/profile', [AuthController::class, 'updateStudentProfile'])->name('student.profile.update');
Route::patch('/student/profile/password', [AuthController::class, 'updateStudentPassword'])->name('student.profile.password');
Route::get('/student/permohonan-status', [PermohonanController::class, 'studentStatus'])->name('student.permohonan.status');
Route::get('/student/tempahan', [OperationsController::class, 'studentOrders'])->name('student.tempahan.index');
Route::post('/student/tempahan', [OperationsController::class, 'storeStudentOrder'])->name('student.tempahan.store');
Route::post('/student/tempahan/troli', [OperationsController::class, 'addStudentOrderCart'])->name('student.tempahan.cart.add');
Route::patch('/student/tempahan/troli/{itemId}', [OperationsController::class, 'updateStudentOrderCart'])->name('student.tempahan.cart.update');
Route::post('/student/tempahan/troli/hantar', [OperationsController::class, 'checkoutStudentOrderCart'])->name('student.tempahan.cart.checkout');
Route::delete('/student/tempahan/troli/{itemId}', [OperationsController::class, 'removeStudentOrderCart'])->name('student.tempahan.cart.remove');
Route::middleware('staff.type:lecturer_member,coop_staff,clothing_staff,share_staff,coop_manager')->group(function (): void {
    Route::get('/student/permohonan/{jenis?}', [PermohonanController::class, 'studentIndex'])->name('student.permohonan.index');
    Route::post('/student/permohonan/{jenis}', [PermohonanController::class, 'store'])->name('student.permohonan.store');
    Route::get('/koperasi/dokumen', [CooperativeController::class, 'documents'])->name('koperasi.documents.index');
    Route::post('/koperasi/dokumen', [CooperativeController::class, 'storeDocument'])->name('koperasi.documents.store');
    Route::get('/koperasi/dokumen/{document}/view', [CooperativeController::class, 'viewDocument'])->name('koperasi.documents.view');
    Route::get('/koperasi/dokumen/{document}/download', [CooperativeController::class, 'downloadDocument'])->name('koperasi.documents.download');
    Route::delete('/koperasi/dokumen/{document}', [CooperativeController::class, 'destroyDocument'])->name('koperasi.documents.destroy');
    Route::get('/koperasi/transaksi-saham', [CooperativeController::class, 'transactions'])->name('koperasi.transactions.index');
    Route::get('/koperasi/notifikasi', [CooperativeController::class, 'notifications'])->name('koperasi.notifications.index');
    Route::post('/koperasi/notifikasi/read', [CooperativeController::class, 'markNotificationsRead'])->name('koperasi.notifications.read');
});

Route::prefix('lecturer-member')->middleware('staff.type:strict,lecturer_member')->name('lecturer-member.')->group(function (): void {
    Route::get('/dashboard', [StaffPortalController::class, 'lecturerDashboard'])->name('dashboard');
    Route::get('/dashboard-saham', [StaffPortalController::class, 'shareDashboard'])->name('dashboard.saham');
    Route::get('/permohonan/{jenis?}', [PermohonanController::class, 'studentIndex'])->name('permohonan.index');
    Route::post('/permohonan/{jenis}', [PermohonanController::class, 'store'])->name('permohonan.store');
    Route::get('/saham', [StaffPortalController::class, 'shares'])->name('shares');
    Route::get('/profil', [StaffPortalController::class, 'profile'])->name('profile');
    Route::patch('/profil', [StaffPortalController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profil/password', [StaffPortalController::class, 'updatePassword'])->name('profile.password');
});

Route::prefix('coop-staff')->middleware('staff.type:strict,coop_staff')->name('coop-staff.')->group(function (): void {
    Route::get('/dashboard', [StaffPortalController::class, 'cooperativeDashboard'])->name('dashboard');
    Route::get('/profil', [StaffPortalController::class, 'profile'])->name('profile');
    Route::patch('/profil', [StaffPortalController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profil/password', [StaffPortalController::class, 'updatePassword'])->name('profile.password');
});

Route::prefix('share-staff')->middleware('staff.type:strict,share_staff')->name('share-staff.')->group(function (): void {
    Route::get('/dashboard', [StaffPortalController::class, 'shareManagerDashboard'])->name('dashboard');
    Route::get('/dashboard-saham', [StaffPortalController::class, 'shareDashboard'])->name('dashboard.saham');
    Route::get('/permohonan/{jenis?}', [PermohonanController::class, 'studentIndex'])->name('permohonan.index');
    Route::post('/permohonan/{jenis}', [PermohonanController::class, 'store'])->name('permohonan.store');
    Route::get('/saham-sendiri', [StaffPortalController::class, 'shares'])->name('shares');
    Route::get('/profil', [StaffPortalController::class, 'profile'])->name('profile');
    Route::patch('/profil', [StaffPortalController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profil/password', [StaffPortalController::class, 'updatePassword'])->name('profile.password');
});

Route::prefix('coop-manager')->middleware('staff.type:strict,coop_manager')->name('coop-manager.')->group(function (): void {
    Route::get('/dashboard', [StaffPortalController::class, 'coopManagerDashboard'])->name('dashboard');
    Route::get('/dashboard-saham', [StaffPortalController::class, 'shareDashboard'])->name('dashboard.saham');
    Route::get('/dashboard-koperasi', [AttendanceController::class, 'dashboard'])->name('dashboard.koperasi');
    Route::get('/permohonan/{jenis?}', [PermohonanController::class, 'studentIndex'])->name('permohonan.index');
    Route::post('/permohonan/{jenis}', [PermohonanController::class, 'store'])->name('permohonan.store');
    Route::get('/saham-sendiri', [StaffPortalController::class, 'shares'])->name('shares');
    Route::get('/profil', [StaffPortalController::class, 'profile'])->name('profile');
    Route::patch('/profil', [StaffPortalController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profil/password', [StaffPortalController::class, 'updatePassword'])->name('profile.password');
});

Route::prefix('coop-staff/attendance')->middleware('attendance.access:worker')->name('coop-staff.attendance.')->group(function (): void {
    Route::get('/', [AttendanceController::class, 'workerIndex'])->name('index');
    Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
    Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');
    Route::get('/history', [AttendanceController::class, 'history'])->name('history');
    Route::get('/corrections', [AttendanceController::class, 'corrections'])->name('corrections');
    Route::post('/corrections', [AttendanceController::class, 'storeCorrection'])->name('corrections.store');
    Route::get('/corrections/{correction}/document', [AttendanceController::class, 'downloadCorrectionDocument'])->name('corrections.document');
});

Route::prefix('clothing-staff')->middleware('staff.type:strict,clothing_staff')->name('clothing-staff.')->group(function (): void {
    Route::get('/dashboard', [StaffPortalController::class, 'clothingDashboard'])->name('dashboard');
    Route::get('/dashboard-saham', [StaffPortalController::class, 'shareDashboard'])->name('dashboard.saham');
    Route::get('/dashboard-baju', [StaffPortalController::class, 'clothingOrderDashboard'])->name('dashboard.baju');
    Route::get('/permohonan/{jenis?}', [PermohonanController::class, 'studentIndex'])->name('permohonan.index');
    Route::post('/permohonan/{jenis}', [PermohonanController::class, 'store'])->name('permohonan.store');
    Route::get('/saham', [StaffPortalController::class, 'shares'])->name('shares');
    Route::get('/baju', [OperationsController::class, 'adminBaju'])->name('baju.index');
    Route::post('/baju', [OperationsController::class, 'storeAdminBaju'])->name('baju.store');
    Route::get('/baju/{id}/edit', [OperationsController::class, 'editAdminBaju'])->name('baju.edit');
    Route::put('/baju/{id}', [OperationsController::class, 'updateAdminBaju'])->name('baju.update');
    Route::delete('/baju/{id}', [OperationsController::class, 'destroyAdminBaju'])->name('baju.destroy');
    Route::get('/tempahan', [OperationsController::class, 'tempahan'])->name('orders.index');
    Route::put('/tempahan/{id}', [OperationsController::class, 'updateTempahan'])->name('orders.update');
    Route::delete('/tempahan/{id}', [OperationsController::class, 'destroyTempahan'])->name('orders.destroy');
    Route::get('/notifikasi', [CooperativeController::class, 'notifications'])->name('notifications.index');
    Route::post('/notifikasi/read', [CooperativeController::class, 'markNotificationsRead'])->name('notifications.read');
    Route::get('/profil', [StaffPortalController::class, 'profile'])->name('profile');
    Route::patch('/profil', [StaffPortalController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profil/password', [StaffPortalController::class, 'updatePassword'])->name('profile.password');
});

Route::get('/notifikasi', [CooperativeController::class, 'notifications'])->name('notifications.index');
Route::post('/notifikasi/read', [CooperativeController::class, 'markNotificationsRead'])->name('notifications.read');

Route::get('/admin/users', [AdminProfileController::class, 'index'])->name('admin.users.index');
Route::get('/admin/users/students', [AdminProfileController::class, 'students'])->name('admin.users.students');
Route::get('/admin/users/staff', [AdminProfileController::class, 'staff'])->name('admin.users.staff');
Route::get('/admin/users/pekerja-koperasi', [AdminProfileController::class, 'coopWorkers'])->name('admin.users.coop-workers');
Route::get('/admin/users/{type}/create', [AdminProfileController::class, 'create'])->name('admin.users.create');
Route::post('/admin/users/students', [AdminProfileController::class, 'storeStudent'])->name('admin.users.students.store');
Route::post('/admin/users/students/import', [AdminProfileController::class, 'importStudents'])->name('admin.users.students.import');
Route::post('/admin/users/staff', [AdminProfileController::class, 'storeStaff'])->name('admin.users.staff.store');
Route::post('/admin/users/staff/import', [AdminProfileController::class, 'importStaff'])->name('admin.users.staff.import');
Route::get('/admin/users/{type}/{id}/edit', [AdminProfileController::class, 'edit'])->name('admin.users.edit');
Route::put('/admin/users/{type}/{id}', [AdminProfileController::class, 'update'])->name('admin.users.update');
Route::delete('/admin/users/{type}/{id}', [AdminProfileController::class, 'destroy'])->name('admin.users.destroy');

Route::get('/admin/ahli', [AhliController::class, 'index'])->name('admin.ahli.index');
Route::post('/admin/ahli/import', [AhliController::class, 'import'])->name('admin.ahli.import');
Route::get('/admin/anggota', [AhliController::class, 'anggotaIndex'])->name('admin.anggota.index');
Route::get('/admin/anggota-student', [AhliController::class, 'anggotaStudents'])->name('admin.anggota.students');
Route::get('/admin/anggota-staff', [AhliController::class, 'anggotaStaff'])->name('admin.anggota.staff');
Route::get('/admin/anggota/{permohonan}', [AhliController::class, 'anggotaShow'])->name('admin.anggota.show');
Route::delete('/admin/anggota/{permohonan}', [AhliController::class, 'anggotaDestroy'])->name('admin.anggota.destroy');

Route::middleware('staff.type:strict,clothing_staff')->group(function (): void {
    Route::get('/staff/tempahan', [OperationsController::class, 'tempahan'])->name('staff.tempahan.index');
    Route::put('/staff/tempahan/{id}', [OperationsController::class, 'updateTempahan'])->name('staff.tempahan.update');
    Route::delete('/staff/tempahan/{id}', [OperationsController::class, 'destroyTempahan'])->name('staff.tempahan.destroy');
    Route::get('/staff/tempahan-baju', [OperationsController::class, 'adminBaju'])->name('staff.baju.index');
    Route::post('/staff/tempahan-baju', [OperationsController::class, 'storeAdminBaju'])->name('staff.baju.store');
    Route::get('/staff/tempahan-baju/{id}/edit', [OperationsController::class, 'editAdminBaju'])->name('staff.baju.edit');
    Route::put('/staff/tempahan-baju/{id}', [OperationsController::class, 'updateAdminBaju'])->name('staff.baju.update');
    Route::delete('/staff/tempahan-baju/{id}', [OperationsController::class, 'destroyAdminBaju'])->name('staff.baju.destroy');
    Route::get('/staff/senarai-tempahan-baju', fn () => redirect()->route('clothing-staff.orders.index'))->name('staff.baju.orders.index');
    Route::put('/staff/tempahan-baju/tempahan/{id}', [OperationsController::class, 'updateAdminTempahanBaju'])->name('staff.baju.orders.update');
    Route::delete('/staff/tempahan-baju/tempahan/{id}', [OperationsController::class, 'destroyAdminTempahanBaju'])->name('staff.baju.orders.destroy');
    Route::get('/staff/stok', [OperationsController::class, 'stok'])->name('staff.stok.index');
    Route::post('/staff/stok', [OperationsController::class, 'storeStok'])->name('staff.stok.store');
    Route::put('/staff/stok/{id}', [OperationsController::class, 'updateStok'])->name('staff.stok.update');
    Route::delete('/staff/stok/{id}', [OperationsController::class, 'destroyStok'])->name('staff.stok.destroy');
    Route::get('/staff/jualan', [OperationsController::class, 'jualan'])->name('staff.jualan.index');
    Route::post('/staff/jualan', [OperationsController::class, 'storeJualan'])->name('staff.jualan.store');
    Route::get('/staff/workflow', [CooperativeController::class, 'staffWorkflow'])->name('staff.workflow.index');
});

Route::get('/admin/tempahan', [OperationsController::class, 'tempahan'])->name('admin.tempahan.index');
Route::put('/admin/tempahan/{id}', [OperationsController::class, 'updateTempahan'])->name('admin.tempahan.update');
Route::delete('/admin/tempahan/{id}', [OperationsController::class, 'destroyTempahan'])->name('admin.tempahan.destroy');
Route::get('/admin/tempahan-baju', [OperationsController::class, 'adminBaju'])->name('admin.baju.index');
Route::post('/admin/tempahan-baju', [OperationsController::class, 'storeAdminBaju'])->name('admin.baju.store');
Route::get('/admin/tempahan-baju/{id}/edit', [OperationsController::class, 'editAdminBaju'])->name('admin.baju.edit');
Route::put('/admin/tempahan-baju/{id}', [OperationsController::class, 'updateAdminBaju'])->name('admin.baju.update');
Route::delete('/admin/tempahan-baju/{id}', [OperationsController::class, 'destroyAdminBaju'])->name('admin.baju.destroy');
Route::get('/admin/senarai-tempahan-baju', fn () => redirect()->route('admin.tempahan.index'))->name('admin.baju.orders.index');
Route::put('/admin/tempahan-baju/tempahan/{id}', [OperationsController::class, 'updateAdminTempahanBaju'])->name('admin.baju.orders.update');
Route::delete('/admin/tempahan-baju/tempahan/{id}', [OperationsController::class, 'destroyAdminTempahanBaju'])->name('admin.baju.orders.destroy');
Route::get('/admin/dashboard/saham', [OperationsController::class, 'shareDashboard'])->name('admin.dashboard.saham');
Route::get('/admin/dashboard/baju', [OperationsController::class, 'clothingDashboard'])->name('admin.dashboard.baju');

Route::get('/admin/vendors', [OperationsController::class, 'vendors'])->name('admin.vendors.index');
Route::post('/admin/vendors', [OperationsController::class, 'storeVendor'])->name('admin.vendors.store');
Route::put('/admin/vendors/{id}', [OperationsController::class, 'updateVendor'])->name('admin.vendors.update');
Route::delete('/admin/vendors/{id}', [OperationsController::class, 'destroyVendor'])->name('admin.vendors.destroy');
Route::get('/admin/pembayaran', [OperationsController::class, 'payments'])->name('admin.pembayaran.index');
Route::post('/admin/pembayaran', [OperationsController::class, 'storePayment'])->name('admin.pembayaran.store');
Route::get('/admin/saham', [OperationsController::class, 'saham'])->name('admin.saham.index');
Route::get('/admin/saham/export/csv', [OperationsController::class, 'exportSahamCsv'])->name('admin.saham.export.csv');
Route::post('/admin/saham', [OperationsController::class, 'storeSaham'])->name('admin.saham.store');
Route::put('/admin/saham/staff/{staff}', [OperationsController::class, 'updateStaffSaham'])->name('admin.saham.staff.update');
Route::put('/admin/saham/{saham}', [OperationsController::class, 'updateSaham'])->name('admin.saham.update');
Route::delete('/admin/saham/{saham}', [OperationsController::class, 'destroySaham'])->name('admin.saham.destroy');
Route::get('/admin/settings-koperasi', [CooperativeController::class, 'settings'])->name('admin.settings.index');
Route::put('/admin/settings-koperasi', [CooperativeController::class, 'updateSettings'])->name('admin.settings.update');
Route::get('/admin/audit-log', [CooperativeController::class, 'auditLog'])->name('admin.audit.index');
Route::get('/admin/events', [CooperativeEventController::class, 'index'])->name('admin.events.index');
Route::get('/admin/events/create', [CooperativeEventController::class, 'create'])->name('admin.events.create');
Route::post('/admin/events', [CooperativeEventController::class, 'store'])->name('admin.events.store');
Route::get('/admin/events/{event}/edit', [CooperativeEventController::class, 'edit'])->name('admin.events.edit');
Route::put('/admin/events/{event}', [CooperativeEventController::class, 'update'])->name('admin.events.update');
Route::delete('/admin/events/{event}', [CooperativeEventController::class, 'destroy'])->name('admin.events.destroy');
Route::get('/admin/announcements', [AnnouncementController::class, 'adminIndex'])->name('admin.announcements.index');
Route::get('/admin/announcements/create', [AnnouncementController::class, 'create'])->name('admin.announcements.create');
Route::post('/admin/announcements', [AnnouncementController::class, 'store'])->name('admin.announcements.store');
Route::get('/admin/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('admin.announcements.edit');
Route::put('/admin/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('admin.announcements.update');
Route::delete('/admin/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('admin.announcements.destroy');

Route::prefix('admin/attendance')->middleware('attendance.access:admin')->name('admin.attendance.')->group(function (): void {
    Route::get('/', [AttendanceController::class, 'adminIndex'])->name('index');
    Route::get('/dashboard', [AttendanceController::class, 'dashboard'])->name('dashboard');
    Route::get('/live', [AttendanceController::class, 'live'])->name('live');
    Route::get('/records', [AttendanceController::class, 'records'])->name('records');
    Route::get('/records/{record}', [AttendanceController::class, 'show'])->name('show');
    Route::delete('/records/{record}', [AttendanceController::class, 'destroyRecord'])->name('destroy');
    Route::get('/workers', [AttendanceController::class, 'workers'])->name('workers');
    Route::get('/corrections', [AttendanceController::class, 'correctionsAdmin'])->name('corrections');
    Route::put('/corrections/{correction}', [AttendanceController::class, 'reviewCorrection'])->name('corrections.review');
    Route::get('/reports', [AttendanceController::class, 'reports'])->name('reports');
    Route::get('/reports/export', [AttendanceController::class, 'exportReport'])->name('reports.export');
    Route::get('/settings', [AttendanceController::class, 'settings'])->name('settings');
    Route::put('/settings', [AttendanceController::class, 'updateSettings'])->name('settings.update');
    Route::get('/corrections/{correction}/document', [AttendanceController::class, 'downloadCorrectionDocument'])->name('corrections.document');
});
Route::get('/admin/permohonan', [PermohonanController::class, 'adminIndex'])->name('admin.permohonan.index');
Route::get('/admin/permohonan/{permohonan}/tambah-saham', [PermohonanController::class, 'createShareAddition'])->name('admin.permohonan.saham.create');
Route::post('/admin/permohonan/{permohonan}/tambah-saham', [PermohonanController::class, 'storeShareAddition'])->name('admin.permohonan.saham.store');
Route::get('/admin/permohonan/{permohonan}/proses-pengeluaran', [PermohonanController::class, 'createWithdrawalProcess'])->name('admin.permohonan.pengeluaran.create');
Route::post('/admin/permohonan/{permohonan}/proses-pengeluaran', [PermohonanController::class, 'storeWithdrawalProcess'])->name('admin.permohonan.pengeluaran.store');
Route::get('/admin/permohonan/{permohonan}', [PermohonanController::class, 'show'])->name('admin.permohonan.show');
Route::put('/admin/permohonan/{permohonan}', [PermohonanController::class, 'update'])->name('admin.permohonan.update');
Route::delete('/admin/permohonan/{permohonan}', [PermohonanController::class, 'destroy'])->name('admin.permohonan.destroy');
Route::get('/admin/reports', [OperationsController::class, 'reports'])->name('admin.reports.index');
Route::get('/rules', [OperationsController::class, 'rules'])->name('rules.index');

Route::any('/koperasi/{any?}', fn () => redirect()->route('auth.dashboard'))->where('any', '.*');
Route::any('/admin/koperasi/{any?}', fn () => redirect()->route('auth.dashboard'))->where('any', '.*');
