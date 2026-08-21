<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Ahli;
use App\Models\AhliImport;
use App\Models\Pekerja;
use App\Models\Permohonan;
use App\Notifications\AhliImportCompleted;
use App\Services\AhliImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AhliController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->session()->get('auth_role');
        $user = match ($role) {
            'admin' => AdminUser::query()->find($request->session()->get('auth_id')),
            'staff' => Pekerja::query()->find($request->session()->get('auth_id')),
            default => null,
        };

        return view('admin.ahli.index', [
            'role' => $role,
            'user' => $user,
            'ahli' => Ahli::query()->with('saham')->orderByDesc('id_ahli')->paginate(20),
            'latestImport' => AhliImport::query()->latest()->first(),
        ]);
    }

    public function import(Request $request, AhliImportService $importer): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $validated['file'];
        $result = $importer->import($file);

        $adminEmail = config('mail.from.address');

        if ($adminEmail) {
            Notification::route('mail', $adminEmail)->notify(new AhliImportCompleted($result));
        }

        return redirect()
            ->route('admin.ahli.index')
            ->with('status', "Berjaya mengimport {$result->importedCount} rekod. Gagal: {$result->failedCount} rekod.");
    }

    public function anggotaIndex(Request $request): View|RedirectResponse
    {
        $role = $request->session()->get('auth_role');

        if ($role !== 'admin') {
            return redirect()->route('login');
        }

        $user = AdminUser::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        return view('admin.anggota.index', [
            'role' => $role,
            'user' => $user,
            'members' => Permohonan::query()
                ->with(['ahli.saham'])
                ->where('jenis', 'anggota')
                ->where('status', 'diluluskan')
                ->latest('tarikh_keputusan')
                ->latest('id_permohonan')
                ->paginate(20),
        ]);
    }

    public function anggotaShow(Request $request, Permohonan $permohonan): View|RedirectResponse
    {
        $role = $request->session()->get('auth_role');

        if ($role !== 'admin') {
            return redirect()->route('login');
        }

        $user = AdminUser::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        abort_unless($permohonan->jenis === 'anggota' && $permohonan->status === 'diluluskan', 404);

        return view('admin.anggota.show', [
            'role' => $role,
            'user' => $user,
            'memberApplication' => $permohonan->load('ahli.saham'),
        ]);
    }
}
