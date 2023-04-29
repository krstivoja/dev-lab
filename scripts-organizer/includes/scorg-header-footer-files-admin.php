<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_header_footer_files_admin')){
	class SCORG_header_footer_files_admin {
		public function __construct(){
			$scorg_b_enqueue = get_option('scorg_b_enqueue');
			$scorg_b_enqueue = (!empty($scorg_b_enqueue)) ? $scorg_b_enqueue : 1000;
			add_action( 'admin_enqueue_scripts', array($this, 'load_scripts'), $scorg_b_enqueue );
		}

		public function load_scripts(){
			//echo SCORG_UPLOADS_DIR_CSS . '/206-header-compiled.css'; exit;
			
			//wp_enqueue_style( 'scorg-style-', SCORG_UPLOADS_URL_CSS . '/206-header-compiled.css', array(), time() );
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
			
			// only load front-end scripts if ct-builder active
			$scripts_query = get_posts($scripts_args);

			if($scripts_query){
				foreach($scripts_query as $script){
					$scripts_fields = get_post_custom($script->ID);
					$this->include_in_header_footer_scripts($scripts_fields, $script->ID);
				}
			}
		}

		public function include_in_header_footer_scripts($scripts_fields, $script_id){
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
	}
	new SCORG_header_footer_files_admin();
}