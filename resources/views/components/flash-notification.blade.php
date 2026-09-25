@php
    $notificationType = null;
    $notificationMessage = null;
    $notificationTitle = null;
    $notificationLink = null;
    $suppressDialogNotice = request()->boolean('dialog');
    $unreadPopupNotification = isset($headerNotifications)
        ? $headerNotifications->first(fn ($notification) => ! $notification->read_at)
        : null;

    if ($suppressDialogNotice) {
        $notificationType = null;
    } elseif (session('error') || $errors->any()) {
        $notificationType = 'error';
        $notificationTitle = 'Tidak berjaya';
        $notificationMessage = session('error') ?: $errors->first();
    } elseif (session('warning')) {
        $notificationType = 'warning';
        $notificationTitle = 'Perhatian';
        $notificationMessage = session('warning');
    } elseif (session('success') || session('status')) {
        $notificationType = 'success';
        $notificationTitle = 'Berjaya';
        $notificationMessage = session('success') ?: session('status');
    } elseif ($unreadPopupNotification) {
        $notificationType = 'success';
        $notificationTitle = $unreadPopupNotification->title;
        $notificationMessage = $unreadPopupNotification->message ?? 'Sila semak notifikasi anda.';
        $notificationLink = $unreadPopupNotification->link;
    }
@endphp

@if ($notificationMessage)
    <div class="system-notice system-notice--{{ $notificationType }}" data-system-notice role="{{ $notificationType === 'error' ? 'alert' : 'status' }}" aria-live="{{ $notificationType === 'error' ? 'assertive' : 'polite' }}">
        <span class="system-notice__icon" aria-hidden="true">
            @if ($notificationType === 'success')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4.2 4.2L19 6.5"/></svg>
            @elseif ($notificationType === 'warning')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4"/><path d="M12 16h.01"/><path d="M10.3 3.9 2.5 17.2A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
            @else
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
            @endif
        </span>
        <div class="system-notice__copy">
            <strong>{{ $notificationTitle }}</strong>
            <span>{{ $notificationMessage }}</span>
            @if ($notificationLink)
                <a href="{{ $notificationLink }}">Lihat maklumat</a>
            @endif
        </div>
        <button class="system-notice__close" type="button" data-system-notice-close aria-label="Tutup notifikasi">&times;</button>
    </div>
@endif

<style>
    .system-notice {
        position: fixed;
        top: max(20px, env(safe-area-inset-top));
        right: 24px;
        z-index: 200;
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr) 32px;
        align-items: start;
        gap: 12px;
        width: min(430px, calc(100vw - 32px));
        padding: 14px;
        border: 1px solid;
        border-radius: 8px;
        box-shadow: 0 18px 38px rgba(15, 23, 42, .16);
        animation: system-notice-in 180ms ease-out;
    }

    .system-notice--success { background: #f0fdf4; border-color: #86efac; color: #166534; }
    .system-notice--error { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
    .system-notice--warning { background: #fffbeb; border-color: #fcd34d; color: #92400e; }

    .system-notice__icon {
        display: grid;
        width: 36px;
        height: 36px;
        place-items: center;
        border-radius: 50%;
        background: currentColor;
        color: #fff;
    }

    .system-notice__icon svg { width: 20px; height: 20px; }
    .system-notice__copy { display: grid; gap: 2px; padding-top: 1px; min-width: 0; }
    .system-notice__copy strong { font-size: 14px; line-height: 1.35; }
    .system-notice__copy span { font-size: 13px; line-height: 1.45; overflow-wrap: anywhere; }
    .system-notice__copy a { margin-top: 4px; color: currentColor; font-size: 13px; font-weight: 900; text-decoration: underline; text-underline-offset: 3px; }
    .system-notice__close { display: grid; width: 32px; height: 32px; place-items: center; padding: 0; border: 0; border-radius: 6px; background: transparent; color: currentColor; font-size: 22px; line-height: 1; }
    .system-notice__close:hover { background: rgba(15, 23, 42, .08); }
    .system-notice.is-closing { opacity: 0; transform: translateY(-8px); transition: opacity 160ms ease, transform 160ms ease; }

    .content > :is(.alert, .staff-alert),
    .login-card > .alert,
    .reset-card > .alert { display: none !important; }

    @keyframes system-notice-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

    @media (max-width: 680px) {
        .system-notice { top: 12px; right: 16px; width: calc(100vw - 32px); }
    }
</style>

<script>
    const bindSystemNotice = (notice) => {
        const close = () => {
            notice.classList.add('is-closing');
            window.setTimeout(() => notice.remove(), 180);
        };

        notice.querySelector('[data-system-notice-close]')?.addEventListener('click', close);

        if (notice.classList.contains('system-notice--success')) {
            window.setTimeout(close, 6000);
        }
    };

    window.showSystemNotice = (payload) => {
        if (!payload || !payload.message) {
            return;
        }

        document.querySelectorAll('[data-system-notice]').forEach((notice) => notice.remove());

        const type = ['success', 'error', 'warning'].includes(payload.type) ? payload.type : 'success';
        const notice = document.createElement('div');
        notice.className = `system-notice system-notice--${type}`;
        notice.dataset.systemNotice = '';
        notice.setAttribute('role', type === 'error' ? 'alert' : 'status');
        notice.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');

        const icon = document.createElement('span');
        icon.className = 'system-notice__icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.innerHTML = type === 'success'
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4.2 4.2L19 6.5"/></svg>'
            : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>';

        const copy = document.createElement('div');
        copy.className = 'system-notice__copy';
        const title = document.createElement('strong');
        title.textContent = payload.title || (type === 'error' ? 'Tidak berjaya' : 'Berjaya');
        const message = document.createElement('span');
        message.textContent = payload.message;
        copy.append(title, message);

        if (payload.link) {
            const link = document.createElement('a');
            link.href = payload.link;
            link.textContent = 'Lihat maklumat';
            copy.append(link);
        }

        const closeButton = document.createElement('button');
        closeButton.className = 'system-notice__close';
        closeButton.type = 'button';
        closeButton.dataset.systemNoticeClose = '';
        closeButton.setAttribute('aria-label', 'Tutup notifikasi');
        closeButton.textContent = '×';

        notice.append(icon, copy, closeButton);
        document.body.append(notice);
        bindSystemNotice(notice);
    };

    document.querySelectorAll('[data-system-notice]').forEach(bindSystemNotice);

    const dialogNotice = window.sessionStorage.getItem('coopbest:dialogNotice');
    if (dialogNotice) {
        window.sessionStorage.removeItem('coopbest:dialogNotice');
        try {
            window.showSystemNotice(JSON.parse(dialogNotice));
        } catch (error) {
            // Ignore malformed stored notice payloads.
        }
    }
</script>
