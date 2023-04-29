<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once SCORG_DIR."/plugins/scssphp/scss.inc.php";
//use Leafo\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Compiler;

if(!class_exists('SCORG_scripts_manager')){
	class SCORG_scripts_manager {
		public function __construct(){
			add_action( 'wp_loaded', array($this, 'SCORG_scripts_manager_table') );
			add_action( 'wp_enqueue_scripts', array($this, 'SCORG_enqueue_register_scripts_manager_scripts') );
			add_action( 'wp_ajax_deleteSCORGScript', array($this, 'deleteSCORGScript_func') );
			add_action( 'wp_ajax_saveScript', array($this, 'saveScript_func') );
			add_filter( 'upload_mimes', array($this, 'SCORG_myme_types'), 101 );
			add_filter( 'wp_check_filetype_and_ext', array($this, 'SCORG_add_allow_upload_extension_exception'), 11, 4 );
		}

		public function check_table_exists(){
			global $wpdb;
			$swiss_knife_scripts = $wpdb->prefix . "swiss_knife_scripts"; 
			$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $swiss_knife_scripts ) );
		    if ( ! $wpdb->get_var( $query ) == $swiss_knife_scripts ) {
		    	return false;
		    } else {
		    	return true;
		    }
		}

		public function SCORG_scripts_manager_table() {
			$swk_sm_created = get_option('swk_sm_created');
			if($swk_sm_created == "yes"){
				return;
			}
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$swiss_knife_scripts = $wpdb->prefix . "swiss_knife_scripts"; 
			$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $swiss_knife_scripts ) );
		    $sql = "";
		    $table_exists = false;
		    if ( ! $wpdb->get_var( $query ) == $swiss_knife_scripts ) { 
		        $table_exists = false;
		    } else {
		        $table_exists = true;
		    }
		    
		    if(!$table_exists){
				$sql = "CREATE TABLE ".$swiss_knife_scripts." (
				  id bigint(20) NOT NULL AUTO_INCREMENT,
				  script_name varchar(255) DEFAULT '' NULL,
				  script_type varchar(255) DEFAULT '' NULL,
				  script_location varchar(255) DEFAULT '' NULL,
				  script_include_type varchar(255) DEFAULT '' NULL,
				  script_file varchar(255) DEFAULT '' NULL,
				  script_frontend_only bigint(10) DEFAULT 0 NULL,
				  script_order bigint(20) DEFAULT 0 NULL,
				  PRIMARY KEY (id)
				) ".$charset_collate.";";

				dbDelta( $sql );
			} else {
				$check_column_sql = $wpdb->get_results("SHOW COLUMNS FROM $swiss_knife_scripts LIKE 'script_order';");
				if(empty($check_column_sql)){
					$sql = "ALTER TABLE ".$swiss_knife_scripts."
					ADD script_order bigint(20) DEFAULT 0 NULL";
					$wpdb->query( $sql );
				}
			}
			update_option('swk_sm_created', 'yes');
		}


		/**
		 * Load assets on front end only.
		 */
		public function SCORG_enqueue_register_scripts_manager_scripts() {
			$all_scripts = $this->SCORG_get_all_scripts();
			if(!empty($all_scripts)){
				foreach($all_scripts as $script){
					$this->SCORG_enqueue_or_register($script);
				}
			}
		}


		public function SCORG_replace_name_with_slug($name){
			$slug = strtolower(str_replace(" ", "-", $name));
			return $slug;
		}

		public function SCORG_enqueue_or_register($script){
			if($script->script_frontend_only == "1"){
				if ( defined( 'SHOW_CT_BUILDER' ) ) {
					return;
				}
				$this->SCORG_load_script($script);
			} else {
				$this->SCORG_load_script($script);
			}
		}

		public function SCORG_load_script($script){
			if($script->script_type == "css"){
				if($script->script_include_type == "register"){
					wp_register_style( $this->SCORG_replace_name_with_slug($script->script_name), set_url_scheme($script->script_file), array(), time() );
				} else {
					wp_enqueue_style( $this->SCORG_replace_name_with_slug($script->script_name), set_url_scheme($script->script_file), array(), time() );
				}
			} else {
				if($script->script_include_type == "register"){
					if($script->script_location == "header"){
						wp_register_script( $this->SCORG_replace_name_with_slug($script->script_name), set_url_scheme($script->script_file), array(), time(), false );
					} else {
						wp_register_script( $this->SCORG_replace_name_with_slug($script->script_name), set_url_scheme($script->script_file), array(), time(), true );
					}
				} else {
					if($script->script_location == "header"){
						wp_enqueue_script( $this->SCORG_replace_name_with_slug($script->script_name), set_url_scheme($script->script_file), array(), time(), false );
					} else {
						wp_enqueue_script( $this->SCORG_replace_name_with_slug($script->script_name), set_url_scheme($script->script_file), array(), time(), true );
					}
				}
			}
		}

		public function SCORG_get_all_scripts(){
			if($this->check_table_exists()){
				global $wpdb;
				$swiss_knife_scripts = $wpdb->prefix . 'swiss_knife_scripts';
				$sql = "SELECT * FROM $swiss_knife_scripts ";
				/*if(!empty($front_or_backend)){
					$sql .= " WHERE script_frontend_only = ".$front_or_backend;
				}*/
				$sql .= " ORDER BY script_order ASC";
				$all_scripts = $wpdb->get_results( $sql );
				if(!empty($all_scripts)){
					return $all_scripts;
				} else {
					return '';
				}
			} else {
				return '';
			}
		}

		public function deleteSCORGScript_func(){
			check_ajax_referer('ajax-nonce', 'verify_nonce');
			global $wpdb;
			$swiss_knife_scripts = $wpdb->prefix . "swiss_knife_scripts";
		  	$script_id = $_POST['script_id'];
		  	$wpdb->query("DELETE FROM $swiss_knife_scripts WHERE id = '$script_id'");

		  	echo 'Saved!';
		  	wp_die();
		}

		public function saveScript_func(){
			check_ajax_referer('ajax-nonce', 'verify_nonce');
			parse_str(urldecode(base64_decode($_REQUEST['form_data'])), $params);
			$php_script = urldecode(base64_decode($_POST['php_script']));
			$php_script_unslashed = str_replace('\\', 'REPLACE_BACKSLASH', $php_script);
			$header_script = urldecode(base64_decode($_POST['header_script']));
			$footer_script = urldecode(base64_decode($_POST['footer_script']));
			$scorg_css_variables = !empty($_POST['scorg_css_variables']) ? explode(",", sanitize_text_field($_POST['scorg_css_variables'])) : array();
			$scorg_css_variables = array_unique($scorg_css_variables);
			update_option('scorg_css_variables', $scorg_css_variables);
			$SCORG_enable_script = $_POST['SCORG_enable_script'];
			//print_r($php_script); exit;
			$post_id = $params['post_ID'];
			$post_name = !empty($params['post_name']) ? $params['post_name'] : strtolower(str_replace(" ", "-", $params['post_name']));
			$post_data = array(
				'post_status' => $params['post_status'],
				'post_title' => $params['post_title'],
				'post_name' => $post_name,
				'post_author' => $params['post_author'],
			);
 			if(get_post($post_id)){
 				$post_data['ID'] = $post_id;
				$post_id = wp_update_post($post_data);
			} else {
				$post_id = wp_insert_post($post_data);
			}
			if(!empty($php_script)){
				$php_script = "<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>\n" . $php_script;
				$SCORG_Post = new SCORG_Post();
				$SCORG_Post->save_php_script($post_id, $php_script);
				update_post_meta($post_id, 'SCORG_php_script', base64_encode($php_script_unslashed));
			} else {
				update_post_meta($post_id, 'SCORG_php_script', "");
			}
			update_post_meta($post_id, 'SCORG_header_script', base64_encode($header_script));
			update_post_meta($post_id, 'SCORG_footer_script', base64_encode($footer_script));
			update_post_meta($post_id, 'SCORG_enable_script', $SCORG_enable_script);
			
			$meta_keys = array( 
				'SCORG_enable_script', 
				'SCORG_exclude_script', 
				'SCORG_exclude_post_type', 
				'SCORG_trigger_location', 
				'SCORG_script_type',
				'SCORG_page_post',
				'SCORG_selected_page_post',
				'SCORG_exclude_page_post',
				'SCORG_exclude_terms',
				'SCORG_exclude_taxonomies',
				'SCORG_specific_post_type',
				'SCORG_specific_taxonomy',
				'SCORG_script_schedule',
				'SCORG_specific_date',
				'SCORG_days',
				'SCORG_specific_date_from',
				'SCORG_specific_date_to',
				'SCORG_script_time',
				'SCORG_specific_time_start',
				'SCORG_specific_time_end',
				'SCORG_scripts_manager',
				'SCORG_script_description',
				'SCORG_custom',
				'SCORG_dequeue_scripts',
				'SCORG_enqueue_scripts',
				'SCORG_scss_partial_manager',
				'SCORG_partials',
				'SCORG_view',
				'SCORG_header_mode',
				'SCORG_footer_mode',
				'SCORG_toggle_sidebar',
				'SCORG_active_tab',
				'SCORG_action_hook',
				'SCORG_priority'
			);

			foreach($meta_keys as $key){
				if(!empty($params[$key])){
					delete_post_meta($post_id, $key);
					if(is_array($params[$key])){
						foreach($params[$key] as $value){
							add_post_meta($post_id, $key, $value);
						}
					} else {
						update_post_meta($post_id, $key, $params[$key]);
					}
				} else {
					delete_post_meta($post_id, $key);
				}
			}

			$checkboxes = array( 'SCORG_header_file', 'SCORG_footer_file' );
			foreach($checkboxes as $checkbox){
				if(isset($params[$checkbox])){
					update_post_meta($post_id, $checkbox, 1);
				} else {
					delete_post_meta($post_id, $checkbox);
				}
			}

			if(!empty($params['tax_input']['scorg_tags'])){
				$term_slugs = explode(",", $params['tax_input']['scorg_tags']);
				if(!empty($term_slugs)){
					wp_set_object_terms($post_id, $term_slugs, 'scorg_tags');
				}
			} else {
				$term_ids = wp_get_object_terms($post_id, 'scorg_tags', array('fields' => 'ids'));
				if(!empty($term_ids)){
					wp_remove_object_terms($post_id, $term_ids, 'scorg_tags');
				}
			}

			$message = SCORG_create_header_footer_file($post_id, $header_script, $footer_script, $params, true);
			echo json_encode(
				array(
					'message' => $message,
					'variables_html' => SCORG_css_variables_picker_lis()
				)
			);

			wp_die();
		}

		/* allow CSS JS uploads */
		public function SCORG_myme_types($mime_types){
			// if not admin
			if(!is_admin() && !current_user_can('administrator')){
				return $mime_types;
			}

			// if SCORG css/js option is enabled
			$scorg_allow_css_js = get_option('scorg_allow_css_js');
			if($scorg_allow_css_js == "yes"){
				if(!isset($mime_types['css'])){
					$mime_types['css'] = 'text/css'; //Adding svg extension
				}
				if(!isset($mime_types['js'])){
					$mime_types['js'] = 'application/javascript'; //Adding svg extension
				}
			}

		    return $mime_types;
		}


		public function SCORG_add_allow_upload_extension_exception( $check, $file, $filename, $mimes ) {
			// if not admin
			if(!is_admin() && !current_user_can('administrator')){
				return $check;
			}
			
			// if SCORG css/js option is enabled
			$scorg_allow_css_js = get_option('scorg_allow_css_js');
			if($scorg_allow_css_js == "yes"){
				if ( false !== strpos( $filename, '.css' ) ) {
			        $check['ext']  = 'css';
			        $check['type'] = 'text/css';
			    }
			    if ( false !== strpos( $filename, '.js' ) ) {
			        $check['ext']  = 'js';
			        $check['type'] = 'text/ecmascript';
			    }
			}

		    return $check;
		}
	}
	new SCORG_scripts_manager();
}

