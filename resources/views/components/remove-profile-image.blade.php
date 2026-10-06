@props(['imagePath' => null, 'name' => '', 'action' => null, 'profile' => false])

@if ($imagePath)
    <form class="profile-image-remove-form {{ $profile ? 'profile-image-remove-form--profile' : '' }}"
          method="POST" action="{{ $action ?? route('profile.image.destroy') }}"
          onsubmit="return confirm('Buang gambar profil ini? Gambar akan diganti dengan ikon pengguna.');">
        @csrf
        @method('DELETE')
        <button class="profile-image-remove-button" type="submit" aria-label="Buang gambar profil {{ $name }}">
            Buang Gambar
        </button>
    </form>
@endif
