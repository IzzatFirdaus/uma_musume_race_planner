<?php
require_once __DIR__ . '/../vendor/autoload.php';

// components/trainee_image_handler.php
// This component handles trainee image preview, upload, and deletion both client-side and server-side.

/**
 * SQL Migration (for reference only):
 * ALTER TABLE `plans` ADD COLUMN `trainee_image_path` VARCHAR(255) DEFAULT NULL AFTER `source`;
 */

// Prevent redeclaration if already defined elsewhere
if (!function_exists('handleTraineeImageUpload')) {
    /**
     * Handles trainee image upload: validates, moves, logs, and returns relative DB path.
     *
     * @param PDO $pdo PDO connection object
     * @param int $planId Associated plan ID
     * @param array $fileData The $_FILES['traineeImageUpload'] input
     * @param string|null $oldImagePath Existing image path (optional)
    * @param \Psr\Log\LoggerInterface $log Logger instance
     * @return string|null Relative path to saved image, or null if deleted
     */
    function handleTraineeImageUpload(PDO $pdo, int $planId, array $fileData, ?string $oldImagePath, $log): ?string
    {
        $uploadDir = __DIR__ . '/../uploads/trainee_images/';
        $relativePath = 'uploads/trainee_images/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $shouldDeleteOldImage = false;

        // Check for replacing or explicitly clearing the old image
        if (!empty($oldImagePath) && file_exists(__DIR__ . '/../' . $oldImagePath)) {
            if ($fileData['error'] === UPLOAD_ERR_OK || ($_POST['clear_trainee_image'] ?? '') === '1') {
                $shouldDeleteOldImage = true;
            }
        }

        // Delete old image if necessary
        if ($shouldDeleteOldImage) {
            if (unlink(__DIR__ . '/../' . $oldImagePath)) {
                $log->info("Deleted old trainee image for plan ID {$planId}: {$oldImagePath}");
            } else {
                $log->error("Failed to delete old trainee image for plan ID {$planId}: {$oldImagePath}");
            }

            // If no new file, nullify DB path
            if ($fileData['error'] !== UPLOAD_ERR_OK) {
                return null;
            }
        }

        // If no file uploaded, return existing path
        if ($fileData['error'] === UPLOAD_ERR_NO_FILE) {
            return $oldImagePath;
        }

        // Handle upload errors
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            $log->warning("Upload error: plan ID {$planId}, error code {$fileData['error']}");
            throw new Exception('Image upload failed due to a file error.');
        }

        // Validate file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxFileSize = 5 * 1024 * 1024;

        if (!in_array($fileData['type'], $allowedTypes)) {
            throw new Exception('Invalid file type.');
        }
        if ($fileData['size'] > $maxFileSize) {
            throw new Exception('File too large. Max 5MB.');
        }

        // Save with unique filename
        $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('trainee_') . '.' . $extension;
        $destination = $uploadDir . $fileName;

        if (move_uploaded_file($fileData['tmp_name'], $destination)) {
            $log->info("Uploaded trainee image: {$fileName}");
            return $relativePath . $fileName;
        } else {
            throw new Exception('Failed to move uploaded image.');
        }
    }
}

// Ensure we have a suffix like `_inline` or `_modal` for unique DOM IDs
$id_suffix = $id_suffix ?? '';
?>

<div class="col-md-6 mb-3 trainee-image-component v8-image-component">
    <label for="traineeImageUpload<?php echo $id_suffix; ?>" class="form-label">Trainee Image</label>
    <div class="input-group">
        <input type="file" class="form-control v8-input" id="traineeImageUpload<?php echo $id_suffix; ?>" name="traineeImageUpload" accept="image/jpeg,image/png,image/gif,image/webp">
        <button class="btn btn-outline-danger v8-animated-pill v8-tap-feedback" type="button" id="clearTraineeImageBtn<?php echo $id_suffix; ?>" style="display:none;">Clear</button>
    </div>
    <div class="mt-2 text-center border p-2 rounded v8-image-preview" id="traineeImagePreviewContainer<?php echo $id_suffix; ?>" style="display: none;">
        <img id="traineeImagePreview<?php echo $id_suffix; ?>" src="#" alt="Image Preview" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
        <p class="text-muted small mt-1">Max 5MB (JPG, PNG, GIF, WEBP)</p>
    </div>
    <!-- Stores current DB image path -->
    <input type="hidden" id="existingTraineeImagePath<?php echo $id_suffix; ?>" name="existingTraineeImagePath" value="">
    <!-- Instructs backend to delete image -->
    <input type="hidden" id="clearTraineeImageFlag<?php echo $id_suffix; ?>" name="clear_trainee_image" value="0">
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Support multiple image upload components in one page
    document.querySelectorAll('.trainee-image-component').forEach(component => {
        const uploadInput = component.querySelector('input[type="file"]');
        const previewImg = component.querySelector('img');
        const previewContainer = component.querySelector('.border');
        const clearBtn = component.querySelector('button');
        const clearFlagInput = component.querySelector('input[name="clear_trainee_image"]');
        const existingPathInput = component.querySelector('input[name="existingTraineeImagePath"]');

        if (!uploadInput || !previewImg) return;

        // Display existing image if available
        const existingPath = existingPathInput?.value?.trim();
        if (existingPath) {
            previewImg.src = existingPath;
            previewContainer.style.display = 'block';
            clearBtn.style.display = 'inline-block';
        }

        // New image selected by user
        uploadInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.style.display = 'block';
                    clearBtn.style.display = 'inline-block';
                    clearFlagInput.value = '0';
                };
                reader.readAsDataURL(file);
            }
        });

        // Clear current image
        clearBtn.addEventListener('click', () => {
            uploadInput.value = '';
            previewImg.src = '#';
            previewContainer.style.display = 'none';
            clearBtn.style.display = 'none';
            clearFlagInput.value = '1';
        });
    });
});
</script>
