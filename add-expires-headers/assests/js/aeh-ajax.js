jQuery(document).ready(function () {
    jQuery(document).on('click', '.aeh-optimize-btn', function () {
        const jQuerybtn = jQuery(this);
        const id = jQuery(this).data('id');
        const status = jQuery('#aeh-optimization-status-' + id);
        status.text('Optimizing...');
        jQuery.post(BulkManager.ajax_url, {
            action: 'aeh_optimize_image',
            id: id,
            nonce: BulkManager.nonce
        }, function (response) {
            if (response.success) {
                status.html('<p><small>Original Size: ' + humanFileSize(response.data.original_size) + ',<br>' + ((response.data.optimized_size) ? 'Optimized Size: ' + humanFileSize(response.data.optimized_size) : '') + '</small></p>');
                const newBtn = jQuery('<a class="button aeh-reverse-btn" data-id="' + id + '">Reverse</a>');
                jQuerybtn.replaceWith(newBtn);
            } else {
                status.text('❌ ' + response.data.msg);
            }
        });
    });
    jQuery(document).on('click', '.aeh-reverse-btn', function () {
        const jQuerybtn = jQuery(this);
        const id = jQuery(this).data('id');
        const status = jQuery('#aeh-optimization-status-' + id);
        status.text('Reversing...');
        jQuery.post(BulkManager.ajax_url, {
            action: 'aeh_reverse_image',
            id: id,
            nonce: BulkManager.nonce
        }, function (response) {
            if (response.success) {
                status.text(response.data.msg);
                const newBtn = jQuery('<a class="button aeh-optimize-btn" data-id="' + id + '">Optimize</a>');
                jQuerybtn.replaceWith(newBtn);
            } else {
                status.text(response.data.msg);
            }
        });
    });
    function humanFileSize(size) {
        var i = size == 0 ? 0 : Math.floor(Math.log(size) / Math.log(1024));
        return +((size / Math.pow(1024, i)).toFixed(2)) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
    }
    jQuery('.clear-browser-cache').click(function () {
        var data = {
            'action': 'purge_cache',
            'security': ajax_object.purge_cache_nonce,
        };
        jQuery.post(ajax_object.ajax_url, data, function () {
            alert('Browser cache cleared Successfully!');
            location.reload();
        });
    });
    jQuery('.aeh-dismiss-maybelater').click(function () {
        var data = {
            'action': 'hide_review_notice',
            'security': ajax_object.maybelater_nonce,
        };
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajax_object.ajax_url, data, function () {
            alert('Thanks for your response!');
            location.reload();
        });
    });
    jQuery('.aeh-dismiss-alreadydid').click(function () {
        var data = {
            'action': 'hide_review_notice',
            'security': ajax_object.alreadydid_nonce,
        };
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajax_object.ajax_url, data, function () {
            alert('Thanks for your response!');
            location.reload();
        });
    });
});