<?php

namespace App\Models;

use Aws\S3\S3Client;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                if (empty($value)) {
                    return null;
                }

                $diskConfig = config('filesystems.disks.s3');
                $bucket = (string) $diskConfig['bucket'];

                // Extract the S3 object key from the stored value.
                // Stored value may be a plain URL, a presigned URL, or a raw path.
                if (str_starts_with($value, 'http')) {
                    $parsed = parse_url($value);
                    $urlPath = ltrim($parsed['path'] ?? '', '/');
                    $objectKey = str_starts_with($urlPath, $bucket . '/')
                        ? substr($urlPath, strlen($bucket) + 1)
                        : $urlPath;
                } else {
                    $objectKey = ltrim($value, '/');
                }

                $client = static::s3Client($diskConfig);
                $command = $client->getCommand('GetObject', [
                    'Bucket' => $bucket,
                    'Key'    => $objectKey,
                ]);

                $request = $client->createPresignedRequest($command, '+24 hours');

                return static::buildPublicUrl(
                    (string) $request->getUri(),
                    rtrim((string) ($diskConfig['public_endpoint'] ?: $diskConfig['endpoint']), '/')
                );
            }
        );
    }

    private static function s3Client(array $diskConfig): S3Client
    {
        static $client = null;

        if ($client === null) {
            $client = new S3Client([
                'version'                 => 'latest',
                'region'                  => (string) $diskConfig['region'],
                'endpoint'                => rtrim((string) $diskConfig['endpoint'], '/'),
                'use_path_style_endpoint' => (bool) $diskConfig['use_path_style_endpoint'],
                'credentials'             => [
                    'key'    => (string) $diskConfig['key'],
                    'secret' => (string) $diskConfig['secret'],
                ],
            ]);
        }

        return $client;
    }

    private static function buildPublicUrl(string $presignedUri, string $publicEndpoint): string
    {
        $parsedRaw    = parse_url($presignedUri);
        $parsedPublic = parse_url($publicEndpoint);

        return ($parsedPublic['scheme'] ?? 'http') . '://'
            . ($parsedPublic['host'] ?? 'localhost')
            . (isset($parsedPublic['port']) ? ':' . $parsedPublic['port'] : '')
            . ($parsedRaw['path'] ?? '')
            . (isset($parsedRaw['query']) ? '?' . $parsedRaw['query'] : '');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function reviews()
    {
        return $this->hasMany(CourseReview::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['status', 'note', 'approved_at', 'cancelled_at'])
            ->withTimestamps();
    }
}
