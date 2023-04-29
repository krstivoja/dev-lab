<?php

/**
 * Plugin Name: Tasks manager
 * Description: test
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://www.example.com
 * License: GPL2
 */

include_once 'inc/cpt.php';
include_once 'inc/taxonomy.php';

add_shortcode("custom_tasks_posts", "custom_tasks_posts_shortcode");

function custom_tasks_posts_shortcode()
{
    ob_start();

    $terms = get_terms([
        "taxonomy" => "progress",
        "hide_empty" => false,
    ]);

    if (is_array($terms)) {
        foreach ($terms as $term) {
            if (is_object($term)) {  // Ensure $term is an object
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
    }

    wp_reset_postdata();
    return ob_get_clean();
}


// function custom_enqueue_scripts() {
//     // Check if the current user is an administrator
//     if ( current_user_can( 'administrator' ) ) {
//       // Enqueue the script in the footer
//       wp_enqueue_script( 'jq', 'https://code.jquery.com/jquery-3.6.4.min.js', array(), '1.0.0', true );
//       wp_enqueue_script( 'sortable', 'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js', array(), '1.0.0', true );
//       wp_enqueue_script( 'custom-script', plugin_dir_url( __FILE__ ) . 'js/manager-script.js', array(), '1.0.0', true );
//     }
//   }
//   add_action( 'wp_footer', 'custom_enqueue_scripts' );

function custom_enqueue_scripts() {
    // Enqueue the custom stylesheet
    wp_enqueue_style( 'custom-style', plugin_dir_url( __FILE__ ) . 'css/manager-style.css', array(), '1.0.0', 'all' );
    
    // Check if the current user is an administrator
    if ( current_user_can( 'administrator' ) ) {
      // Enqueue the scripts and styles in the footer
      wp_enqueue_script( 'wp-api' );
      wp_enqueue_script( 'jq', 'https://code.jquery.com/jquery-3.6.4.min.js', array('wp-api'), '1.0.0', true );
      wp_enqueue_script( 'sortable', 'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js', array('wp-api'), '1.0.0', true );
      wp_enqueue_script( 'custom-script', plugin_dir_url( __FILE__ ) . 'js/manager-script.js', array('wp-api'), '1.0.0', true );

    }
  }
  add_action( 'wp_enqueue_scripts', 'custom_enqueue_scripts' );
