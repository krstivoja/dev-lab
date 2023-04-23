<?php

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
