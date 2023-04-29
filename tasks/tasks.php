<?php

/**
 * Plugin Name: Tasks
 * Description: test
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://www.example.com
 * License: GPL2
 */

 add_shortcode("custom_tasks_posts", "custom_tasks_posts_shortcode");

 function custom_tasks_posts_shortcode()
 {
     ob_start();
 
     $terms = get_terms([
         "taxonomy" => "progress",
         "hide_empty" => false,
     ]);
 
     if ($terms) {
         foreach ($terms as $term) {
             $args = [
                 "post_type" => "tasks",
                 "posts_per_page" => -1,
                 "tax_query" => [
                     [
                         "taxonomy" => "progress",
                         "field" => "slug",
                         "terms" => $term->slug,
                     ],
                 ],
                 "orderby" => "menu_order",
                 "order" => "ASC",
             ];
 
             $query = new WP_Query($args);
 
             if ($query->have_posts()) {
                 echo "<div class='tasks-group' data-term_slug='$term->slug'>";
 
                 echo "<h2 class='tasks-group-title'>$term->name</h2>";
 
                 echo "<div class='tasks-list'>";
 
                 while ($query->have_posts()) {
                     $query->the_post();
 
                     echo "<div class='task-item' data-post_id='" .
                         get_the_ID() .
                         "'>";
                     the_title();
                     echo "</div>";
                 }
 
                 echo "</div>"; // End of .tasks-list
 
                 echo "</div>"; // End of .tasks-group
             }
         }
     }
 
     wp_reset_postdata();
     return ob_get_clean();
 }
 
 function custom_tasks_posts_enqueue_scripts() {
    // Enqueue SortableJS library
    wp_enqueue_script( 'sortablejs', 'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js', [], '', true );

    // Enqueue custom-tasks-posts.js file
    wp_enqueue_script( 'custom-tasks-posts', plugins_url( '/js/custom-tasks-posts.js', __FILE__ ), array( 'jquery', 'sortablejs' ), '', true );

    // Pass 'ajaxurl' to JavaScript via wp_localize_script
    wp_localize_script( 'custom-tasks-posts', 'custom_tasks_posts_data', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'custom_tasks_posts_enqueue_scripts' );

// AJAX handler
add_action( 'wp_ajax_update_post_term', 'update_post_term_callback' );
add_action( 'wp_ajax_nopriv_update_post_term', 'update_post_term_callback' );

function update_post_term_callback() {
    $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
    $term_slug = isset( $_POST['term_slug'] ) ? sanitize_text_field( $_POST['term_slug'] ) : '';

    if($post_id && $term_slug) {
        wp_set_object_terms($post_id, $term_slug, 'progress');
        wp_send_json_success('Post term updated.');
    }
    wp_send_json_error('Post term update failed.');
}
