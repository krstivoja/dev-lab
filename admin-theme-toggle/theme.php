<?php

/**
 * Plugin Name: Theme Toggle
 * Description: A plugin that allows users to toggle between a dark and light theme and remembers their preference using AJAX without reloading the page.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://www.example.com
 * License: GPL2
 */

// Register menu page for the plugin
function theme_toggle_register_menu_page()
{
    add_menu_page(
        "Theme Toggle Settings",
        "Theme Toggle",
        "manage_options",
        "theme_toggle_settings",
        "theme_toggle_render_settings",
        "dashicons-art",
        90
    );
}
add_action("admin_menu", "theme_toggle_register_menu_page");

// Render plugin settings page
function theme_toggle_render_settings()
{
    ?>
    <div class="wrap">
        <h1>Theme Toggle Settings</h1>
        <p>Click the button below to toggle between a light and dark theme.</p>
        <button id="theme-toggle-button">Toggle Dark Mode</button>
    </div>

    <style>
        .dark{
            background: red;
        }
    </style>

    <script type="text/javascript">
        var toggleButton = document.getElementById('theme-toggle-button');
        var body = document.querySelector('body');
        var darkMode = <?php echo get_user_meta(
            get_current_user_id(),
            "theme_toggle_dark_mode",
            true
        ) == 1
            ? "true"
            : "false"; ?>;

        if (darkMode) {
            body.classList.add('dark');
        }

        toggleButton.addEventListener('click', function() {
            var xhr = new XMLHttpRequest();
            var newDarkMode = !darkMode;

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    body.classList.toggle('dark');
                    darkMode = newDarkMode;
                }
            };

            xhr.open('POST', '<?php echo admin_url("admin-ajax.php"); ?>');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('action=theme_toggle_update_settings&dark_mode=' + (newDarkMode ? '1' : '0'));
        });
    </script>
    
    <?php
}

// Update user preference via AJAX
function theme_toggle_update_settings()
{
    $dark_mode = isset($_POST["dark_mode"]) ? $_POST["dark_mode"] : "0";
    update_user_meta(
        get_current_user_id(),
        "theme_toggle_dark_mode",
        $dark_mode
    );
    die();
}
add_action(
    "wp_ajax_theme_toggle_update_settings",
    "theme_toggle_update_settings"
);

// Add dark theme

// Add dark theme class to login body
function theme_toggle_login_body_class($classes)
{
    $dark_mode = get_user_meta(
        get_current_user_id(),
        "theme_toggle_dark_mode",
        true
    );
    if ($dark_mode) {
        $classes .= "dark";
    }
    return $classes;
}
add_filter("login_body_class", "theme_toggle_login_body_class");

// Add dark theme class to front-end body
function theme_toggle_front_end_body_class($classes)
{
    $dark_mode = get_user_meta(
        get_current_user_id(),
        "theme_toggle_dark_mode",
        true
    );
    if ($dark_mode) {
        $classes .= "dark";
    }
    return $classes;
}
add_filter("body_class", "theme_toggle_front_end_body_class");
