<?php
/*
	Plugin Name: Scripts Organizer
	Version: 3.5.3
	Description: Advanced Code editor for Wordpress
	Author: DPlugins
	Author URI: https://dplugins.com/
	License: GNU General Public License v2.0 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
    Text Domain: scorg
*/

/**
 *  Make sure the plugin is accessed through the appropriate channels
 */
defined('ABSPATH') || die;

// EDD Plugin const
define('SCORG_SAMPLE_STORE_URL', 'https://dplugins.com/');
define('SCORG_SAMPLE_ITEM_ID', 423);
define('SCORG_SAMPLE_ITEM_NAME',  'Scripts Organizer');
define('SCORG_SAMPLE_PLUGIN_LICENSE_PAGE', 'scorg-license');

define('SCORG_AUTHOR', 'devusrmk');
define('SCORG_PLUGINVERSION',  '3.5.3');


// Plugin const
define('SCORG_UPDATER', __FILE__);
define('SCORG_BASE',	plugin_basename(__FILE__));
define('SCORG_URL',	plugin_dir_url(__FILE__));
define('SCORG_DIR',	plugin_dir_path(__FILE__));

$scorg_files_path = get_option('scorg_files_path');
$upload_dir = wp_upload_dir();
if (!empty($scorg_files_path)) {
	$upload_dir = array();
	$upload_dir['basedir'] = ABSPATH . $scorg_files_path;
	$upload_dir['baseurl'] = get_site_url() . '/' . $scorg_files_path;
} else {
	$scorg_files_path = 'scripts-organizer';
	$upload_dir['basedir'] = $upload_dir['basedir'] . '/' . $scorg_files_path;
	$upload_dir['baseurl'] = $upload_dir['baseurl'] . '/' . $scorg_files_path;
}
define('SCORG_ERROR_LOG_CHECK',	$scorg_files_path);
define('SCORG_UPLOADS_DIR',	$upload_dir['basedir']);
define('SCORG_UPLOADS_DIR_SCSS', $upload_dir['basedir'] . '/scss');
define('SCORG_UPLOADS_DIR_CSS',	$upload_dir['basedir'] . '/css');
define('SCORG_UPLOADS_DIR_JS',	$upload_dir['basedir'] . '/js');
define('SCORG_UPLOADS_DIR_OLD',	SCORG_DIR . 'code-snippets');
define('SCORG_UPLOADS_URL',	$upload_dir['baseurl']);
define('SCORG_UPLOADS_URL_SCSS',	$upload_dir['baseurl'] . '/scss');
define('SCORG_UPLOADS_URL_CSS',	$upload_dir['baseurl'] . '/css');
define('SCORG_UPLOADS_URL_JS',	$upload_dir['baseurl'] . '/js');
define('SCORG_UPLOADS_URL_OLD',	SCORG_URL . 'code-snippets');

// Load Admin
if (!class_exists('EDD_SL_Plugin_Updater')) {
	// load our custom updater
	include(dirname(__FILE__) . '/admin/edd/EDD_SL_Plugin_Updater.php');
}
require_once SCORG_DIR . 'admin/admin.php';
if (get_option('scorg_oxy_stylesheets') == "yes") {
	require_once SCORG_DIR . 'admin/admin__oxy_stylesheets.php';
}
require_once SCORG_DIR . 'admin/admin__feature-reorder.php';
//require_once SCORG_DIR . 'includes/scorg-post.php';
require_once SCORG_DIR . 'admin/feature__oxy-colors-picker.php';
require_once SCORG_DIR . 'admin/feature__css-variables-picker.php';
require_once SCORG_DIR . 'admin/scorg_licence.php';

/**
 * Including php scripts everywhere file if licnese is active
 */
$status  = get_option('scorg_license_status');
if ($status !== false && $status == 'valid') {
	require_once SCORG_DIR . 'includes/scorg-php-scripts-everywhere.php';
	require_once SCORG_DIR . 'includes/scorg-php-scripts-admin-only.php';
	require_once SCORG_DIR . 'includes/scorg-header-footer-files-admin.php';
	require_once SCORG_DIR . 'includes/scorg-script-display-admin.php';
	require_once SCORG_DIR . 'admin/feature__code-snippets-import.php';
	require_once SCORG_DIR . 'admin/feature__advanced-scripts-import.php';
	require_once SCORG_DIR . 'admin/feature__scripts-organizer-import.php';
	require_once SCORG_DIR . 'admin/feature__export.php';
	require_once SCORG_DIR . 'admin/feature__trash.php';
}

