<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProfileImageService
{
    public function replace(UploadedFile $image, ?string $previousPath = null): string
    {
        $directory = public_path('uploads/profile');

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');
        $filename = Str::uuid().'.'.$extension;
        $image->move($directory, $filename);

        $this->delete($previousPath);

        return 'uploads/profile/'.$filename;
    }

    private function delete(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'uploads/profile/')) {
            return;
        }

        $fullPath = public_path($path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
