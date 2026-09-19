<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Throwable;

class ImageHelper
{
    public static function disk()
    {
        return Storage::disk('s3');
    }

    public static function exists(?string $relativePath): bool
    {
        if ($relativePath === null || trim($relativePath) === '') {
            return false;
        }
        try {
            return (bool) self::disk()->exists($relativePath);
        } catch (Throwable $e) {
            self::logError('ImageHelper.exists failed', $e, ['path' => $relativePath]);
            return false;
        }
    }

    public static function delete(?string $relativePath): bool
    {
        if ($relativePath === null || trim($relativePath) === '') {
            return true;
        }
        try {
            self::disk()->delete($relativePath);
            return true;
        } catch (Throwable $e) {
            self::logError('ImageHelper.delete failed', $e, ['path' => $relativePath]);
            return false;
        }
    }

    public static function publicUrl(?string $relativePath): ?string
    {
        if ($relativePath === null || trim((string) $relativePath) === '') {
            return null;
        }
        if (str_starts_with((string) $relativePath, 'http://') || str_starts_with((string) $relativePath, 'https://')) {
            return $relativePath;
        }
        try {
            return (string) self::disk()->url($relativePath);
        } catch (Throwable $e) {
            self::logError('ImageHelper.publicUrl gagal', $e, ['path' => $relativePath]);
            return null;
        }
    }

    public static function storeFile(
        UploadedFile $file,
        string $directory,
        string $visibility = 'public'
    ): ?string {
        try {
            if (! $file->isValid()) {
                throw new \RuntimeException('Uploaded file tidak valid: ' . ($file->getErrorMessage() ?: 'unknown error'));
            }
            $dirClean = trim((string) rtrim($directory, '/'));
            $ext = (string) $file->getClientOriginalExtension();
            if ($ext === '') {
                $mime = (string) $file->getMimeType();
                $guess = $mime !== '' ? \Illuminate\Support\Facades\File::extension($mime) : '';
                $ext = $guess !== '' ? $guess : 'bin';
            }
            $filename = Str::random(40) . '.' . strtolower($ext);
            $relativePath = ($dirClean === '' ? '' : $dirClean . '/') . $filename;

            $result = self::disk()->putFileAs(
                $dirClean === '' ? '.' : $dirClean,
                $file,
                $filename,
                $visibility
            );

            if ($result === false || $result === null) {
                throw new \RuntimeException('Storage::putFileAs return gagal untuk path: ' . $relativePath);
            }

            return $relativePath;
        } catch (Throwable $e) {
            self::logError('ImageHelper.storeFile gagal', $e, [
                'dir' => $directory ?? null,
                'original_name' => $file?->getClientOriginalName() ?? null,
                'size' => $file?->getSize() ?? null,
            ]);
            return null;
        }
    }

    public static function compressAndStoreFile(
        UploadedFile $file,
        string $directory,
        int $quality = 80,
        int $maxWidth = 1600,
        string $visibility = 'public'
    ): ?string {
        try {
            if (! $file->isValid()) {
                throw new \RuntimeException('Uploaded file tidak valid: ' . ($file->getErrorMessage() ?: 'unknown error'));
            }
            $quality = max(10, min(100, $quality));
            $maxWidth = max(100, $maxWidth);

            $originalExt = strtolower((string) $file->getClientOriginalExtension());
            $mime = strtolower((string) $file->getMimeType());

            $isImage = in_array($originalExt, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'], true)
                || str_starts_with($mime, 'image/');

            if (! $isImage) {
                self::logInfo('ImageHelper: Bukan file image, store langsung tanpa compress', [
                    'ext' => $originalExt,
                    'mime' => $mime,
                    'name' => $file->getClientOriginalName(),
                ]);
                return self::storeFile($file, $directory, $visibility);
            }

            $dirClean = trim((string) rtrim($directory, '/'));
            $filename = Str::random(40) . '.jpg';
            $relativePath = ($dirClean === '' ? '' : $dirClean . '/') . $filename;

            $image = Image::make($file->getRealPath())
                ->resize($maxWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('jpg', $quality);

            $putResult = self::disk()->put(
                $relativePath,
                (string) $image,
                $visibility
            );

            if ($putResult === false || $putResult === null) {
                throw new \RuntimeException('Storage::put() compress return gagal untuk path: ' . $relativePath);
            }

            return $relativePath;
        } catch (Throwable $e) {
            self::logError('ImageHelper.compressAndStoreFile gagal, fallback store langsung', $e, [
                'name' => $file?->getClientOriginalName() ?? null,
                'dir' => $directory ?? null,
            ]);
            return self::storeFile($file, $directory, $visibility);
        }
    }

    public static function uploadBase64ToS3(
        $base64Image,
        $folder = 'dokumen-persyaratan',
        $maxWidth = 800,
        $quality = 70,
        string $visibility = 'public'
    ) {
        try {
            if (! $base64Image) {
                return null;
            }

            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
            if ($imageData === null) {
                return null;
            }
            $imageData = base64_decode($imageData, true);
            if ($imageData === false) {
                return null;
            }

            $filename = uniqid('img_') . '.jpg';
            $folderClean = trim((string) rtrim((string) $folder, '/'));
            $relativePath = ($folderClean === '' ? '' : $folderClean . '/') . $filename;

            $image = Image::make($imageData)
                ->resize($maxWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('jpg', $quality);

            $putResult = self::disk()->put(
                $relativePath,
                (string) $image,
                $visibility
            );

            if ($putResult === false || $putResult === null) {
                return null;
            }

            return $relativePath;
        } catch (Throwable $e) {
            self::logError('ImageHelper.uploadBase64ToS3 gagal', $e);
            return null;
        }
    }

    protected static function logError(string $msg, Throwable $e, array $context = []): void
    {
        Log::error('[ImageHelper] ' . $msg, array_merge($context, [
            'exception_class' => get_class($e),
            'exception_message' => $e->getMessage(),
            'exception_code' => $e->getCode(),
            'trace' => $e->getTraceAsString(),
        ]));
    }

    protected static function logInfo(string $msg, array $context = []): void
    {
        Log::info('[ImageHelper] ' . $msg, $context);
    }
}
