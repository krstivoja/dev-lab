<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_everywhere')){
	class SCORG_everywhere {
		public function __construct(){
			$this->SCORG_everywhere_scripts();
		}

		public function SCORG_everywhere_scripts(){
			$scripts_args = array(
				'post_type'			=> 'scorg',
				'posts_per_page' 	=> -1,
				'orderby' => 'menu_order',
				'order' => 'ASC',
				'meta_query' => array(
					'relation' => 'AND',
					/* array(
						'key' => 'SCORG_script_type',
						'value' => array('php'),
						'compare' => 'IN',
					), */
					array(
						'key' => 'SCORG_enable_script',
						'value' => 1,
						'compare' => '=',
					),
					array(
						'key' => 'SCORG_trigger_location',
						'value' => 'everywhere',
						'compare' => '=',
					),
				)
			);

			// only load front-end scripts if ct-builder active
			$scripts_args = SCORG_front_end_only($scripts_args);
			$scripts_query = get_posts($scripts_args);
			//echo "<pre>"; print_r($scripts_query); "</pre>"; exit;
			if($scripts_query){
				foreach($scripts_query as $script){
					$script_id = $script->ID;
					$SCORG_custom = get_post_meta($script->ID, 'SCORG_custom', true);
					$SCORG_action_hook = get_post_meta($script->ID, 'SCORG_action_hook', true);
					$SCORG_priority = get_post_meta($script->ID, 'SCORG_priority', true);
					$SCORG_priority = (empty($SCORG_priority)) ? 1 : (int) $SCORG_priority;
					$this->SCORG_php_everywhere_display_option($script_id, $SCORG_custom, $SCORG_action_hook, $SCORG_priority);
				}
			}
		}

		public function SCORG_php_everywhere_display_option($script_id, $SCORG_custom, $SCORG_action_hook, $SCORG_priority){
			$script_file_url = SCORG_UPLOADS_DIR.'/'.$script_id.'.php';
			if(!empty($SCORG_custom)){
				$condition = $SCORG_custom;
				if(eval("return $condition;")){
					$this->SCORG_php_everywhere_only_load_file($script_file_url, $SCORG_action_hook, $SCORG_priority);
				}
			} else {
				$this->SCORG_php_everywhere_only_load_file($script_file_url, $SCORG_action_hook, $SCORG_priority);
			}
		}

		public function SCORG_php_everywhere_only_load_file($script_file_url, $SCORG_action_hook, $SCORG_priority){
			if(file_exists($script_file_url)){
				$SCORG_can_include_php = SCORG_can_include_php();
				if($SCORG_can_include_php == "no"){
					if($SCORG_action_hook != ""){
						$arrContextOptions = array(
							"ssl" => array(
								"verify_peer" => false,
								"verify_peer_name" => false,
							),
						);
						add_action($SCORG_action_hook, function() use ( $script_file_url ){
							require_once $script_file_url;
						}, $SCORG_priority );
					} else {
						require_once $script_file_url;
					}
				}
			}
		}
	}
	new SCORG_everywhere();
}