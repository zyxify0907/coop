<dialog id="profile-image-cropper" aria-labelledby="profile-image-cropper-title">
    <header class="profile-image-cropper__header">
        <div>
            <h2 id="profile-image-cropper-title">Laraskan gambar profil</h2>
            <p>Tarik gambar dan gunakan zoom supaya ia muat dalam bulatan.</p>
        </div>
        <button type="button" class="profile-image-cropper__close" aria-label="Tutup tanpa menggunakan gambar">&times;</button>
    </header>
    <div class="profile-image-cropper__body">
        <div class="profile-image-cropper__stage" aria-label="Kawasan potongan gambar">
            <img class="profile-image-cropper__image" alt="Pratonton gambar profil">
        </div>
        <label class="profile-image-cropper__zoom">
            <span>Zoom</span>
            <input type="range" min="1" max="3.5" step="0.01" value="1" aria-label="Laraskan zoom gambar">
        </label>
    </div>
    <footer class="profile-image-cropper__actions">
        <button type="button" class="profile-image-cropper__cancel">Batal</button>
        <button type="button" class="profile-image-cropper__apply">Gunakan Gambar</button>
    </footer>
</dialog>

<style>
    #profile-image-cropper {
        width: min(430px, calc(100vw - 32px));
        max-width: none;
        margin: auto;
        padding: 0;
        overflow: hidden;
        border: 1px solid #D7E3F1;
        border-radius: 16px;
        background: #FFFFFF;
        color: #082F59;
        box-shadow: 0 24px 80px rgba(0, 0, 0, .3);
    }
    #profile-image-cropper::backdrop { background: rgba(8, 25, 45, .75); }
    .profile-image-cropper__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px 14px;
        border-bottom: 1px solid #E2EAF4;
    }
    .profile-image-cropper__header h2 { margin: 0; font-size: 18px; }
    .profile-image-cropper__header p { margin: 5px 0 0; color: #526987; font-size: 13px; line-height: 1.45; }
    .profile-image-cropper__close {
        flex: 0 0 36px;
        width: 36px;
        height: 36px;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: #EDF3FB;
        color: #082F59;
        font-size: 26px;
        line-height: 1;
        cursor: pointer;
    }
    .profile-image-cropper__body { display: grid; gap: 18px; justify-items: center; padding: 22px 20px; }
    .profile-image-cropper__stage {
        position: relative;
        width: min(280px, calc(100vw - 96px));
        aspect-ratio: 1;
        overflow: hidden;
        border: 4px solid #DBEAFE;
        border-radius: 50%;
        background: #F1F5F9;
        box-shadow: 0 8px 18px rgba(8, 47, 89, .12);
        cursor: grab;
        touch-action: none;
        user-select: none;
    }
    .profile-image-cropper__stage:active { cursor: grabbing; }
    .profile-image-cropper__image {
        position: absolute;
        top: 50%;
        left: 50%;
        max-width: none;
        max-height: none;
        pointer-events: none;
        user-select: none;
        transform: translate(-50%, -50%);
    }
    .profile-image-cropper__zoom { display: grid; width: min(280px, 100%); gap: 8px; }
    .profile-image-cropper__zoom span { color: #46617F; font-size: 13px; font-weight: 800; }
    .profile-image-cropper__zoom input { width: 100%; accent-color: #2563EB; }
    .profile-image-cropper__actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 20px 20px;
        border-top: 1px solid #E2EAF4;
    }
    .profile-image-cropper__actions button {
        min-height: 40px;
        padding: 0 15px;
        border-radius: 7px;
        font: inherit;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
    }
    .profile-image-cropper__cancel { border: 1px solid #C8D6E7; background: #FFFFFF; color: #365477; }
    .profile-image-cropper__apply { border: 1px solid #1A5FBD; background: #1A5FBD; color: #FFFFFF; }
    .profile-image-cropper__actions button:focus-visible,
    .profile-image-cropper__close:focus-visible { outline: 3px solid rgba(37, 99, 235, .32); outline-offset: 2px; }
    @media (max-width: 480px) {
        .profile-image-cropper__actions { flex-direction: column-reverse; }
        .profile-image-cropper__actions button { width: 100%; }
    }
</style>

<script>
    (() => {
        const cropper = document.getElementById('profile-image-cropper');
        const stage = cropper.querySelector('.profile-image-cropper__stage');
        const image = cropper.querySelector('.profile-image-cropper__image');
        const zoom = cropper.querySelector('.profile-image-cropper__zoom input');
        const cancel = cropper.querySelector('.profile-image-cropper__cancel');
        const close = cropper.querySelector('.profile-image-cropper__close');
        const apply = cropper.querySelector('.profile-image-cropper__apply');
        let crop = null;
        let drag = null;

        const clamp = (value, minimum, maximum) => Math.min(Math.max(value, minimum), maximum);

        function renderCrop() {
            if (!crop) return;

            const circle = stage.clientWidth;
            const scale = crop.baseScale * Number(zoom.value);
            const width = crop.width * scale;
            const height = crop.height * scale;
            const maxX = Math.max(0, (width - circle) / 2);
            const maxY = Math.max(0, (height - circle) / 2);

            crop.x = clamp(crop.x, -maxX, maxX);
            crop.y = clamp(crop.y, -maxY, maxY);
            crop.renderedWidth = width;
            crop.renderedHeight = height;
            image.style.width = `${width}px`;
            image.style.height = `${height}px`;
            image.style.transform = `translate(calc(-50% + ${crop.x}px), calc(-50% + ${crop.y}px))`;
        }

        function resetCropper(clearInput = true) {
            if (crop?.input && clearInput && !crop.applied) crop.input.value = '';
            image.removeAttribute('src');
            image.removeAttribute('style');
            zoom.value = '1';
            drag = null;
            crop = null;
        }

        function openCropper(input, file) {
            if (!file || !file.type.startsWith('image/')) {
                input.value = '';
                return;
            }

            crop = { input, applied: false, width: 0, height: 0, baseScale: 1, x: 0, y: 0 };
            zoom.value = '1';
            const reader = new FileReader();
            reader.addEventListener('load', () => {
                if (!crop || crop.input !== input) return;
                image.src = String(reader.result);
            });
            reader.readAsDataURL(file);
            cropper.showModal();
        }

        image.addEventListener('load', () => {
            if (!crop) return;
            crop.width = image.naturalWidth;
            crop.height = image.naturalHeight;
            crop.baseScale = Math.max(stage.clientWidth / crop.width, stage.clientWidth / crop.height);
            crop.x = 0;
            crop.y = 0;
            renderCrop();
        });

        zoom.addEventListener('input', renderCrop);
        stage.addEventListener('pointerdown', (event) => {
            if (!crop) return;
            stage.setPointerCapture(event.pointerId);
            drag = { pointerId: event.pointerId, x: event.clientX, y: event.clientY, cropX: crop.x, cropY: crop.y };
            event.preventDefault();
        });
        stage.addEventListener('pointermove', (event) => {
            if (!drag || drag.pointerId !== event.pointerId || !crop) return;
            crop.x = drag.cropX + event.clientX - drag.x;
            crop.y = drag.cropY + event.clientY - drag.y;
            renderCrop();
        });
        stage.addEventListener('pointerup', () => { drag = null; });
        stage.addEventListener('pointercancel', () => { drag = null; });

        apply.addEventListener('click', () => {
            if (!crop || !image.complete) return;
            const canvas = document.createElement('canvas');
            const outputSize = 512;
            const circle = stage.clientWidth;
            const ratio = outputSize / circle;
            canvas.width = outputSize;
            canvas.height = outputSize;
            const context = canvas.getContext('2d');
            context.drawImage(
                image,
                ((circle - crop.renderedWidth) / 2 + crop.x) * ratio,
                ((circle - crop.renderedHeight) / 2 + crop.y) * ratio,
                crop.renderedWidth * ratio,
                crop.renderedHeight * ratio,
            );
            canvas.toBlob((blob) => {
                if (!blob || !crop) return;
                const data = new DataTransfer();
                data.items.add(new File([blob], 'gambar-profil.webp', { type: 'image/webp' }));
                crop.input.files = data.files;
                crop.applied = true;
                cropper.close();
            }, 'image/webp', .92);
        });

        const cancelCrop = () => cropper.close();
        cancel.addEventListener('click', cancelCrop);
        close.addEventListener('click', cancelCrop);
        cropper.addEventListener('cancel', (event) => {
            event.preventDefault();
            cropper.close();
        });
        cropper.addEventListener('close', () => resetCropper());

        document.querySelectorAll('.profile-photo-upload input[type="file"]').forEach((input) => {
            input.addEventListener('change', () => openCropper(input, input.files?.[0]));
        });
    })();
</script>
