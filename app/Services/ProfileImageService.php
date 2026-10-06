<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\Ahli;
use App\Models\Pekerja;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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

    public function remove(AdminUser|Ahli|Pekerja $user): void
    {
        $previousPath = DB::transaction(function () use ($user): ?string {
            $owner = $user->newQuery()->lockForUpdate()->findOrFail($user->getKey());
            $path = $owner->profile_image_path;
            $owner->update(['profile_image_path' => null]);

            return $path;
        });

        $this->delete($previousPath);
    }

    private function delete(?string $path): void
    {
        if (! $path || ! preg_match('#\Auploads/profile/[a-z0-9_-]+\.(?:jpe?g|png|webp)\z#i', $path)) {
            return;
        }

        $fullPath = public_path($path);
        $directory = realpath(public_path('uploads/profile'));
        $resolvedPath = realpath($fullPath);

        if ($directory !== false && $resolvedPath !== false
            && dirname($resolvedPath) === $directory
            && ! is_link($fullPath) && File::isFile($fullPath)) {
            File::delete($fullPath);
        }
    }
}
