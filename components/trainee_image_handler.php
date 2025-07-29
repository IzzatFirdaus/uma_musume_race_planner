<?php

// components/trainee_image_handler.php
// This file contains HTML for image upload/display, client-side JS for preview,
// and a server-side PHP function for handling the file upload.

/**
 * SQL Schema Snippet for `plans` table (for documentation purposes)
 * You would execute this manually or via migrations.
 *
 * ALTER TABLE `plans` ADD COLUMN `trainee_image_path` VARCHAR(255) DEFAULT NULL AFTER `source`;
 */

// --- PHP FILE UPLOAD HANDLING FUNCTION ---
// This function will be called from handle_plan_crud.php when the form is submitted.
// It is wrapped in function_exists to prevent redeclaration if the file is included multiple times
// in different contexts (though it should primarily be included where needed, like handle_plan_crud.php).
if (!function_exists('handleTraineeImageUpload')) {
    /**
         * Handles the upload of a trainee image file.
         *
         * @param PDO $pdo The PDO database connection.
         * @param int $planId The ID of the plan the image belongs to.
         * @param array $fileData The $_FILES array entry for the uploaded image (e.g., $_FILES['trainee_image_upload']).
         * @param string|null $oldImagePath The existing image path from the database, if updating a plan.
         * @param Monolog\Logger $log The logger instance for recording events.
         * @return string|null The relative path to the saved image, or null if no valid file was uploaded/saved.
         */
    function handleTraineeImageUpload(PDO $pdo, int $planId, array $fileData, ?string $oldImagePath, Monolog\Logger $log): ?string
    {
        $uploadDir = __DIR__ . '/../uploads/trainee_images/';
        // Absolute path to storage directory
        $relativePath = 'uploads/trainee_images/';
        // Relative path for database storage

        // Create the directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
            // Ensure write permissions for web server
        }

        // Handle deletion of existing image if a new one is uploaded or user explicitly clears
        $shouldDeleteOldImage = false;
        if (!empty($oldImagePath) && file_exists(__DIR__ . '/../' . $oldImagePath)) {
            // Check if a new file is actually being uploaded OR if the 'clear_image' flag is set
            // The frontend should set a hidden field like 'clear_trainee_image' if the user clears it.
            // For simplicity here, we assume if fileData['error'] is UPLOAD_ERR_NO_FILE and oldImagePath exists,
            // AND there's a specific flag, we delete. For now, we only delete if a new valid file replaces it.
            if ($fileData['error'] === UPLOAD_ERR_OK) {
                // A new file is being uploaded, delete the old one first
                $shouldDeleteOldImage = true;
            } else {
                // Check if client explicitly sent a signal to clear existing image without new upload
                // This requires a new hidden input in the form, e.g., <input type="hidden" name="clear_trainee_image" value="1">
                if (isset($_POST['clear_trainee_image']) && $_POST['clear_trainee_image'] === '1') {
                    $shouldDeleteOldImage = true;
                }
            }
        }

        if ($shouldDeleteOldImage) {
            if (unlink(__DIR__ . '/../' . $oldImagePath)) {
                $log->info("Deleted old trainee image for plan ID {$planId}: {$oldImagePath}");
            } else {
                $log->error("Failed to delete old trainee image for plan ID {$planId}: {$oldImagePath}");
            }
            // After deleting, ensure old path is nullified in DB if no new file is uploaded
            if ($fileData['error'] !== UPLOAD_ERR_OK) {
                return null;
                // Return null to update DB path to null
            }
        }


        if ($fileData['error'] === UPLOAD_ERR_NO_FILE) {
            // No new file uploaded, and no explicit clear signal detected, retain existing path (or null if none)
            return $oldImagePath;
        }

        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            $log->warning("Trainee image upload error for plan ID {$planId}: {$fileData['error']}");
            // Handle specific upload errors
            $errorMessage = '';
            switch ($fileData['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errorMessage = 'Uploaded file exceeds size limit.';

                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errorMessage = 'File upload was interrupted.';

                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errorMessage = 'Missing a temporary folder for uploads.';

                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errorMessage = 'Failed to write file to disk.';

                    break;
                case UPLOAD_ERR_EXTENSION:
                    $errorMessage = 'A PHP extension stopped the file upload.';

                    break;
                default:
                    $errorMessage = 'Unknown upload error.';
            }
            throw new Exception("Image upload failed: {$errorMessage}");
            // Throw to be caught by handle_plan_crud.php
        }

        // Validate file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxFileSize = 5 * 1024 * 1024;
        // 5 MB

        if (!in_array($fileData['type'], $allowedTypes)) {
            throw new Exception('Invalid image type. Only JPG, PNG, GIF, WEBP are allowed.');
        }
        if ($fileData['size'] > $maxFileSize) {
            throw new Exception('Image file is too large (max 5MB).');
        }

        // Generate a unique filename
        $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('trainee_') . '.' . $extension;
        $destinationPath = $uploadDir . $fileName;
        if (move_uploaded_file($fileData['tmp_name'], $destinationPath)) {
            $log->info("Trainee image uploaded successfully for plan ID {$planId}: {$fileName}");
            return $relativePath . $fileName;
            // Return relative path for DB
        } else {
            $log->error("Failed to move uploaded trainee image for plan ID {$planId}. Temp: {$fileData['tmp_name']}, Dest: {$destinationPath}");
            throw new Exception('Failed to save the uploaded image.');
        }
    }

}

