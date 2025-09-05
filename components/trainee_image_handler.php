<?php
require_once __DIR__ . '/../vendor/autoload.php';

// components/trainee_image_handler.php
// This component handles trainee image preview, upload, and deletion both client-side and server-side.
// Security hardening: MIME validation via finfo, constrained paths, and safe permissions.

/**
 * SQL Migration (for reference only):
 * ALTER TABLE `plans` ADD COLUMN `trainee_image_path` VARCHAR(255) DEFAULT NULL AFTER `source`;
 */

// Prevent redeclaration if already defined elsewhere
// Only declare the function if not already defined
if (!function_exists('handleTraineeImageUpload')) {
    /**
     * Handles trainee image upload: validates, moves, logs, and returns relative DB path.
     *
     * @param PDO $pdo PDO connection object
     * @param int $planId Associated plan ID
     * @param array $fileData The $_FILES['traineeImageUpload'] input
     * @param string|null $oldImagePath Existing image path (optional)
     * @param \Psr\Log\LoggerInterface|mixed $log Logger instance (must support info(), warning(), error())
     * @return string|null Relative path to saved image, or null if deleted
     * @throws Exception on validation or move errors
     */
    function handleTraineeImageUpload(
        PDO $pdo,
        int $planId,
        array $fileData,
        ?string $oldImagePath,
        $log
    ): ?string {
        $uploadDir = realpath(__DIR__ . '/../assets/images') ?: (__DIR__ . '/../assets/images');
        $uploadDir = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'trainee_images' . DIRECTORY_SEPARATOR;

        // Ensure upload directory exists with safe permissions
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new Exception('Failed to create upload directory.');
        }

        $relativeBase = 'assets/images/trainee_images/';
        $shouldDeleteOldImage = false;

        // Normalize and validate that a path is inside our upload base
        $isPathInsideBase = static function (string $path) use ($uploadDir): bool {
            $real = realpath($path);
            return $real !== false && str_starts_with($real, rtrim($uploadDir, DIRECTORY_SEPARATOR));
        };

        // Check for replacing or explicitly clearing the old image
        if (!empty($oldImagePath)) {
            $oldAbs = realpath(__DIR__ . '/../' . ltrim($oldImagePath, '/\\'));
            if ($oldAbs && $isPathInsideBase($oldAbs)) {
                if (
                    ($fileData['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK ||
                    (($_POST['clear_trainee_image'] ?? '') === '1')
                ) {
                    $shouldDeleteOldImage = true;
                }
            }
        }

        // Delete old image if necessary
        if ($shouldDeleteOldImage && !empty($oldImagePath)) {
            $oldAbs = realpath(__DIR__ . '/../' . ltrim($oldImagePath, '/\\'));
            if ($oldAbs && $isPathInsideBase($oldAbs)) {
                if (@unlink($oldAbs)) {
                    if ($log && method_exists($log, 'info')) {
                        $log->info("Deleted old trainee image for plan ID {$planId}: {$oldImagePath}");
                    }
                } else {
                    if ($log && method_exists($log, 'error')) {
                        $log->error("Failed to delete old trainee image for plan ID {$planId}: {$oldImagePath}");
                    }
                }
            }
            // If no new file, nullify DB path
            if (($fileData['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                return null;
            }
        }

        // If no file uploaded, keep existing path
        if (($fileData['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $oldImagePath;
        }

        // Handle upload errors
        if (($fileData['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            if (isset($log) && method_exists($log, 'warning')) {
                $log->warning("Upload error: plan ID {$planId}, error code " . ($fileData['error'] ?? 'unknown'));
            }
            throw new Exception('Image upload failed due to a file error.');
        }

        // Validate file size
        $maxFileSize = 5 * 1024 * 1024;
        if (($fileData['size'] ?? 0) > $maxFileSize) {
            throw new Exception('File too large. Max 5MB.');
        }

        // Validate MIME type using Fileinfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tmpPath = $fileData['tmp_name'] ?? '';
        if (!is_uploaded_file($tmpPath)) {
            throw new Exception('Invalid upload source.');
        }
        $mime = $finfo->file($tmpPath) ?: '';
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        if (!array_key_exists($mime, $allowed)) {
            throw new Exception('Invalid file type.');
        }

        // Save with cryptographically-strong filename
        $extension = $allowed[$mime];
        $fileName = bin2hex(random_bytes(10)) . '.' . $extension;
        $destination = $uploadDir . $fileName;

        if (!move_uploaded_file($tmpPath, $destination)) {
            throw new Exception('Failed to move uploaded image.');
        }

        // Set safe file permissions (rw-r--r--)
        @chmod($destination, 0644);

        if ($log && method_exists($log, 'info')) {
            $log->info("Uploaded trainee image: {$fileName} for plan {$planId}");
        }

        return $relativeBase . $fileName;
    }
}

// Ensure we have a suffix like `_inline` or `_modal` for unique DOM IDs
$id_suffix = $id_suffix ?? '';
// Compute base path for assets whether included from /public or root
$isPublic = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/public/') !== false;
$base = $isPublic ? '../' : '';
$baseEsc = htmlspecialchars($base, ENT_QUOTES, 'UTF-8');
?>
<div class="col-md-6 mb-3 trainee-image-component">
  <label for="traineeImageUpload<?php echo $id_suffix; ?>" class="form-label">Trainee Image</label>
  <div class="input-group">
    <input type="file"
           class="form-control"
           id="traineeImageUpload<?php echo $id_suffix; ?>"
           name="traineeImageUpload"
           accept="image/jpeg,image/png,image/gif,image/webp"
           aria-describedby="traineeImageHelp<?php echo $id_suffix; ?>">
    <button class="btn btn-outline-danger" type="button" id="clearTraineeImageBtn<?php echo $id_suffix; ?>" style="display:none;">Clear</button>
  </div>
  <div id="traineeImageHelp<?php echo $id_suffix; ?>" class="form-text">
    Max 5MB (JPG, PNG, GIF, WEBP)
  </div>
  <div class="mt-2 text-center border p-2 rounded" id="traineeImagePreviewContainer<?php echo $id_suffix; ?>" style="display: none;">
    <img id="traineeImagePreview<?php echo $id_suffix; ?>" src="#" alt="Image Preview" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
  </div>
  <!-- Stores current DB image path -->
  <input type="hidden" id="existingTraineeImagePath<?php echo $id_suffix; ?>" name="existingTraineeImagePath" value="">
  <!-- Instructs backend to delete image -->
  <input type="hidden" id="clearTraineeImageFlag<?php echo $id_suffix; ?>" name="clear_trainee_image" value="0">
</div>
<script src="<?= $baseEsc ?>assets/js/trainee_image_handler.js" defer></script>
