<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\ImageProcessingService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * TraineeImageHandler Livewire Component
 *
 * Handles trainee image upload with preview, validation, and storage.
 * Supports both Account_Runs (server storage) and Local_Runs (path reference only).
 *
 * Implements Requirements:
 * - 17.1: Image upload control in General tab
 * - 17.2: Preview image before saving
 * - 17.3: Store image and associate with plan
 * - 17.4: Validate file type (JPEG, PNG, WebP) and size (max 2MB)
 * - 17.5: Display error messages on upload failure
 * - 72.2, 72.3: Storage mode handling
 */
class TraineeImageHandler extends Component
{
    use WithFileUploads;

    /**
     * The plan ID (for Account_Runs).
     */
    public ?int $planId = null;

    /**
     * The local UUID (for Local_Runs).
     */
    public ?string $localUuid = null;

    /**
     * The storage mode (local or account).
     */
    public string $storageMode = 'account';

    /**
     * The uploaded file for preview.
     * Validates: JPEG, PNG, WebP, max 2MB (2048KB).
     */
    #[Validate('nullable|image|mimes:jpeg,jpg,png,webp|max:2048')]
    public $traineeImage = null;

    /**
     * The existing image path (if any).
     */
    public ?string $existingImagePath = null;

    /**
     * Preview URL for the uploaded image.
     */
    public ?string $previewUrl = null;

    /**
     * Flag to clear the existing image.
     */
    public bool $clearImage = false;

    /**
     * Error message for display.
     */
    public ?string $errorMessage = null;

    /**
     * Success message for display.
     */
    public ?string $successMessage = null;

    /**
     * Whether the component is in loading state.
     */
    public bool $isUploading = false;

    /**
     * Allowed MIME types for validation display.
     */
    public const ALLOWED_TYPES = ['JPEG', 'PNG', 'WebP'];

    /**
     * Maximum file size in MB for display.
     */
    public const MAX_SIZE_MB = 2;

    /**
     * Mount the component with initial data.
     */
    public function mount(
        ?int $planId = null,
        ?string $localUuid = null,
        string $storageMode = 'account',
        ?string $existingImagePath = null
    ): void {
        $this->planId = $planId;
        $this->localUuid = $localUuid;
        $this->storageMode = $storageMode;
        $this->existingImagePath = $existingImagePath;

        // Set preview URL if existing image exists
        if ($this->existingImagePath && $this->storageMode === 'account') {
            $this->previewUrl = $this->getImageUrl($this->existingImagePath);
        }
    }

    /**
     * Handle file upload and generate preview.
     * Implements Requirement 17.2: Preview image before saving.
     */
    public function updatedTraineeImage(): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;
        $this->clearImage = false;

        // Validate the uploaded file
        try {
            $this->validateOnly('traineeImage');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->errorMessage = $e->validator->errors()->first('traineeImage');
            $this->traineeImage = null;
            $this->previewUrl = null;

            return;
        }