function SCORG_top_bars()
{
	$screen = get_current_screen();
	if ($screen->id == "scorg" || $screen->id == "scorg_scss") {
		include("admin/sidebars.php");
	}
}
add_action('admin_footer', 'SCORG_top_bars');

function SCORG_can_include_php()
{
	$scorg_safemode = get_option('scorg_safemode');
	if ($scorg_safemode == "yes" || (isset($_GET['scorg_safemode']) && $_GET['scorg_safemode'] == "yes")) {
		return "yes";
	} else {
		return "no";
	}
}

function SCORG_sl_sample_plugin_updater()
{

	// create scripts-organizer folder in uploads directory 
	if (!is_dir(SCORG_UPLOADS_DIR)) {
		mkdir(SCORG_UPLOADS_DIR);
	}

	if (!is_dir(SCORG_UPLOADS_DIR_SCSS)) {
		mkdir(SCORG_UPLOADS_DIR_SCSS);
	}

	if (!is_dir(SCORG_UPLOADS_DIR_CSS)) {
		mkdir(SCORG_UPLOADS_DIR_CSS);
	}

	if (!is_dir(SCORG_UPLOADS_DIR_JS)) {
		mkdir(SCORG_UPLOADS_DIR_JS);
	}

	// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
	$doing_cron = defined('DOING_CRON') && DOING_CRON;
	if (!current_user_can('manage_options') && !$doing_cron) {
		return;
	}

	// retrieve our license key from the DB
	$license_key = trim(get_option('scorg_license_key'));

	// setup the updater
	$SCORG_updater = new EDD_SL_Plugin_Updater(
		SCORG_SAMPLE_STORE_URL,
		SCORG_UPDATER,
		array(
			'version' => SCORG_PLUGINVERSION,                    // current version number
			'license' => $license_key,             // license key (used get_option above to retrieve from DB)
			'item_id' => SCORG_SAMPLE_ITEM_ID,       // ID of the product
			'author'  => SCORG_AUTHOR, // author of this plugin
			'beta'    => false,
		)
	);
}
add_action('init', 'SCORG_sl_sample_plugin_updater');

function SCORG_delete_move_code_snippets()
{
	if (file_exists(SCORG_UPLOADS_DIR_OLD)) {
		$files = scandir(SCORG_UPLOADS_DIR_OLD);
		$files = array_diff(scandir(SCORG_UPLOADS_DIR_OLD), array('.', '..'));
		$files = array_values($files);
		$SCORG_Post = new SCORG_Post();
		if (!is_dir(SCORG_UPLOADS_DIR)) {
			mkdir(SCORG_UPLOADS_DIR);
		}
		foreach ($files as $script_file) {
			$file = $script_file;
			$script_file = str_replace(".php", "", $script_file);
			$exp_script_file = explode("-", $script_file);
			$total_index = count($exp_script_file);
			$last_index = $total_index - 1;
			$post_id = $exp_script_file[$last_index];
			if (get_post()) {
				$php_script = get_post_meta($post_id, 'SCORG_php_script', true);
				if (!empty($php_script)) {
					$php_script = "<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly\n ?>\n" . $php_script;
					$SCORG_Post->save_php_script($post_id, $php_script);
				}
			}
			unlink(SCORG_UPLOADS_DIR_OLD . '/' . $file);
		}

		rmdir(SCORG_UPLOADS_DIR_OLD);
	}
}
add_action('init', 'SCORG_delete_move_code_snippets');

add_filter('style_loader_tag', 'SCORG_monaco_editor_stylesheet', 10, 2);
function SCORG_monaco_editor_stylesheet($html, $handle)
{
	if ($handle === 'monaco') {
		$html = str_replace('/>', 'data-name="vs/editor/editor.main" />', $html);
	}
	return $html;
}

function read_functions_json(){
	//  Initiate curl
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, SCORG_URL.'admin/js/functions.json');
	$result = curl_exec($ch);
	$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	if($http_status == "200"){
		return $result;
	} else {
		return json_encode(array());
	}
}

