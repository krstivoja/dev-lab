<?php

/**
 * Plugin Name: Tasks manager
 * Description: test
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://www.example.com
 * License: GPL2
 */


include 'register.php';

function enqueue_sortable_scripts() {
    if (current_user_can('administrator')) {
        wp_enqueue_script('sortablejs', 'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js', array(), null, true);
        wp_enqueue_script('tasks-sortable', plugin_dir_url(__FILE__) . 'js/tasks-sortable.js', array('jquery', 'sortablejs'), '1.0.0', true);
        wp_localize_script('tasks-sortable', 'ajaxObject', array('ajaxUrl' => admin_url('admin-ajax.php')));
    }

    wp_enqueue_style('manager-style', plugin_dir_url(__FILE__) . 'css/manager-style.css'); // Added this line to enqueue the CSS file

}
add_action('wp_enqueue_scripts', 'enqueue_sortable_scripts');



function tasks_shortcode($atts) {
    $output = '';
    $terms = get_terms(array('taxonomy' => 'progress', 'hide_empty' => false));

    $output .= '<div class="tasks-shortcode">';
    foreach ($terms as $term) {
        $output .= '<div class="tasks-group-wrap">'; // Added this line
        $output .= '<h3>' . $term->name . '</h3>';
        $task_args = array(
            'post_type' => 'tasks',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'tax_query' => array(
                array(
                    'taxonomy' => 'progress',
                    'field' => 'term_id',
                    'terms' => $term->term_id
                )
            )
        );

        $tasks = new WP_Query($task_args);

        $output .= '<div class="tasks-list" id="' . $term->slug . '" data-term-id="' . $term->term_id . '">';
        if ($tasks->have_posts()) {
            while ($tasks->have_posts()) {
                $tasks->the_post();
                $output .= '<div class="task-item" data-id="' . get_the_ID() . '">' . get_the_title() . '</div>';
            }
        }
        $output .= '</div>';

        $output .= '</div>'; // Added this line
        wp_reset_postdata();
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('tasks_progress', 'tasks_shortcode');




// AJAX function for updating tasks order
function update_tasks_order() {
    if (current_user_can('administrator') && isset($_POST['tasks_order'])) {
        $tasks_order = $_POST['tasks_order'];
        foreach ($tasks_order as $order => $task_id) {
            wp_update_post(array('ID' => $task_id, 'menu_order' => $order));
        }
        wp_send_json_success('Tasks order updated.');
    } else {
        wp_send_json_error('Error updating tasks order.');
    }
}
add_action('wp_ajax_update_tasks_order', 'update_tasks_order');



// AJAX function for updating tasks taxonomy term
function update_tasks_taxonomy() {
    if (current_user_can('administrator') && isset($_POST['task_id']) && isset($_POST['term_id'])) {
        $task_id = intval($_POST['task_id']);
        $term_id = intval($_POST['term_id']);

        $term = get_term($term_id, 'progress');
        if ($term) {
            wp_set_object_terms($task_id, $term->term_id, 'progress');
            wp_send_json_success('Task taxonomy term updated. Task ID: ' . $task_id . ' | Term ID: ' . $term_id);
        } else {
            wp_send_json_error('Error updating task taxonomy term. Task ID: ' . $task_id . ' | Term ID: ' . $term_id);
        }
    } else {
        wp_send_json_error('Error updating task taxonomy term. Insufficient data or permissions.');
    }
}
add_action('wp_ajax_update_tasks_taxonomy', 'update_tasks_taxonomy');



