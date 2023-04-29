<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_code_snippets_import')){
	class SCORG_code_snippets_import {
		public function __construct(){
			add_action( 'init', array($this, 'code_snippet_boot'), 20 );
		}

		public function code_snippet_boot() {
		    if (
		        $_SERVER['REQUEST_METHOD'] === 'POST'
		        && isset( $_REQUEST["code_snippet_action"] )
		        && wp_verify_nonce( $_REQUEST["_wpnonce"], "code_snippet" )
		        && isset( $_FILES['jsonfile'] )
		    ) {
		        $this->code_snippet_import_jsonfile();
		    }
		}

		public function code_snippet_allow_json_upload($types, $user){
		    $types['txt']  = 'text/plain';
		    $types['json'] = 'application/json';

		    return $types;
		}

		public function code_snippet_has_valid_file(){
		    add_filter('upload_mimes', array($this, 'code_snippet_allow_json_upload'), 10, 2);

		    if (!function_exists('wp_handle_upload')) {
		        require_once ABSPATH . '/wp-admin/includes/file.php';
		    }
		    $file = wp_handle_upload($_FILES['jsonfile'], ['test_form' => false, 'test_type' => false,]);

		    remove_filter('upload_mimes', array($this, 'code_snippet_allow_json_upload'), 10);

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

		public function code_snippet_get_filesystem(){
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

		public function code_snippet_import_jsonfile(){
		    $file = $this->code_snippet_has_valid_file();
		    if (false === $file) {
		        return false;
		    }

		    $wp_filesystem = $this->code_snippet_get_filesystem();
		    $jsonfile      = $wp_filesystem->get_contents($file['file']);
		    $jsonfile      = json_decode($jsonfile);

		    unlink($file['file']);

		    if ($this->code_snippet_do_import_jsonfile($jsonfile)) {
		        code_snippet_notice('JSON File successfully imported.', 'success');

		        return;
		    }

		    code_snippet_notice('No JSON File found to be imported', 'error');
		}

		public function code_snippet_do_import_jsonfile($data){
		    $scripts = [];

		    if ($data && isset($data->generator)) {
		        $generator = $data->generator ?? "";
		        /*echo $generator . '<br/>';
		        echo "<pre>"; print_r($data->snippets); "</pre>"; exit;*/

		        if (strpos($generator, "Code Snippets") === 0) {
		            foreach ($data->snippets as $s) {
		                $scope = $s->scope ?? "";
		                if ($scope == "single-use") {
		                    continue;
		                }

		                switch ($scope) {
		                    case "front-end":
		                        $s->location = "conditions";
		                        break;
		                    case "admin":
		                        $s->location = "admin_only";
		                        break;
		                    default:
		                        $s->location = "everywhere";
		                        break;
		                }

		                if (!empty($s->desc)) {
		                    $desc = strip_tags($s->desc);
		                    $desc = str_replace("&nbsp;", " ", $desc);
		                    $desc = preg_replace("/ +/", " ", $desc);
		                    $desc = trim($desc);

		                    $s->desc = $desc;
		                }

		                $s->code = "<?php\n\n" . trim($s->code ?? "");

		                $script = [];

		                $script['post'] = [
		                    'post_status' => 'publish',
		                    'post_title' => $s->name ?? '',
		                    'post_author' => wp_get_current_user()->ID,
		                    'post_type' => 'scorg',
		                ];

		                $php_script_unslashed = str_replace('\\', 'REPLACE_BACKSLASH', $s->code);
		                $script['meta'] = [
		                    'SCORG_script_description' => $s->desc ?? '',
		                    'SCORG_custom' => '',
		                    'SCORG_active_tab' => 'php',
		                    'SCORG_page_post' => 'all',
							'SCORG_exclude_page_post' => '',
							'SCORG_exclude_post_type' => '',
		                    'SCORG_exclude_terms' => '',
		                    'SCORG_exclude_taxonomies' => '',
		                    'SCORG_trigger_location' =>  $s->location,
		                    'SCORG_php_script_file' => $s->code,
		                    'SCORG_php_script' => base64_encode($php_script_unslashed),
		                    'SCORG_enable_script' => false,
		                    'SCORG_exclude_script' => false,
		                    'SCORG_footer_file' => 0,
		                    'SCORG_footer_mode' => 'html',
		                    'SCORG_header_file' => 0,
		                    'SCORG_header_mode' => 'html',
		                    'SCORG_script_schedule' => 'daily',
		                    'SCORG_script_time' => 'all_day',
		                    'SCORG_script_type' => 'php',
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
		            if($k == "SCORG_php_script_file"){
		            	// generate php file
						$this->create_php_file($post_id, $v);
		            } else {
		            	update_post_meta($post_id, $k, $v);
		            }
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
	new SCORG_code_snippets_import();
}

function code_snippet_notice($message, $level = 'error'){
    add_action('admin_notices', function () use ($message, $level) {
        echo '<div class="notice notice-' . $level . ' is-dismissible"><p>' . $message . '</p></div>';
    });
}