function SCORG_selectively_enqueue_admin_script($hook_suffix)
{
	$screen = get_current_screen();

	if ($screen->id == "scorg" || $screen->id == "scorg_scss" || $screen->id == "scorg_ga") {
		wp_enqueue_style('SCORG_single_css', SCORG_URL . '/admin/css/single.css', array(), SCORG_PLUGINVERSION);
		wp_enqueue_script('mousetrap_scripts', SCORG_URL . 'admin/js/mousetrap.min.js', array(), SCORG_PLUGINVERSION, true);
		/* unregister Metabox.io styles */
		wp_dequeue_style('rwmb');
		wp_dequeue_style('rwmb-switch');
		wp_dequeue_style('rwmb-input-list');
		wp_dequeue_style('rwmb-select');
		wp_dequeue_style('rwmb-select-tree');
		wp_dequeue_style('rwmb-select-advanced');
		wp_dequeue_style('jquery-ui-core');
		wp_dequeue_style('jquery-ui-theme');
		wp_dequeue_style('jquery-ui-datepicker');
		wp_dequeue_style('rwmb-date');
		wp_dequeue_style('jquery-ui-slider');
		wp_deregister_script('rwmb-select-tree');
		wp_deregister_script('rwmb-select-advanced');
		wp_deregister_script('selectwoo-js');
		wp_enqueue_style('monaco', SCORG_URL . 'admin/js/node_modules/monaco-editor/min/vs/editor/editor.main.css', array(), SCORG_PLUGINVERSION);

		$vs_theme = "light";
		$vs_font_size = "14";
		$scorg_darkmode = get_option('scorg_darkmode');
		$scorg_fontsize = get_option('scorg_fontsize');
		$scorg_css_variables = get_option('scorg_css_variables');
		if (empty($scorg_css_variables)) {
			$scorg_css_variables = array();
		}
		if ($scorg_darkmode == "yes") {
			$vs_theme = "dark";
		}
		if ($scorg_fontsize == "yes") {
			$vs_font_size = get_option('scorg_font_value');
		}
		wp_enqueue_script("split", SCORG_URL . 'admin/js/split.min.js', array('jquery'), SCORG_PLUGINVERSION, true);
		wp_enqueue_script("monaco-loader", SCORG_URL . 'admin/js/node_modules/monaco-editor/min/vs/loader.js', array('jquery'), SCORG_PLUGINVERSION, true);
		wp_enqueue_script("monaco-editor-nls", SCORG_URL . 'admin/js/node_modules/monaco-editor/min/vs/editor/editor.main.nls.js', array('jquery'), SCORG_PLUGINVERSION, true);
		wp_enqueue_script("monaco-editor", SCORG_URL . 'admin/js/node_modules/monaco-editor/min/vs/editor/editor.main.js', array('jquery'), SCORG_PLUGINVERSION, true);
		wp_enqueue_script("monaco-emmet", SCORG_URL . 'admin/js/node_modules/emmet-monaco-es/dist/emmet-monaco.min.js', array('monaco-editor'), SCORG_PLUGINVERSION, true);
		wp_enqueue_script("rwmb-select-advanced", SCORG_URL . 'plugins/meta-box/js/select-advanced.js', array('rwmb-select2', 'rwmb-select', 'rwmb-select2-i18n'), SCORG_PLUGINVERSION, true);
		wp_enqueue_script("select2-sortable", SCORG_URL . 'admin/js/select2-sortable-min.js', array('rwmb-select-advanced'), SCORG_PLUGINVERSION, true);
		wp_enqueue_script("resize", SCORG_URL . 'admin/js/jquery-resizable.min.js', array('jquery'), SCORG_PLUGINVERSION);
	}
	if ($screen->id == "scorg" || $screen->id == "scorg_scss") {
		// Get hooks as JSON:
		$functions_json = read_functions_json();
		
		// Convert hooks to PHP:
		$functions = json_decode( $functions_json, true, JSON_UNESCAPED_SLASHES );
		
		wp_enqueue_script("SCORG_ajax_scripts", SCORG_URL . 'admin/js/admin.js', array('jquery'), SCORG_PLUGINVERSION);
		wp_localize_script('SCORG_ajax_scripts', 'SCORG_ajax', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'scorg_vs' => SCORG_URL . 'admin/js/node_modules/monaco-editor/min/vs',
			'vs_theme' => $vs_theme,
			'vs_font_size' => $vs_font_size,
			'scorg_css_variables' => json_encode($scorg_css_variables),
			'SCORG_nonce' => wp_create_nonce('ajax-nonce'),
			'functions_json' => json_encode($functions)
		));
	}
	
	$scorg_monaco_in_theme = get_option('scorg_monaco_in_theme');
	$scorg_monaco_in_plugin = get_option('scorg_monaco_in_plugin');
	if ($scorg_monaco_in_theme == "yes" || $scorg_monaco_in_plugin == "yes") {
		// Only load scripts on Theme/Plugin editor page.
		if (($scorg_monaco_in_theme == "yes" && "theme-editor.php" === $hook_suffix) || ($scorg_monaco_in_plugin == "yes" && "plugin-editor.php" === $hook_suffix)) {
			SCORG_load_monaco_in_theme_plugin_editor();
		}
	}

	if ($screen->id == "scorg" || $screen->id == "scorg_ga") {
		wp_enqueue_style('SCORG-jquery-ui', SCORG_URL . '/admin/css/jquery-ui.css', array(), SCORG_PLUGINVERSION);
	}

	if (in_array($screen->id, array('edit-scorg', 'edit-scorg_scss', 'edit-scorg_ga'))) {
		wp_enqueue_style('SCORG_admin_css', SCORG_URL . '/admin/css/admin.css', array(), SCORG_PLUGINVERSION);
	}
	if ($screen->id == "scripts-organizer_page_scorg_import_export") {
		wp_enqueue_style('SCORG_import_css', SCORG_URL . '/admin/css/import.css', array(), SCORG_PLUGINVERSION);
	}
	if ($screen->id == "toplevel_page_scorg-license") {
		wp_enqueue_style('SCORG_license_css', SCORG_URL . '/admin/css/license.css', array(), SCORG_PLUGINVERSION);
	}
	if ($screen->id == "scripts-organizer_page_scorg_scripts_manager") {
		wp_enqueue_style('SCORG_manager_css', SCORG_URL . '/admin/css/scripts_manager.css', array(), SCORG_PLUGINVERSION);
	}
	if ($screen->id == "scripts-organizer_page_scorg_features") {
		wp_enqueue_style('SCORG_features_css', SCORG_URL . '/admin/css/features.css', array(), SCORG_PLUGINVERSION);
	}

	if ($screen->id == "edit-scorg_scss" || $screen->id == "edit-scorg") {
		wp_enqueue_script("SCORG_edit", SCORG_URL . 'admin/js/edit.js', array('jquery'), SCORG_PLUGINVERSION);
		wp_localize_script("SCORG_edit", 'SCORG_ajax', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'SCORG_nonce' => wp_create_nonce('ajax-nonce'),
		));
	}

	if ($screen->id == "scripts-organizer_page_scorg_features") {
		wp_enqueue_script("SCORG_features", SCORG_URL . 'admin/js/features.js', array('jquery'), SCORG_PLUGINVERSION);
		wp_localize_script("SCORG_features", 'SCORG_ajax', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'SCORG_nonce' => wp_create_nonce('ajax-nonce'),
		));
	}
	if ($screen->id == "scripts-organizer_page_scorg_scripts_manager") {
		wp_enqueue_script('jquery');
		wp_enqueue_media();
		wp_enqueue_style('SCORG-reorder-jquery-ui', SCORG_URL . '/admin/css/jquery-ui.css', array(), time());
		wp_enqueue_script('SCORG-reorder-jquery-ui', SCORG_URL . 'admin/js/jquery-ui.min.js', array('jquery'), time());
		wp_enqueue_script("SCORG_scripts-manager", SCORG_URL . 'admin/js/scripts-manager.js', array('jquery'), SCORG_PLUGINVERSION);
		wp_localize_script("SCORG_scripts-manager", 'SCORG_ajax', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'SCORG_nonce' => wp_create_nonce('ajax-nonce'),
		));
	}

	$scorg_livereload = get_option('scorg_livereload');
	if ($scorg_livereload == "yes" && !in_array($screen->id, array('scorg_scss', 'scorg'))) {
		wp_enqueue_script("SCORG_live-reload", SCORG_URL . 'admin/js/live-reload.js', array('jquery'), SCORG_PLUGINVERSION);
	}
}