        if ($this->traineeImage) {
            // Generate temporary preview URL
            $this->previewUrl = $this->traineeImage->temporaryUrl();
        }
    }

    /**
     * Save the uploaded image to storage.
     * Implements Requirements 17.3, 72.2, 72.3: Storage mode handling.
     *
     * @return array{success: bool, path: string|null, message: string}
     */
    public function saveImage(): array
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        // Handle clear image request
        if ($this->clearImage) {
            return $this->handleClearImage();
        }

        // No new image to save
        if (! $this->traineeImage) {
            return [
                'success' => true,
                'path' => $this->existingImagePath,
                'message' => 'No changes to image.',
            ];
        }

        // Validate before saving
        try {
            $this->validateOnly('traineeImage');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->errorMessage = $e->validator->errors()->first('traineeImage');

            return [
                'success' => false,
                'path' => null,
                'message' => $this->errorMessage,
            ];
        }

        try {
            $this->isUploading = true;

            // For Account_Runs: Upload to server storage
            if ($this->storageMode === 'account') {
                return $this->saveToServerStorage();
            }

            // For Local_Runs: Return temporary URL (no base64 in localStorage)
            // The actual image handling for local runs is done client-side
            return $this->handleLocalRunImage();
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to upload image: '.$e->getMessage();

            return [
                'success' => false,
                'path' => null,
                'message' => $this->errorMessage,
            ];
        } finally {
            $this->isUploading = false;
        }
    }

    /**
     * Save image to server storage for Account_Runs.
     *
     * @return array{success: bool, path: string|null, message: string}
     */
    private function saveToServerStorage(): array
    {
        /** @var ImageProcessingService $imageService */
        $imageService = app(ImageProcessingService::class);

        // Process and store the image
        $result = $imageService->processUpload(
            $this->traineeImage,
            'trainee_images',
            true // Generate thumbnail
        );

        // Delete old image if exists
        if ($this->existingImagePath) {
            $this->deleteExistingImage();
        }

        // Update state
        $this->existingImagePath = $result['path'];
        $this->previewUrl = $this->getImageUrl($result['path']);
        $this->traineeImage = null;
        $this->successMessage = 'Image uploaded successfully!';

        // Dispatch event for parent component
        $this->dispatch('trainee-image-saved', [
            'path' => $result['path'],
            'thumbnailPath' => $result['thumbnail_path'],
        ]);

        return [
            'success' => true,
            'path' => $result['path'],
            'message' => $this->successMessage,
        ];
    }

    /**
     * Handle image for Local_Runs (path reference only).
     * Implements Requirement 72.2: Store image path/URL reference only (no base64 in localStorage).
     *
     * @return array{success: bool, path: string|null, message: string}
     */
    private function handleLocalRunImage(): array
    {
        // For local runs, we still upload to server but mark it as temporary
        // The path is stored in localStorage, not the actual image data
        /** @var ImageProcessingService $imageService */
        $imageService = app(ImageProcessingService::class);

        $result = $imageService->processUpload(
            $this->traineeImage,
            'trainee_images/local',
            true
        );

        $this->existingImagePath = $result['path'];
        $this->previewUrl = $this->getImageUrl($result['path']);
        $this->traineeImage = null;
        $this->successMessage = 'Image uploaded successfully!';

        // Dispatch event for parent component
        $this->dispatch('trainee-image-saved', [
            'path' => $result['path'],
            'thumbnailPath' => $result['thumbnail_path'],
            'storageMode' => 'local',
        ]);

        return [
            'success' => true,
            'path' => $result['path'],
            'message' => $this->successMessage,
        ];
    }

    /**
     * Handle clearing the existing image.
     *
     * @return array{success: bool, path: string|null, message: string}
     */
    private function handleClearImage(): array
    {
        if ($this->existingImagePath && $this->storageMode === 'account') {
            $this->deleteExistingImage();
        }

        $this->existingImagePath = null;
        $this->previewUrl = null;
        $this->traineeImage = null;
        $this->clearImage = false;
        $this->successMessage = 'Image removed successfully!';

        // Dispatch event for parent component
        $this->dispatch('trainee-image-cleared');

        return [
            'success' => true,
            'path' => null,
            'message' => $this->successMessage,
        ];
    }

    /**
     * Clear the current image selection/preview.
     */
    public function clearSelection(): void
    {
        $this->traineeImage = null;
        $this->errorMessage = null;
        $this->successMessage = null;

        // Restore existing image preview if available
        if ($this->existingImagePath && $this->storageMode === 'account') {
            $this->previewUrl = $this->getImageUrl($this->existingImagePath);
        } else {
            $this->previewUrl = null;
        }
    }

    /**
     * Mark the existing image for removal.
     */
    public function removeImage(): void
    {
        $this->clearImage = true;
        $this->traineeImage = null;
        $this->previewUrl = null;
        $this->errorMessage = null;
        $this->successMessage = null;

        // Dispatch event to notify parent
        $this->dispatch('trainee-image-marked-for-removal');
    }

    /**
     * Cancel the removal and restore the existing image.
     */
    public function cancelRemoval(): void
    {
        $this->clearImage = false;

        if ($this->existingImagePath && $this->storageMode === 'account') {
            $this->previewUrl = $this->getImageUrl($this->existingImagePath);
        }
    }

    /**
     * Delete the existing image from storage.
     */
    private function deleteExistingImage(): void
    {
        if (! $this->existingImagePath) {
            return;
        }

        /** @var ImageProcessingService $imageService */
        $imageService = app(ImageProcessingService::class);

        // Generate thumbnail path
        $pathInfo = pathinfo($this->existingImagePath);
        $thumbnailPath = $pathInfo['dirname'].'/thumbnails/'.$pathInfo['filename'].'_thumb.'.$pathInfo['extension'];

        $imageService->deleteImage($this->existingImagePath, $thumbnailPath);
    }

    /**
     * Get the public URL for an image path.
     */
    private function getImageUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    /**
     * Listen for external save trigger.
     */
    #[On('save-trainee-image')]
    public function onSaveTriggered(): void
    {
        $this->saveImage();
    }

    /**
     * Listen for external clear trigger.
     */
    #[On('clear-trainee-image')]
    public function onClearTriggered(): void
    {
        $this->removeImage();
        $this->saveImage();
    }

    /**
     * Get the current image path (for parent component).
     */
    public function getImagePath(): ?string
    {
        if ($this->clearImage) {
            return null;
        }

        return $this->existingImagePath;
    }

    /**
     * Check if there are pending changes.
     */
    public function hasPendingChanges(): bool
    {
        return $this->traineeImage !== null || $this->clearImage;
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.trainee-image-handler');
    }
}
