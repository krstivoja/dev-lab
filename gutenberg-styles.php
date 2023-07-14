<?php

/*
  Plugin Name: Gutenberg Styles
  Version: 1.0
  Author: Your Name
  Author URI: Your Website
*/

// Create Gutenberg Styles admin page
function create_gutenberg_styles_admin_page() {
  add_menu_page(
      'Gutenberg Styles',
      'Gutenberg Styles',
      'manage_options',
      'gutenberg-styles',
      'render_gutenberg_styles_admin_page',
      '',
      20
  );
}

add_action('admin_menu', 'create_gutenberg_styles_admin_page');

// This function will render the output for your Gutenberg Styles page.
function render_gutenberg_styles_admin_page() {
    echo '<div id="app"></div>';
    echo '<div id="app2">This is the Gutenberg Styles page.</div>';
}

// Register and enqueue scripts and styles for Gutenberg Styles admin page
function enqueue_gutenberg_styles_scripts($hook) {
    if ('toplevel_page_gutenberg-styles' != $hook) {
        return;
    }

    wp_register_script('makeUpANameHereScript', plugin_dir_url(__FILE__) . 'build/index.js', array('wp-blocks', 'wp-element', 'wp-editor'));
    wp_register_style('makeUpANameHereStyle', plugin_dir_url(__FILE__) . 'build/index.css');

    wp_enqueue_script('makeUpANameHereScript');
    wp_enqueue_style('makeUpANameHereStyle');
}

add_action('admin_enqueue_scripts', 'enqueue_gutenberg_styles_scripts');
