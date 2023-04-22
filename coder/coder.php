<?php

/**
 * Plugin Name: Coder
 * Description: A plugin that allows users to toggle between a dark and light theme and remembers their preference using AJAX without reloading the page.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://www.example.com
 * License: GPL2
 */

// Register menu page for the plugin
function coder_register_menu_page()
{
    add_menu_page(
        "Coder",
        "Coder",
        "manage_options",
        "coder",
        "coder",
        "dashicons-editor-code",
        90
    );
}
add_action("admin_menu", "coder_register_menu_page");

// Enqueue scripts to the admin header
function my_admin_enqueue_scripts() {
    wp_enqueue_script('alpine-js', '//unpkg.com/alpinejs', [], false, false);
    wp_enqueue_script('coder-ajax', plugin_dir_url(__FILE__) . 'ajax.js', ['alpine-js'], false, false);
    wp_localize_script('coder-ajax', 'ajaxData', ['ajaxurl' => admin_url('admin-ajax.php')]);
    wp_enqueue_style('my-admin-styles', plugin_dir_url(__FILE__) . 'styles.css', [], false, 'all');
}
add_action('admin_enqueue_scripts', 'my_admin_enqueue_scripts');



// Define the callback function
function coder() {
    ?>
    

    <div x-data="coder()" x-init="init()" x-cloak class="code-wrapper">
        <aside style="width: 300px; min-width: 300px;">
            <ul>
                <template x-for="file in files" :key="file">
                    <li @click="fetchFileContent(file)" x-text="file" :class="{ 'active': file === selectedFile }"></li>
                </template>
            </ul>
        </aside>


        <main>
            <pre>
<code x-text="fileContent"></code>
            </pre>
        </main>

        
    </div>
    <?php
}


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

