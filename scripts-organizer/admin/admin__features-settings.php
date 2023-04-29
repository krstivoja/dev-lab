<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*====================================
=            Features Form            =
====================================*/

if(!class_exists('SCORG_features')){
	class SCORG_features {
		public function __construct(){
			add_action( 'wp_ajax_saveSCORGOptions', array($this, 'saveSCORGOptions_func') );
			add_action( 'wp_ajax_regenerateFiles', array($this, 'regenerateFiles_func') );
		}

		public function get_scorg_options(){
			/*================================
			=            Features            =
			================================*/
			$scorg_options = array(
				'scorg_scripts_manager' => array(
					'Enable Scripts Manager',
					'styles' => array(),
					'scripts' => array()
				),

				'scorg_allow_css_js' => array(
					'Allow CSS/JS Uploads',
					'styles' => array(),
					'scripts' => array()
				),
				'scorg_show_shortcode' => array(
					'Display shortcode in the Admin Column',
					'styles' => array(),
					'scripts' => array()
				),
				'scorg_fontsize' => array(
					'Code Editor font size',
					'styles' => array(''),
					'scripts' => array()
				),
				'scorg_code_blocks' => array(
					'Code Blocks in admin bar',
					'styles' => array(''),
					'scripts' => array()
				),
				'scorg_scss_partials' => array(
					'SCSS Partials in admin bar',
					'styles' => array(''),
					'scripts' => array()
				),
				'scorg_oxy_stylesheets' => array(
					'Oxygen Stylesheets',
					'styles' => array(''),
					'scripts' => array()
				),
				'scorg_monaco_in_theme' => array(
					'VS in theme editor',
					'styles' => array(''),
					'scripts' => array()
				),
				'scorg_monaco_in_plugin' => array(
					'VS in plugin editor',
					'styles' => array(''),
					'scripts' => array()
				),
				'scorg_sourcemaps' => array(
					'Create a SCSS Source Map',
					'styles' => array(''),
					'scripts' => array()
				),
				'scorg_livereload' => array(
					'Live reload on save (CTRL + S)',
					'styles' => array(''),
					'scripts' => array()
				),
				'scorg_hsf_wo_file' => array(
					'<span><b>Frontend</b> Header Scripts (Embeded)</span>',
					'value' => 999999999,
					'styles' => array(''),
					'scripts' => array()
				),
				'scorg_fsf_wo_file' => array(
					'<span><b>Frontend</b> Footer Scripts (Embeded)</span>',
					'value' => 101,
					'styles' => array(''),
					'scripts' => array()
				),
				'scorg_hsb_wo_file' => array(
					'<span><b>Backend</b> Header Scripts (Embeded)</span>',
					'value' => 101,
					'styles' => array(''),
					'scripts' => array()
				),
				'scorg_fsb_wo_file' => array(
					'<span><b>Backend</b> Footer Scripts (Embeded)</span>',
					'value' => 101,
					'styles' => array(''),
					'scripts' => array()
				),
				'scorg_f_enqueue' => array(
					'<span><b>Frontend</b> Enqueue Scripts (Link File)</span>',
					'value' => 1000,
					'styles' => array(''),
					'scripts' => array()
				),
				'scorg_b_enqueue' => array(
					'<span><b>Frontend</b> Enqueue Scripts (Link File)</span>',
					'value' => 1000,
					'styles' => array(''),
					'scripts' => array()
				),
			);
			return $scorg_options;
		}

		public function priority_options(){
			return array(
				'scorg_hsf_wo_file',
				'scorg_fsf_wo_file',
				'scorg_hsb_wo_file',
				'scorg_fsb_wo_file',
				'scorg_f_enqueue',
				'scorg_b_enqueue',
			);
		}

		public function saveSCORGOptions_func(){
			check_ajax_referer('ajax-nonce', 'verify_nonce');
		  	parse_str($_POST['form_data'], $form_data);
		  	$selected_options = array_keys($form_data);
		  	$scorg_options = $this->get_scorg_options();
		  	$pr_options = $this->priority_options();
		  	foreach($scorg_options as $key => $val){
		  		if(in_array($key, $selected_options)){
		  			update_option($key, 'yes');
		  		} else {
		  			update_option($key, 'no');
		  		}
		  	}

		  	if(isset($form_data['scorg_font_value'])){
		  		update_option('scorg_font_value', $form_data['scorg_font_value']);
		  	}
			foreach($pr_options as $pr_option){
				if(isset($form_data[$pr_option])){
					update_option($pr_option, $form_data[$pr_option]);
				}
			}
			if(!empty($form_data['scorg_files_path'])){
				$files_path = ABSPATH . sanitize_text_field( $form_data['scorg_files_path'] );
				if (!is_dir($files_path)) {
					mkdir($files_path);
				}
			}
			update_option('scorg_files_path', $form_data['scorg_files_path']);

		  	echo 'Saved!';

		  	wp_die();
		}
		
		public function regenerateFiles_func(){
			check_ajax_referer('ajax-nonce', 'verify_nonce');

			update_option('SCORG_regenerated', 'yes');

			$this->generate_partials_files();
			$this->generate_code_blocks_files();

		  	echo 'Done!';

		  	wp_die();
		}

		public function generate_partials_files(){
			global $wpdb;
			$posts = $wpdb->prefix . 'posts';
			$all_partials = $wpdb->get_results("SELECT ID FROM $posts WHERE post_type = 'scorg_scss'", OBJECT);
			if(!empty($all_partials)){
				foreach($all_partials as $partial){
					$post_id = $partial->ID;
					$editor_scss = SCORG_is_base64(get_post_meta($post_id, 'SCSS_scss_scripts', true));
					$folder_path = SCORG_UPLOADS_DIR_SCSS.'/'.$post_id;
					if (!is_dir($folder_path)) {
						mkdir($folder_path);
					}
					$editor_scss_file = SCORG_UPLOADS_DIR_SCSS.'/_'.$post_id.'.scss';
					$fh = fopen($editor_scss_file, 'wa+');
					fwrite($fh, $editor_scss."\n");
					fclose($fh);
				}
			}
		}
		
		public function generate_code_blocks_files(){
			global $wpdb;
			$posts = $wpdb->prefix . 'posts';
			$all_code_blocks = $wpdb->get_results("SELECT ID FROM $posts WHERE post_type = 'scorg'", OBJECT);
			if(!empty($all_code_blocks)){
				foreach($all_code_blocks as $code_block){
					$post_id = $code_block->ID;
					$params = array(
						'SCORG_header_mode' => get_post_meta($post_id, 'SCORG_header_mode', true),
						'SCORG_footer_mode' => get_post_meta($post_id, 'SCORG_footer_mode', true)
					);
					// header/footer scripts
					$header_script = SCORG_is_base64(get_post_meta($post_id, 'SCORG_header_script', true));
					$footer_script = SCORG_is_base64(get_post_meta($post_id, 'SCORG_footer_script', true));
					if(!empty($header_script) || !empty($footer_script)){
						SCORG_create_header_footer_file($post_id, $header_script, $footer_script, $params, false);
					}
					// header/footer scripts

					// php script
					$php_script = SCORG_is_base64(get_post_meta($post_id, 'SCORG_php_script', true));
					if(!empty($php_script)){
						$php_script = "<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>\n" . $php_script;
						$php_script = str_replace("REPLACE_BACKSLASH", "\\", $php_script);
						$php_script_file = SCORG_UPLOADS_DIR.'/'.$post_id.'.php';
						$fh = fopen($php_script_file, 'wa+');
						fwrite($fh, $php_script."\n");
						fclose($fh);
					}
				}
			}
		}
	}
	new SCORG_features();
}

