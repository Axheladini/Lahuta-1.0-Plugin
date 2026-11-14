<?php 


jQuery(function ($) {
    const $input = $('#myplugin-images');
    const $preview = $('#myplugin-image-preview');
    const $status = $('#myplugin-upload-status');

    let selectedFiles = [];
    let previewUrls = [];

    function clearPreviewUrls() {
        previewUrls.forEach(function (url) {
            URL.revokeObjectURL(url);
        });

        previewUrls = [];
    }

    function renderPreview() {
        clearPreviewUrls();
        $preview.empty();

        selectedFiles.forEach(function (file, index) {
            const url = URL.createObjectURL(file);
            previewUrls.push(url);

            const $item = $('<div>', {
                class: 'myplugin-image-item'
            });

            const $image = $('<img>', {
                src: url,
                alt: file.name
            });

            const $filename = $('<p>').text(file.name);

            const $remove = $('<button>', {
                type: 'button',
                class: 'button myplugin-remove-image',
                text: 'Remove'
            }).data('index', index);

            $item.append($image, $filename, $remove);
            $preview.append($item);
        });
    }

    $input.on('change', function () {
        selectedFiles = Array.from(this.files || []);
        $status.text('');
        renderPreview();
    });

    $preview.on('click', '.myplugin-remove-image', function () {
        const index = $(this).data('index');

        selectedFiles.splice(index, 1);

        // Update the native file input to match the selection.
        const dataTransfer = new DataTransfer();

        selectedFiles.forEach(function (file) {
            dataTransfer.items.add(file);
        });

        $input[0].files = dataTransfer.files;

        renderPreview();
    });

    // Make selected files available to the AJAX upload handler.
    $('#myplugin-upload-form').on('submit', function (event) {
        event.preventDefault();

        if (!selectedFiles.length) {
            $status.text('Please select at least one image.');
            return;
        }

        const formData = new FormData();

        formData.append('action', 'myplugin_upload_images');
        formData.append('nonce', myPluginUpload.nonce);

        selectedFiles.forEach(function (file) {
            formData.append('images[]', file);
        });

        $status.text('Uploading...');

        $.ajax({
            url: myPluginUpload.ajaxUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function (response) {
                if (!response.success) {
                    $status.text(
                        response.data?.message || 'Upload failed.'
                    );
                    return;
                }

                const results = response.data.results;
                const successful = results.filter(
                    item => item.success
                );

                $status.text(
                    successful.length + ' of ' +
                    results.length + ' images uploaded.'
                );

                // These are now saved WordPress image URLs.
                successful.forEach(function (image) {
                    console.log(image.id, image.url);
                });
            },

            error: function () {
                $status.text('An error occurred during upload.');
            }
        });
    });
});



?>