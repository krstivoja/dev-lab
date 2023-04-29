<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_enqueue_dequeue')){
	class SCORG_enqueue_dequeue {
		public function __construct(){
			$scorg_f_enqueue = get_option('scorg_f_enqueue');
			$scorg_f_enqueue = (!empty($scorg_f_enqueue)) ? $scorg_f_enqueue : 1000;
			add_action( 'wp_enqueue_scripts', array($this, 'SCORG_enqueue_dequeue_scripts'), $scorg_f_enqueue );
		}

		public function SCORG_enqueue_dequeue_scripts(){
			$scripts_args = array(
				'post_type'			=> 'scorg',
				'posts_per_page' 	=> -1,
				'orderby' => 'menu_order',
				'order' => 'ASC',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'SCORG_enable_script',
						'value' => 1,
						'compare' => '=',
					),
					array(
						'key' => 'SCORG_scripts_manager',
						'value' => 'show',
						'compare' => '=',
					),
					array(
						'key' => 'SCORG_trigger_location',
						'value' => 'conditions',
						'compare' => '=',
					),
					array(
						'relation' => 'OR',
						array(
							'key' => 'SCORG_dequeue_scripts',
							'value' => '',
							'compare' => '!=',
						),
						array(
							'key' => 'SCORG_enqueue_scripts',
							'value' => '',
							'compare' => '!=',
						),
					)
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
				$SCORG_load_script = new SCORG_load_script();
				$SCORG_header_script = "";
				$SCORG_footer_script = "";
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
						$header_footer_scripts_array = $SCORG_load_script->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
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
							$header_footer_scripts_array = $SCORG_load_script->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
						}
					}

					/* 
						script_schedule = specific days 
						script_time = all day
					*/
					if($SCORG_script_schedule == "specific_days" && $SCORG_script_time == "all_day"){
						$currentDay = strtolower(date("l"));
						if(in_array($currentDay, $SCORG_days)){
							$header_footer_scripts_array = $SCORG_load_script->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
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
								$header_footer_scripts_array = $SCORG_load_script->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
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
							$header_footer_scripts_array = $SCORG_load_script->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
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
								$header_footer_scripts_array = $SCORG_load_script->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
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
							$header_footer_scripts_array = $SCORG_load_script->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
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
								$header_footer_scripts_array = $SCORG_load_script->include_in_header_footer_scripts($scripts_fields, $SCORG_header_script, $SCORG_footer_script, $header_footer_scripts_array, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $SCORG_dequeue_scripts, $SCORG_enqueue_scripts, $script->ID, $current_term);
							}
						}
					}
				}
			}

			if(!empty($header_footer_scripts_array)){
				if(!empty($header_footer_scripts_array['dequeue']) || !empty($header_footer_scripts_array['enqueue'])){
					$SCORG_load_script->SCORG_dequeue_enqueue_scripts($header_footer_scripts_array);
				}
			}
		}
	}
	new SCORG_enqueue_dequeue();
}