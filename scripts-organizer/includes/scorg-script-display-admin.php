<?php
require_once SCORG_DIR."/plugins/scssphp/scss.inc.php";
//use Leafo\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Compiler;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_load_script_admin')){
	class SCORG_load_script_admin {
		public function __construct(){
			add_action( 'admin_init', array($this, 'SCORG_load_script_admins') );
		}

		public function SCORG_load_script_admins(){
			if(!is_admin()){
				return;
			}
			$scripts_args = array(
				'post_type'			=> 'scorg',
				'posts_per_page' 	=> -1,
				'orderby' => 'menu_order',
				'order' => 'ASC',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'SCORG_script_type',
						'value' => array('header', 'footer'),
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
			
			$header_footer_scripts_array = array();
			// only load front-end scripts if ct-builder active
			$scripts_query = get_posts($scripts_args);
			if($scripts_query){
				foreach($scripts_query as $script){
					$scripts_fields = get_post_custom($script->ID);
                    $header_footer_scripts_array = $this->include_in_header_footer_scripts($scripts_fields, $script->ID, $header_footer_scripts_array);
				}
			}

			if(!empty($header_footer_scripts_array)){
				if(!empty($header_footer_scripts_array['header_scripts'])){
					$scorg_hsb_wo_file = get_option('scorg_hsb_wo_file');
					$scorg_hsb_wo_file = (!empty($scorg_hsb_wo_file)) ? $scorg_hsb_wo_file : 101;
					add_action( 'admin_head', function() use ( $header_footer_scripts_array ){
						foreach($header_footer_scripts_array['header_scripts'] as $header_script){
							if(!empty($header_script)){
								echo do_shortcode($header_script);
							}		
						}
					}, $scorg_hsb_wo_file, 1 );	
				}

				if(!empty($header_footer_scripts_array['footer_scripts'])){
					$scorg_fsb_wo_file = get_option('scorg_fsb_wo_file');
					$scorg_fsb_wo_file = (!empty($scorg_fsb_wo_file)) ? $scorg_fsb_wo_file : 101;
					add_action( 'admin_footer', function() use ( $header_footer_scripts_array ){
						foreach($header_footer_scripts_array['footer_scripts'] as $footer_script){
							if(!empty($footer_script)){
								echo do_shortcode($footer_script);
							}
						}
					}, $scorg_fsb_wo_file );	
				}
				/*echo "<pre>"; print_r($header_footer_scripts_array); "</pre>";
				exit;*/
			}
		}

		public function include_in_header_footer_scripts($scripts_fields, $script_id, $header_footer_scripts_array){
			$compiled_code = $this->return_compiled_header_footer_code($script_id, $scripts_fields);
			if(isset($scripts_fields['SCORG_header_script'])){
				if(isset($compiled_code['SCORG_header_script'])){
					$header_footer_scripts_array['header_scripts'][] = $compiled_code['SCORG_header_script'];
				}
			}
			if(isset($scripts_fields['SCORG_footer_script'])){
				if(isset($compiled_code['SCORG_footer_script'])){
					$header_footer_scripts_array['footer_scripts'][] = $compiled_code['SCORG_footer_script'];
				}
			}

			return $header_footer_scripts_array;
		}

		public function return_compiled_header_footer_code($script_id, $scripts_fields){
			$compiled_code = array();
			$SCORG_script_type = get_post_meta($script_id, 'SCORG_script_type');
			$SCORG_header_file = get_post_meta($script_id, 'SCORG_header_file', true);
			if(in_array('header', $SCORG_script_type) && empty($SCORG_header_file)){
				$header_mode = get_post_meta($script_id, 'SCORG_header_mode', true);
				switch($header_mode){
					case 'scss';
						SCORG_create_SCSS_file_on_load($script_id, "header");
						$compiled_code['SCORG_header_script'] = '<style>'.SCORG_process_colors(SCORG_fetch_or_load_scripts($header_mode, $script_id,  "header-compiled", true)).'</style>';
						break;
					case 'css';
						$compiled_code['SCORG_header_script'] = '<style>'.SCORG_process_colors(SCORG_fetch_or_load_scripts($header_mode, $script_id,  "header", true)).'</style>';
						break;
					case 'javascript';
						$compiled_code['SCORG_header_script'] = '<script>'.SCORG_fetch_or_load_scripts($header_mode, $script_id,  "header", true).'</script>';
						break;
					default:
						$compiled_code['SCORG_header_script'] = $scripts_fields['SCORG_header_script'];
				}
			}

			$SCORG_footer_file = get_post_meta($script_id, 'SCORG_footer_file', true);
			if(in_array('footer', $SCORG_script_type) && empty($SCORG_footer_file)){
				$footer_mode = get_post_meta($script_id, 'SCORG_footer_mode', true);
				switch($footer_mode){
					case 'scss';
						SCORG_create_SCSS_file_on_load($script_id, "header");
						$compiled_code['SCORG_footer_script'] = '<style>'.SCORG_process_colors(SCORG_fetch_or_load_scripts($footer_mode, $script_id, "footer-compiled", true)).'</style>';
						break;
					case 'css';
						$compiled_code['SCORG_footer_script'] = '<style>'.SCORG_process_colors(SCORG_fetch_or_load_scripts($footer_mode, $script_id, "footer", true)).'</style>';
						break;
					case 'javascript';
						$compiled_code['SCORG_footer_script'] = '<script>'.SCORG_fetch_or_load_scripts($footer_mode, $script_id, "footer", true).'</script>';
						break;
					default:
						$compiled_code['SCORG_footer_script'] = $scripts_fields['SCORG_footer_script'];
				}
			}

			return $compiled_code;
		}
	}
	new SCORG_load_script_admin();
}