function SCORG_create_header_footer_file($post_id, $header_script, $footer_script, $params, $doing_ajax = false){
	$message = "";
	$SCORG_can_include_php = SCORG_can_include_php();
	if(!empty($header_script) && $params['SCORG_header_mode'] != "html"){
		switch($params['SCORG_header_mode']){
			case 'javascript';
				SCORG_save_script_file($post_id, $header_script, "header", "js");
				break;
			case 'css';
				$header_script = SCORG_process_colors($header_script);
				SCORG_save_script_file($post_id, $header_script, "header", "css");
				break;
			case 'scss'; 
				$compiled_scss = "";
				$header_script = SCORG_process_colors($header_script);
				$scss_partial_manager = get_post_meta($post_id, 'SCORG_scss_partial_manager', true);
				$partials = get_post_meta($post_id, 'SCORG_partials');
				if($scss_partial_manager == "show" && !empty($partials)){
					$editor_scss_file = SCORG_UPLOADS_DIR_SCSS.'/'.$post_id.'-header.scss';
					if(file_exists($editor_scss_file)){
						unlink($editor_scss_file);
					}
					$fh = fopen($editor_scss_file, 'a+');
					foreach($partials as $partial){
						if(file_exists(SCORG_UPLOADS_DIR_SCSS.'/_'.$partial.'.scss')){
							fwrite($fh, "@import '_".$partial.".scss'; \n");
						}
					}
					if(!empty($partials)){
						fwrite($fh, "\n".$header_script."\n");
					}
					fclose($fh);
					$result = SCORG_compile_scss('@import "'.$post_id.'-header";', $post_id, "header", $partials, $doing_ajax);
				} else {
					$result = SCORG_compile_scss($header_script, $post_id, "header", $partials, $doing_ajax);
				}
				if(!empty($result) && $result['error'] == "yes"){
					$message = $result['content'];
				} else {
					if($SCORG_can_include_php == "no"){
						$result['content'] = SCORG_process_colors($result['content']);
						SCORG_save_script_file($post_id, $result['content'], "header-compiled", "css");
					}
				}

				break;
		}
	}

	if(!empty($footer_script) && $params['SCORG_footer_mode'] != "html"){
		switch($params['SCORG_footer_mode']){
			case 'javascript';
				SCORG_save_script_file($post_id, $footer_script, "footer", "js");
				break;
			case 'css';
				$footer_script = SCORG_process_colors($footer_script);
				SCORG_save_script_file($post_id, $footer_script, "footer", "css");
				break;
			case 'scss';
				$compiled_scss = "";
				$result = array();
				$scss_partial_manager = get_post_meta($post_id, 'SCORG_scss_partial_manager', true);
				$partials = get_post_meta($post_id, 'SCORG_partials');
				if($scss_partial_manager == "show" && !empty($partials)){
					$editor_scss_file = SCORG_UPLOADS_DIR_SCSS.'/'.$post_id.'-footer.scss';
					if(file_exists($editor_scss_file)){
						unlink($editor_scss_file);
					}
					$fh = fopen($editor_scss_file, 'a+');
					foreach($partials as $partial){
						if(file_exists(SCORG_UPLOADS_DIR_SCSS.'/_'.$partial.'.scss')){
							fwrite($fh, "@import '_".$partial.".scss'; \n");
						}
					}
					if(!empty($partials)){
						fwrite($fh, "\n".$footer_script."\n");
					}
					fclose($fh);

					$result = SCORG_compile_scss('@import "'.$post_id.'-footer";', $post_id, "footer", $partials, $doing_ajax);
				} else {
					$footer_script = SCORG_process_colors($footer_script);
					$result = SCORG_compile_scss($footer_script, $post_id, "footer", $partials, $doing_ajax);
				}
				if(!empty($result) && $result['error'] == "yes"){
					$message = $result['content'];
				} else {
					if($SCORG_can_include_php == "no"){
						$result['content'] = SCORG_process_colors($result['content']);
						SCORG_save_script_file($post_id, $result['content'], "footer-compiled", "css");
					}
				}

				break;
		}
	}

	return $message;
}

