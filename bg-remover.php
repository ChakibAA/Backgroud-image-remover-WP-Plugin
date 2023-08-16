<?php
/*

    Plugin Name: Backgroud remover
    Description: Remove the backgroud's image 
    Version: 1.0
    Author: Chakib
    Author URI: https://www.linkedin.com/in/chakib-ammar-aouchiche-a25150220/
    License: GPL-2.0+
    License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/


function my_enqueue_scripts()
{

    wp_enqueue_script('my-main-script', plugins_url('/js/custom-script.js', __FILE__), array('jquery'), '1.0.0', true);
    wp_localize_script('my-main-script', 'php_data', array('ajax_url' => admin_url('admin-ajax.php')));
    wp_enqueue_style('my-plugin-styles', plugins_url('/css/styles.css', __FILE__), array(), '1.0');

}
add_action('admin_enqueue_scripts', 'my_enqueue_scripts');


function custom_attachment_button($form_fields, $post)
{

    $form_fields['remove-bg'] = array(
        'label' => 'Generate image without backgroud',
        'input' => 'html',
        'html' => '<button onclick="remove_bg()" name="?remove-bg" class="button button-primary">Generate</button>'

    );

    $form_fields['remove-bg-spinner'] = array(
        'input' => 'html',
        'html' => '<div id="customModal" class="custom-modal">
                        <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title">Loading...</h2>
                                    <span class="close" name="close">&times;</span>
                                </div>
                                <div class="modal-body">
                                    <p id="modalMessage">Please wait while loading...</p>
                                    <div class="spinner"></div>
                                </div>
                        </div>
                    </div>',
    );

    return $form_fields;
}

add_filter('attachment_fields_to_edit', 'custom_attachment_button', 10, 2);


add_action('wp_ajax_execute_remover', 'execute_remover');
add_action('wp_ajax_nopriv_execute_remover', 'execute_remover');


function execute_remover()
{

    // Get the url of image
    $attachment_url = $_POST['attachment_url'];

    // Get the id of image 
    $attachment_id = attachment_url_to_postid($attachment_url);


    $attachment = get_post($attachment_id);

    $python_utl = 'http://127.0.0.1:5000';

    $api_url = $python_utl . '/remove_background?image_url=' . $attachment_url;

    $response = wp_remote_get($api_url);

    if (!is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);


        $filename = $attachment->post_title . '-no-bg.png';
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['path'];

        // Full path to the image file
        $image_path = $upload_path . '/' . $filename;
        file_put_contents($image_path, $body);

        $attachment = array(
            'post_mime_type' => 'image/png',
            'post_title' => $filename,
            'post_content' => $attachment->post_content,
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment($attachment, $image_path);

        // Generate metadata and update the attachment
        $attach_data = wp_generate_attachment_metadata($attach_id, $image_path);
        wp_update_attachment_metadata($attach_id, $attach_data);

        echo 'succes';
    } else {
        echo 'error'; // Request failed
    }

    wp_die();
}


function custom_spinner_plugin_modal()
{
    ob_start();
    ?>


    <!-- Modal -->
    <div id="customModal" class="custom-modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p id="modalMessage">Please wait while loading...</p>
            <div class="spinner"></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('custom_spinner', 'custom_spinner_plugin_modal');