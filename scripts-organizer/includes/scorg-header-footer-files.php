<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_header_footer_files')){
	class SCORG_header_footer_files {
		public function __construct(){
			$scorg_f_enqueue = get_option('scorg_f_enqueue');
			$scorg_f_enqueue = (!empty($scorg_f_enqueue)) ? $scorg_f_enqueue : 1000;
			add_action( 'wp_enqueue_scripts', array($this, 'load_scripts'), $scorg_f_enqueue );
		}

		public function load_scripts(){
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
			
			// only load front-end scripts if ct-builder active
			$scripts_args = SCORG_front_end_only($scripts_args);
			$scripts_query = get_posts($scripts_args);
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
					$SCORG_specific_time_end = !empty($scripts_fields['SCORG_specific_time_end'][0]) ? $scripts_fields['SCORG_specific_time_end'][0] : '';
					$SCORG_dequeue_scripts = !empty($scripts_fields['SCORG_dequeue_scripts']) ? $scripts_fields['SCORG_dequeue_scripts'] : array();
					$SCORG_enqueue_scripts = !empty($scripts_fields['SCORG_enqueue_scripts']) ? $scripts_fields['SCORG_enqueue_scripts'] : array();
					/* 
						script_schedule = daily 
						script_time = all day
					*/
					if($SCORG_script_schedule == "daily" && $SCORG_script_time == "all_day"){
						$this->include_in_header_footer_scripts($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script->ID, $current_term);
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
							$this->include_in_header_footer_scripts($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script->ID, $current_term);
						}
					}

					/* 
						script_schedule = specific days 
						script_time = all day
					*/
					if($SCORG_script_schedule == "specific_days" && $SCORG_script_time == "all_day"){
						$currentDay = strtolower(date("l"));
						if(in_array($currentDay, $SCORG_days)){
							$this->include_in_header_footer_scripts($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script->ID, $current_term);
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
								$this->include_in_header_footer_scripts($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script->ID, $current_term);
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
							$this->include_in_header_footer_scripts($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script->ID, $current_term);
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
								$this->include_in_header_footer_scripts($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script->ID, $current_term);
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
							$this->include_in_header_footer_scripts($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script->ID, $current_term);
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
								$this->include_in_header_footer_scripts($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script->ID, $current_term);
							}
						}
					}
				}
			}
		}

		public function include_in_header_footer_scripts($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script_id, $current_term){
			
			$conditions_meet = $this->page_post_display_option($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $current_term);
			if($conditions_meet){
				$file_url = "";
				$SCORG_script_type = get_post_meta($script_id, 'SCORG_script_type');
				$SCORG_header_file = get_post_meta($script_id, 'SCORG_header_file', true);
				if(in_array('header', $SCORG_script_type) && !empty($SCORG_header_file)){
					$header_mode = get_post_meta($script_id, 'SCORG_header_mode', true);
					switch($header_mode){
						case 'scss';
							SCORG_create_SCSS_file_on_load($script_id, "header");
							$file_url = SCORG_fetch_or_load_scripts($header_mode, $script_id, "header-compiled", false);
							$this->enqueue_file($file_url, $script_id, "style", "header", false);
							break;
						case 'css';
							$file_url = SCORG_fetch_or_load_scripts($header_mode, $script_id, "header", false);
							$this->enqueue_file($file_url, $script_id, "style", "header", false);
							break;
						case 'javascript';
							$file_url = SCORG_fetch_or_load_scripts($header_mode, $script_id, "header", false);
							$this->enqueue_file($file_url, $script_id, "script", "header", false);
							break;
					}
				}

				$SCORG_footer_file = get_post_meta($script_id, 'SCORG_footer_file', true);
				if(in_array('footer', $SCORG_script_type) && !empty($SCORG_footer_file)){
					$footer_mode = get_post_meta($script_id, 'SCORG_footer_mode', true);
					switch($footer_mode){
						case 'scss';
							SCORG_create_SCSS_file_on_load($script_id, "header");
							$file_url = SCORG_fetch_or_load_scripts($footer_mode, $script_id, "footer-compiled", false);
							$this->enqueue_file($file_url, $script_id, "style", "footer", true);
							break;
						case 'css';
							$file_url = SCORG_fetch_or_load_scripts($footer_mode, $script_id, "footer", false);
							$this->enqueue_file($file_url, $script_id, "style", "footer", true);
							break;
						case 'javascript';
							$file_url = SCORG_fetch_or_load_scripts($footer_mode, $script_id, "footer", false);
							$this->enqueue_file($file_url, $script_id, "script", "footer", true);
							break;
					}
				}
			}
		}

		public function enqueue_file($file_url, $script_id, $style_or_script, $header_or_footer, $load_in_header){
			if(!empty($file_url)){
				$file_path = $_SERVER['DOCUMENT_ROOT'] . parse_url($file_url, PHP_URL_PATH);
				$ver = SCORG_get_file_time($file_path);
				if($style_or_script == "style"){
					wp_enqueue_style( 'scorg-style-'.$header_or_footer.'-'.$script_id, set_url_scheme($file_url), array(), $ver );
				} else {
					wp_enqueue_script( 'scorg-script-'.$header_or_footer.'-'.$script_id, set_url_scheme($file_url), array(), $ver, $load_in_header );
				}
			}
		}

		public function page_post_display_option($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $current_term){
			$conditions_meet = false;
			$check_exclude = (isset($scripts_fields['SCORG_exclude_script']) && $scripts_fields['SCORG_exclude_script'][0]) ? true : false;
			$SCORG_exclude_post_type = !empty($scripts_fields['SCORG_exclude_post_type']) ? $scripts_fields['SCORG_exclude_post_type'] : array();
			$SCORG_exclude_page_post = !empty($scripts_fields['SCORG_exclude_page_post']) ? $scripts_fields['SCORG_exclude_page_post'] : array();
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
	}
	new SCORG_header_footer_files();
}