function SCORG_compile_scss($scss_code, $script_id, $header_or_footer, $partials, $doing_ajax = false){
	$return_data = array();
	$scorg_sourcemaps = get_option('scorg_sourcemaps');
	try {
	    $scssc = new Compiler();
	    $scssc->setImportPaths(SCORG_UPLOADS_DIR_SCSS.'/');
	    $scssc->setOutputStyle( 'expanded' );

		// creates sourcemaps if option is enabled
		$SCORG_header_file = get_post_meta($script_id, 'SCORG_header_file', true);
		$SCORG_footer_file = get_post_meta($script_id, 'SCORG_footer_file', true);
		if($scorg_sourcemaps == "yes" && (($header_or_footer == "header" && !empty($SCORG_header_file)) || ($header_or_footer == "footer" && !empty($SCORG_footer_file)))){
			if(!empty($partials)){
				foreach($partials as $partial){
					$s_map_name = $script_id . '-partials';
					$scssc->setSourceMap(Compiler::SOURCE_MAP_FILE);  // SOURCE_MAP_NONE, SOURCE_MAP_INLINE, or SOURCE_MAP_FILE
					$scssc->setSourceMapOptions(array(
						// absolute path to write .map file
						'sourceMapWriteTo'  => SCORG_UPLOADS_DIR_CSS . '/' . $s_map_name . '.map',
						// relative or full url to the above .map file
						'sourceMapURL'      => $s_map_name . '.map',
						// (optional) relative or full url to the .css file
						'sourceMapFilename' => $script_id . '-' . $header_or_footer . '-compiled' . '.css',  // url location of .css file
						// partial path (server root) removed (normalized) to create a relative url
						'sourceMapBasepath' => rtrim(ABSPATH, '/'), // base path for filename normalization
						// (optional) prepended to 'source' field entries for relocating source files
						'sourceRoot' => home_url('/'),
					));
				}
			}
		}
	    $return_data['error'] = "no";
	    $return_data['content'] = $scssc->compile($scss_code);
	} catch (\Exception $e) {
		if($doing_ajax){
			$return_data['error'] = "yes";
			$return_data['content'] = '<div id="compilation-error">
			    <h2>There is some error in your SCSS</h2>
			    <p>'.$e->getMessage().'</p>
			</div>';
		} else {
			$scorg_safemode = get_option('scorg_safemode');
			if($scorg_safemode == "yes") return '';
			require_once(ABSPATH.'wp-includes/pluggable.php');
			if(!is_user_logged_in()){
				return '';
			}
			if(is_user_logged_in() && !current_user_can('administrator')){
				return '';
			}
			$safe_mode_url = site_url().'/wp-admin/post.php?post='.$script_id.'&action=edit&scorg_safemode=no';
		    $safe_mode_enable_url = add_query_arg('scorg_safemode', "yes", $safe_mode_url);
		    ?>
		    <div id="enable-scorg_safemode">

				<svg viewBox="0 0 100 100">
			 		<path d="m50 100c-53.82-22.02-39.902-55.797-44.066-86.441 17.664-0.3125 32.031-5.2891 44.066-13.559 12.035 8.2695 26.402 13.246 44.066 13.559-4.168 30.645 9.7539 64.422-44.066 86.441zm-17.191-52.996 9.8984 9.8438 24.465-24.465 7.1914 7.1914-31.617 31.617-17.086-17 7.1523-7.1875z" fill-rule="evenodd"/>
				</svg>

				<div class="safemode-content">
					<h1><span>Scripts Organizer</span><br>Safe Mode is Not active</h1>
					<p><?php echo $e->getMessage(); ?></p>
					<h2>Do not panic. You can fix it!</h2>
					<a class="safe-mode-btn" href="<?= $safe_mode_enable_url ?>">Enable Safe Mode</a>
				</div>
				
			</div>




			<style type="text/css">
				<?php include SCORG_DIR . 'includes/css/safe_mode.css'; ?>
			</style>
		    <?php
		    exit;
		}
	}

	return $return_data;
}

