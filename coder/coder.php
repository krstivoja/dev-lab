<?php

/**
 * Plugin Name: Coder
 * Description: A plugin that allows users to toggle between a dark and light theme and remembers their preference using AJAX without reloading the page.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://www.example.com
 * License: GPL2
 */

require_once 'inc/register-menu.php';
require_once 'inc/files-list.php';


// Define the callback function
function coder() {
?>


<?php require_once 'inc/content.php'; ?>

<?php
}


function coder_enqueue_admin_css() {
    wp_enqueue_style('coder-admin-css', plugins_url('css/coder-style.css', __FILE__));
    wp_enqueue_script('coder-admin-js', plugins_url('js/coder-script.js', __FILE__), array('jquery'), '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'coder_enqueue_admin_css');



// AJAX action for saving the file content
function coder_save_file_content() {
    $file = sanitize_text_field($_POST['file']);
    $content = $_POST['content'];
    $dir = wp_upload_dir()['basedir'].'/code/';

    if (is_dir($dir) && file_exists($dir.'/'.$file)) {
        file_put_contents($dir.'/'.$file, $content);
        wp_send_json_success('File saved successfully.');
    } else {
        wp_send_json_error('Oops! Could not save the file.');
    }
}
add_action('wp_ajax_coder_save_file_content', 'coder_save_file_content');
