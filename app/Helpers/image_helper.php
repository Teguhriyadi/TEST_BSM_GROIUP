<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('neo_storage_disk')) {
    function neo_storage_disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk('neo');
    }
}

if (! function_exists('neo_file_exists')) {
    function neo_file_exists(?string $relativePath): bool
    {
        if ($relativePath === null || trim($relativePath) === '') {
            return false;
        }
        try {
            return neo_storage_disk()->exists($relativePath);
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }
}

if (! function_exists('neo_delete_file')) {
    function neo_delete_file(?string $relativePath): bool
    {
        if ($relativePath === null || trim($relativePath) === '') {
            return true;
        }
        try {
            neo_storage_disk()->delete($relativePath);
            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }
}

if (! function_exists('neo_public_url')) {
    function neo_public_url(?string $relativePath, int $expireMinutes = 1440): ?string
    {
        if ($relativePath === null || trim($relativePath) === '') {
            return null;
        }
        try {
            $disk = neo_storage_disk();
            $visibility = null;
            try {
                $visibility = $disk->getVisibility($relativePath);
            } catch (\Throwable $e) {
                $visibility = null;
            }
            if ($visibility === 'public') {
                return (string) $disk->url($relativePath);
            }
            $expires = now()->addMinutes(max(1, $expireMinutes));
            return (string) $disk->temporaryUrl($relativePath, $expires);
        } catch (\Throwable $e) {
            report($e);
            try {
                return (string) neo_storage_disk()->url($relativePath);
            } catch (\Throwable $inner) {
                report($inner);
                return null;
            }
        }
    }
}

if (! function_exists('neo_store_file')) {
    function neo_store_file(UploadedFile $file, string $directory, ?string $visibility = 'private', ?string $overrideFilename = null): ?string
    {
        try {
            $dirClean = trim((string) rtrim($directory, '/'));
            if ($overrideFilename !== null && trim((string) $overrideFilename) !== '') {
                $filename = trim((string) $overrideFilename);
            } else {
                $ext = (string) $file->getClientOriginalExtension();
                if ($ext === '') {
                    $mime = (string) $file->getMimeType();
                    $guess = $mime !== '' ? \Illuminate\Support\Facades\File::extension($mime) : '';
                    $ext = $guess !== '' ? $guess : 'bin';
                }
                $filename = Str::random(40) . '.' . strtolower($ext);
            }
            $relativePath = ($dirClean === '' ? '' : $dirClean . '/') . $filename;
            neo_storage_disk()->putFileAs($dirClean, $file, $filename, $visibility ?? 'private');
            return $relativePath;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }
}

if (! function_exists('compressImage')) {
    function compressImage(
        UploadedFile $file,
        string $directory,
        int $quality = 80,
        int $maxWidth = 1600,
        ?string $visibility = 'private'
    ): ?string {
        try {
            $quality = max(10, min(100, $quality));
            $maxWidth = max(100, $maxWidth);

            $originalExt = strtolower((string) $file->getClientOriginalExtension());
            $mime = strtolower((string) $file->getMimeType());

            $isJpeg = in_array($originalExt, ['jpg', 'jpeg'], true)
                || in_array($mime, ['image/jpeg', 'image/jpg'], true);
            $isPng = $originalExt === 'png' || $mime === 'image/png';

            if (! $isJpeg && ! $isPng) {
                $filename = Str::random(40) . '.' . ($originalExt ?: 'bin');
                $relative = (string) $file->storeAs(rtrim($directory, '/'), $filename, 'neo');
                return $relative;
            }

            if (! extension_loaded('gd')) {
                $filename = Str::random(40) . '.' . ($isJpeg ? 'jpg' : 'png');
                $relative = (string) $file->storeAs(rtrim($directory, '/'), $filename, 'neo');
                return $relative;
            }

            [$sourceWidth, $sourceHeight, $sourceType] = @getimagesize($file->getRealPath());
            if (
                (! $isJpeg || ! in_array($sourceType, [IMAGETYPE_JPEG], true))
                && (! $isPng || ! in_array($sourceType, [IMAGETYPE_PNG], true))
            ) {
                $filename = Str::random(40) . '.' . ($isJpeg ? 'jpg' : 'png');
                $relative = (string) $file->storeAs(rtrim($directory, '/'), $filename, 'neo');
                return $relative;
            }

            $sourceImage = $isJpeg ? @imagecreatefromjpeg($file->getRealPath()) : @imagecreatefrompng($file->getRealPath());
            if ($sourceImage === false) {
                $filename = Str::random(40) . '.' . ($isJpeg ? 'jpg' : 'png');
                $relative = (string) $file->storeAs(rtrim($directory, '/'), $filename, 'neo');
                return $relative;
            }

            if ($isPng) {
                imagealphablending($sourceImage, true);
                imagesavealpha($sourceImage, true);
            }

            $sourceWidth = (int) ($sourceWidth ?: imagesx($sourceImage));
            $sourceHeight = (int) ($sourceHeight ?: imagesy($sourceImage));
            $newWidth = $sourceWidth;
            $newHeight = $sourceHeight;

            if ($sourceWidth > $maxWidth) {
                $ratio = $maxWidth / $sourceWidth;
                $newWidth = $maxWidth;
                $newHeight = (int) max(1, round($sourceHeight * $ratio));
            }

            if ($newWidth !== $sourceWidth || $newHeight !== $sourceHeight) {
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                if ($resized !== false) {
                    if ($isPng) {
                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);
                        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                        imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
                    }
                    imagecopyresampled(
                        $resized,
                        $sourceImage,
                        0,
                        0,
                        0,
                        0,
                        $newWidth,
                        $newHeight,
                        $sourceWidth,
                        $sourceHeight,
                    );
                    imagedestroy($sourceImage);
                    $sourceImage = $resized;
                }
            }

            $dirClean = rtrim($directory, '/');
            $filename = Str::random(40) . '.' . ($isJpeg ? 'jpg' : 'png');
            $relativePath = ($dirClean === '' ? '' : $dirClean . '/') . $filename;

            $tmpDir = rtrim((string) sys_get_temp_dir(), '/');
            if (! is_dir($tmpDir)) {
                $tmpDir = '/tmp';
            }
            $tmpName = 'neo_compress_' . bin2hex(random_bytes(10));
            $tmpPath = $tmpDir . '/' . $tmpName . '.' . ($isJpeg ? 'jpg' : 'png');

            $success = false;
            if ($isJpeg) {
                $success = imagejpeg($sourceImage, $tmpPath, $quality);
            } else {
                $pngQuality = (int) max(0, min(9, (int) round((100 - $quality) / 10)));
                $success = imagepng($sourceImage, $tmpPath, $pngQuality);
            }

            imagedestroy($sourceImage);

            if (! $success || ! is_file($tmpPath)) {
                @unlink($tmpPath);
                $filename2 = Str::random(40) . '.' . ($isJpeg ? 'jpg' : 'png');
                $relative = (string) $file->storeAs($dirClean, $filename2, 'neo');
                return $relative;
            }

            try {
                $content = file_get_contents($tmpPath);
                if ($content === false || $content === '') {
                    throw new \RuntimeException('Gagal membaca temporary file hasil compress.');
                }
                neo_storage_disk()->put($relativePath, $content, $visibility ?? 'private');
            } catch (\Throwable $e) {
                report($e);
                @unlink($tmpPath);
                $filename3 = Str::random(40) . '.' . ($isJpeg ? 'jpg' : 'png');
                $relative = (string) $file->storeAs(rtrim($directory, '/'), $filename3, 'neo');
                return $relative;
            }

            @unlink($tmpPath);
            return $relativePath;
        } catch (\Throwable $e) {
            report($e);
            try {
                $filename = Str::random(40) . '.' . (strtolower((string) $file->getClientOriginalExtension()) ?: 'bin');
                $relative = (string) $file->storeAs(rtrim($directory, '/'), $filename, 'neo');
                return $relative;
            } catch (\Throwable $inner) {
                report($inner);
                return null;
            }
        }
    }
}
