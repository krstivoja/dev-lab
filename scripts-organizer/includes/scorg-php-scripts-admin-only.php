<?php
/* php scripts */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_admin_only')){
	class SCORG_admin_only {
		public function __construct(){
			//add_action( 'admin_init', array($this, 'SCORG_admin_only_scripts') );
			$this->SCORG_admin_only_scripts();
		}

		public function SCORG_admin_only_scripts(){
			$scripts_args = array(
				'post_type'			=> 'scorg',
				'posts_per_page' 	=> -1,
				'orderby' => 'menu_order',
				'order' => 'ASC',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'SCORG_script_type',
						'value' => array('php'),
						'compare' => 'IN',
					),
					array(
						'key' => 'SCORG_enable_script',
						'value' => 1,
						'compare' => '=',
					),
					array(
						'key' => 'SCORG_trigger_location',
						'value' => 'admin_only',
						'compare' => '=',
					),
				)
			);

			$scripts_query = get_posts($scripts_args);
			//echo "<pre>"; print_r($scripts_query); "</pre>"; exit;
			if($scripts_query){
				foreach($scripts_query as $script){
					$script_id = $script->ID;
					$SCORG_custom = get_post_meta($script->ID, 'SCORG_custom', true);
					$this->SCORG_php_admin_only_display_option($script_id, $SCORG_custom);
				}
			}
		}

		public function SCORG_php_admin_only_display_option($script_id, $SCORG_custom){
			$script_file_url = SCORG_UPLOADS_DIR.'/'.$script_id.'.php';
			if(!empty($SCORG_custom)){
				$condition = $SCORG_custom;
				if(eval("return $condition;")){
					$this->SCORG_php_admin_only_load_file($script_file_url);
				}
			} else {
				$this->SCORG_php_admin_only_load_file($script_file_url);
			}
		}

		public function SCORG_php_admin_only_load_file($script_file_url){
			if(file_exists($script_file_url)){
				$SCORG_can_include_php = SCORG_can_include_php();
				if($SCORG_can_include_php == "no"){
					require_once $script_file_url;
				}
			}
		}
	}
}
if(is_admin()){
	new SCORG_admin_only();
}