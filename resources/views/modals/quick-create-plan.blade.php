{{-- Converted from quick_create_plan_modal.php --}}
@livewire('quick-create-plan')
</div>

@push('scripts')
<script>
    // This script handles the client-side validation for the quick create form.
    // The actual form submission is handled by the global event listener in app.js.
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('quickCreatePlanForm');
        if (!form) return;

        // Add Bootstrap's validation classes on submission attempt.
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);

        // Reset validation state when the modal is hidden.
        const createPlanModal = document.getElementById('createPlanModal');
        if (createPlanModal) {
            createPlanModal.addEventListener('hidden.bs.modal', function() {
                form.classList.remove('was-validated');
                form.reset();
            });
        }
    });
</script>
@endpush
