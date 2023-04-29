<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_scripts_organizer_import')){
	class SCORG_scripts_organizer_import {
		public function __construct(){
			add_action( 'init', array($this, 'scripts_organizer_boot'), 20 );
		}

		public function scripts_organizer_boot() {
		    if (
		        $_SERVER['REQUEST_METHOD'] === 'POST'
		        && isset( $_REQUEST["scripts_organizer_action"] )
		        && wp_verify_nonce( $_REQUEST["_wpnonce"], "scripts_organizer" )
		        && isset( $_FILES['jsonfile'] )
		    ) {
		        $this->scripts_organizer_import_jsonfile();
		    }
		}

		public function scripts_organizer_allow_json_upload($types, $user){
		    $types['txt']  = 'text/plain';
		    $types['json'] = 'application/json';

		    return $types;
		}

		public function scripts_organizer_has_valid_file(){
		    add_filter('upload_mimes', array($this, 'scripts_organizer_allow_json_upload'), 10, 2);

		    if (!function_exists('wp_handle_upload')) {
		        require_once ABSPATH . '/wp-admin/includes/file.php';
		    }
		    $file = wp_handle_upload($_FILES['jsonfile'], ['test_form' => false, 'test_type' => false,]);

		    remove_filter('upload_mimes', array($this, 'scripts_organizer_allow_json_upload'), 10);

		    if (is_wp_error($file)) {
		        /** @var WP_Error $file */
		        code_snippet_notice('JSON File could not be imported: ' . $file->get_error_message());
		        return false;
		    }

		    if (isset($file['error'])) {
		        code_snippet_notice('JSON File could not be imported: ' . $file['error']);

		        return false;
		    }

		    if (!isset($file['file'])) {
		        code_snippet_notice('JSON File could not be imported: Upload failed.');

		        return false;
		    }

		    return $file;
		}

		public function scripts_organizer_get_filesystem(){
		    global $wp_filesystem;

		    if (!defined('FS_METHOD')) {
		        define('FS_METHOD', 'direct');
		    }

		    if (empty($wp_filesystem)) {
		        require_once ABSPATH . '/wp-admin/includes/file.php';
		        WP_Filesystem();
		    }

		    return $wp_filesystem;
		}

		public function scripts_organizer_import_jsonfile(){
		    $file = $this->scripts_organizer_has_valid_file();
		    if (false === $file) {
		        return false;
		    }

		    $wp_filesystem = $this->scripts_organizer_get_filesystem();
		    $jsonfile      = $wp_filesystem->get_contents($file['file']);
		    $jsonfile      = json_decode($jsonfile);

		    unlink($file['file']);

		    if ($this->scripts_organizer_do_import_jsonfile($jsonfile)) {
		        code_snippet_notice('JSON File successfully imported.', 'success');

		        return;
		    }

		    code_snippet_notice('No JSON File found to be imported', 'error');
		}

		public function scripts_organizer_do_import_jsonfile($data){
		    if ($data) {
                if($data->post_type == "scorg_scss"){
                    return $this->prepare_scss_data($data);
                } else {
                    return $this->prepare_code_block_data($data);
                }
		    }
		}

        public function prepare_code_block_data($data){
            unset($data->post_type);
            //echo "<pre>"; print_r($data); "</pre>"; exit;
            foreach ($data as $key => $s) {
                if (isset($s->code)) {
                    $s->code = $s->code;
                }

                if(isset($s->SCORG_php_script)){
                    $s->SCORG_php_script = SCORG_is_base64($s->SCORG_php_script);
                    $s->php_script_unslashed = str_replace('\\', 'REPLACE_BACKSLASH', base64_encode($s->SCORG_php_script));
                }

                if(isset($s->SCORG_header_script)){
                    $s->SCORG_header_script = $s->SCORG_header_script;
                }
                
                if(isset($s->SCORG_footer_script)){
                    $s->SCORG_footer_script = $s->SCORG_footer_script;
                }

                if(isset($s->SCORG_partials) && !empty($s->SCORG_partials)){
                    $partials_ids = array();
                    foreach($s->SCORG_partials as $partial){
                        $partial_id = $this->get_partial_id($partial);
                        if(!empty($partial_id) && $partial_id > 0){
                            $partials_ids[] = $partial_id;    
                        }
                    }
                    if(!empty($partials_ids)){
                        $s->SCORG_partials = $partials_ids;
                        $s->SCORG_scss_partial_manager = 'show';
                    } else {
                        $s->SCORG_scss_partial_manager = 'hide';
                    }
                } else {
                    $s->SCORG_scss_partial_manager = 'hide';
                }

                if (!empty($s->SCORG_script_description)) {
                    $desc = strip_tags($s->SCORG_script_description);
                    $desc = str_replace("&nbsp;", " ", $desc);
                    $desc = preg_replace("/ +/", " ", $desc);
                    $desc = trim($desc);

                    $s->description = $desc;
                }
                
                $script = [];

                $script['post'] = [
                    'post_status' => 'publish',
                    'post_title' => $s->title ?? '',
                    'post_author' => wp_get_current_user()->ID,
                    'post_type' => 'scorg',
                ];

                /* tags */
                if (isset($s->scorg_tags)) {
                    $script = $this->save_scorg_tags($script, $s->scorg_tags);
                }

                $script['meta'] = [
                    'SCORG_active_tab' => $s->SCORG_active_tab,
                    'SCORG_page_post' => $s->SCORG_page_post,
                    'SCORG_exclude_page_post' => $s->SCORG_exclude_page_post ?? '',
                    'SCORG_exclude_post_type' => $s->SCORG_exclude_post_type ?? '',
                    'SCORG_exclude_terms' => $s->SCORG_exclude_terms ?? '',
                    'SCORG_exclude_taxonomies' => $s->SCORG_exclude_taxonomies ?? '',
                    'SCORG_trigger_location' =>  $s->SCORG_trigger_location,
                    'SCORG_php_script_file' => $s->SCORG_php_script ?? '',
                    'SCORG_php_script' => $s->php_script_unslashed ?? '',
                    'SCORG_header_script' => $s->SCORG_header_script ?? '',
                    'SCORG_footer_script' => $s->SCORG_footer_script ?? '',
                    'SCORG_enable_script' => 0,
                    'SCORG_exclude_script' => $s->SCORG_exclude_script,
                    'SCORG_footer_file' => $s->SCORG_footer_file,
                    'SCORG_footer_mode' => $s->SCORG_footer_mode,
                    'SCORG_header_file' => $s->SCORG_header_file,
                    'SCORG_header_mode' => $s->SCORG_header_mode,
                    'SCORG_script_schedule' => $s->SCORG_script_schedule,
                    'SCORG_script_time' => $s->SCORG_script_time,
                    'SCORG_script_type' => $s->SCORG_script_type,
                    'SCORG_scss_partial_manager' => $s->SCORG_scss_partial_manager,
                    'SCORG_scripts_manager' => "hide",
                    'SCORG_only_frontend' => $s->SCORG_only_frontend,
                    'SCORG_script_description' => $s->description ?? '',
                    'SCORG_custom' => $s->SCORG_custom ?? '',
                    'SCORG_partials' => $s->SCORG_partials ?? '',
                    'SCORG_action_hook' => $s->SCORG_action_hook ?? '',
                    'SCORG_priority' => $s->SCORG_priority ?? ''
                ];

                //echo "<pre>"; print_r($script); "</pre>"; exit;

                $script = array_filter($script, function ($value) {
                    return $value !== "";
                });

                $scripts[] = $script;
            }

		    foreach ($scripts as $_s) {
		        $post_id = wp_insert_post($_s['post']);
		        foreach ($_s['meta'] as $k => $v) {
		            if($k == "SCORG_php_script_file" && !empty($v)){
		            	// generate php file
                        $v = "<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>\n" . $v;
						$this->create_php_file($post_id, $v);
		            } else {
                        if(is_array($v)){
                            foreach($v as $meta_value){
                                add_post_meta($post_id, $k, $meta_value);
                            }
                        } else {
                            update_post_meta($post_id, $k, $v);
                        }
		            }
		        }

		        SCORG_create_header_footer_file($post_id, SCORG_is_base64($_s['meta']['SCORG_header_script']), SCORG_is_base64($_s['meta']['SCORG_footer_script']), $_s['meta'], false);

		    }

		    return true;
        }

        public function save_scorg_tags($script, $script_tags){
            $scorg_tags = array();
            foreach($script_tags as $key => $val){
                $term_found = term_exists($key, 'scorg_tags');
                if(is_array($term_found)){
                    $scorg_tags[] = $val;
                } else {
                    $term = wp_insert_term($val, 'scorg_tags', array('slug' => $key));
                    if(!is_wp_error($term)){
                        $scorg_tags[] = $val;
                    }
                }
            }
            if(!empty($scorg_tags)){
                $script['post']['tax_input'] = array(
                    'scorg_tags' => $scorg_tags
                );
            }
            return $script;
        }

        public function get_partial_id($partial){
            global $wpdb;
            $postmeta = $wpdb->postmeta;
            $partial_id = $wpdb->get_var("SELECT post_id FROM $postmeta WHERE meta_key = 'SCSS_partial_id' AND meta_value = '$partial'");
            return $partial_id;
        }

        public function prepare_scss_data($data){
            unset($data->post_type);
            $scripts = [];
            foreach ($data as $key => $s) {
                if (isset($s->SCSS_scss_scripts)) {
                    $s->code = $s->SCSS_scss_scripts;
                }

                if (!empty($s->SCSS_description)) {
                    $desc = strip_tags($s->SCSS_description);
                    $desc = str_replace("&nbsp;", " ", $desc);
                    $desc = preg_replace("/ +/", " ", $desc);
                    $desc = trim($desc);

                    $s->description = $desc;
                }

                $script = [];

                $script['post'] = [
                    'post_status' => 'publish',
                    'post_title' => $s->title ?? '',
                    'post_author' => wp_get_current_user()->ID,
                    'post_type' => 'scorg_scss',
                ];

                /* tags */
                if (isset($s->scorg_tags)) {
                    $script = $this->save_scorg_tags($script, $s->scorg_tags);
                }

                $script['meta'] = [
                    'SCSS_description' => $s->description ?? '',
                    'SCSS_scss_scripts' => $s->code ?? '',
                    'SCSS_partial_id' => $key,
                ];

                $script = array_filter($script, function ($value) {
                    return $value !== "";
                });

                $scripts[] = wp_slash($script);
            }

            $SCORG_SCSS = new SCORG_SCSS();
            foreach ($scripts as $_s) {
		        $post_id = wp_insert_post($_s['post']);
		        foreach ($_s['meta'] as $k => $v) {
                    update_post_meta($post_id, $k, $v);
		        }

                $folder_path = SCORG_UPLOADS_DIR_SCSS.'/'.$post_id;
                $scss_scripts = SCORG_is_base64($_s['meta']['SCSS_scss_scripts']);
                if(!empty($scss_scripts)){
                    if(SCORG_color_picker_option()){
                        $scss_scripts = SCORG_process_colors($scss_scripts);
                    }
                    $SCORG_SCSS->save_scss_script($post_id, $scss_scripts, $folder_path);
                } else {
                    $SCORG_SCSS->save_scss_script($post_id, $scss_scripts, $folder_path);
                }
		    }
            
            return true;
        }

		public function create_php_file($post_id, $code){
			$php_script_file = SCORG_UPLOADS_DIR.'/'.$post_id.'.php';
			$fh = fopen($php_script_file, 'wa+');
			fwrite($fh, $code."\n");
			fclose($fh);
		}
	}
	new SCORG_scripts_organizer_import();
}