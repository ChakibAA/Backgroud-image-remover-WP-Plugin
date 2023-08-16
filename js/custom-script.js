function remove_bg() {

    jQuery('#customModal').fadeIn();


    jQuery('.custom-modal').click(function (event) {
        if (jQuery(event.target).hasClass('custom-modal')) {
            jQuery('#customModal').fadeOut();
        }
    });

    jQuery('.close').click(function () {
        jQuery('#customModal').fadeOut();
    });

    attachment_url = jQuery('img[class="details-image"]').attr('src')

    jQuery.ajax({
        url: php_data.ajax_url,
        type: 'POST',
        data: {
            action: 'execute_remover',
            attachment_url: attachment_url,
        },
        success: function (response) {
            console.log(response)
            jQuery('#customModal').fadeOut();
            alert('Image has been successfully generated')
        },
        error: function (error) {
            console.log(error);
            jQuery('#customModal').fadeOut();
            alert('Image has not been generated')
        }
    });
}