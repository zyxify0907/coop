<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $role = $request->session()->get('auth_role');

        if (! $role) {
            return redirect()->route('login');
        }

        $user = $this->currentUser($request);
        $announcements = Announcement::query()
            ->visibleTo($role)
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(12);

        return view('announcements.index', compact('role', 'user', 'announcements'));
    }

    public function adminIndex(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'admin') {
            return redirect()->route('login');
        }

        $role = 'admin';
        $user = $this->currentUser($request);
        $announcements = Announcement::query()
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(15);

        return view('admin.announcements.index', compact('role', 'user', 'announcements'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'admin') {
            return redirect()->route('login');
        }

        $role = 'admin';
        $user = $this->currentUser($request);
        $announcement = new Announcement(['audience' => 'all', 'category' => 'General', 'is_active' => true]);

        return view('admin.announcements.form', compact('role', 'user', 'announcement'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        Announcement::query()->create($this->validated($request) + [
            'created_by' => $request->session()->get('auth_id'),
        ]);

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement berjaya ditambah.');
    }

    public function edit(Request $request, Announcement $announcement): View|RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'admin') {
            return redirect()->route('login');
        }

        $role = 'admin';
        $user = $this->currentUser($request);

        return view('admin.announcements.form', compact('role', 'user', 'announcement'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->ensureAdmin($request);
        $announcement->update($this->validated($request));

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement berjaya dikemaskini.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->ensureAdmin($request);
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement berjaya dipadam.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string'],
            'category' => ['required', 'in:Important,Reminder,Update,General'],
            'audience' => ['required', 'in:all,ahli,staff,admin'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_pinned' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'is_pinned' => false,
            'is_active' => false,
        ];
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->session()->get('auth_role') === 'admin', 403);
    }

    private function currentUser(Request $request): mixed
    {
        $role = $request->session()->get('auth_role');
        $id = $request->session()->get('auth_id');

        return match ($role) {
            'admin' => \App\Models\AdminUser::query()->find($id),
            'staff' => \App\Models\Pekerja::query()->find($id),
            'ahli' => \App\Models\Ahli::query()->find($id),
            default => null,
        };
    }
}
