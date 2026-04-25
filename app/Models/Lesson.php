<?php

namespace App\Models;

use App\Models\Concerns\HasS3PresignedUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasS3PresignedUrl;

    protected $fillable = [
        'course_id',
        'title',
        'slug',
        'content',
        'video_url',
        'order',
        'duration',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'duration' => 'integer',
    ];

    protected function videoUrl(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => static::presignedGetUrl($value),
            set: fn(?string $value) => static::presignedSetValue($value),
        );
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }
}
