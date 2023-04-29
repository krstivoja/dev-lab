<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_advanced_scripts_import')){
	class SCORG_advanced_scripts_import {
		public function __construct(){
			add_action( 'init', array($this, 'advanced_scripts_boot'), 20 );
		}

		public function advanced_scripts_boot() {
		    if (
		        $_SERVER['REQUEST_METHOD'] === 'POST'
		        && isset( $_REQUEST["advanced_scripts_action"] )
		        && wp_verify_nonce( $_REQUEST["_wpnonce"], "advanced_scripts" )
		        && isset( $_FILES['jsonfile'] )
		    ) {
		        $this->advanced_scripts_import_jsonfile();
		    }
		}

		public function advanced_scripts_allow_json_upload($types, $user){
		    $types['txt']  = 'text/plain';
		    $types['json'] = 'application/json';

		    return $types;
		}

		public function advanced_scripts_has_valid_file(){
		    add_filter('upload_mimes', array($this, 'advanced_scripts_allow_json_upload'), 10, 2);

		    if (!function_exists('wp_handle_upload')) {
		        require_once ABSPATH . '/wp-admin/includes/file.php';
		    }
		    $file = wp_handle_upload($_FILES['jsonfile'], ['test_form' => false, 'test_type' => false,]);

		    remove_filter('upload_mimes', array($this, 'advanced_scripts_allow_json_upload'), 10);

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

		public function advanced_scripts_get_filesystem(){
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

		public function advanced_scripts_import_jsonfile(){
		    $file = $this->advanced_scripts_has_valid_file();
		    if (false === $file) {
		        return false;
		    }

		    $wp_filesystem = $this->advanced_scripts_get_filesystem();
		    $jsonfile      = $wp_filesystem->get_contents($file['file']);
		    $jsonfile      = json_decode($jsonfile);

		    unlink($file['file']);

		    if ($this->advanced_scripts_do_import_jsonfile($jsonfile)) {
		        code_snippet_notice('JSON File successfully imported.', 'success');

		        return;
		    }

		    code_snippet_notice('No JSON File found to be imported', 'error');
		}

		public function advanced_scripts_do_import_jsonfile($data){
		    $scripts = [];
		    $allowed_types = array('text/javascript', 'application/x-httpd-php', 'text/css', 'text/x-scss');

		    if ($data && isset($data->generator)) {
		        $generator = $data->generator ?? "";

		        if (strpos($generator, "Advanced Scripts") === 0) {
		        	/*echo $generator . '<br/>';
		        	echo "<pre>"; print_r($data->scripts); "</pre>";*/
		            foreach ($data->scripts as $s) {
		            	if (!in_array($s->type, $allowed_types)) {
		                    continue;
		                }

		                if (isset($s->code)) {
		                    $s->code = $s->code;
		                }

		                switch ($s->location) {
		                    case "front":
		                        $s->location = "conditions";
		                        break;
		                    case "admin":
		                        $s->location = "admin_only";
		                        break;
	                        case "all":
		                        $s->location = "everywhere";
		                        break;
		                    default:
		                        $s->location = "everywhere";
		                        break;
		                }

		                switch ($s->type) {
		                    case "text/javascript":
		                        $s->type = 'footer';
		                        $s->footer_file = 1;
		                        $s->header_file = 0;
		                        $s->header_mode = '';
		                        $s->footer_mode = 'javascript';
		                        $s->active_tab = 'footer';
		                        $s->php_script = '';
		                        $s->php_script_unslashed = '';
		                        $s->header_script = '';
		                        $s->footer_script = $s->code;
		                        $s->location = "conditions";
		                        break;
	                        case "text/css":
		                        $s->type = 'header';
		                        $s->footer_file = 0;
		                        $s->header_file = 1;
		                        $s->header_mode = 'css';
		                        $s->footer_mode = '';
		                        $s->active_tab = 'header';
		                        $s->php_script = '';
		                        $s->php_script_unslashed = '';
		                        $s->header_script = $s->code;
		                        $s->footer_script = '';
		                        $s->location = "conditions";
		                        break;
	                        case "text/x-scss":
		                        $s->type = 'header';
		                        $s->footer_file = 0;
		                        $s->header_file = 1;
		                        $s->header_mode = 'scss';
		                        $s->footer_mode = '';
		                        $s->active_tab = 'header';
		                        $s->php_script = '';
		                        $s->php_script_unslashed = '';
		                        $s->header_script = $s->code;
		                        $s->footer_script = '';
		                        $s->location = "conditions";
		                        break;
	                        case "application/x-httpd-php":
		                        $s->type = 'php';
		                        $s->footer_file = 0;
		                        $s->header_file = 0;
		                        $s->header_mode = '';
		                        $s->footer_mode = '';
		                        $s->active_tab = 'php';
		                        $s->php_script = $s->code;
		                        $s->php_script_unslashed = str_replace('\\', 'REPLACE_BACKSLASH', $s->code);
		                        $s->header_script = '';
		                        $s->footer_script = '';
		                        break;
		                    default:
		                        break;
		                }

		                if (!empty($s->description)) {
		                    $desc = strip_tags($s->description);
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

		                $script['meta'] = [
		                    'SCORG_script_description' => $s->description ?? '',
		                    'SCORG_custom' => '',
		                    'SCORG_active_tab' => $s->active_tab,
		                    'SCORG_page_post' => 'all',
		                    'SCORG_exclude_page_post' => '',
		                    'SCORG_exclude_post_type' => '',
		                    'SCORG_exclude_terms' => '',
		                    'SCORG_exclude_taxonomies' => '',
		                    'SCORG_trigger_location' =>  $s->location,
		                    'SCORG_php_script_file' => $s->php_script,
		                    'SCORG_php_script' => $s->php_script_unslashed,
		                    'SCORG_header_script' => $s->header_script,
		                    'SCORG_footer_script' => $s->footer_script,
		                    'SCORG_enable_script' => false,
		                    'SCORG_exclude_script' => false,
		                    'SCORG_footer_file' => $s->footer_file,
		                    'SCORG_footer_mode' => $s->footer_mode,
		                    'SCORG_header_file' => $s->header_file,
		                    'SCORG_header_mode' => $s->header_mode,
		                    'SCORG_script_schedule' => 'daily',
		                    'SCORG_script_time' => 'all_day',
		                    'SCORG_script_type' => $s->type,
		                    'SCORG_scripts_manager' => 'hide',
		                    'SCORG_scss_partial_manager' => 'hide',
		                ];

		                $script = array_filter($script, function ($value) {
		                    return $value !== "";
		                });

		                $scripts[] = $script;
		            }
		        }
		    }

		    foreach ($scripts as $_s) {
		        $post_id = wp_insert_post($_s['post']);
		        foreach ($_s['meta'] as $k => $v) {
		            if($k == "SCORG_php_script_file" && !empty($v)){
		            	// generate php file
						$this->create_php_file($post_id, $v);
		            } else {
		            	update_post_meta($post_id, $k, $v);
		            }
		        }

		        SCORG_create_header_footer_file($post_id, SCORG_is_base64($_s['meta']['SCORG_header_script']), SCORG_is_base64($_s['meta']['SCORG_footer_script']), $_s['meta'], false);

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
	new SCORG_advanced_scripts_import();
}