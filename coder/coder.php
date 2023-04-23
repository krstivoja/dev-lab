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
