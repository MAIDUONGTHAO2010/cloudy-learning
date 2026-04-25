<?php

namespace App\Models;

use App\Models\Concerns\HasS3PresignedUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Profile extends Model
{
    use HasS3PresignedUrl;

    protected $fillable = ['user_id', 'avatar', 'date_of_birth', 'sex', 'bio'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'sex' => 'integer',
        ];
    }

    protected function avatar(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn (?string $value) => static::presignedGetUrl($value),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'profile_category');
    }
}
