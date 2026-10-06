<dialog id="profile-image-viewer" aria-labelledby="profile-image-viewer-title">
    <header class="profile-image-viewer__header">
        <h2 id="profile-image-viewer-title">Gambar profil</h2>
        <button type="button" class="profile-image-viewer__close" aria-label="Tutup gambar profil" autofocus>
            <span aria-hidden="true">&times;</span>
        </button>
    </header>
    <div class="profile-image-viewer__body">
        <p class="profile-image-viewer__status" role="status">Memuatkan gambar…</p>
        <img class="profile-image-viewer__image" alt="Gambar profil" hidden>
    </div>
</dialog>

<style>
    .profile-photo-viewable { position: relative; }
    .profile-photo-viewable > .profile-photo-trigger {
        position: absolute;
        inset: 0;
        z-index: 1;
        display: block;
        width: 100%;
        height: 100%;
        min-width: 0;
        min-height: 0;
        margin: 0;
        padding: 0;
        border: 0;
        border-radius: inherit;
        background: transparent;
        box-shadow: none;
        cursor: zoom-in;
    }
    .profile-photo-viewable > .profile-photo-trigger:focus-visible {
        outline: 3px solid #2563EB;
        outline-offset: -3px;
    }
    #profile-image-viewer {
        width: min(720px, calc(100vw - 32px));
        max-width: none;
        max-height: calc(100dvh - 32px);
        margin: auto;
        padding: 0;
        overflow: auto;
        border: 1px solid #D7E3F1;
        border-radius: 16px;
        background: #FFFFFF;
        color: #082F59;
        box-shadow: 0 24px 80px rgba(0, 0, 0, .3);
    }
    #profile-image-viewer::backdrop { background: rgba(8, 25, 45, .75); }
    .profile-image-viewer__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 18px;
        border-bottom: 1px solid #D7E3F1;
    }
    #profile-image-viewer-title { margin: 0; font-size: 18px; }
    .profile-image-viewer__close {
        flex: 0 0 40px;
        width: 40px;
        height: 40px;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: #EDF3FB;
        color: #082F59;
        font-size: 28px;
        cursor: pointer;
    }
    .profile-image-viewer__close:focus-visible { outline: 2px solid #2563EB; outline-offset: 2px; }
    .profile-image-viewer__body { padding: 16px; text-align: center; }
    .profile-image-viewer__status { margin: 24px 0; }
    #profile-image-viewer .profile-image-viewer__image {
        display: block;
        width: 100%;
        height: auto;
        max-height: calc(100dvh - 132px);
        object-fit: contain;
        border-radius: 0;
    }
    #profile-image-viewer [hidden] { display: none; }
</style>

<script>
    (() => {
        const viewer = document.getElementById('profile-image-viewer');
        const photo = viewer.querySelector('.profile-image-viewer__image');
        const status = viewer.querySelector('.profile-image-viewer__status');
        const closeButton = viewer.querySelector('.profile-image-viewer__close');
        let opener = null;
        let previousOverflow = '';

        photo.addEventListener('load', () => {
            if (!viewer.open) return;
            status.hidden = true;
            photo.hidden = false;
        });
        photo.addEventListener('error', () => {
            if (!viewer.open) return;
            photo.hidden = true;
            status.textContent = 'Gambar tidak dapat dimuatkan. Sila cuba semula.';
            status.hidden = false;
        });

        // Enhance only existing profile photos; placeholder icons stay unchanged.
        document.querySelectorAll('.avatar, .profile-avatar, .member-avatar, .attendance-person__avatar').forEach((frame) => {
            const thumbnail = frame.querySelector('img');
            if (!thumbnail || frame.querySelector('.profile-photo-trigger')) return;

            const name = thumbnail.alt || frame.closest('td')?.querySelector('.user-name, strong')?.textContent.trim() || 'pengguna';
            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'profile-photo-trigger';
            trigger.title = 'Lihat gambar profil';
            trigger.setAttribute('aria-label', `Lihat gambar profil: ${name}`);
            trigger.setAttribute('aria-haspopup', 'dialog');
            trigger.setAttribute('aria-controls', 'profile-image-viewer');
            frame.removeAttribute('aria-hidden');
            frame.classList.add('profile-photo-viewable');
            frame.append(trigger);

            trigger.addEventListener('click', (event) => {
                // Clicking the navbar photo opens the viewer without toggling its menu.
                event.preventDefault();
                if (viewer.open) return;
                opener = trigger;
                previousOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
                photo.hidden = true;
                status.hidden = false;
                status.textContent = 'Memuatkan gambar…';
                photo.alt = thumbnail.alt || `Gambar profil ${name}`;
                viewer.showModal();
                photo.src = thumbnail.currentSrc || thumbnail.src;
            });
        });

        closeButton.addEventListener('click', () => viewer.close());
        viewer.addEventListener('click', (event) => {
            if (event.target !== viewer) return;
            const bounds = viewer.getBoundingClientRect();
            if (event.clientX < bounds.left || event.clientX > bounds.right
                || event.clientY < bounds.top || event.clientY > bounds.bottom) viewer.close();
        });
        viewer.addEventListener('close', () => {
            document.body.style.overflow = previousOverflow;
            photo.removeAttribute('src');
            photo.hidden = true;
            if (opener?.isConnected) opener.focus({ preventScroll: true });
        });
    })();
</script>
