{{--
    This partial provides the UI for the trainee image upload component.
    The server-side PHP function has been removed; that logic is now in PlanController.php.
--}}
@props(['id_suffix' => ''])

<div class="col-md-6 mb-3 trainee-image-component">
    <label for="traineeImageUpload{{ $id_suffix }}" class="form-label">Trainee Image</label>
    <div class="input-group">
        <input type="file" class="form-control" id="traineeImageUpload{{ $id_suffix }}" name="traineeImageUpload" accept="image/jpeg,image/png,image/gif,image/webp">
        <button class="btn btn-outline-danger" type="button" id="clearTraineeImageBtn{{ $id_suffix }}" style="display:none;">Clear</button>
    </div>
    <div class="mt-2 text-center border p-2 rounded" id="traineeImagePreviewContainer{{ $id_suffix }}" style="display: none;">
        <img id="traineeImagePreview{{ $id_suffix }}" src="#" alt="Image Preview" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
        <p class="text-muted small mt-1">Max 5MB (JPG, PNG, GIF, WEBP)</p>
    </div>
    <input type="hidden" id="existingTraineeImagePath{{ $id_suffix }}" name="existingTraineeImagePath" value="">
    <input type="hidden" id="clearTraineeImageFlag{{ $id_suffix }}" name="clear_trainee_image" value="0">
</div>

@push('scripts')
<script>
    // This script is scoped to run once but handles all instances of the image handler on the page.
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.trainee-image-component').forEach(component => {
            const uploadInput = component.querySelector('input[type="file"]');
            const previewImg = component.querySelector('img');
            const previewContainer = component.querySelector('.border');
            const clearBtn = component.querySelector('button');
            const clearFlagInput = component.querySelector('input[name="clear_trainee_image"]');
            const existingPathInput = component.querySelector('input[name="existingTraineeImagePath"]');

            if (!uploadInput || !previewImg) return;

            // Handler for when a new image is selected
            uploadInput.addEventListener('change', (event) => {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewContainer.style.display = 'block';
                        clearBtn.style.display = 'inline-block';
                        clearFlagInput.value = '0'; // A new file overrides the clear flag
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Handler for the "Clear" button
            clearBtn.addEventListener('click', () => {
                uploadInput.value = ''; // Clear the file input
                previewImg.src = '#';   // Reset the preview image
                previewContainer.style.display = 'none';
                clearBtn.style.display = 'none';
                clearFlagInput.value = '1'; // Set the flag to tell the server to delete the image
                existingPathInput.value = '';
            });
        });
    });
</script>
@endpush