add_action('admin_enqueue_scripts', 'SCORG_selectively_enqueue_admin_script', 1000);

function SCORG_live_reload()
{
	if (is_user_logged_in() && current_user_can('administrator')) {
		$scorg_livereload = get_option('scorg_livereload');
		if ($scorg_livereload == "yes") {
			wp_enqueue_script("SCORG_live-reload", SCORG_URL . 'admin/js/live-reload.js', array('jquery'), SCORG_PLUGINVERSION);
		}
	}
}
add_action('wp_enqueue_scripts', 'SCORG_live_reload', 9);

/**
 * Adds one or more classes to the body tag in the theme/plugin editor.
 */
function SCORG_theme_plugin_body_class($classes)
{
	$screen = get_current_screen();
	$classes_append = "";

	$scorg_monaco_in_theme = get_option('scorg_monaco_in_theme');
	$scorg_monaco_in_plugin = get_option('scorg_monaco_in_plugin');
	if ($scorg_monaco_in_theme == "yes" || $scorg_monaco_in_plugin == "yes") {
		// Only load scripts on Theme/Plugin editor page.
		if (($scorg_monaco_in_theme == "yes" && "theme-editor" === $screen->base) || ($scorg_monaco_in_plugin == "yes" && "plugin-editor" === $screen->base)) {
			$classes_append .= " dplugins--monaco--active ";
		}
	}

	$classes .= " $classes_append";

	return $classes;
}
add_filter('admin_body_class', 'SCORG_theme_plugin_body_class');

