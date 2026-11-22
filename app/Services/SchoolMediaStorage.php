<?php

namespace App\Services;

/**
 * Validates the S3-compatible disk used for permanent school images.
 *
 * School logos, badges, covers and gallery photos must have a configured
 * remote disk and a public delivery URL before the API accepts an upload.
 */
final class SchoolMediaStorage
{
    public static function isConfigured(): bool
    {
        $diskName = (string) config('media-library.disk_name', 'public');
        $disk = config('filesystems.disks.'.$diskName, []);

        if (! is_array($disk)) {
            return false;
        }

        $configured = ($disk['driver'] ?? null) === 's3'
            && filled($disk['key'] ?? null)
            && filled($disk['secret'] ?? null)
            && filled($disk['bucket'] ?? null);

        if (! $configured) {
            return false;
        }

        // R2 and Filebase require an explicit S3 API endpoint and public URL.
        if (in_array($diskName, ['r2', 'filebase'], true)) {
            return filled($disk['endpoint'] ?? null)
                && filled($disk['url'] ?? null);
        }

        return true;
    }
}