function SCORG_save_script_file($post_id, $code, $type, $ext){
	$folder_name = "";
	if($ext == "scss"){
		$folder_name = SCORG_UPLOADS_DIR_SCSS;
		$code = SCORG_process_colors($code);
	} else if($ext == "css"){
		$folder_name = SCORG_UPLOADS_DIR_CSS;
		$code = SCORG_process_colors($code);
	} else {
		$folder_name = SCORG_UPLOADS_DIR_JS;
	}
	$code_file = $folder_name.'/'.$post_id.'-'.$type.'.'.$ext;
	$fh = fopen($code_file, 'wa+');
	fwrite($fh, $code."\n");
	fclose($fh);
}

function SCORG_fetch_or_load_scripts($mode, $post_id, $header_or_footer, $inline = true){
	$script_file_url = "";
	$script_file_include_url = "";
	if($mode == "scss" || $mode == "css"){
		$mode = "css";
		$script_file_url = SCORG_UPLOADS_DIR_CSS.'/'.$post_id.'-'.$header_or_footer.'.'.$mode;
		$script_file_include_url = SCORG_UPLOADS_URL_CSS.'/'.$post_id.'-'.$header_or_footer.'.'.$mode;
	} else {
		if($mode == "javascript"){
			$mode = "js";
		}
		$script_file_url = SCORG_UPLOADS_DIR_JS.'/'.$post_id.'-'.$header_or_footer.'.'.$mode;
		$script_file_include_url = SCORG_UPLOADS_URL_JS.'/'.$post_id.'-'.$header_or_footer.'.'.$mode;
	}
	//echo $script_file_url; exit;
	if(file_exists($script_file_url)){
		//echo $script_file_url; exit;
		if($inline){
			$script = file_get_contents($script_file_url);
			return $script;
		} else {
			return $script_file_include_url;
		}
	} else {
		return '';
	}
}