function SCORG_load_monaco_in_theme_plugin_editor()
{
	$vs_theme = "light";
	$vs_font_size = "14";
	$scorg_darkmode = get_option('scorg_darkmode');
	$scorg_fontsize = get_option('scorg_fontsize');
	if ($scorg_darkmode == "yes") {
		$vs_theme = "dark";
	}
	if ($scorg_fontsize == "yes") {
		$vs_font_size = get_option('scorg_font_value');
	}
	$curr_url = $_SERVER['REQUEST_URI'];
	$file_type = pathinfo($curr_url, PATHINFO_EXTENSION);
	wp_enqueue_style('monaco', SCORG_URL . 'admin/js/node_modules/monaco-editor/min/vs/editor/editor.main.css', array(), SCORG_PLUGINVERSION);
	wp_enqueue_style('SCORG_theme_plugin_editor_css', SCORG_URL . 'admin/css/theme-plugin-editor.css', array(), SCORG_PLUGINVERSION);
	wp_enqueue_script('mousetrap_scripts', SCORG_URL . 'admin/js/mousetrap.min.js', array(), SCORG_PLUGINVERSION, true);
	wp_enqueue_script("monaco-loader", SCORG_URL . 'admin/js/node_modules/monaco-editor/min/vs/loader.js', array('jquery'), SCORG_PLUGINVERSION, true);
	wp_enqueue_script("monaco-editor-nls", SCORG_URL . 'admin/js/node_modules/monaco-editor/min/vs/editor/editor.main.nls.js', array('jquery'), SCORG_PLUGINVERSION, true);
	wp_enqueue_script("monaco-editor", SCORG_URL . 'admin/js/node_modules/monaco-editor/min/vs/editor/editor.main.js', array('jquery'), SCORG_PLUGINVERSION, true);
	wp_enqueue_script("monaco-emmet", SCORG_URL . 'admin/js/node_modules/emmet-monaco-es/dist/emmet-monaco.min.js', array('monaco-editor'), SCORG_PLUGINVERSION, true);
	wp_enqueue_script("SCORG_theme_plugin_editor", SCORG_URL . 'admin/js/theme-plugin-editor.js', array('jquery'), SCORG_PLUGINVERSION);
	wp_localize_script('SCORG_theme_plugin_editor', 'SCORG_them_plugin', array(
		'scorg_vs' => SCORG_URL . 'admin/js/node_modules/monaco-editor/min/vs',
		'vs_theme' => $vs_theme,
		'vs_font_size' => $vs_font_size,
		'file_type' => $file_type,
	));
}

/*================================================
=            Clean up After Uninstall            =
================================================*/

function SCORG_delete_plugin_database_tables()
{
	global $wpdb;
	$options_table = $wpdb->prefix . 'options';
	$db_fields = array(
		"scorg_license_key",
		"scorg_license_status",
	);
	$db_fields = join("','", $db_fields);
	$wpdb->query("DELETE FROM $options_table WHERE option_name IN('" . $db_fields . "')");
}

register_uninstall_hook(__FILE__, 'SCORG_delete_plugin_database_tables');

function SCORG_set_safe_mode()
{
	if (isset($_GET['scorg_safemode'])) {
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		if (is_user_logged_in()) {
			if (current_user_can('administrator')) {
				$scorg_safemode = get_option('scorg_safemode');
				$safe_mode = $_GET['scorg_safemode'];
				if ($safe_mode != $scorg_safemode) {
					update_option('scorg_safemode', $safe_mode);
				}
			}
		}
	}
}
add_action('admin_head', 'SCORG_set_safe_mode', -1);

function SCORG_set_safe_mode_style()
{
?>
	<style type="text/css">
		/*Safe mode quick fix*/
		li#wp-admin-bar-scorg-safemode-admin-bar.active {
			background: #ffe200 !important;
		}

		li#wp-admin-bar-scorg-safemode-admin-bar.active:hover a {
			background: orange !important;
		}

		li#wp-admin-bar-scorg-safemode-admin-bar.active a {
			color: black !important;
		}
	</style>
