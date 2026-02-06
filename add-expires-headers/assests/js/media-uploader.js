// Wait for both jQuery and wp.media to be available
function initMediaUploader() {
    if (typeof jQuery === 'undefined' || typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        console.log('Waiting for dependencies...');
        setTimeout(initMediaUploader, 100);
        return;
    }

    console.log('Media uploader script loaded');
    console.log('wp object:', typeof wp);
    console.log('wp.media:', typeof wp !== 'undefined' ? typeof wp.media : 'wp not defined');

    jQuery(document).ready(function ($) {
        var mediaUploader;

        // Check if the button exists
        if ($('#upload_media_button').length === 0) {
            console.log('Upload button not found on this page');
            return;
        }

        console.log('Setting up upload button click handler');

        $('#upload_media_button').click(function (e) {
            e.preventDefault();
            console.log('Upload button clicked');

            // If the uploader object has already been created, reopen the dialog
            if (mediaUploader) {
                console.log('Reopening existing media uploader');
                mediaUploader.open();
                return;
            }

            console.log('Creating new media uploader');
            // Create the media frame
            mediaUploader = wp.media({
                title: 'Choose Placeholder Image',
                button: {
                    text: 'Choose Image'
                },
                multiple: false
            });

            // When a file is selected, run a callback
            mediaUploader.on('select', function () {
                console.log('File selected');
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                console.log('Selected attachment:', attachment);
                $('#media_url').val(attachment.url);
                $('#preview_image').attr('src', attachment.url).show();
            });

            // Open the uploader dialog
            console.log('Opening media uploader dialog');
            mediaUploader.open();
        });
    });
}

// Start the initialization
initMediaUploader();
