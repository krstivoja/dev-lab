<?php

function cptui_register_my_taxes_progress() {

	/**
	 * Taxonomy: Progresses.
	 */

	$labels = [
		"name" => esc_html__( "Progresses", "twentytwentythree" ),
		"singular_name" => esc_html__( "Progress", "twentytwentythree" ),
	];

	
	$args = [
		"label" => esc_html__( "Progresses", "twentytwentythree" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'progress', 'with_front' => true,  'hierarchical' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "progress",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => false,
		"sort" => true,
		"show_in_graphql" => false,
	];
	register_taxonomy( "progress", [ "tasks" ], $args );
}
add_action( 'init', 'cptui_register_my_taxes_progress' );

?>