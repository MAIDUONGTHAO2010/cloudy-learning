<?php

namespace App\Models;

use App\Models\Concerns\HasS3PresignedUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasS3PresignedUrl;

    protected $fillable = [
        'quiz_id',
        'content',
        'type',
        'answer_type',
        'order',
    ];

    protected $casts = [
        'type' => 'integer',
        'answer_type' => 'integer',
        'order' => 'integer',
    ];

    protected function content(): Attribute
    {
        return Attribute::make(
            // Only presign when content is an S3 URL or raw key; plain question
            // text (which never starts with "http" or contains a "/") is returned unchanged.
            get: fn(?string $value) => static::presignedGetUrl($value)
        );
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('label');
    }
}