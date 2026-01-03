<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Image Processing Service Class
 *
 * Handles image upload, EXIF stripping, and thumbnail generation.
 * Implements FR-11.3, FR-11.4, FR-11.5: Image processing requirements.
 */
class ImageProcessingService
{
    /**
     * Allowed MIME types.
     * Implements FR-11.2: Validate file type (jpg/png/webp).
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Maximum file size in bytes (2MB).
     * Implements FR-11.2: Validate file size (max 2MB).
     */
    private const MAX_FILE_SIZE = 2 * 1024 * 1024;

    /**
     * Thumbnail dimensions.
     */
    private const THUMBNAIL_WIDTH = 150;
    private const THUMBNAIL_HEIGHT = 150;

    /**
     * Storage disk to use.
     */
    private string $disk = 'public';

    /**
     * Process and store an uploaded image.
     *
     * @return array{path: string, thumbnail_path: string|null}
     * @throws \InvalidArgumentException
     */
    public function processUpload(
        UploadedFile $file,
        string $directory = 'images',
        bool $generateThumbnail = true
    ): array {
        // Validate the file
        $this->validateFile($file);

        // Generate unique filename
        $filename = $this->generateFilename($file);

        // Strip EXIF data and store the image
        $processedImage = $this->stripExifData($file);
        $path = $this->storeImage($processedImage, $directory, $filename);

        // Generate thumbnail if requested
        $thumbnailPath = null;
        if ($generateThumbnail) {
            $thumbnailPath = $this->generateThumbnail($processedImage, $directory, $filename);
        }

        return [
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
        ];
    }

    /**
     * Validate the uploaded file.
     * Implements FR-11.2, FR-11.3: Validate file type, size, and MIME by content sniffing.
     *
     * @throws \InvalidArgumentException
     */
    public function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \InvalidArgumentException(
                'File size exceeds maximum allowed size of 2MB.'
            );
        }

        // Verify MIME type by content sniffing (security)
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new \InvalidArgumentException(
                'Invalid file type. Allowed types: JPEG, PNG, WebP.'
            );
        }

        // Additional validation: check file extension matches MIME
        $extension = strtolower($file->getClientOriginalExtension());
        $validExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $validExtensions, true)) {
            throw new \InvalidArgumentException(
                'Invalid file extension. Allowed extensions: jpg, jpeg, png, webp.'
            );
        }
    }

    /**
     * Strip EXIF metadata from an image.
     * Implements FR-11.4: Strip EXIF metadata (privacy + security).
     */
    public function stripExifData(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType();
        $tempPath = $file->getRealPath();

        // Only JPEG files have EXIF data that needs stripping
        if ($mimeType === 'image/jpeg') {
            return $this->stripJpegExif($tempPath);
        }

        // For PNG and WebP, just return the original content
        return file_get_contents($tempPath);
    }

    /**
     * Strip EXIF data from JPEG image.
     */
    private function stripJpegExif(string $path): string
    {
        // Load the image
        $image = imagecreatefromjpeg($path);
        if ($image === false) {
            return file_get_contents($path);
        }

        // Capture output to string
        ob_start();
        imagejpeg($image, null, 90);
        $content = ob_get_clean();

        imagedestroy($image);

        return $content;
    }

    /**
     * Generate a unique filename.
     */
    private function generateFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return Str::uuid()->toString() . '.' . $extension;
    }

    /**
     * Store the processed image.
     */
    private function storeImage(string $content, string $directory, string $filename): string
    {
        $path = $directory . '/' . $filename;
        Storage::disk($this->disk)->put($path, $content);

        return $path;
    }

    /**
     * Generate a thumbnail for the image.
     * Implements FR-11.5: Generate thumbnail variant for list views (performance).
     */
    public function generateThumbnail(
        string $imageContent,
        string $directory,
        string $filename,
        int $width = self::THUMBNAIL_WIDTH,
        int $height = self::THUMBNAIL_HEIGHT
    ): ?string {
        // Create image from string
        $sourceImage = imagecreatefromstring($imageContent);
        if ($sourceImage === false) {
            return null;
        }

        // Get original dimensions
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        // Calculate thumbnail dimensions maintaining aspect ratio
        $ratio = min($width / $sourceWidth, $height / $sourceHeight);
        $newWidth = (int) round($sourceWidth * $ratio);
        $newHeight = (int) round($sourceHeight * $ratio);

        // Create thumbnail image
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        if ($thumbnail === false) {
            imagedestroy($sourceImage);
            return null;
        }

        // Preserve transparency for PNG
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);

        // Resize
        imagecopyresampled(
            $thumbnail,
            $sourceImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $sourceWidth,
            $sourceHeight
        );

        // Generate thumbnail filename
        $pathInfo = pathinfo($filename);
        $thumbnailFilename = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        $thumbnailPath = $directory . '/thumbnails/' . $thumbnailFilename;

        // Capture output
        ob_start();
        $extension = strtolower($pathInfo['extension']);
        switch ($extension) {
            case 'png':
                imagepng($thumbnail, null, 9);
                break;
            case 'webp':
                imagewebp($thumbnail, null, 90);
                break;
            default:
                imagejpeg($thumbnail, null, 85);
        }
        $thumbnailContent = ob_get_clean();

        // Store thumbnail
        Storage::disk($this->disk)->put($thumbnailPath, $thumbnailContent);

        // Cleanup
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);

        return $thumbnailPath;
    }

    /**
     * Delete an image and its thumbnail.
     */
    public function deleteImage(string $path, ?string $thumbnailPath = null): void
    {
        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }

        if ($thumbnailPath && Storage::disk($this->disk)->exists($thumbnailPath)) {
            Storage::disk($this->disk)->delete($thumbnailPath);
        }
    }

    /**
     * Get the URL for an image.
     */
    public function getUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Check if an image exists.
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Set the storage disk.
     */
    public function setDisk(string $disk): self
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * Get image dimensions.
     */
    public function getDimensions(string $path): ?array
    {
        $content = Storage::disk($this->disk)->get($path);
        $image = imagecreatefromstring($content);

        if ($image === false) {
            return null;
        }

        $dimensions = [
            'width' => imagesx($image),
            'height' => imagesy($image),
        ];

        imagedestroy($image);

        return $dimensions;
    }
}
