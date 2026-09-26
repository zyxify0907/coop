<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    'created_by_role',
])]
class Announcement extends Model
{
    protected $with = ['creatorAdmin', 'creatorStaff'];

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

    public function creatorAdmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by', 'id_admin');
    }

    public function creatorStaff(): BelongsTo
    {
        return $this->belongsTo(Pekerja::class, 'created_by', 'id_pekerja');
    }

    public function getAnnouncerNameAttribute(): string
    {
        return match ($this->created_by_role) {
            'staff' => $this->creatorStaff?->nama ?? 'Pengurusan CoopBest',
            'admin' => $this->creatorAdmin?->nama ?? 'Admin CoopBest',
            default => $this->creatorAdmin?->nama ?? $this->creatorStaff?->nama ?? 'Admin CoopBest',
        };
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