<?php
}
add_action('admin_head', 'SCORG_set_safe_mode_style', 100);

function SCORG_front_end_only($query_args)
{
	if ((isset($_GET['ct_builder']) && $_GET['ct_builder'] == true) || (isset($_GET['bricks'])) || (isset($_GET['action']) && $_GET['action'] == "elementor")) {
		$query_args['meta_query'][] = array(
			array(
				'relation' => 'OR',
				array(
					'key' => 'SCORG_only_frontend',
					'value' => 0,
					'compare' => '=',
				),
				array(
					'key' => 'SCORG_only_frontend',
					'compare' => 'NOT EXISTS',
				),
			)
		);
	}

	return $query_args;
}

function SCORG_safemode_bar_action($admin_bar)
{
	if (current_user_can('administrator')) {
		$label = __('Enable Safemode', 'scorg');
		$class = 'inactive';
		$url = esc_url(add_query_arg('scorg_safemode', 'yes'));
		$scorg_safemode = get_option('scorg_safemode');
		if ($scorg_safemode == "yes") {
			$label = __('Disable Safemode', 'scorg');
			$class = 'active';
			$url = esc_url(add_query_arg('scorg_safemode', 'no'));
		}
		$admin_bar->add_menu(array(
			'id'    => 'scorg-safemode-admin-bar',
			'parent' => 'top-secondary',
			'title' => $label,
			'href'  => esc_url($url),
			'meta'  => array(
				'title' => $label,
				'class' => 'scorg-safemode-admin-bar ' . $class,
			),
		));
	}
}
add_action('admin_bar_menu', 'SCORG_safemode_bar_action', 100);
SCORG_set_safe_mode();

/* color picker options */

function SCORG_get_oxygen_colors()
{
	global $oxygen_vsb_global_colors;
	$colors = [];

	if (!empty($oxygen_vsb_global_colors)) {
		foreach ($oxygen_vsb_global_colors["sets"] as $colors_set) {
			list($colors_set_id, $colors_set_name) = array_values($colors_set);

			$colors['sets'][$colors_set_name] = [];

			foreach ($oxygen_vsb_global_colors["colors"] as $color) {
				if ($color["set"] == $colors_set_id) {
					$colors['sets'][$colors_set_name][] = $color;
				}
			}
		}

		foreach ($oxygen_vsb_global_colors["colors"] as $color) {
			list($color_id, $color_name, $color_value) = array_values($color);
			$colors['colors'][$color_id] = $color_value;
		}
	}

	return $colors;
}

function SCORG_elementor_colors()
{
	$elementor_colors = array();
	$SCORG_elementor_active = get_option('SCORG_elementor_active');
	if ($SCORG_elementor_active == "yes") {
		global $wpdb;
		$postmeta = $wpdb->prefix . 'postmeta';
		$all_colors = $wpdb->get_results("SELECT meta_value FROM $postmeta WHERE meta_key = '_elementor_page_settings'", OBJECT);
		if (!empty($all_colors)) {
			foreach ($all_colors as $elem_colors) {
				$colors = unserialize($elem_colors->meta_value);
				foreach ($colors as $key => $elem) {
					if (strpos($key, 'colors') !== false) {
						$elementor_colors['sets'][$key] = $elem;
						foreach ($elem as $color) {
							if (count($color) == 2) {
								list($_id, $color_value) = array_values($color);
							}
							if (count($color) == 3) {
								list($_id, $title, $color_value) = array_values($color);
							}
							$elementor_colors['colors'][$_id] = $color_value;
						}
					}
				}
			}
			//echo "<pre>"; print_r($elementor_colors); "</pre>"; exit;
		}
	}
	return $elementor_colors;
}

function SCORG_bricks_colors()
{
	$bricks_colors = array();
	$SCORG_bricks_active = get_option('SCORG_bricks_active');
	if ($SCORG_bricks_active == "yes") {
		$bricks_theme_styles = get_option('bricks_theme_styles');
		if (!empty($bricks_theme_styles)) {
			foreach ($bricks_theme_styles as $theme => $settings) {
				$i = 0;
				if (isset($settings['settings']['colors'])) {
					foreach ($settings['settings']['colors'] as $key => $color) {
						$bricks_colors['sets'][$theme][$i]['title'] = $key;
						$bricks_colors['sets'][$theme][$i]['color'] = $color['hex'];
						$bricks_colors['sets'][$theme][$i]['_id'] = $theme . '_' . $key;
						$bricks_colors['colors'][$theme . '_' . $key] = $color['hex'];
						$i++;
					}
				}
			}
		}
	}
	return $bricks_colors;
}

