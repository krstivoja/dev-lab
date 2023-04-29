<?php

function register_tasks_post_type() {
	$labels = array(
	  'name' => _x( 'Tasks', 'Post Type General Name', 'textdomain' ),
	  'singular_name' => _x( 'Task', 'Post Type Singular Name', 'textdomain' ),
	  'menu_name' => __( 'Tasks', 'textdomain' ),
	  'all_items' => __( 'All Tasks', 'textdomain' ),
	  'view_item' => __( 'View Task', 'textdomain' ),
	  'add_new_item' => __( 'Add New Task', 'textdomain' ),
	  'add_new' => __( 'Add New', 'textdomain' ),
	  'edit_item' => __( 'Edit Task', 'textdomain' ),
	  'update_item' => __( 'Update Task', 'textdomain' ),
	  'search_items' => __( 'Search Task', 'textdomain' ),
	  'not_found' => __( 'Not Found', 'textdomain' ),
	  'not_found_in_trash' => __( 'Not found in Trash', 'textdomain' )
	);
	
	$args = array(
	  'label' => __( 'Tasks', 'textdomain' ),
	  'description' => __( 'Tasks', 'textdomain' ),
	  'labels' => $labels,
	  'supports' => array( 'title', 'editor', 'thumbnail' ),
	  'taxonomies' => array( 'progress' ),
	  'hierarchical' => false,
	  'public' => true,
	  'show_ui' => true,
	  'show_in_menu' => true,
	  'show_in_nav_menus' => true,
	  'show_in_admin_bar' => true,
	  'menu_position' => 5,
	  'menu_icon' => 'dashicons-clipboard',
	  'can_export' => true,
	  'has_archive' => true,
	  'exclude_from_search' => false,
	  'publicly_queryable' => true,
	  'capability_type' => 'post',
	  'show_in_rest' => true // This enables the post type to be used with the WordPress REST API
	);
	
	register_post_type( 'tasks', $args );
  }
  add_action( 'init', 'register_tasks_post_type', 0 );
  

// Register Progress taxonomy
function register_progress_taxonomy() {
	$labels = array(
	  'name'                       => _x( 'Progress', 'taxonomy general name', 'textdomain' ),
	  'singular_name'              => _x( 'Progress', 'taxonomy singular name', 'textdomain' ),
	  'search_items'               => __( 'Search Progress', 'textdomain' ),
	  'popular_items'              => __( 'Popular Progress', 'textdomain' ),
	  'all_items'                  => __( 'All Progress', 'textdomain' ),
	  'parent_item'                => __( 'Parent Progress', 'textdomain' ),
	  'parent_item_colon'          => __( 'Parent Progress:', 'textdomain' ),
	  'edit_item'                  => __( 'Edit Progress', 'textdomain' ),
	  'view_item'                  => __( 'View Progress', 'textdomain' ),
	  'update_item'                => __( 'Update Progress', 'textdomain' ),
	  'add_new_item'               => __( 'Add New Progress', 'textdomain' ),
	  'new_item_name'              => __( 'New Progress Name', 'textdomain' ),
	  'separate_items_with_commas' => __( 'Separate progress with commas', 'textdomain' ),
	  'add_or_remove_items'        => __( 'Add or remove progress', 'textdomain' ),
	  'choose_from_most_used'      => __( 'Choose from the most used progress', 'textdomain' ),
	  'not_found'                  => __( 'No progress found.', 'textdomain' ),
	  'menu_name'                  => __( 'Progress', 'textdomain' ),
	);
   
	$args = array(
		'public' => true,
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'show_admin_column' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'progress' ),
		'show_in_rest' => true // This enables the taxonomy to be used with the WordPress REST API
	  );
	  register_taxonomy( 'progress', 'tasks', $args );
	}
	add_action( 'init', 'register_progress_taxonomy', 0 );
	

		