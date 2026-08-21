<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'body',
    'category',
    'audience',
    'is_pinned',
    'is_active',
    'starts_at',
    'ends_at',
    'created_by',
])]
class Announcement extends Model
{
    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function scopeVisibleTo(Builder $query, string $role): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')->orWhereDate('starts_at', '<=', now()->toDateString());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', now()->toDateString());
            })
            ->where(function (Builder $query) use ($role): void {
                $query->where('audience', 'all')->orWhere('audience', $role);
            });
    }

    public function getAudienceLabelAttribute(): string
    {
        return match ($this->audience) {
            'ahli' => 'Student',
            'staff' => 'Staff',
            'admin' => 'Admin',
            default => 'Semua',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) {
            return 'Tidak Aktif';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'Dijadualkan';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'Tamat';
        }

        return 'Aktif';
    }
}
