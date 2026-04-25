<?php

namespace App\Models\Concerns;

use Aws\S3\S3Client;

trait HasS3PresignedUrl
{
    /**
     * Extract the raw S3 object key from a full URL or path before saving.
     *
     * Handles:
     *  - Plain URLs:     http://host:9000/bucket/key   → key
     *  - Presigned URLs: http://host:9000/bucket/key?X-Amz-...  → key
     *  - Raw S3 keys:    courses/thumbnails/2026/04/uuid-name.png → unchanged
     *
     * @param  string|null  $value  The value being saved
     * @return string|null
     */
    protected static function presignedSetValue(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (! str_starts_with($value, 'http')) {
            return ltrim($value, '/');
        }

        $diskConfig = config('filesystems.disks.s3');
        $bucket = (string) $diskConfig['bucket'];

        $urlPath = ltrim(parse_url($value, PHP_URL_PATH) ?? '', '/');

        $publicEndpoint = rtrim((string) ($diskConfig['public_endpoint'] ?: $diskConfig['endpoint']), '/');
        $publicBasePath = ltrim(parse_url($publicEndpoint, PHP_URL_PATH) ?? '', '/');
        if ($publicBasePath !== '' && str_starts_with($urlPath, $publicBasePath . '/')) {
            $urlPath = substr($urlPath, strlen($publicBasePath) + 1);
        }

        return str_starts_with($urlPath, $bucket . '/')
            ? substr($urlPath, strlen($bucket) + 1)
            : $urlPath;
    }

    /**
     * Generate a presigned S3 GET URL from a stored value.
     *
     * Handles:
     *  - Plain URLs:     http://host:9000/bucket/key
     *  - Presigned URLs: http://host:9000/bucket/key?X-Amz-...
     *  - Raw S3 keys:    courses/thumbnails/2026/04/uuid-name.png
     *  - Plain text:     "What is 2+2?" (returned unchanged)
     *
     * @param  string|null  $value   The stored attribute value
     * @param  string       $expiry  Presigned URL expiry (e.g. '+24 hours')
     * @return string|null
     */
    protected static function presignedGetUrl(?string $value, string $expiry = '+24 hours'): ?string
    {
        if (empty($value)) {
            return null;
        }

        $diskConfig = config('filesystems.disks.s3');
        $bucket = (string) $diskConfig['bucket'];

        // Extract the S3 object key from the stored value.
        if (str_starts_with($value, 'http')) {
            // Plain URL or presigned URL — strip the host/bucket prefix to get the key.
            $urlPath = ltrim(parse_url($value, PHP_URL_PATH) ?? '', '/');

            // Strip the public-endpoint base path (e.g. "minio-proxy/") when the
            // stored URL was built against the public proxy rather than the
            // internal MinIO endpoint (e.g. http://host/minio-proxy/bucket/key).
            $publicEndpoint  = rtrim((string) ($diskConfig['public_endpoint'] ?: $diskConfig['endpoint']), '/');
            $publicBasePath  = ltrim(parse_url($publicEndpoint, PHP_URL_PATH) ?? '', '/');
            if ($publicBasePath !== '' && str_starts_with($urlPath, $publicBasePath . '/')) {
                $urlPath = substr($urlPath, strlen($publicBasePath) + 1);
            }

            $objectKey = str_starts_with($urlPath, $bucket . '/')
                ? substr($urlPath, strlen($bucket) + 1)
                : $urlPath;
        } elseif (str_contains($value, '/')) {
            // Raw S3 object key (e.g. "courses/thumbnails/2026/04/uuid-name.png").
            $objectKey = ltrim($value, '/');
        } else {
            // Plain text (e.g. a question's text content) — return as-is.
            return $value;
        }

        $client  = static::s3Client($diskConfig);
        $command = $client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key'    => $objectKey,
        ]);

        $request = $client->createPresignedRequest($command, $expiry);

        return static::buildPublicPresignedUrl(
            (string) $request->getUri(),
            rtrim((string) ($diskConfig['public_endpoint'] ?: $diskConfig['endpoint']), '/')
        );
    }

    /**
     * Returns a cached S3Client built from the given disk config.
     */
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

    /**
     * Rewrites a presigned URI's host/scheme to the configured public endpoint.
     */
    private static function buildPublicPresignedUrl(string $presignedUri, string $publicEndpoint): string
    {
        $parsedRaw    = parse_url($presignedUri);
        $parsedPublic = parse_url($publicEndpoint);
        $basePath     = rtrim($parsedPublic['path'] ?? '', '/');

        return ($parsedPublic['scheme'] ?? 'http') . '://'
            . ($parsedPublic['host'] ?? 'localhost')
            . (isset($parsedPublic['port']) ? ':' . $parsedPublic['port'] : '')
            . $basePath
            . ($parsedRaw['path'] ?? '')
            . (isset($parsedRaw['query']) ? '?' . $parsedRaw['query'] : '');
    }
}
