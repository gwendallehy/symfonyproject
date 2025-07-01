document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('input[type="file"][name$="[picture]"]');
    const previewImage = document.getElementById('preview-image');

    if (!input || !previewImage) {
        return;
    }

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                previewImage.src = e.target.result;
            }

            reader.readAsDataURL(file);
        }
    });
});
