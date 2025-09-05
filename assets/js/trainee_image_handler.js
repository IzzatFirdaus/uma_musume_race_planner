/* eslint-env browser */
(() => {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.trainee-image-component').forEach(component => {
            const uploadInput = component.querySelector('input[type="file"]');
            const previewImg = component.querySelector('img');
            const previewContainer = previewImg ? previewImg.closest('.mt-2') : null;
            const clearBtn = component.querySelector('button');
            const clearFlagInput = component.querySelector('input[name="clear_trainee_image"]');
            const existingPathInput = component.querySelector('input[name="existingTraineeImagePath"]');
            if (!uploadInput || !previewImg || !previewContainer) {
                return;
            }

            const existingPath = existingPathInput?.value?.trim();
            if (existingPath) {
                previewImg.src = existingPath;
                previewContainer.style.display = 'block';
                if (clearBtn) {
                    clearBtn.style.display = 'inline-block';
                }
            }

            uploadInput.addEventListener('change', (event) => {
                const file = event.target.files?.[0];
                if (file) {
                    const validTypes = ['image/jpeg','image/png','image/gif','image/webp'];
                    import('sweetalert2').then(Swal => {
                        if (!validTypes.includes(file.type)) {
                            Swal.default.fire({
                                title: 'Unsupported image type!',
                                text: 'Please upload JPG, PNG, GIF, or WEBP.',
                                icon: 'error',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            uploadInput.value = '';
                            return;
                        }
                        if (file.size > 5 * 1024 * 1024) {
                            Swal.default.fire({
                                title: 'File too large!',
                                text: 'Max file size is 5MB.',
                                icon: 'error',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            uploadInput.value = '';
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            previewImg.src = e.target?.result || '#';
                            previewContainer.style.display = 'block';
                            if (clearBtn) {
                                clearBtn.style.display = 'inline-block';
                            }
                            if (clearFlagInput) {
                                clearFlagInput.value = '0';
                            }
                        };
                        reader.readAsDataURL(file);
                    });
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.src = e.target?.result || '#';
                        previewContainer.style.display = 'block';
                        if (clearBtn) {
                            clearBtn.style.display = 'inline-block';
                        }
                        if (clearFlagInput) {
                            clearFlagInput.value = '0';
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (uploadInput) {
                    uploadInput.value = '';
                }
                previewImg.src = '#';
                previewContainer.style.display = 'none';
                clearBtn.style.display = 'none';
                if (clearFlagInput) {
                    clearFlagInput.value = '1';
                }
              });
        }
        });
    });
})();
