<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Ahli;
use App\Models\Pekerja;
use App\Services\ProfileImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileImageController extends Controller
{
    public function destroy(Request $request, ProfileImageService $images): RedirectResponse
    {
        // Resolve the owner from the session, never from a submitted user ID.
        $user = match ($request->session()->get('auth_role')) {
            'admin' => AdminUser::query()->find($request->session()->get('auth_id')),
            'ahli' => Ahli::query()->find($request->session()->get('auth_id')),
            'staff' => Pekerja::query()->find($request->session()->get('auth_id')),
            default => null,
        };

        if (! $user) {
            return redirect()->route('login');
        }

        abort_unless($user->status_aktif, 403);
        $images->remove($user);

        return back()->with('success', 'Gambar profil berjaya dibuang.');
    }

    public function destroyUser(Request $request, string $type, int $id, ProfileImageService $images): RedirectResponse
    {
        abort_unless($request->session()->get('auth_role') === 'admin', 403);
        abort_unless(AdminUser::query()
            ->whereKey($request->session()->get('auth_id'))
            ->where('status_aktif', true)
            ->exists(), 403);

        $user = match ($type) {
            'student' => Ahli::query()->findOrFail($id),
            'staff' => Pekerja::query()->findOrFail($id),
            default => abort(404),
        };

        $images->remove($user);

        return back()->with('status', 'Gambar profil pengguna berjaya dibuang.');
    }
}
