<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_shortcode')){
	class SCORG_shortcode {
		public function __construct(){
			add_shortcode( 'scorg_shortcode', array($this, 'SCORG_shortcode_func') );
		}

		public function SCORG_shortcode_func( $atts ){
			$attrributes = shortcode_atts( array(
		        'id' => '',
		    ), $atts );
		    ob_start();
		    $script_id = $attrributes['id'];
		    $SCORG_enable_script = get_post_meta($script_id, 'SCORG_enable_script', true);
		    if($SCORG_enable_script){
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
				if(!empty($script_id)){
					$post_type = get_post_type($script_id);
					if($post_type == "scorg"){
						$scripts_fields = get_post_custom($script_id);
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

						/* 
							script_schedule = daily 
							script_time = all day
						*/
						if($SCORG_script_schedule == "daily" && $SCORG_script_time == "all_day"){
							$this->SCORG_page_shortcode_display_option($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script_id, $current_term);
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
								$this->SCORG_page_shortcode_display_option($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy,  $current_taxonomy, $script_id, $current_term);
							}
						}

						/* 
							script_schedule = specific days 
							script_time = all day
						*/
						if($SCORG_script_schedule == "specific_days" && $SCORG_script_time == "all_day"){
							$currentDay = strtolower(date("l"));
							if(in_array($currentDay, $SCORG_days)){
								$this->SCORG_page_shortcode_display_option($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script_id, $current_term);
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
									$this->SCORG_page_shortcode_display_option($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script_id, $current_term);
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
								$this->SCORG_page_shortcode_display_option($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script_id, $current_term);
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
									$this->SCORG_page_shortcode_display_option($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script_id, $current_term);
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
								$this->SCORG_page_shortcode_display_option($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script_id, $current_term);
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
									$this->SCORG_page_shortcode_display_option($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script_id, $current_term);
								}
							}
						}
					}
				}
		    }
			return ob_get_clean();
		}

		public function SCORG_page_shortcode_display_option($scripts_fields, $SCORG_page_post, $currentPagePostId, $current_post, $SCORG_selected_page_post, $SCORG_specific_post_type, $SCORG_specific_taxonomy, $current_taxonomy, $script_id, $current_term){
			$SCORG_php = new SCORG_php();
			$check_exclude = (isset($scripts_fields['SCORG_exclude_script']) && $scripts_fields['SCORG_exclude_script'][0]) ? true : false;
			$SCORG_exclude_post_type = !empty($scripts_fields['SCORG_exclude_post_type']) ? $scripts_fields['SCORG_exclude_post_type'] : array();
			$SCORG_exclude_page_post = !empty($scripts_fields['SCORG_exclude_page_post']) ? $scripts_fields['SCORG_exclude_page_post'] : array();
			$SCORG_exclude_terms = !empty($scripts_fields['SCORG_exclude_terms']) ? $scripts_fields['SCORG_exclude_terms'] : array();
			$SCORG_exclude_taxonomies = !empty($scripts_fields['SCORG_exclude_taxonomies']) ? $scripts_fields['SCORG_exclude_taxonomies'] : array();
			if($SCORG_page_post == "all"){
				if($check_exclude){
					if(!in_array($currentPagePostId, $SCORG_exclude_page_post)
					&& !in_array($current_post, $SCORG_exclude_post_type)
					&& !in_array($current_taxonomy, $SCORG_exclude_taxonomies)
					&& !in_array($current_term, $SCORG_exclude_terms)){
						$SCORG_php->SCORG_include_script_file($script_id);
					}
				} else {
					$SCORG_php->SCORG_include_script_file($script_id);
				}
			} else if($SCORG_page_post == "specific_page_post"){
				if(in_array($currentPagePostId, $SCORG_selected_page_post)){
					$SCORG_php->SCORG_include_script_file($script_id);
				}
			} else if($SCORG_page_post == "specific_post_type"){
				if(in_array($current_post, $SCORG_specific_post_type)){
					if($check_exclude){
						if(!in_array($currentPagePostId, $SCORG_exclude_page_post)){
							$SCORG_php->SCORG_include_script_file($script_id);
						}
					} else {
						$SCORG_php->SCORG_include_script_file($script_id);
					}
				}
			} else if($SCORG_page_post == "custom"){
				$condition = $scripts_fields['SCORG_custom'][0];
				if(eval("return $condition;")){
					$SCORG_php->SCORG_include_script_file($script_id);
				}
			} else {
				if(in_array($current_taxonomy, $SCORG_specific_taxonomy)){
					if($check_exclude){
						if(!in_array($current_term, $SCORG_exclude_terms)){
							$SCORG_php->SCORG_include_script_file($script_id);
						}
					} else {
						$SCORG_php->SCORG_include_script_file($script_id);
					}
				}
			}
		}
	}
	new SCORG_shortcode();
}