<?php
require_once SCORG_DIR."/plugins/scssphp/scss.inc.php";
//use Leafo\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Compiler;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_load_script')){
	class SCORG_load_script {
		public function __construct(){
			add_action( 'wp', array($this, 'SCORG_load_scripts') );
		}

		public function SCORG_load_scripts(){
			if(is_admin()){
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
						'value' => 'conditions',
						'compare' => '=',
					),
				)
			);
			
			$header_footer_scripts_array = array();
			// only load front-end scripts if ct-builder active
			$scripts_args = SCORG_front_end_only($scripts_args);
			$scripts_query = get_posts($scripts_args);
			//global $wp_query;
			//echo "<pre>"; print_r($scripts_query); "</pre>"; exit;
			$current_post = "";
			$current_taxonomy = "";
			$current_term = "";
			$currentPagePostId = "";
			if(is_single() || is_page() || is_home() || is_front_page()){
				$currentPagePostId = get_queried_object_id();
				$current_post = get_post_type($currentPagePostId);
			} else {
				$queried_object = get_queried_object();
				if(isset($queried_object->taxonomy)){
					if(!isset($queried_object->term_id)){
						$current_taxonomy = $queried_object->taxonomy;
					} else {
						$current_taxonomy = $queried_object->taxonomy;
						$current_term = $queried_object->term_id;
					}
				}
			}

			if($scripts_query){
				$SCORG_dequeue_scripts = "";
				$SCORG_enqueue_scripts = "";
				foreach($scripts_query as $script){
					$scripts_fields = get_post_custom($script->ID);

					$SCORG_page_post = !empty($scripts_fields['SCORG_page_post'][0]) ? $scripts_fields['SCORG_page_post'][0] : '';
					$SCORG_selected_page_post = !empty($scripts_fields['SCORG_selected_page_post']) ? $scripts_fields['SCORG_selected_page_post'] : array();
					$SCORG_specific_post_type = !empty($scripts_fields['SCORG_specific_post_type']) ? $scripts_fields['SCORG_specific_post_type'] : array();
					$SCORG_specific_taxonomy = !empty($scripts_fields['SCORG_specific_taxonomy']) ? $scripts_fields['SCORG_specific_taxonomy'] : array();
					$SCORG_script_schedule = !empty($scripts_fields['SCORG_script_schedule'][0]) ? $scripts_fields['SCORG_script_schedule'][0] : '';
					$SCORG_specific_date = !empty($scripts_fields['SCORG_specific_date'][0]) ? $scripts_fields['SCORG_specific_date'][0] : '';
					$SCORG_specific_date_from = !empty($scripts_fields['SCORG_specific_date_from'][0]) ? $scripts_fields['SCORG_specific_date_from'][0] : '';
					$SCORG_specific_date_to = !empty($scripts_fields['SCORG_specific_date_to'][0]) ? $scripts_fields['SCORG_specific_date_to'][0] : '';
					$SCORG_days = !empty($scripts_fields['SCORG_days']) ? $scripts_fields['SCORG_days'] : array();
					$SCORG_script_time = !empty($scripts_fields['SCORG_script_time'][0]) ? $scripts_fields['SCORG_script_time'][0] : '';
					$SCORG_specific_time_start = !empty($scripts_fields['SCORG_specific_time_start'][0]) ? $scripts_fields['SCORG_specific_time_start'][0] : '';
					$SCORG_specific_time_end = !empty($scripts_fields['SCORG_specific_time_end'][0]) ? $scripts_fields['SCORG_specific_time_end'][0]: '';
					$SCORG_header_script = !empty($scripts_fields['SCORG_header_script'][0]) ? SCORG_is_base64($scripts_fields['SCORG_header_script'][0]) : '';
					$SCORG_footer_script = !empty($scripts_fields['SCORG_footer_script'][0]) ? SCORG_is_base64($scripts_fields['SCORG_footer_script'][0]) : '';
					/* 
						script_schedule = daily 
						script_time = all day
					*/
					if($SCORG_script_schedule == "daily" && $SCORG_script_time == "all_day"){
						$header_footer_scripts_array = $this->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
					}

					/* 
						script_schedule = daily 
						script_time = specific time
					*/
					if($SCORG_script_schedule == "daily" && $SCORG_script_time == "specific_time"){
						$currentTime = current_time("H:i");
						$start_time = date("H:i", strtotime($SCORG_specific_time_start));
						$end_time = date("H:i", strtotime($SCORG_specific_time_end));
						$date1 = strtotime($currentTime);
						$date2 = strtotime($start_time);
						$date3 = strtotime($end_time);
						if ($date1 > $date2 && $date1 < $date3){
							$header_footer_scripts_array = $this->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
						}
					}

					/* 
						script_schedule = specific days 
						script_time = all day
					*/
					if($SCORG_script_schedule == "specific_days" && $SCORG_script_time == "all_day"){
						$currentDay = strtolower(date("l"));
						if(in_array($currentDay, $SCORG_days)){
							$header_footer_scripts_array = $this->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
						}
					}

					/* 
						script_schedule = specific days 
						script_time = specific time
					*/
					if($SCORG_script_schedule == "specific_days" && $SCORG_script_time == "specific_time"){
						$currentTime = current_time("H:i");
						$start_time = date("H:i", strtotime($SCORG_specific_time_start));
						$end_time = date("H:i", strtotime($SCORG_specific_time_end));
						$date1 = strtotime($currentTime);
						$date2 = strtotime($start_time);
						$date3 = strtotime($end_time);
						$currentDay = strtolower(date("l"));
						if(in_array($currentDay, $SCORG_days)){
							if ($date1 > $date2 && $date1 < $date3){
								$header_footer_scripts_array = $this->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
							}
						}
					}

					/* 
						script_schedule = specific date range
						script_time = all day
					*/
					if($SCORG_script_schedule == "specific_date_range" && $SCORG_script_time == "all_day"){
						$specific_date_from = date("Ymd", strtotime($SCORG_specific_date_from));
						$specific_date_to = date("Ymd", strtotime($SCORG_specific_date_to));
						$current_date = date("Ymd");
						if($current_date >= $specific_date_from && $current_date <= $specific_date_to){
							$header_footer_scripts_array = $this->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
						}
					}

					/* 
						script_schedule = specific date range
						script_time = specific time
					*/
					if($SCORG_script_schedule == "specific_date_range" && $SCORG_script_time == "specific_time"){
						$specific_date_from = date("Ymd", strtotime($SCORG_specific_date_from));
						$specific_date_to = date("Ymd", strtotime($SCORG_specific_date_to));
						$current_date = date("Ymd");
						if($current_date >= $specific_date_from && $current_date <= $specific_date_to){
							$currentTime = current_time("H:i");
							$start_time = date("H:i", strtotime($SCORG_specific_time_start));
							$end_time = date("H:i", strtotime($SCORG_specific_time_end));
							$date1 = strtotime($currentTime);
							$date2 = strtotime($start_time);
							$date3 = strtotime($end_time);
							if ($date1 > $date2 && $date1 < $date3){
								$header_footer_scripts_array = $this->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
							}
						}
					}

					/* 
						script_schedule = specific date
						script_time = all day
					*/
					if($SCORG_script_schedule == "specific_date" && $SCORG_script_time == "all_day"){
						$specific_date = date("Ymd", strtotime($SCORG_specific_date));
						$current_date = date("Ymd");
						if($current_date == $specific_date){
							$header_footer_scripts_array = $this->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
						}
					}

					/* 
						script_schedule = specific date
						script_time = specific time
					*/
					if($SCORG_script_schedule == "specific_date" && $SCORG_script_time == "all_day"){
						$specific_date = date("Ymd", strtotime($SCORG_specific_date));
						$current_date = date("Ymd");
						if($current_date == $specific_date){
							$currentTime = current_time("H:i");
							$start_time = date("H:i", strtotime($SCORG_specific_time_start));
							$end_time = date("H:i", strtotime($SCORG_specific_time_end));
							$date1 = strtotime($currentTime);
							$date2 = strtotime($start_time);
							$date3 = strtotime($end_time);
							if ($date1 > $date2 && $date1 < $date3){
								$header_footer_scripts_array = $this->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
							}
						}
					}
				}
			}

			if(!empty($header_footer_scripts_array)){
				if(!empty($header_footer_scripts_array['header_scripts'])){
					$scorg_hsf_wo_file = get_option('scorg_hsf_wo_file');
					$scorg_hsf_wo_file = (!empty($scorg_hsf_wo_file)) ? $scorg_hsf_wo_file : 999999999;
					add_action( 'wp_head', function() use ( $header_footer_scripts_array ){
						foreach($header_footer_scripts_array['header_scripts'] as $header_script){
							if(!empty($header_script)){
								echo do_shortcode($header_script);
							}		
						}
					}, $scorg_hsf_wo_file, 1 );	
				}

				if(!empty($header_footer_scripts_array['footer_scripts'])){
					$scorg_fsf_wo_file = get_option('scorg_fsf_wo_file');
					$scorg_fsf_wo_file = (!empty($scorg_fsf_wo_file)) ? $scorg_fsf_wo_file : 101;
					add_action( 'wp_footer', function() use ( $header_footer_scripts_array ){
						foreach($header_footer_scripts_array['footer_scripts'] as $footer_script){
							if(!empty($footer_script)){
								echo do_shortcode($footer_script);
							}
						}
					}, $scorg_fsf_wo_file );	
				}
				/*echo "<pre>"; print_r($header_footer_scripts_array); "</pre>";
				exit;*/
			}
		}

		public function include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script_id, $current_term){
			$conditions_meet = $this->SCORG_page_post_display_option($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $current_term);

			$compiled_code = $this->return_compiled_header_footer_code($script_id, $SCORG_header_script, $SCORG_footer_script);

			if($conditions_meet){
				if(isset($compiled_code['SCORG_header_script'])){
					$header_footer_scripts_array['header_scripts'][] = $compiled_code['SCORG_header_script'];
				}
			}
			if($conditions_meet && !empty($SCORG_footer_script)){
				if(isset($compiled_code['SCORG_footer_script'])){
					$header_footer_scripts_array['footer_scripts'][] = $compiled_code['SCORG_footer_script'];
				}
			}

			if($conditions_meet && !empty($SCORG_dequeue_scripts)){
				$header_footer_scripts_array['dequeue'][] = $SCORG_dequeue_scripts;
			}

			if($conditions_meet && !empty($SCORG_enqueue_scripts)){
				$header_footer_scripts_array['enqueue'][] = $SCORG_enqueue_scripts;
			}

			return $header_footer_scripts_array;
		}

		public function return_compiled_header_footer_code($script_id, $SCORG_header_script, $SCORG_footer_script){
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
						$compiled_code['SCORG_header_script'] = $SCORG_header_script;
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
						$compiled_code['SCORG_footer_script'] = $SCORG_footer_script;
				}
			}

			return $compiled_code;
		}

		public function SCORG_page_post_display_option($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $current_term){
			$conditions_meet = false;
			$check_exclude = (isset($scripts_fields['SCORG_exclude_script']) && $scripts_fields['SCORG_exclude_script'][0]) ? true : false;
			$SCORG_exclude_page_post = !empty($scripts_fields['SCORG_exclude_page_post']) ? $scripts_fields['SCORG_exclude_page_post'] : array();
			$SCORG_exclude_post_type = !empty($scripts_fields['SCORG_exclude_post_type']) ? $scripts_fields['SCORG_exclude_post_type'] : array();
			$SCORG_exclude_terms = !empty($scripts_fields['SCORG_exclude_terms']) ? $scripts_fields['SCORG_exclude_terms'] : array();
			$SCORG_exclude_taxonomies = !empty($scripts_fields['SCORG_exclude_taxonomies']) ? $scripts_fields['SCORG_exclude_taxonomies'] : array();
			if($SCORG_page_post == "all"){
				if($check_exclude){
					if(!in_array($currentPagePostId, $SCORG_exclude_page_post)
					&& !in_array($current_taxonomy, $SCORG_exclude_taxonomies)
					&& !in_array($current_post, $SCORG_exclude_post_type)
					&& !in_array($current_term, $SCORG_exclude_terms)){
						$conditions_meet = true;
					}
				} else {
					$conditions_meet = true;
				}
			} else if($SCORG_page_post == "specific_page_post"){
				if(in_array($currentPagePostId, $SCORG_selected_page_post)){
					$conditions_meet = true;
				}
			} else if($SCORG_page_post == "specific_post_type"){
				if(in_array($current_post, $SCORG_specific_post_type)){
					if($check_exclude){
						if(!in_array($currentPagePostId, $SCORG_exclude_page_post)){
							$conditions_meet = true;
						}
					} else {
						$conditions_meet = true;
					}
				}
			} else if($SCORG_page_post == "custom"){
				$condition = $scripts_fields['SCORG_custom'][0];
				if(eval("return $condition;")){
					$conditions_meet = true;
				}
			} else {
				if(in_array($current_taxonomy, $SCORG_specific_taxonomy)){
					if($check_exclude){
						if(!in_array($current_term, $SCORG_exclude_terms)){
							$conditions_meet = true;
						}
					} else {
						$conditions_meet = true;
					}
				}
			}
			
			return $conditions_meet;
		}

		public function SCORG_dequeue_enqueue_scripts($header_footer_scripts_array){
			//echo "<pre>"; print_r($header_footer_scripts_array); "</pre>"; exit;
			global $wpdb;
			$swiss_knife_scripts = $wpdb->prefix . 'swiss_knife_scripts';
			$script_ids = array();
			if(!empty($header_footer_scripts_array['dequeue'])){
				$script_ids = array_merge($script_ids, $header_footer_scripts_array['dequeue']);
			}

			if(!empty($header_footer_scripts_array['enqueue'])){
				$script_ids = array_merge($script_ids, $header_footer_scripts_array['enqueue']);
			}

			if(!empty($script_ids)){
				$all_script_ids = array();
				foreach($script_ids as $key => $val){
					$all_script_ids = array_merge($all_script_ids, $val);
				}
				
				if(!empty($all_script_ids)){
					$all_script_ids = array_unique($all_script_ids);
					$all_script_ids = join("','",$all_script_ids);
					$get_scripts = $wpdb->get_results( "SELECT script_name, script_type, script_include_type FROM $swiss_knife_scripts WHERE id IN('$all_script_ids') ORDER BY script_order ASC" );
					if(!empty($get_scripts)){
						$script_name = "";
						$SCORG_scripts_manager = new SCORG_scripts_manager();
						foreach($get_scripts as $script){
							$script_name = $SCORG_scripts_manager->SCORG_replace_name_with_slug($script->script_name);
							if($script->script_type == "js" && $script->script_include_type == "register"){
								wp_enqueue_script($script_name);
							} else if($script->script_type == "js" && $script->script_include_type == "enqueue"){
								wp_dequeue_script($script_name);
							} else if($script->script_type == "css" && $script->script_include_type == "enqueue"){
								wp_dequeue_style($script_name);
							} else {
								wp_enqueue_style($script_name);
							}
						}
					}
				}
			}
		}
	}
	new SCORG_load_script();
}