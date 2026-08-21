<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\CooperativeEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CooperativeEventController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $auth = $this->admin($request);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        return view('admin.events.index', [
            ...$auth,
            'events' => CooperativeEvent::query()->orderBy('event_date')->latest()->paginate(20),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $auth = $this->admin($request);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        return view('admin.events.form', [
            ...$auth,
            'event' => new CooperativeEvent(['category' => 'Umum', 'is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $auth = $this->admin($request);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        CooperativeEvent::query()->create($this->validated($request) + [
            'created_by' => $auth['user']->id_admin,
        ]);

        return redirect()->route('admin.events.index')->with('status', 'Aktiviti koperasi berjaya ditambah.');
    }

    public function edit(Request $request, CooperativeEvent $event): View|RedirectResponse
    {
        $auth = $this->admin($request);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        return view('admin.events.form', compact('event') + $auth);
    }

    public function update(Request $request, CooperativeEvent $event): RedirectResponse
    {
        $auth = $this->admin($request);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $event->update($this->validated($request));

        return redirect()->route('admin.events.index')->with('status', 'Aktiviti koperasi berjaya dikemaskini.');
    }

    public function destroy(Request $request, CooperativeEvent $event): RedirectResponse
    {
        $auth = $this->admin($request);

        if ($auth instanceof RedirectResponse) {
            return $auth;
        }

        $event->delete();

        return back()->with('status', 'Aktiviti koperasi berjaya dipadam.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:500'],
            'event_date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:60'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => false];
    }

    private function admin(Request $request): array|RedirectResponse
    {
        if ($request->session()->get('auth_role') !== 'admin') {
            return redirect()->route('login');
        }

        $user = AdminUser::query()->find($request->session()->get('auth_id'));

        if (! $user) {
            return redirect()->route('login');
        }

        return ['role' => 'admin', 'user' => $user];
    }
}
