@props(['initials', 'imagePath' => null, 'name' => 'Gambar profil'])

<span class="avatar" aria-hidden="true">
    @if ($imagePath)
        <img src="{{ asset($imagePath) }}" alt="{{ $name }}">
    @else
        {{ $initials }}
    @endif
</span>
