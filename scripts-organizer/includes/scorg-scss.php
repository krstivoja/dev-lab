<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_SCSS')){
	class SCORG_SCSS {
		public function __construct(){
			add_filter( 'use_block_editor_for_post_type', array($this, 'SCSS_disable_gutenberg'), 10, 2 );
			add_action( 'manage_scorg_scss_posts_custom_column' , array($this, 'SCSS_custom_scorg_scss_column'), 10, 2 );
			add_filter( 'rwmb_before', array($this, 'SCSS_rwmb_before'), 100 );
			add_filter( 'rwmb_after', array($this, 'SCSS_rwmb_after'), 100 );
			add_filter( 'rwmb_outer_html', array($this, 'SCSS_outer_html'), 100, 3 );
			add_filter( 'admin_body_class', array($this, 'SCSS_admin_body_class') );
			add_filter( 'rwmb_meta_boxes', array($this, 'SCSS_order_details_meta_boxes') );
			add_filter( 'manage_scorg_scss_posts_columns', array($this, 'SCSS_set_custom_edit_scorg_scss_columns') );
			add_filter( 'manage_edit-scorg_scss_sortable_columns', array($this, 'SCSS_set_sortable_columns') );
			add_action( 'admin_head', array($this, 'meta_box_filter') );
			add_action( 'wp_ajax_saveSCSSScript', array($this, 'saveSCSSScript_func') );
			add_action( 'wp_ajax_syncFromFile', array($this, 'syncFromFile_func') );
			add_action( 'admin_bar_menu', array($this, 'scss_admin_bar'), 99 );
			add_action( 'admin_head-edit.php', array($this, 'export_button') );
			add_filter( 'bulk_actions-edit-scorg_scss', array($this, 'bulk_actions') );
			add_filter( 'post_row_actions', array($this, 'row_actions'), 10, 2);
			add_filter( 'rwmb_SCSS_scss_scripts_value', array($this, 'encode_scss_field_value') );
			add_filter( 'rwmb_field_meta', array($this, 'decode_field_value'), 11, 3 );
		}

		public function decode_field_value( $meta, $field, $saved ) {
			if ( 'SCSS_scss_scripts' != $field['id'] ) {
				return $meta;
			}
			return SCORG_is_base64($meta);
		}

		public function encode_scss_field_value(){
			global $post;
			if($post->post_type == "scorg_scss"){
				// get data
				$scss_script = isset( $_POST['SCSS_scss_scripts'] ) ? $_POST['SCSS_scss_scripts'] : null;

				// do something with your data, multiple by 100 for instance
				$modified_data = base64_encode(stripslashes($scss_script));

				// return the modified data
				return $modified_data;
			}
		}

		public function row_actions($actions, $post){
			if ($post->post_type == "scorg_scss"){
				$scorg_scss_actions = array();
				foreach($actions as $key => $val){
					if($key != "trash"){
						$scorg_scss_actions[$key] = $val;
					} else {
						$scorg_scss_actions['export_scorg_scss'] = '<a href="'.admin_url().'edit.php?post_type=scorg_scss&scss_export=single&id='.$post->ID.'">'.__('Export', 'scorg').'</a>';
						$scorg_scss_actions[$key] = $val;
					}
				}
				return $scorg_scss_actions;
			}

			return $actions;
		}

		public function bulk_actions($bulk){
			$bulk['scss_bulk'] = 'Export Selected';
			$bulk['scss_bulk_disable'] = 'Disable Selected';
			$bulk['scss_bulk_delete'] = 'Move to Trash';
			return $bulk;
		}

		public function export_button(){
			global $current_screen;
			// Not our post type, exit earlier
			if ('scorg_scss' != $current_screen->post_type)
				return;
			
			$code_blocks = get_posts(array( 'post_type' => 'scorg', 'posts_per_page' => 1 ));
			if($code_blocks){
				$export_all_url = add_query_arg('scss_export', 'all', site_url() . '/wp-admin/edit.php?post_type=scorg_scss');
				?>
				<script type="text/javascript">
					jQuery(document).ready(function ($) {
						$('.tablenav.top .tablenav-pages').before('<div class="alignleft actions"><a href="<?php echo esc_url($export_all_url); ?>" class="of-tag button">Export All</a></div>');
					});
				</script>
				<?php
			} else {
				return;
			}
		}

		public function scss_admin_bar($wp_admin_bar){
			$scorg_scss_partials = get_option('scorg_scss_partials');
			if($scorg_scss_partials == "yes"){
				$parent_menu_args = array(
			        'id' => 'scss-admin-bar',
			        'title' => 'SCSS Partials',
			        'href' => site_url().'/wp-admin/edit.php?post_type=scorg_scss',
			        'meta' => array(
			            'class' => 'scorg-admin-bar',
			            'title' => __('SCSS Partials', 'scorg')
			        )
			    );
			    $wp_admin_bar->add_node($parent_menu_args);

			    // Add Oxygen templates
			    $scss_partials = get_posts(array(
			        'post_type' => 'scorg_scss',
			        'posts_per_page' => -1,
			        'orderby' => 'ID',
			        'order' => 'DESC',
			        'post_status' => array('draft', 'publish'),
			    ));
			    if($scss_partials){
			        foreach($scss_partials as $scss_partial){
			            $scss_partial_args = array(
			                'id' => 'scorg-'.$scss_partial->post_name,
			                'title' => $scss_partial->post_title,
			                'href' => site_url().'/wp-admin/post.php?post='.$scss_partial->ID.'&action=edit',
			                'parent' => 'scss-admin-bar',
			                'meta' => array(
			                    'class' => 'scorg-admin-links',
			                    'title' => $scss_partial->post_title,
			                )
			            );
			            $wp_admin_bar->add_node($scss_partial_args);
			        }
			    }
			}
		}

		public function meta_box_filter(){
			$screen = get_current_screen();
			if($screen->id == "scorg_scss"){
				add_filter( 'esc_html', array($this, 'filter_html'), 10, 2 );
			}
			if($screen->id == "scorg_scss" || $screen->id == "edit-scorg_scss"){ ?>
				<style>
					.dp--on-load__hide{	display: none;}
					.dp--on-load__opacity{ opacity: 0; }
					.dp--on-load__opacity label{ opacity: 0; }
					.dp--hidden{	display: none !important;	}
				</style>
			<?php }
		}

		public function SCSS_disable_gutenberg($current_status, $post_type){
		    // Use your post type key instead of 'product'
		    if ($post_type === 'scorg_scss') return false;
		    return $current_status;
		}

		/**
		 * Register meta boxes
		 *
		 * Remember to change "your_prefix" to actual prefix in your project
		 *
		 * @param array $meta_boxes List of meta boxes
		 *
		 * @return array
		*/
		public function SCSS_order_details_meta_boxes( $meta_boxes ) {
			/**
			 * prefix of meta keys (optional)
			 * Use underscore (_) at the beginning to make keys hidden
			 * Alt.: You also can make prefix empty to disable it
			 */
			// Better has an underscore as last sign
			/*error_reporting(E_ALL); 
		 	ini_set("display_errors", 1);*/
			$post_type_args = array(
			   'public'   => true,
			);

			$output = 'names'; // names or objects, note names is the default
			$operator = 'and'; // 'and' or 'or'

			$post_types = get_post_types( $post_type_args, $output, $operator );
			unset($post_types['attachment']);
			unset($post_types['revision']);
			unset($post_types['nav_menu_item']);
			unset($post_types['custom_css']);
			unset($post_types['customize_changeset']);
			unset($post_types['oembed_cache']);
			unset($post_types['scorg_scss']);
			unset($post_types['scorg']);

			$all_taxonomies = get_taxonomies();

			$post_id = false;
			if ( isset( $_GET['post'] ) ) {
			    $post_id = intval( $_GET['post'] );
			} elseif ( isset( $_POST['post_ID'] ) ) {
			    $post_id = intval( $_POST['post_ID'] );
			}

			//echo "<pre>"; print_r($all_scripts); "</pre>"; exit;

			$prefix = 'SCSS_';

			// create scss file on load if not exists
			if(get_post_type($post_id) == "scorg_scss"){
				$scss_scripts = SCORG_is_base64(get_post_meta($post_id, $prefix.'scss_scripts', true));
				$folder_path = SCORG_UPLOADS_DIR_SCSS.'/'.$post_id;
				if(!empty($scss_scripts)){
					/*if (!is_dir($folder_path)) {
				        mkdir($folder_path);
				    }*/
					$scss_scripts = SCORG_process_colors($scss_scripts);
					$this->save_scss_script($post_id, $scss_scripts, $folder_path);
				} else {
					$this->save_scss_script($post_id, $scss_scripts, $folder_path);
					//$this->delete_scss_code_folder($post_id, $folder_path);
				}
			}
			$color_picker_hidden = '';
			if ( !SCORG_color_picker_option() ) {
				$color_picker_hidden = 'dp--hidden';
			}
			// 1st meta box
			$meta_boxes[] = array(
				// Meta box id, UNIQUE per meta box. Optional since 4.1.5
				'id'         => 'scsssettings',
				// Meta box title - Will appear at the drag and drop handle bar. Required.
				'title'      => esc_html__( 'SCSS Settings', 'scorg' ),
				// Post types, accept custom post types as well - DEFAULT is 'post'. Can be array (multiple post types) or string (1 post type). Optional.
				'post_types' => array( 'scorg_scss' ),
				// Where the meta box appear: normal (default), advanced, side. Optional.
				'context'    => 'normal',
				// Order of meta box: high (default), low. Optional.
				'priority'   => 'high',
				// Auto save: true, false (default). Optional.
				'autosave'   => true,
				// List of meta fields
				'fields'     => array(
					array(
						'name'    => esc_html__('Description', 'scorg'),
					    'id'      => "{$prefix}description",
					    'type'    => 'textarea',
					    'cols' => 10,
					    'sanitize_callback' => 'none',
					    'class' => 'dp--textarea',
					),
					array(
					    'type'    => 'custom_html',
					    'id' => "{$prefix}editor_main",
					    'std'  => '	<div id="scss_script" class="dp--scss_script-script"></div>
					    ',
					),
					array(
					    'type'    => 'custom_html',
						'id' => "{$prefix}picker_open",
						'std' => SCORG_oxy_colors(),
						'class' => 'oxy-color-picker ' . $color_picker_hidden,
					),
					array(
						'type'    => 'custom_html',
						'id' => "{$prefix}picker_close",
						'std' => SCORG_css_variables_picker(),
						'class' => 'css-variables-picker header-variable-picker',
					),
					array(
						'name'    => esc_html__('', 'scorg'),
					    'id'      => "{$prefix}scss_scripts",
					    'type'    => 'textarea',
					    'sanitize_callback' => 'none',
					    'class' => 'dp--textarea dp--code-editor dp--on-load__opacity conditions trigger-location dp--hidden dp--show-header-box dp-monaco-box dp-monaco-box__header',
					),
					array(
					    'name'    => esc_html__('', 'scorg'),
					    'id'      => "{$prefix}toggle_sidebar",
					    'type'    => 'text',
					    'sanitize_callback' => 'none',
					    'class' => 'dp--hidden',
					),
					array(
					    'name'    => esc_html__('', 'scorg'),
					    'id'      => "{$prefix}last_field",
					    'type'    => 'textarea',
					    'sanitize_callback' => 'none',
					    'class' => 'dp--textarea dp--code-editor dp--hidden',
					)
				),
			);

			//echo "<pre>"; print_r($meta_boxes); "</pre>"; exit;

			return $meta_boxes;
		}

		public function delete_scss_code_folder($post_id, $path){
			if (file_exists($path)) {
				$files = scandir($path);
				$files = array_diff(scandir($path), array('.', '..'));
				$files = array_values($files);
				foreach($files as $script_file){
					$file = $script_file;
					unlink($path.'/'.$file);
				}

				rmdir($path);
			}
		}

		public function SCSS_set_custom_edit_scorg_scss_columns($columns) {
			$new_columns = array();
		    $new_columns['cb'] = $columns['cb'];
		    $new_columns['title'] = $columns['title'];
		    $new_columns['scss_id'] = __('ID', 'scorg');
		    $new_columns['SCSS_description'] = __( 'Description', 'scorg' );
		    $new_columns['taxonomy-scorg_tags'] = $columns['taxonomy-scorg_tags'];
		    $new_columns['author'] = __( 'Author', 'scorg' );
		    $new_columns['date'] = $columns['date'];
		    //$new_columns['enable_script'] = __( 'Enable/Disable Script', 'scorg' );

		    return $new_columns;
		}

		// Add the data to the custom columns for the scorg post type:

		public function SCSS_custom_scorg_scss_column( $column, $post_id ) {
			$SCSS_description = get_post_meta($post_id, 'SCSS_description', true);
		    switch ( $column ) {
		        case 'SCSS_description' :
		            if(!empty($SCSS_description)){
		            	echo $SCSS_description;
		            } else {
		            	echo '';
		            }
		            break;
	            case 'scss_id' :
		            echo $post_id;
		        	break;
				case 'author' :
						$author_id = get_post_field ('post_author', $post_id);
						echo get_the_author_meta( 'display_name', $author_id );
					break;
		        	break;
		    }
		}

		// make columns sortable
		public function SCSS_set_sortable_columns($columns){
			$columns['scss_id'] = 'ID';
		    return $columns;
		}

		public function SCSS_rwmb_before($rwmb_meta){
			if(!is_admin()){
				return;
			}
			$screen = get_current_screen();
			if($screen->id != "scorg_scss" || $rwmb_meta->meta_box['id'] == "scsspartials"){
				return;
			}
			echo '	<div class="dp--scorg-wrapper">';
		}


		public function SCSS_rwmb_after($rwmb_meta){
			if(!is_admin()){
				return;
			}
			$screen = get_current_screen();
			if($screen->id != "scorg_scss" || $rwmb_meta->meta_box['id'] == "scsspartials"){
				return;
			}
			echo '	</div>';
		}


		public function SCSS_outer_html($outer_html, $field, $meta){
			if(!is_admin()){
				return $outer_html;
			}
			$screen = get_current_screen();
			if($screen->id != "scorg_scss"){
				return $outer_html;
			}
			global $post;
			$SCSS_toggle_sidebar = get_post_meta($post->ID, 'SCSS_toggle_sidebar', true);
			$hide_sidebar = 'hide-sidebar';
			if($SCSS_toggle_sidebar == "no"){
				$hide_sidebar = '';
			}
			$SCSS_view = get_post_meta($post->ID, 'SCSS_view', true);
			$editor_main_class = "";
			$columns_direction_class = "";
			if($SCSS_view == "yes"){
				$editor_main_class = "column-active";
				$columns_direction_class = "active";
			}
			$new_outer_html = "";
			if($field['id'] == "SCSS_description"){
				$new_outer_html = '<div class="dp--scorg-column dp--settings-sidebar '.$hide_sidebar.'"><div class="toggle-sidebar">Toggle sidebar</div>';
			}
			if($field['id'] == "SCSS_editor_main"){
				$new_outer_html = '</div><div class="wrap-editor-and-iframe"><div class="dp--scorg-column dp--editor-main panel-left '.$editor_main_class.'">
				<a href="#" class="top__btn preview" id="dp--show-hide-preview-full-view">Preview</a>';
			}
			if($field['id'] == "SCSS_picker_open"){
				$new_outer_html = '<div id="picker">';
			}
			if($field['id'] == "SCSS_last_field"){
				$new_outer_html = '</div>
				<div class="panel-right" id="dp--preview-iframe" style="display:none;">
					<div id="dp--iframe_header">
						'.preview_screen_sizes().'
						'.preview_screen_zooms().'
						<button id="open-home" data-url="'.site_url().'">Home</button>
						<input type="text" placeholder="Paste Websute URL" value="" id="dp--preview-url">
						<button>Submit</button>
					</div>
				
					<div id="dp--iframe_body"><iframe></iframe></div> 
				</div></div>';
			}
			if($field['id'] == "SCSS_view"){
				$new_outer_html = '</div>';
			}
			$new_outer_html .= $outer_html;
			if($field['id'] == "SCSS_picker_close"){
				$new_outer_html .= '</div>';
			}

			return $new_outer_html;
		}

		/**
		 * Adds one or more classes to the body tag in the scripts dashboard.
		*/
		public function SCSS_admin_body_class( $classes ) {
			$screen = get_current_screen();
			$classes_append = "";
			if($screen->id == "scorg_scss"){
				global $post;
				$SCORG_focus_mode = get_post_meta($post->ID, 'SCORG_focus_mode', true);
				if($SCORG_focus_mode == "yes"){
					$classes_append .= " sidebar__focus--active ";
				}
				$SCORG_sidebar_settings = get_post_meta($post->ID, 'SCORG_sidebar_settings', true);
				if($SCORG_sidebar_settings == "yes"){
					$classes_append .= " sidebar__settings--active ";
				}
				$SCORG_page_settings = get_post_meta($post->ID, 'SCORG_page_settings', true);
				if($SCORG_page_settings == "yes"){
					$classes_append .= " sidebar__pages--active ";
				}
			}
			if($screen->id == "scorg_scss"){
				$classes_append .= " scorg_css-edit-scripts ";
			}

			$classes .= " $classes_append";

			return $classes;
		}

		public function filter_html( $safe_text, $text ){
			return $text;
		}

		public function saveSCSSScript_func(){
			check_ajax_referer('ajax-nonce', 'verify_nonce');
			parse_str(urldecode(base64_decode($_REQUEST['form_data'])), $params);
			$editor_scss = urldecode(base64_decode($_POST['editor_scss']));
			$post_id = $params['post_ID'];
			$post_name = !empty($params['post_name']) ? $params['post_name'] : strtolower(str_replace(" ", "-", $params['post_name']));
			$scorg_css_variables = !empty($_POST['scorg_css_variables']) ? explode(",", sanitize_text_field($_POST['scorg_css_variables'])) : array();
			$scorg_css_variables = array_unique($scorg_css_variables);
			update_option('scorg_css_variables', $scorg_css_variables);
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
			update_post_meta($post_id, 'SCSS_scss_scripts', base64_encode($editor_scss));
			$folder_path = SCORG_UPLOADS_DIR_SCSS.'/'.$post_id;
			if(!empty($editor_scss)){
				$this->save_scss_script($post_id, $editor_scss, $folder_path);
			} else {
				$this->save_scss_script($post_id, $editor_scss, $folder_path);
			}
			
			$meta_keys = array( 
				'SCSS_description',
				'SCSS_toggle_sidebar',
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

			$this->regenerate_code_blocks_for_partial($post_id);

			echo json_encode(
				array(
					'message' => '',
					'variables_html' => SCORG_css_variables_picker_lis()
				)
			);
			wp_die();
		}

		public function regenerate_code_blocks_for_partial($partial_id){
			global $wpdb;
			$posts = $wpdb->prefix . 'posts';
			$postmeta = $wpdb->prefix . 'postmeta';
			$code_blocks = $wpdb->get_results("SELECT pm.post_id, p.post_type FROM $postmeta pm LEFT JOIN $posts p ON pm.post_id = p.ID WHERE meta_key = 'SCORG_partials' AND meta_value = '".$partial_id."'", OBJECT);
			if(!empty($code_blocks)){
				foreach($code_blocks as $block){
					if($block->post_type != "scorg"){
						continue;
					}
					$post_id = $block->post_id;
					$params = array(
						'SCORG_header_mode' => get_post_meta($post_id, 'SCORG_header_mode', true),
						'SCORG_footer_mode' => get_post_meta($post_id, 'SCORG_footer_mode', true)
					);
					// header/footer scripts
					$header_script = SCORG_is_base64(get_post_meta($post_id, 'SCORG_header_script', true));
					$footer_script = SCORG_is_base64(get_post_meta($post_id, 'SCORG_footer_script', true));
					if(!empty($header_script) || !empty($footer_script)){
						SCORG_create_header_footer_file($post_id, $header_script, $footer_script, $params, true);
					}
					// header/footer scripts
				}
			}
		}
		
		public function syncFromFile_func(){
			check_ajax_referer('ajax-nonce', 'verify_nonce');
			$post_id = $_POST['id'];
			$editor_scss_file = SCORG_UPLOADS_DIR_SCSS.'/_'.$post_id.'.scss';
			if(file_exists($editor_scss_file)){
				$editor_scss = file_get_contents($editor_scss_file);
				update_post_meta($post_id, 'SCSS_scss_scripts', base64_encode($editor_scss));
			}
			
			wp_die();
		}

		public function save_scss_script($post_id, $editor_scss, $folder_path){
			$editor_scss_file = SCORG_UPLOADS_DIR_SCSS.'/_'.$post_id.'.scss';
			$fh = fopen($editor_scss_file, 'wa+');
			fwrite($fh, $editor_scss."\n");
			fclose($fh);
		}
	}
	new SCORG_SCSS();
}