{{--
    TraineeImageHandler Livewire Component View

    Implements Requirements:
    - 17.1: Image upload control in General tab
    - 17.2: Preview image before saving
    - 17.4: Validate file type (JPEG, PNG, WebP) and size (max 2MB)
    - 17.5: Display error messages on upload failure
--}}
<div class="trainee-image-handler" data-testid="trainee-image-handler" x-data="{
    isDragging: false,
    handleDrop(e) {
        this.isDragging = false;
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            $refs.fileInput.files = files;
            $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
}">
    {{-- Label --}}
    <label for="trainee-image-upload-{{ $planId ?? ($localUuid ?? 'new') }}"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Trainee Image
        <span class="text-gray-500 dark:text-gray-400 text-xs ml-1">
            ({{ implode(', ', \App\Livewire\TraineeImageHandler::ALLOWED_TYPES) }}, max
            {{ \App\Livewire\TraineeImageHandler::MAX_SIZE_MB }}MB)
        </span>
    </label>

    {{-- Drop Zone / Upload Area --}}
    <div class="relative border-2 border-dashed rounded-lg p-4 transition-colors duration-200
            {{ $errorMessage ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : '' }}
            {{ !$errorMessage && $previewUrl ? 'border-green-400 bg-green-50 dark:bg-green-900/20' : '' }}
            {{ !$errorMessage && !$previewUrl ? 'border-gray-300 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500' : '' }}"
        :class="{ 'border-blue-500 bg-blue-50 dark:bg-blue-900/20': isDragging }" @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop($event)" data-testid="trainee-image-dropzone">
        {{-- Loading Overlay --}}
        <div wire:loading wire:target="traineeImage"
            class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 flex items-center justify-center rounded-lg z-10">
            <div class="flex flex-col items-center">
                <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span class="mt-2 text-sm text-gray-600 dark:text-gray-400">Processing image...</span>
            </div>
        </div>

        {{-- Preview Area --}}
        @if ($previewUrl && !$clearImage)
            <div class="flex flex-col items-center" data-testid="trainee-image-preview-container">
                <div class="relative group">
                    <img src="{{ $previewUrl }}" alt="Trainee image preview"
                        class="max-h-48 max-w-full rounded-lg shadow-md object-contain"
                        data-testid="trainee-image-preview" />
                    {{-- Remove Button Overlay --}}
                    <button type="button" wire:click="removeImage"
                        class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 hover:bg-red-600 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        title="Remove image" aria-label="Remove trainee image" data-testid="trainee-image-remove-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Change Image Button --}}
                <label for="trainee-image-upload-{{ $planId ?? ($localUuid ?? 'new') }}"
                    class="mt-3 inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 cursor-pointer">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    Change image
                </label>
            </div>
        @elseif($clearImage)
            {{-- Image Marked for Removal --}}
            <div class="flex flex-col items-center py-4" data-testid="trainee-image-removal-pending">
                <div class="text-amber-600 dark:text-amber-400 mb-2">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Image will be removed on save</p>
                <button type="button" wire:click="cancelRemoval"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                    data-testid="trainee-image-cancel-removal-btn">
                    Cancel removal
                </button>
            </div>
        @else
            {{-- Upload Prompt --}}
            <div class="flex flex-col items-center py-4" data-testid="trainee-image-upload-prompt">
                <div class="text-gray-400 dark:text-gray-500 mb-3">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                    <span x-show="!isDragging">Drag and drop an image here, or</span>
                    <span x-show="isDragging" class="text-blue-600 dark:text-blue-400">Drop image here</span>
                </p>
                <label for="trainee-image-upload-{{ $planId ?? ($localUuid ?? 'new') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg cursor-pointer transition-colors duration-200 focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                        </path>
                    </svg>
                    Browse files
                </label>
            </div>
        @endif

        {{-- Hidden File Input --}}
        <input type="file" id="trainee-image-upload-{{ $planId ?? ($localUuid ?? 'new') }}" wire:model="traineeImage"
            accept="image/jpeg,image/png,image/webp" class="sr-only" x-ref="fileInput"
            aria-describedby="trainee-image-help trainee-image-error" data-testid="trainee-image-input" />
    </div>

    {{-- Help Text --}}
    <p id="trainee-image-help" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
        Supported formats: {{ implode(', ', \App\Livewire\TraineeImageHandler::ALLOWED_TYPES) }}.
        Maximum file size: {{ \App\Livewire\TraineeImageHandler::MAX_SIZE_MB }}MB.
    </p>

    {{-- Error Message --}}
    @if ($errorMessage)
        <div id="trainee-image-error" class="mt-2 flex items-center text-sm text-red-600 dark:text-red-400"
            role="alert" data-testid="trainee-image-error">
            <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd"></path>
            </svg>
            {{ $errorMessage }}
        </div>
    @endif

    {{-- Success Message --}}
    @if ($successMessage)
        <div class="mt-2 flex items-center text-sm text-green-600 dark:text-green-400" role="status"
            data-testid="trainee-image-success">
            <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd"></path>
            </svg>
            {{ $successMessage }}
        </div>
    @endif

    {{-- Storage Mode Indicator --}}
    @if ($storageMode === 'local')
        <p class="mt-2 text-xs text-amber-600 dark:text-amber-400 flex items-center">
            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Image will be stored on server. Only the path reference is saved locally.
        </p>
    @endif
</div>
