{{--
    This Blade partial is converted from the original plan_details_modal.php.
    It provides a full-featured modal for editing the details of a selected plan.
--}}
@livewire('plan-details')

@push('scripts')
<script>
            const planTitle = document.getElementById('plan_title').value || 'plan';
            const safeFileName = planTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase();
            const fileName = `${safeFileName}_${planId}.txt`;

            this.href = `/plans/${planId}/export?format=txt`;
            this.download = fileName;
            this.target = '_blank';
            this.click();
        });
    }
});
</script>
@endpush