// This component now uses a passed-in suffix to create unique IDs.
$id_suffix = $id_suffix ?? ''; // Fallback to empty string if not provided
?>

<div class="col-md-6 mb-3 trainee-image-component">
    <label for="traineeImageUpload<?php echo $id_suffix; ?>" class="form-label">Trainee Image</label>
    <div class="input-group">
        <input type="file" class="form-control" id="traineeImageUpload<?php echo $id_suffix; ?>" name="traineeImageUpload" accept="image/jpeg,image/png,image/gif,image/webp">
        <button class="btn btn-outline-danger" type="button" id="clearTraineeImageBtn<?php echo $id_suffix; ?>" style="display:none;">Clear</button>
    </div>
    <div class="mt-2 text-center border p-2 rounded" id="traineeImagePreviewContainer<?php echo $id_suffix; ?>" style="display: none;">
        <img id="traineeImagePreview<?php echo $id_suffix; ?>" src="#" alt="Image Preview" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
        <p class="text-muted small mt-1">Max 5MB (JPG, PNG, GIF, WEBP)</p>
    </div>
    <input type="hidden" id="existingTraineeImagePath<?php echo $id_suffix; ?>" name="existingTraineeImagePath" value="">
    <input type="hidden" id="clearTraineeImageFlag<?php echo $id_suffix; ?>" name="clear_trainee_image" value="0">
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // This script makes each image handler self-contained for previewing a selected file.
    // It finds its own elements by looking for the unique parent container.
    // This runs for both the modal and inline instances of the component.
    document.querySelectorAll('.trainee-image-component').forEach(componentContainer => {
        const uploadInput = componentContainer.querySelector('input[type="file"]');
        const previewImg = componentContainer.querySelector('img');
        const previewContainer = componentContainer.querySelector('.border');
        const clearBtn = componentContainer.querySelector('button');
        const clearFlagInput = componentContainer.querySelector('input[name="clear_trainee_image"]');

        if (!uploadInput) return;

        // Listener for when a user selects a new file, to preview it
        uploadInput.addEventListener('change', function(event) {
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

        // Listener for the "Clear" button
        clearBtn.addEventListener('click', function() {
            uploadInput.value = '';
            previewImg.src = '#';
            previewContainer.style.display = 'none';
            clearBtn.style.display = 'none';
            // We do NOT clear the existingTraineeImagePath here. That is handled by logic in index.php
            clearFlagInput.value = '1'; // Set flag to indicate explicit clear to backend
        });
    });
});
</script>