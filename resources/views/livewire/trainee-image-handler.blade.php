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
