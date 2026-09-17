<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('compressImage')) {
    function compressImage(
        UploadedFile $file,
        string $directory,
        int $quality = 80,
        int $maxWidth = 1600
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
                return (string) $file->storeAs(rtrim($directory, '/'), $filename, 'public');
            }

            if (! extension_loaded('gd')) {
                $filename = Str::random(40) . '.' . ($isJpeg ? 'jpg' : 'png');
                return (string) $file->storeAs(rtrim($directory, '/'), $filename, 'public');
            }

            [$sourceWidth, $sourceHeight, $sourceType] = @getimagesize($file->getRealPath());
            if (
                (! $isJpeg || ! in_array($sourceType, [IMAGETYPE_JPEG], true))
                && (! $isPng || ! in_array($sourceType, [IMAGETYPE_PNG], true))
            ) {
                $filename = Str::random(40) . '.' . ($isJpeg ? 'jpg' : 'png');
                return (string) $file->storeAs(rtrim($directory, '/'), $filename, 'public');
            }

            $sourceImage = $isJpeg ? @imagecreatefromjpeg($file->getRealPath()) : @imagecreatefrompng($file->getRealPath());
            if ($sourceImage === false) {
                $filename = Str::random(40) . '.' . ($isJpeg ? 'jpg' : 'png');
                return (string) $file->storeAs(rtrim($directory, '/'), $filename, 'public');
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
            $fullDir = rtrim((string) Storage::disk('public')->path($dirClean), '/');
            if (! is_dir($fullDir)) {
                @mkdir($fullDir, 0755, true);
            }

            $filename = Str::random(40) . '.' . ($isJpeg ? 'jpg' : 'png');
            $destinationPath = $fullDir . '/' . $filename;

            $success = false;
            if ($isJpeg) {
                $success = imagejpeg($sourceImage, $destinationPath, $quality);
            } else {
                $pngQuality = (int) max(0, min(9, (int) round((100 - $quality) / 10)));
                $success = imagepng($sourceImage, $destinationPath, $pngQuality);
            }

            imagedestroy($sourceImage);

            if (! $success) {
                $filename = Str::random(40) . '.' . ($isJpeg ? 'jpg' : 'png');
                return (string) $file->storeAs($dirClean, $filename, 'public');
            }

            $relativePath = ($dirClean === '' ? '' : $dirClean . '/') . $filename;
            return $relativePath;
        } catch (Throwable $e) {
            report($e);
            try {
                $filename = Str::random(40) . '.' . (strtolower((string) $file->getClientOriginalExtension()) ?: 'bin');
                return (string) $file->storeAs(rtrim($directory, '/'), $filename, 'public');
            } catch (Throwable $inner) {
                report($inner);
                return null;
            }
        }
    }
}
