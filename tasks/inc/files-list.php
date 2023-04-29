<?php

// AJAX action for fetching the list of files
function coder_fetch_files_list() {
    $dir = wp_upload_dir()['basedir'].'/code/';

    if (is_dir($dir)) {
        $files = scandir($dir);
        $files = array_filter($files, function($file) {
            return $file != '.' && $file != '..';
        });
        wp_send_json_success($files);
    } else {
        wp_send_json_error('Oops! Could not find the directory.');
    }
}
add_action('wp_ajax_coder_fetch_files_list', 'coder_fetch_files_list');

// AJAX action for fetching the file content
function coder_fetch_file_content() {
    $file = sanitize_text_field($_POST['file']);
    $dir = wp_upload_dir()['basedir'].'/code/';

    if (is_dir($dir) && file_exists($dir.'/'.$file)) {
        $content = file_get_contents($dir.'/'.$file);
        wp_send_json_success($content);
    } else {
        wp_send_json_error('Oops! Could not find the file.');
    }
}
add_action('wp_ajax_coder_fetch_file_content', 'coder_fetch_file_content');