function SCORG_process_colors($code)
{
	$SCORG_elementor_active = get_option('SCORG_elementor_active');
	$SCORG_bricks_active = get_option('SCORG_bricks_active');
	if (defined('CT_VERSION')) {
		$oxy_colors = SCORG_get_oxygen_colors();
		if (!empty($oxy_colors['colors'])) {
			$colors = $oxy_colors['colors'];
			$callback = function ($matches) use ($colors) {
				$id = $matches[1];
				$color = $colors[$id] ?? "";

				return $color;
			};
			$code = preg_replace_callback('/oxycolor\((\d+)\)/i', $callback, $code);
		}
	}
	if ($SCORG_elementor_active == "yes") {
		$elementor_colors = SCORG_elementor_colors();
		if (!empty($elementor_colors['colors'])) {
			$colors = $elementor_colors['colors'];
			$callback = function ($matches) use ($colors) {
				$id = $matches[1];
				$color = $colors[$id] ?? "";

				return $color;
			};
			$code = preg_replace_callback('/elementor\((.*?)\)/i', $callback, $code);
		}
	}
	if ($SCORG_bricks_active == "yes") {
		$bricks_colors = SCORG_bricks_colors();
		if (!empty($bricks_colors['colors'])) {
			$colors = $bricks_colors['colors'];
			$callback = function ($matches) use ($colors) {
				$id = $matches[1];
				$color = $colors[$id] ?? "";

				return $color;
			};
			$code = preg_replace_callback('/bricks\((.*?)\)/i', $callback, $code);
		}
	}
	return $code;
}

function SCORG_color_picker_option()
{
	$SCORG_elementor_active = get_option('SCORG_elementor_active');
	$SCORG_bricks_active = get_option('SCORG_bricks_active');
	if (defined('CT_VERSION') || $SCORG_elementor_active == "yes" || $SCORG_bricks_active == "yes") {
		return true;
	} else {
		return false;
	}
}

function SCORG_check_plugin_activeness()
{
	// elementor
	$SCORG_elementor_active_new = "";
	$SCORG_elementor_active_old = get_option('SCORG_elementor_active');
	if (is_plugin_active('elementor/elementor.php')) {
		$SCORG_elementor_active_new = 'yes';
	} else {
		$SCORG_elementor_active_new = 'no';
	}
	if ($SCORG_elementor_active_new != $SCORG_elementor_active_old) {
		update_option('SCORG_elementor_active', $SCORG_elementor_active_new, false);
	}

	// bricks
	$SCORG_bricks_active_new = "";
	$SCORG_bricks_active_old = get_option('SCORG_bricks_active');
	if (defined('BRICKS_VERSION')) {
		$SCORG_bricks_active_new = 'yes';
	} else {
		$SCORG_bricks_active_new = 'no';
	}
	if ($SCORG_bricks_active_new != $SCORG_bricks_active_old) {
		update_option('SCORG_bricks_active', $SCORG_bricks_active_new, false);
	}
}
add_action('admin_init', 'SCORG_check_plugin_activeness');


$post_type = 'scorg'; // Change this to a post type you'd want
function one_columns_scorg($selected)
{
	// if( false === $selected ) { 
	//     return 1; // Use 1 column if user hasn't selected anything in Screen Options
	// }
	// return $selected; // Use what the user wants

	return 1; // Use 1 column if user hasn't selected anything in Screen Options
}
add_filter("get_user_option_screen_layout_{$post_type}", 'one_columns_scorg');


$post_type = 'scorg_scss'; // Change this to a post type you'd want
function one_columns_scorg_scss($selected)
{
	return 1; // Use 1 column if user hasn't selected anything in Screen Options
}
add_filter("get_user_option_screen_layout_{$post_type}", 'one_columns_scorg_scss');

