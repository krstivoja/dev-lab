<?php
/**
 * Plugin Name: Gutenberg Styles
 * Description: Displays a list of registered Gutenberg blocks sorted by groups.
 * Version: 0.0.1
 */

function gutenberg_block_list_shortcode($atts) {
    // Retrieve all registered block types
    $block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();

    // Organize blocks by groups
    $blocks_by_group = array();

    foreach ($block_types as $block_type) {
        $group = $block_type->category ?? 'Uncategorized';
        $blocks_by_group[$group][] = $block_type;
    }

    // Sort blocks by group
    ksort($blocks_by_group);

    // Output the block list
    $output = '';

    foreach ($blocks_by_group as $group => $blocks) {
        $output .= '<h3>' . esc_html($group) . '</h3>';

        foreach ($blocks as $block) {
            $block_name = $block->title ?? $block->name; // Use title if available, otherwise fallback to name
            $output .= '<p>' . esc_html($block_name) . '</p>';
        }
    }

    return $output;
}
add_shortcode('gutenberg_block_list', 'gutenberg_block_list_shortcode');
