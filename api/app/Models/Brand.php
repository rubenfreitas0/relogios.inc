<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class Brand extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'   => 'boolean',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Brand $brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });

        static::updating(function (Brand $brand) {
            if ($brand->isDirty('name')) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    /* ----- Helpers ----- */
    public function getLogoUrlAttribute(): ?string
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        return $this->logo ? $disk->url($this->logo) : null;
    }
}