function preview_screen_sizes()
{
	$html = '';

	$all_sizes = array(
		'Phone' => array(
			array(
				'text' => 'iPhone 13 Max 428 x 926',
				'width' => 428,
				'height' => 926,
			),
			array(
				'text' => 'iPhone 13 390 x 844',
				'width' => 390,
				'height' => 844,
			),
			array(
				'text' => 'iPhone 13 mini 375 x 812',
				'width' => 375,
				'height' => 812,
			),
			array(
				'text' => 'iPhone 11 Max 414 x896',
				'width' => 414,
				'height' => 896,
			),
			array(
				'text' => 'iPhone 11 375 x 812',
				'width' => 375,
				'height' => 812,
			),
			array(
				'text' => 'iPhone SE 320 x 568',
				'width' => 320,
				'height' => 568,
			),
			array(
				'text' => 'iPhone 8 Plus 414 x736',
				'width' => 414,
				'height' => 736,
			),
			array(
				'text' => 'iPhone 8 375 x 667',
				'width' => 375,
				'height' => 667,
			),
			array(
				'text' => 'Google Pixel 2 411x 731',
				'width' => 411,
				'height' => 731,
			),
			array(
				'text' => 'Google Pixel 2 XL 411 x 823',
				'width' => 411,
				'height' => 823,
			),
			array(
				'text' => 'Android 360 x 640',
				'width' => 360,
				'height' => 640,
			),
		),
		'Tablet' => array(
			array(
				'text' => 'iPad mini 768 x1024',
				'width' => 768,
				'height' => 1024,
			),
			array(
				'text' => 'iPad Pro 11" 834 x1194',
				'width' => 834,
				'height' => 1194,
			),
			array(
				'text' => 'iPad Pro 12.9" 1024 x1366',
				'width' => 1024,
				'height' => 1366,
			),
			array(
				'text' => 'Surface Pro 3 1440 x 990',
				'width' => 1440,
				'height' => 990,
			),
			array(
				'text' => 'Surface Pro 4 1368 x 912',
				'width' => 1368,
				'height' => 912,
			),
		),
		'Desktop' => array(
			array(
				'text' => 'Desktop 1440 x1024',
				'width' => 1440,
				'height' => 1024,
			),
			array(
				'text' => 'MacBook 1152 x 700',
				'width' => 1152,
				'height' => 700,
			),
			array(
				'text' => 'MacBook Pro 1440 x 900',
				'width' => 1440,
				'height' => 900,
			),
			array(
				'text' => 'Surface Book 1500 x1000',
				'width' => 1500,
				'height' => 1000,
			),
			array(
				'text' => 'iMac 1280 x 720',
				'width' => 1280,
				'height' => 720,
			),
			array(
				'text' => 'Full Width',
				'width' => '',
				'height' => '',
			),
		)
	);

	$html .= '<div class="rwmb-input preview-size-wrap"> <select id="preview-size">';
	foreach ($all_sizes as $key => $sizes) {
		$html .= '<optgroup label="' . $key . '">';
		foreach ($sizes as $size) {
			$html .= '<option ' . (($size['text'] == "Full Width") ? 'selected' : '') . ' data-width="' . $size['width'] . '" data-height="' . $size['height'] . '">' . $size['text'] . '</option>';
		}
		$html .= '</optgroup>';
	}
	$html .= '</select></div>';

	return $html;
}

function preview_screen_zooms()
{
	$html = '';

	$all_zoom_levels = array(
		'1' => '100%',
		'0.75' => '75%',
		'0.50' => '50%',
	);

	$html .= '<div class="rwmb-input"> <select id="preview-zoom">';
	foreach ($all_zoom_levels as $key => $zoom) {
		$html .= '<option ' . (($key == "1") ? 'selected' : '') . ' data-zoom="' . $key . '">' . $zoom . '</option>';
	}
	$html .= '</select></div>';

	return $html;
}

function SCORG_is_base64($data)
{
	if (base64_encode(base64_decode($data, true)) === $data) {
		return base64_decode($data);
	} else {
		return $data;
	}
}

function SCORG_get_file_time($file_path)
{
	$ver = '';
	try {
		$ver = filemtime($file_path);
	}
	//catch exception
	catch (Exception $e) {
		$ver = SCORG_PLUGINVERSION;
	}

	return $ver;
}

// regenerate notice
/* function SCORG_regenerate_notice(){
	$status  = get_option( 'scorg_license_status' );
	if( $status !== false && $status == 'valid' ) {
		$SCORG_regenerated = get_option('SCORG_regenerated');
		if($SCORG_regenerated != "yes"){
			echo '<div class="dplugins--notice notice notice-warning is-dismissible">
				<p>Before using the latest version of Scripts Organizer, you need to <strong><a href="'.admin_url().'admin.php?page=scorg_features">Regenerate Files</a></strong></p>
			</div>';
		}
	}
}
add_action('admin_notices', 'SCORG_regenerate_notice'); */