function SCORG_create_SCSS_file_on_load($post_id, $header_or_footer){
	$doing_ajax = false;
	$compiled_scss = "";
	$SCORG_can_include_php = SCORG_can_include_php();
	$result = array();
	$scss_script = SCORG_is_base64(get_post_meta($post_id, 'SCORG_'.$header_or_footer.'_script', true));
	$scss_partial_manager = get_post_meta($post_id, 'SCORG_scss_partial_manager', true);
	$partials = get_post_meta($post_id, 'SCORG_partials');
	if(!empty($scss_script)){
		if($scss_partial_manager == "show" && !empty($partials)){
			$partials = get_post_meta($post_id, 'SCORG_partials');
			$editor_scss_file = SCORG_UPLOADS_DIR_SCSS.'/'.$post_id.'-header.scss';
			if(file_exists($editor_scss_file)){
				unlink($editor_scss_file);
			}
			$fh = fopen($editor_scss_file, 'a+');
			foreach($partials as $partial){
				if(file_exists(SCORG_UPLOADS_DIR_SCSS.'/_'.$partial.'.scss')){
					fwrite($fh, "@import '_".$partial.".scss'; \n");
				}
			}
			if(!empty($partials)){
				fwrite($fh, "\n".$scss_script."\n");
			}
			fclose($fh);
			$result = SCORG_compile_scss('@import "'.$post_id.'-header";', $post_id, $header_or_footer, $partials, $doing_ajax);
		} else {
			$result = SCORG_compile_scss($scss_script, $post_id, $header_or_footer, $partials, $doing_ajax);
		}
		if(!empty($result) && $result['error'] == "yes"){
			$message = $result['content'];
		} else {
			if($SCORG_can_include_php == "no"){
				SCORG_save_script_file($post_id, $result['content'], $header_or_footer."-compiled", "css");
			}
		}
	}
}
