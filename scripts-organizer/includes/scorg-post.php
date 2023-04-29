<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_Post')){
	class SCORG_Post {
		public function __construct(){
			add_filter( 'use_block_editor_for_post_type', array($this, 'SCORG_disable_gutenberg'), 10, 2 );
			add_action( 'manage_scorg_posts_custom_column' , array($this, 'SCORG_custom_scorg_column'), 10, 2 );
			add_action( 'pre_get_posts', array($this, 'SCORG_sort_custom_column_query'), 101 );
			add_action( 'wp_ajax_saveAction', array($this, 'SCORG_saveAction_func') );
			add_action( 'admin_head', array($this, 'SCORG_checkphp_for_errors'), 1 );
			add_filter( 'rwmb_before', array($this, 'SCORG_rwmb_before'), 100 );
			add_filter( 'rwmb_after', array($this, 'SCORG_rwmb_after'), 100 );
			add_filter( 'rwmb_outer_html', array($this, 'SCORG_outer_html'), 100, 3 );
			add_filter( 'admin_body_class', array($this, 'SCORG_admin_body_class') );
			add_filter( 'rwmb_meta_boxes', array($this, 'SCORG_order_details_meta_boxes') );
			add_filter( 'rwmb_meta_boxes', array($this, 'SCORG_code_blocks') );
			add_filter( 'manage_scorg_posts_columns', array($this, 'SCORG_set_custom_edit_scorg_columns') );
			add_filter( 'manage_edit-scorg_sortable_columns', array($this, 'SCORG_set_sortable_columns') );
			add_action( 'admin_head', array($this, 'meta_box_filter') );
			add_action( 'admin_bar_menu', array($this, 'scorg_admin_bar'), 999 );
			add_action( 'before_delete_post', array($this, 'empty_trash') );
			add_action( 'admin_head-edit.php', array($this, 'export_button') );
			add_filter( 'bulk_actions-edit-scorg', array($this, 'bulk_actions') );
			add_filter( 'post_row_actions', array($this, 'row_actions'), 10, 2);
			add_filter( 'post_class', array($this, 'row_active_class'), 10, 3);
			add_filter( 'rwmb_SCORG_header_script_value', array($this, 'encode_header_field_value') );
			add_filter( 'rwmb_SCORG_footer_script_value', array($this, 'encode_footer_field_value') );
			add_filter( 'rwmb_SCORG_php_script_value', array($this, 'encode_php_field_value') );
			add_filter( 'rwmb_field_meta', array($this, 'decode_field_value'), 10, 3 );
			add_action( 'restrict_manage_posts', array($this, 'scorg_tags_filter') );
			add_action( 'wp_ajax_syncFromFileSCORG', array($this, 'syncFromFileSCORG_func') );
		}

		public function syncFromFileSCORG_func(){
			check_ajax_referer('ajax-nonce', 'verify_nonce');
			$post_id = $_POST['id'];
			$SCORG_partials = get_post_meta($post_id, 'SCORG_partials');
			$partials = array();
			if(is_array($SCORG_partials)){
				foreach($SCORG_partials as $partial){
					$editor_scss_file = SCORG_UPLOADS_DIR_SCSS.'/_'.$partial.'.scss';
					if(file_exists($editor_scss_file)){
						$partials[] = "@import '_".$partial.".scss'; \n";
						$editor_scss = file_get_contents($editor_scss_file);
						update_post_meta($post_id, 'SCSS_scss_scripts', base64_encode($editor_scss));
					}
				}
			}
			$SCORG_header_mode = get_post_meta($post_id, 'SCORG_header_mode', true);
			$SCORG_footer_mode = get_post_meta($post_id, 'SCORG_footer_mode', true);
			switch($SCORG_header_mode){
				case 'scss';
					$header_scss_file = SCORG_UPLOADS_DIR_SCSS.'/'.$post_id.'-header.scss';
					if(file_exists($header_scss_file)){
						$header_code = $this->clean_scss_file_from_imports($partials, file_get_contents($header_scss_file));
						update_post_meta($post_id, 'SCORG_header_script', base64_encode($header_code));
					}
					break;
				case 'css';
					$header_css_file = SCORG_UPLOADS_DIR_CSS.'/'.$post_id.'-header.css';
					if(file_exists($header_css_file)){
						$header_code = file_get_contents($header_css_file);
						update_post_meta($post_id, 'SCORG_header_script', base64_encode($header_code));
					}
					break;
				case 'javascript';
					$header_js_file = SCORG_UPLOADS_DIR_JS.'/'.$post_id.'-header.js';
					if(file_exists($header_js_file)){
						$header_code = file_get_contents($header_js_file);
						update_post_meta($post_id, 'SCORG_header_script', base64_encode($header_code));
					}
					break;
			}

			switch($SCORG_footer_mode){
				case 'scss';
					$footer_scss_file = SCORG_UPLOADS_DIR_SCSS.'/'.$post_id.'-footer.scss';
					if(file_exists($footer_scss_file)){
						$footer_code = $this->clean_scss_file_from_imports($partials, file_get_contents($footer_scss_file));
						update_post_meta($post_id, 'SCORG_footer_script', base64_encode($footer_code));
					}
					break;
				case 'css';
					$footer_css_file = SCORG_UPLOADS_DIR_CSS.'/'.$post_id.'-footer.css';
					if(file_exists($footer_css_file)){
						$footer_code = file_get_contents($footer_css_file);
						update_post_meta($post_id, 'SCORG_footer_script', base64_encode($footer_code));
					}
					break;
				case 'javascript';
					$footer_js_file = SCORG_UPLOADS_DIR_JS.'/'.$post_id.'-footer.js';
					if(file_exists($footer_js_file)){
						$footer_code = file_get_contents($footer_js_file);
						update_post_meta($post_id, 'SCORG_footer_script', base64_encode($footer_code));
					}
					break;
			}

			$php_script_file = SCORG_UPLOADS_DIR.'/'.$post_id.'.php';
			if(file_exists($php_script_file)){
				$php_script = file_get_contents($php_script_file);
				$php_script = str_replace("<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>", "", $php_script);
				update_post_meta($post_id, 'SCORG_php_script', base64_encode($php_script));
			}

			wp_die();
		}

		public function clean_scss_file_from_imports($partials, $code){
			if(is_array($partials) && count($partials) > 0){
				$code = str_replace($partials, '', $code);
			}

			return $code;
		}

		public function scorg_tags_filter($post_type){
			/** Ensure this is the correct Post Type*/
			if($post_type !== 'scorg' && $post_type !== 'scorg_scss')
				return;
		
			
			$all_tags = get_terms(array(
				'taxonomy' => 'scorg_tags',
				'hide_empty' => true,
				'orderby' => 'term_order',
				'number' => 999,
			));
		
			/** Grab all of the dest_options that should be shown */
			$tag_options[] = sprintf('<option value="">%1$s</option>', __('All Tags', 'scorg'));
			$selected = "";
			foreach($all_tags as $tag) :
				if(isset($_GET['scorg_tags']) && $_GET['scorg_tags'] == $tag->slug){
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
				$tag_options[] = sprintf('<option value="%1$s" %2$s>%3$s</option>', esc_attr($tag->slug), $selected, $tag->name);
			endforeach;
		
			/** Output the dropdown menu */
			echo '<select class="" id="scorg-tags" name="scorg_tags">';
			echo join("\n", $tag_options);
			echo '</select>';
		}

		public function decode_field_value( $meta, $field, $saved ) {
			if ( 'SCORG_header_script' != $field['id']
			&& 'SCORG_footer_script' != $field['id']
			&& 'SCORG_php_script' != $field['id'] ) {
				return $meta;
			}
			return SCORG_is_base64($meta);
		}

		public function encode_header_field_value(){
			global $post;
			if($post->post_type == "scorg"){
				// get data
				$header_script = isset( $_POST['SCORG_header_script'] ) ? $_POST['SCORG_header_script'] : null;
				
				// do something with your data, multiple by 100 for instance
				$modified_data = base64_encode(stripslashes($header_script));

				// return the modified data
				return $modified_data;
			}
		}
		
		public function encode_footer_field_value(){
			global $post;
			if($post->post_type == "scorg"){
				// get data
				$footer_script = isset( $_POST['SCORG_footer_script'] ) ? $_POST['SCORG_footer_script'] : null;

				// do something with your data, multiple by 100 for instance
				$modified_data = base64_encode(stripslashes($footer_script));

				// return the modified data
				return $modified_data;
			}
		}
		
		public function encode_php_field_value(){
			global $post;
			if($post->post_type == "scorg"){
				// get data
				$php_script = isset( $_POST['SCORG_php_script'] ) ? $_POST['SCORG_php_script'] : null;

				// do something with your data, multiple by 100 for instance
				$modified_data = base64_encode(stripslashes($php_script));

				// return the modified data
				return $modified_data;
			}
		}

		public function row_active_class( $classes, $class, $postID ){
			global $typenow; // current post type
			if($typenow == "scorg"){
				$SCORG_enable_script = get_post_meta($postID, 'SCORG_enable_script', true);
				if($SCORG_enable_script){
					$classes[] = 'script-active';
				}
			}
			return $classes;
		}

		public function row_actions($actions, $post){
			if ($post->post_type == "scorg"){
				$scorg_actions = array();
				foreach($actions as $key => $val){
					if($key != "trash"){
						$scorg_actions[$key] = $val;
					} else {
						$scorg_actions['export_scorg'] = '<a href="'.admin_url().'edit.php?post_type=scorg&scorg_export=single&id='.$post->ID.'">'.__('Export', 'scorg').'</a>';
						$scorg_actions[$key] = $val;
					}
				}
				return $scorg_actions;
			}

			return $actions;
		}

		public function bulk_actions($bulk){
			$bulk['scorg_bulk'] = 'Export Selected';
			$bulk['scorg_enable_bulk'] = 'Enable Selected';
			$bulk['scorg_disable_bulk'] = 'Disable Selected';
			//$bulk['scorg_bulk_delete'] = 'Move to Trash';
			return $bulk;
		}

		public function export_button(){
			global $current_screen;
			// Not our post type, exit earlier
			if ('scorg' != $current_screen->post_type)
				return;
			
			$code_blocks = get_posts(array( 'post_type' => 'scorg', 'posts_per_page' => 1 ));
			if($code_blocks){
				$export_all_url = add_query_arg('scorg_export', 'all', site_url() . '/wp-admin/edit.php?post_type=scorg');
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

		public function empty_trash( $post_id ) {

		    $post_type = get_post_type( $post_id );
		    if ( $post_type == 'scorg') {
		    	if(file_exists(SCORG_UPLOADS_DIR.'/'.$post_id.".php")){
		    		unlink(SCORG_UPLOADS_DIR.'/'.$post_id.".php");
		    	}
		    	if(file_exists(SCORG_UPLOADS_DIR_SCSS.'/'.$post_id.".scss")){
		    		unlink(SCORG_UPLOADS_DIR_SCSS.'/'.$post_id.".scss");
		    	}
		    	if(file_exists(SCORG_UPLOADS_DIR_CSS.'/'.$post_id.".css")){
		    		unlink(SCORG_UPLOADS_DIR_CSS.'/'.$post_id.".css");
		    	}
		    	if(file_exists(SCORG_UPLOADS_DIR_CSS.'/'.$post_id."-header.css")){
		    		unlink(SCORG_UPLOADS_DIR_CSS.'/'.$post_id."-header.css");
		    	}
		    	if(file_exists(SCORG_UPLOADS_DIR_CSS.'/'.$post_id."-footer.css")){
		    		unlink(SCORG_UPLOADS_DIR_CSS.'/'.$post_id."-footer.css");
		    	}
		    	if(file_exists(SCORG_UPLOADS_DIR_CSS.'/'.$post_id."-header-compiled.css")){
		    		unlink(SCORG_UPLOADS_DIR_CSS.'/'.$post_id."-header-compiled.css");
		    	}
		    	if(file_exists(SCORG_UPLOADS_DIR_CSS.'/'.$post_id."-footer-compiled.css")){
		    		unlink(SCORG_UPLOADS_DIR_CSS.'/'.$post_id."-footer-compiled.css");
		    	}
		    	if(file_exists(SCORG_UPLOADS_DIR_JS.'/'.$post_id.".js")){
		    		unlink(SCORG_UPLOADS_DIR_JS.'/'.$post_id.".js");
		    	}
		    	if(file_exists(SCORG_UPLOADS_DIR_JS.'/'.$post_id."-header.js")){
		    		unlink(SCORG_UPLOADS_DIR_JS.'/'.$post_id."-header.js");
		    	}
		    	if(file_exists(SCORG_UPLOADS_DIR_JS.'/'.$post_id."-footer.js")){
		    		unlink(SCORG_UPLOADS_DIR_JS.'/'.$post_id."-footer.js");
		    	}
		    }
		}

		public function scorg_admin_bar($wp_admin_bar){
			$scorg_code_blocks = get_option('scorg_code_blocks');
			if($scorg_code_blocks == "yes"){
				$parent_menu_args = array(
			        'id' => 'scorg-admin-bar',
			        'title' => 'Code Blocks',
			        'href' => site_url().'/wp-admin/edit.php?post_type=scorg',
			        'meta' => array(
			            'class' => 'scorg-admin-bar',
			            'title' => __('Code Blocks', 'scorg')
			        )
			    );
			    $wp_admin_bar->add_node($parent_menu_args);

			    // Add Oxygen templates
			    $code_blocks = get_posts(array(
			        'post_type' => 'scorg',
			        'posts_per_page' => -1,
			        'orderby' => 'title',
			        'order' => 'ASC',
			        'post_status' => array('draft', 'publish'),
			    ));
			    if($code_blocks){
			        foreach($code_blocks as $code_block){
			            $code_block_args = array(
			                'id' => 'scorg-'.$code_block->post_name,
			                'title' => $code_block->post_title,
			                'href' => site_url().'/wp-admin/post.php?post='.$code_block->ID.'&action=edit',
			                'parent' => 'scorg-admin-bar',
			                'meta' => array(
			                    'class' => 'scorg-admin-links',
			                    'title' => $code_block->post_title,
			                )
			            );
			            $wp_admin_bar->add_node($code_block_args);
			        }
			    }
			}
		}

		public function meta_box_filter(){
			$screen = get_current_screen();
			if($screen->id == "scorg"){
				add_filter( 'esc_html', array($this, 'filter_html'), 10, 2 );
			}
		}

		public function SCORG_disable_gutenberg($current_status, $post_type){
		    // Use your post type key instead of 'product'
		    if ($post_type === 'scorg') return false;
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
		public function SCORG_code_blocks( $meta_boxes ) {
			global $wpdb;
			$curr_id = (isset($_GET['post'])) ? $_GET['post'] : '';
			if(is_array($curr_id) || !is_admin()){ return $meta_boxes; }
			$posts = $wpdb->prefix . 'posts';
			$code_blocks = $wpdb->get_results("SELECT ID, post_title FROM $posts WHERE post_type = 'scorg' AND post_status = 'publish' AND ID != '$curr_id' ORDER BY post_title ASC", OBJECT);
			$scorg_tags_html = '';
			$scss_tags_html = '';
			$scorg_gas_html_output = '';
			$scorg_tags = array();
			$scss_tags = array();
			$scorg_ga_tags = array();
			
			$scorg_search_html = '<input class="scorg-search" type="text" placeholder="Search..">';
			$code_blocks_html = '';
			if(!empty($code_blocks)){
				$code_blocks_html .= $scorg_search_html.'<ul class="code-blocks">';
				foreach($code_blocks as $code_block){
					$tags = wp_get_post_terms($code_block->ID, 'scorg_tags', array('fields' => 'slugs'));
					if(!empty($tags)){
						foreach($tags as $tag){
							$scorg_tags[] = $tag;
						}	
					}
					$code_blocks_html .= '<li class="'.((!empty($tags) && is_array($tags)) ? implode(" ", $tags) : '').'"><a href="'.site_url().'/wp-admin/post.php?post='.$code_block->ID.'&action=edit">'.$code_block->post_title.' <span class="id">'.$code_block->ID.'</span></a></li>';
				}
				$code_blocks_html .= '</ul>';
			}

			$scss_partials = $wpdb->get_results("SELECT ID, post_title FROM $posts WHERE post_type = 'scorg_scss' AND post_status = 'publish' AND ID != '$curr_id' ORDER BY post_title ASC", OBJECT);
			$scss_partials_html = '';
			if(!empty($scss_partials)){
				$scss_partials_html .= $scorg_search_html.'<ul class="code-blocks">';
				foreach($scss_partials as $scss_partial){
					$tags = wp_get_post_terms($scss_partial->ID, 'scorg_tags', array('fields' => 'slugs'));
					if(!empty($tags)){
						foreach($tags as $tag){
							$scss_tags[] = $tag;
						}	
					}
					$scss_partials_html .= '<li class="'.((!empty($tags) && is_array($tags)) ? implode(" ", $tags) : '').'"><a href="'.site_url().'/wp-admin/post.php?post='.$scss_partial->ID.'&action=edit">'.$scss_partial->post_title.' <span class="id">'.$scss_partial->ID.'</span></a></li>';
				}
				$scss_partials_html .= '</ul>';
			}

			$scorg_gas = $wpdb->get_results("SELECT ID, post_title FROM $posts WHERE post_type = 'scorg_ga' AND post_status = 'publish' AND ID != '$curr_id' ORDER BY post_title ASC", OBJECT);
			$scorg_gas_html = '';
			if(!empty($scorg_gas)){
				$scorg_gas_html .= $scorg_search_html.'<ul class="code-blocks">';
				foreach($scorg_gas as $scorg_ga){
					$tags = wp_get_post_terms($scorg_ga->ID, 'scorg_tags', array('fields' => 'slugs'));
					if(!empty($tags)){
						foreach($tags as $tag){
							$scorg_ga_tags[] = $tag;
						}	
					}
					$scorg_gas_html .= '<li class="'.((!empty($tags) && is_array($tags)) ? implode(" ", $tags) : '').'"><a href="'.site_url().'/wp-admin/post.php?post='.$scorg_ga->ID.'&action=edit">'.$scorg_ga->post_title.' <span class="id">'.$scorg_ga->ID.'</span></a></li>';
				}
				$scorg_gas_html .= '</ul>';
			}

			if(!empty($scorg_tags)){
				$scorg_tags = array_unique($scorg_tags);
				$scorg_tags_html .= '<div class="scorg-tags">';
				$scorg_tags_html .= '<a href="#" data-tag="all" class="scorg-tag">No filter</a> ';
				foreach($scorg_tags as $all_tag){
					$scorg_tag = get_term_by('slug', $all_tag, 'scorg_tags');
					$scorg_tags_html .= '<a href="#" data-tag="'.$scorg_tag->slug.'" class="scorg-tag">'.$scorg_tag->name.'</a> ';
				}
				$scorg_tags_html .= '</div>';
			}
			
			if(!empty($scss_tags)){
				$scss_tags = array_unique($scss_tags);
				$scss_tags_html .= '<div class="scorg-tags">';
				$scss_tags_html .= '<a href="#" data-tag="all" class="scorg-tag">No filter</a> ';
				foreach($scss_tags as $all_tag){
					$scorg_tag = get_term_by('slug', $all_tag, 'scorg_tags');
					$scss_tags_html .= '<a href="#" data-tag="'.$scorg_tag->slug.'" class="scorg-tag">'.$scorg_tag->name.'</a> ';
				}
				$scss_tags_html .= '</div>';
			}

			if(!empty($scorg_ga_tags)){
				$scorg_ga_tags = array_unique($scorg_ga_tags);
				$scorg_gas_html_output .= '<div class="scorg-tags">';
				$scorg_gas_html_output .= '<a href="#" data-tag="all" class="scorg-tag">No filter</a> ';
				foreach($scorg_ga_tags as $scorg_ga_tag){
					$scorg_ga_tag_a = get_term_by('slug', $scorg_ga_tag, 'scorg_tags');
					$scorg_gas_html_output .= '<a href="#" data-tag="'.$scorg_ga_tag_a->slug.'" class="scorg-tag">'.$scorg_ga_tag_a->name.'</a> ';
				}
				$scorg_gas_html_output .= '</div>';
			}

			$prefix = 'SCORG_';
			// 1st meta box
			$meta_boxes[] = array(
				// Meta box id, UNIQUE per meta box. Optional since 4.1.5
				'id'         => 'codeblocks',
				// Meta box title - Will appear at the drag and drop handle bar. Required.
				'title'      => esc_html__( 'Code Blocks', 'off-the-tools' ),
				// Post types, accept custom post types as well - DEFAULT is 'post'. Can be array (multiple post types) or string (1 post type). Optional.
				'post_types' => array( 'scorg', 'scorg_scss', 'scorg_ga' ),
				// Where the meta box appear: normal (default), advanced, side. Optional.
				'context'    => 'side',
				// Order of meta box: high (default), low. Optional.
				'priority'   => 'low',
				// Auto save: true, false (default). Optional.
				'autosave'   => true,
				// List of meta fields
				'fields'     => array(
					array(
						'name'    => '',
					    'id'      => "{$prefix}code_blocks",
					    'type'    => 'custom_html',
					    'std' => $scorg_tags_html . $code_blocks_html,
					    'class' => 'dp--code-blocks',
					)
				),
			);
			
			$meta_boxes[] = array(
				// Meta box id, UNIQUE per meta box. Optional since 4.1.5
				'id'         => 'scss_parials',
				// Meta box title - Will appear at the drag and drop handle bar. Required.
				'title'      => esc_html__( 'SCSS Partials', 'off-the-tools' ),
				// Post types, accept custom post types as well - DEFAULT is 'post'. Can be array (multiple post types) or string (1 post type). Optional.
				'post_types' => array( 'scorg', 'scorg_scss', 'scorg_ga' ),
				// Where the meta box appear: normal (default), advanced, side. Optional.
				'context'    => 'side',
				// Order of meta box: high (default), low. Optional.
				'priority'   => 'low',
				// Auto save: true, false (default). Optional.
				'autosave'   => true,
				// List of meta fields
				'fields'     => array(
					array(
						'name'    => '',
					    'id'      => "{$prefix}scss_partials",
					    'type'    => 'custom_html',
					    'std' => $scss_tags_html . $scss_partials_html,
					    'class' => 'dp--code-blocks',
					)
				),
			);

			if( is_plugin_active( 'scripts-organizer--gutenberg-acf/scripts-organizer--gutenberg-acf.php' )){
				$meta_boxes[] = array(
					// Meta box id, UNIQUE per meta box. Optional since 4.1.5
					'id'         => 'gutenberg-codeblocks',
					// Meta box title - Will appear at the drag and drop handle bar. Required.
					'title'      => esc_html__( 'Gutenberg Blocks', 'scorg_ga' ),
					// Post types, accept custom post types as well - DEFAULT is 'post'. Can be array (multiple post types) or string (1 post type). Optional.
					'post_types' => array( 'scorg', 'scorg_scss', 'scorg_ga' ),
					// Where the meta box appear: normal (default), advanced, side. Optional.
					'context'    => 'side',
					// Order of meta box: high (default), low. Optional.
					'priority'   => 'low',
					// Auto save: true, false (default). Optional.
					'autosave'   => true,
					// List of meta fields
					'fields'     => array(
						array(
							'name'    => '',
							'id'      => "{$prefix}scorg_gas",
							'type'    => 'custom_html',
							'std' => $scorg_gas_html_output . $scorg_gas_html,
							'class' => 'dp--code-blocks',
						)
					),
				);
			}

			return $meta_boxes;
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
		public function SCORG_order_details_meta_boxes( $meta_boxes ) {
			/**
			 * prefix of meta keys (optional)
			 * Use underscore (_) at the beginning to make keys hidden
			 * Alt.: You also can make prefix empty to disable it
			 */
			// Better has an underscore as last sign
			/*error_reporting(E_ALL); 
		 	ini_set("display_errors", 1);*/

			/* for exclude terms */
			global $wpdb;
			$terms = $wpdb->prefix . 'terms';
			$term_taxonomy = $wpdb->prefix . 'term_taxonomy';
			$all_terms = $wpdb->get_results("SELECT t.term_id, t.name, tt.taxonomy FROM $terms t
			INNER JOIN $term_taxonomy tt ON t.term_id = tt.term_taxonomy_id
			ORDER BY t.name ASC", OBJECT);
			$all_terms_option = array();
			if(!empty($all_terms)){
				foreach($all_terms as $term){
					$all_terms_option[$term->term_id] = $term->name . ' ('.$term->taxonomy.')';
				}
			}
			/* for exclude terms */


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
			unset($post_types['scorg']);
			unset($post_types['scorg_ga']);
			unset($post_types['scorg_scss']);
			unset($post_types['ct_template']);

			$all_taxonomies = get_taxonomies();

			$post_id = false;
			if ( isset( $_GET['post'] ) ) {
			    $post_id = intval( $_GET['post'] );
			} elseif ( isset( $_POST['post_ID'] ) ) {
			    $post_id = intval( $_POST['post_ID'] );
			}

			$all_scripts = $this->get_all_scripts_grouped_by_type();
			$dp_exclude = 'dp--exclude';

			$prefix = 'SCORG_';
			// 1st meta box
			$meta_boxes[] = array(
				// Meta box id, UNIQUE per meta box. Optional since 4.1.5
				'id'         => 'scriptsettings',
				// Meta box title - Will appear at the drag and drop handle bar. Required.
				'title'      => esc_html__( 'Script Settings', 'off-the-tools' ),
				// Post types, accept custom post types as well - DEFAULT is 'post'. Can be array (multiple post types) or string (1 post type). Optional.
				'post_types' => array( 'scorg' ),
				// Where the meta box appear: normal (default), advanced, side. Optional.
				'context'    => 'normal',
				// Order of meta box: high (default), low. Optional.
				'priority'   => 'high',
				// Auto save: true, false (default). Optional.
				'autosave'   => true,
				// List of meta fields
				'fields'     => array(
					array(
						'name'    => esc_html__('Enable Code Block', 'scorg'),
					    'id'      => "{$prefix}enable_script",
					    'type'    => 'switch',
					    // Array of 'value' => 'Label' pairs for radio options.
					    // Note: the 'value' is stored in meta field, not the 'Label'
					    'style'     => 'rounded',
					    'on_label'  => 'On',
					    'off_label' => '',
					    'std' => 'on_label',
					    'class' => 'dp--switch dp--switch_move-in-header dp-switch-save',
					),
					array(
						'name'    => esc_html__('Exclude page builders', 'scorg'),
					    'id'      => "{$prefix}only_frontend",
					    'type'    => 'switch',
					    // Array of 'value' => 'Label' pairs for radio options.
					    // Note: the 'value' is stored in meta field, not the 'Label'
					    'style'     => 'rounded',
					    'on_label'  => 'On',
					    'off_label' => '',
					    'std' => '',
					    'class' => 'dp--switch dp--switch_move-in-header only-front-end hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Trigger location', 'scorg'),
					    'id'      => "{$prefix}trigger_location",
					    'type'    => 'radio',
					    // Array of 'value' => 'Label' pairs for radio options.
					    // Note: the 'value' is stored in meta field, not the 'Label'
					    'options' => array(
					        'everywhere' => '<span>Everywhere</span>',
					        'admin_only' => '<span>Admin only</span>',
					        'conditions' => '<span>Conditions</span>',
					    ),
					    'std' => 'conditions',
					    // Show choices in the same line?
					    'inline' => false,
					    'class' => 'dp--radio dp-tabular dp-trigger',
					),
					array(
						'name'    => esc_html__('Action/Hook', 'scorg'),
					    'id'      => "{$prefix}action_hook",
					    'type'		=> 'select',
    					'multiple'	=> false,
					    'std' => 'conditions',
						'options'         => [
							'' => 'Action/Hook',
							'muplugins_loaded' => 'muplugins_loaded',
							'plugins_loaded' => 'plugins_loaded',
							'setup_theme' => 'setup_theme',
							'after_setup_theme' => 'after_setup_theme',
							'init' => 'init',
							'wp_loaded' => 'wp_loaded',
							'wp_head' => 'wp_head',
							'wp_footer' => 'wp_footer',
							'admin_head' => 'admin_head',
							'admin_footer' => 'admin_footer',
							'wp_body_open' => 'wp_body_open'
						],
					    // Show choices in the same line?
					    'class' => 'dp--select child-group dp--on-load__hide hide-in-admin-and-everywhere action-hook',
					),
					array(
						'name'    => esc_html__('Priority', 'scorg'),
						'id'      => "{$prefix}priority",
						'type'    => 'text',
						'std' 	  => '1',
						'sanitize_callback' => 'none',
					    'class' => 'dp--on-load__hide dp-tabular hide-in-admin-and-everywhere action-hook',
					),
					array(
						'name'    => esc_html__('Script Location', 'scorg'),
					    'id'      => "{$prefix}script_type",
					    'type'    => 'checkbox_list',
					    // Array of 'value' => 'Label' pairs for radio options.
					    // Note: the 'value' is stored in meta field, not the 'Label'
					    'options' => array(
					        'header' => '<span>Header</span>',
					        'footer' => '<span>Footer</span>',
					        'php' => '<span>PHP</span>',
					        'shortcode' => '<span>Shortcode</span>',
					    ),
					    'std' => '',
					    // Show choices in the same line?
					    'inline' => false,
					    'class' => 'dp-tabular script-location hide-in-admin-and-everywhere',
					),
					array(
					    'type'    => 'custom_html',
					    'class' => 'child-group dp--on-load__hide conditions trigger-location dp--shortcode dp--show-shortcode-box dp-monaco-box',
					    'std'  => '	<div class="shortcode">[scorg_shortcode id="'.$post_id.'"]</div>',
					),
					array(
						'name'    => esc_html__('Template', 'scorg'),
					    'id'      => "{$prefix}page_post",
					    'type'    => 'radio',
					    // Array of 'value' => 'Label' pairs for radio options.
					    // Note: the 'value' is stored in meta field, not the 'Label'
					    'options' => array(
					        'all' => '<span>All</span>',
					        'specific_page_post' => '<span>Page, Post</span>',
					        'specific_post_type' => '<span>Post Type</span>',
					        'specific_taxonomy' => '<span>Taxonomy</span>',
					        'custom' => '<span>Custom</span>',
					    ),
					    'std' => 'all',
					    // Show choices in the same line?
					    'inline' => false,
					    'class' => 'dp--radio dp-tabular script-template hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Custom Condition', 'scorg'),
						'id'      => "{$prefix}custom",
						'type'    => 'text',
						'sanitize_callback' => 'none',
					    'class' => 'dp--on-load__hide dp-tabular script-template',
					),
					array(
						'name'    => esc_html__('Select Page/Post', 'scorg'),
					    'id'      => "{$prefix}selected_page_post",
					    'type'    => 'post',
					    // Post type.
					    'post_type'   => $post_types,
					    // Field type.
					    'field_type'  => 'select_advanced',
						'select_all_none' => false,
					    // Placeholder, inherited from `select_advanced` field.
					    'placeholder' => 'Select a page/post',
					    'multiple'    => true,
						'ajax' => true,
					    // Query arguments. See https://codex.wordpress.org/Class_Reference/WP_Query
					    'query_args'  => array(
					        'post_status'    => 'publish',
					        'posts_per_page' => 10,
					    ),
					    'class' => 'dp--select-advanced child-group dp--on-load__hide hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Select Post Types', 'scorg'),
					    'id'      => "{$prefix}specific_post_type",
					    'type'    => 'select_advanced',
					    // Post type.
					    'options'   => $post_types,
						'select_all_none' => false,
						'ajax' => true,
					    // Placeholder, inherited from `select_advanced` field.
					    'placeholder' => 'Select a post type',
					    'multiple'        => true,
					    'class' => 'dp--select-advanced child-group dp--on-load__hide hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Select Taxonomies', 'scorg'),
					    'id'      => "{$prefix}specific_taxonomy",
					    'type'    => 'select_advanced',
						'select_all_none' => false,
						'ajax' => true,
					    // Post type.
					    'options'   => $all_taxonomies,
					    // Placeholder, inherited from `select_advanced` field.
					    'placeholder' => 'Select a taxonomy',
					    'multiple'        => true,
					    'class' => 'dp--select-advanced child-group dp--on-load__hide hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Enable Exclude', 'scorg'),
					    'id'      => "{$prefix}exclude_script",
					    'type'    => 'switch',
					    // Array of 'value' => 'Label' pairs for radio options.
					    // Note: the 'value' is stored in meta field, not the 'Label'
					    'style'     => 'rounded',
					    'on_label'  => 'On',
					    'off_label' => '',
					    'std' => '',
					    'class' => 'dp--switch dp-switch-exclude exclude-message hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Exclude Post Types', 'scorg'),
					    'id'      => "{$prefix}exclude_post_type",
					    'type'    => 'select_advanced',
					    // Post type.
					    'options'   => $post_types,
						'select_all_none' => false,
						'ajax' => true,
					    // Placeholder, inherited from `select_advanced` field.
					    'placeholder' => 'Select a post type',
					    'multiple'        => true,
					    'class' => 'dp--select-advanced dp--exclude-pt '.$dp_exclude.' child-group dp--on-load__hide hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Exclude Page/Post', 'scorg'),
					    'id'      => "{$prefix}exclude_page_post",
					    'type'    => 'post',
					    // Post type.
					    'post_type'   => $post_types,
					    // Field type.
					    'field_type'  => 'select_advanced',
						'select_all_none' => false,
						'ajax' => true,
					    // Placeholder, inherited from `select_advanced` field.
					    'placeholder' => 'Select a page/post',
					    'multiple'        => true,
					    // Query arguments. See https://codex.wordpress.org/Class_Reference/WP_Query
					    'query_args'  => array(
					        'post_status'    => 'publish',
					        'posts_per_page' => 10,
					    ),
					    'class' => 'dp--select-advanced dp--exclude-pp '.$dp_exclude.' child-group dp--on-load__hide hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Exclude Terms', 'scorg'),
					    'id'      => "{$prefix}exclude_terms",
					    'type'    => 'select_advanced',
					    // Post type.
					    'options'   => $all_terms_option,
						'select_all_none' => false,
						'ajax' => true,
					    // Placeholder, inherited from `select_advanced` field.
					    'placeholder' => 'Select a term',
					    'multiple'        => true,
					    'class' => 'dp--select-advanced dp--exclude-t '.$dp_exclude.' child-group dp--on-load__hide hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Exclude Taxonomies', 'scorg'),
					    'id'      => "{$prefix}exclude_taxonomies",
					    'type'    => 'select_advanced',
					    // Post type.
					    'options'   => $all_taxonomies,
						'select_all_none' => false,
						'ajax' => true,
					    // Placeholder, inherited from `select_advanced` field.
					    'placeholder' => 'Select a taxonomy',
					    'multiple'        => true,
					    'class' => 'dp--select-advanced dp--exclude-tx '.$dp_exclude.' child-group dp--on-load__hide hide-in-admin-and-everywhere',
					),
					array(
					    'type'    => 'custom_html',
					    'class' => 'hide-in-admin-and-everywhere',
					    'std'  => '	<div class="alert alert-warning">
							<span class="alert__current-time">Current server time:</span>
								<span class="alert__date-wrap">
									<span class="alert__date">'.current_time("l").'</span><span class="alert__space">,</span>
									<span class="alert__date">'.current_time("H:i").'</span>  
								</span>
							<span class="alert__set-date">Please set the Days and Specific Time here accordingly.</span>
						</div>',
					),
					array(
						'name'    => esc_html__('Schedule', 'scorg'),
					    'id'      => "{$prefix}script_schedule",
					    'type'    => 'radio',
					    // Array of 'value' => 'Label' pairs for radio options.
					    // Note: the 'value' is stored in meta field, not the 'Label'
					    'options' => array(
					        'daily' => '<span>Daily</span>',
					        'specific_days' => '<span>Days</span>',
					        'specific_date' => '<span>Date</span>',
					        'specific_date_range' => '<span>Date Range</span>',
					    ),
					    'std' => 'daily',
					    // Show choices in the same line?
					    'inline' => false,
					    'class' => 'dp--radio dp-tabular hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Date', 'scorg'),
					    'id'      => "{$prefix}specific_date",
					    'type'    => 'date',
					    'js_options' => array(
					        'dateFormat'      => 'yy-mm-dd',
					        'showButtonPanel' => false,
					    ),
					    // Display inline?
					    'inline' => false,
					    // Save value as timestamp?
					    'timestamp' => false,
					    'class' => 'dp--date child-group dp--on-load__hide hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Date From', 'scorg'),
					    'id'      => "{$prefix}specific_date_from",
					    'type'    => 'date',
					    'js_options' => array(
					        'dateFormat'      => 'yy-mm-dd',
					        'showButtonPanel' => false,
					    ),
					    // Display inline?
					    'inline' => false,
					    // Save value as timestamp?
					    'timestamp' => false,
					    'class' => 'dp--date child-group dp--on-load__hide hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Date To', 'scorg'),
					    'id'      => "{$prefix}specific_date_to",
					    'type'    => 'date',
					    'js_options' => array(
					        'dateFormat'      => 'yy-mm-dd',
					        'showButtonPanel' => false,
					    ),
					    // Display inline?
					    'inline' => false,
					    // Save value as timestamp?
					    'timestamp' => false,
					    'class' => 'dp--date child-group multiple-child-group dp--on-load__hide hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Days', 'scorg'),
					    'id'      => "{$prefix}days",
					    'type'    => 'select_advanced',
					    // Array of 'value' => 'Label' pairs
					    'options'         => array(
					        'monday'       => 'Mondays',
					        'tuesday' 		=> 'Tuesdays',
					        'wednesday'    => 'Wednesdays',
					        'thursday'     => 'Thursdays',
					        'friday' 		=> 	'Fridays',
					        'saturday'     => 'Saturdays',
					        'sunday'      => 'Sundays',
					    ),
					    // Allow to select multiple value?
					    'multiple'        => true,
						'select_all_none' => false,
					    // Placeholder text
					    'placeholder'     => 'Select days',
					    // Display "Select All / None" button?
					    'class' => 'dp--select-advanced child-group dp--on-load__hide hide-in-admin-and-everywhere',
					    // select2 configuration. See https://select2.org/configuration
					    /*'js_options'      => array(
					        'containerCssClass' => 'my-custom-class',
					    ),*/
					),
					array(
						'name'    => esc_html__('Script Duration', 'scorg'),
					    'id'      => "{$prefix}script_time",
					    'type'    => 'radio',
					    // Array of 'value' => 'Label' pairs for radio options.
					    // Note: the 'value' is stored in meta field, not the 'Label'
					    'options' => array(
					        'all_day' => '<span>All Day</span>',
					        'specific_time' => '<span>Time</span>',
					    ),
					    'std' => 'all_day',
					    // Show choices in the same line?
					    'inline' => false,
					    'class' => 'dp--radio dp-tabular hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Start at Specific Time', 'scorg'),
					    'id'      => "{$prefix}specific_time_start",
					    'type'    => 'time',
					    // Time options, see here http://trentrichardson.com/examples/timepicker/
					    'js_options' => array(
					        'stepMinute'      => 1,
					        'controlType'     => 'select',
					        'showButtonPanel' => true,
					        'oneLine'         => true,
					    ),
					    // Display inline?
					    'inline'     => false,
					    'class' => 'dp--time child-group dp--on-load__hide hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('End at Specific Time', 'scorg'),
					    'id'      => "{$prefix}specific_time_end",
					    'type'    => 'time',
					    // Time options, see here http://trentrichardson.com/examples/timepicker/
					    'js_options' => array(
					        'stepMinute'      => 1,
					        'controlType'     => 'select',
					        'showButtonPanel' => true,
					        'oneLine'         => true,
					    ),
					    // Display inline?
					    'inline'     => false,
					    'class' => 'dp--time child-group multiple-child-group dp--on-load__hide hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Scripts Manager', 'scorg'),
					    'id'      => "{$prefix}scripts_manager",
					    'type'    => 'radio',
					    // Array of 'value' => 'Label' pairs for radio options.
					    // Note: the 'value' is stored in meta field, not the 'Label'
					    'options' => array(
					        'hide' => '<span>Hide</span>',
					        'show' => '<span>Show</span>',
					    ),
					    'std' => 'hide',
					    // Show choices in the same line?
					    'inline' => false,
					    'class' => 'dp--radio dp-tabular conditions trigger-location hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Enqueue Scripts', 'scorg'),
					    'id'      => "{$prefix}enqueue_scripts",
					    'type'    => 'select_advanced',
						'select_all_none' => false,
					    'options'   => $all_scripts['register'],
					    // Placeholder, inherited from `select_advanced` field.
					    'placeholder' => 'Select scripts to enqueue',
					    'multiple'        => true,
					    'class' => 'dp--select-advanced child-group dp--on-load__hide hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Dequeue Scripts', 'scorg'),
					    'id'      => "{$prefix}dequeue_scripts",
					    'type'    => 'select_advanced',
					    'options'   => $all_scripts['enqueue'],
						'select_all_none' => false,
					    // Placeholder, inherited from `select_advanced` field.
					    'placeholder' => 'Select scripts to dequeue',
					    'multiple'        => true,
					    'class' => 'dp--select-advanced child-group dp--on-load__hide hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('SCSS Partials Manager', 'scorg'),
					    'id'      => "{$prefix}scss_partial_manager",
					    'type'    => 'radio',
					    // Array of 'value' => 'Label' pairs for radio options.
					    // Note: the 'value' is stored in meta field, not the 'Label'
					    'options' => array(
					        'hide' => '<span>Hide</span>',
					        'show' => '<span>Show</span>',
					    ),
					    'std' => 'hide',
					    // Show choices in the same line?
					    'inline' => false,
					    'class' => 'dp--radio dp-tabular conditions trigger-location hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Load partials', 'scorg'),
					    'id'      => "{$prefix}partials",
					    'type'    => 'post',
					    // Post type.
					    'post_type'   => array('scorg_scss'),
					    // Field type.
					    'field_type'  => 'select_advanced',
						'select_all_none' => false,
					    // Placeholder, inherited from `select_advanced` field.
					    'placeholder' => 'Select partials',
					    'multiple'        => true,
						'ajax' => true,
					    // Query arguments. See https://codex.wordpress.org/Class_Reference/WP_Query
					    'query_args'  => array(
					        'post_status'    => 'publish',
					        'posts_per_page' => 10,
					    ),
					    'class' => 'dp--select-advanced child-group dp--on-load__hide load-partials hide-in-admin-and-everywhere',
					),
					array(
						'name'    => esc_html__('Description', 'scorg'),
					    'id'      => "{$prefix}script_description",
					    'type'    => 'textarea',
					    'cols' => 10,
					    'sanitize_callback' => 'none',
					    'class' => 'dp--textarea',
					)
				),
			);

			$index_to_add = 0;
			foreach($meta_boxes as $key => $meta_box){
				if(isset($meta_box['id'])){
					if($meta_box['id'] == "scriptsettings"){
						$index_to_add = $key;
					}
				}
			}

			if($post_id > 0){
				$this_post_type = get_post_type($post_id);
				if($this_post_type == "scorg"){
					// header/footer scripts
					$header_script = SCORG_is_base64(get_post_meta($post_id, $prefix.'header_script', true));
					$footer_script = SCORG_is_base64(get_post_meta($post_id, $prefix.'footer_script', true));
					if(!empty($header_script) || !empty($footer_script)){
						$params = array(
							'SCORG_header_mode' => get_post_meta($post_id, 'SCORG_header_mode', true),
							'SCORG_footer_mode' => get_post_meta($post_id, 'SCORG_footer_mode', true)
						);
						SCORG_create_header_footer_file($post_id, $header_script, $footer_script, $params, false);
					}
					// header/footer scripts

					$php_script = SCORG_is_base64(get_post_meta($post_id, $prefix.'php_script', true));
					if(!empty($php_script)){
						$php_script = "<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>\n" . $php_script;
						$php_script = str_replace("REPLACE_BACKSLASH", "\\", $php_script);
						$this->save_php_script($post_id, $php_script);
					}
				}
			}

			$meta_boxes[$index_to_add]['fields'][] = array(
				'name'    => esc_html__('Header script active', 'scorg'),
			    'id'      => "{$prefix}header_script",
			    'type'    => 'textarea',
			    'sanitize_callback' => 'none',
			    'class' => 'dp--textarea dp--code-editor dp--on-load__opacity conditions trigger-location dp--hidden dp--show-header-box dp-monaco-box dp-monaco-box__header',
			);
			$meta_boxes[$index_to_add]['fields'][] = array(
				'name'    => esc_html__('Footer script active', 'scorg'),
			    'id'      => "{$prefix}footer_script",
			    'type'    => 'textarea',
			    'sanitize_callback' => 'none',
			    'class' => 'dp--textarea dp--code-editor dp--on-load__opacity conditions trigger-location dp--hidden dp--show-footer-box dp-monaco-box dp-monaco-box__header',
			);
			$meta_boxes[$index_to_add]['fields'][] = array(
				'name'    => esc_html__('PHP script active', 'scorg'),
			    'id'      => "{$prefix}php_script",
			    'type'    => 'textarea',
			    'sanitize_callback' => 'none',
			    'class' => 'dp--textarea dp--code-editor dp--on-load__opacity everywhere conditions trigger-location dp--hidden dp-monaco-box dp-monaco-box__header dp--show-php-box',
			);

			$SCORG_active_tab = get_post_meta($post_id, 'SCORG_active_tab', true);
			$SCORG_trigger_location = get_post_meta($post_id, 'SCORG_trigger_location', true);
			$active = "";
			$tabs = array(
				'header' => 'Header',
				'footer' => 'Footer',
				'php' => 'PHP',
			);
			$tabs_html = '';
			$SCORG_script_type = get_post_meta($post_id, 'SCORG_script_type');
			foreach($tabs as $key => $val){
				if($SCORG_trigger_location != "admin_only" && $SCORG_trigger_location != "everywhere"){
					if(!empty($SCORG_active_tab) && $SCORG_active_tab == $key && in_array($key, $SCORG_script_type)){
						$active = "active";
					} else {
						$active = "";
					}
				} else {
					if($key == "php"){
						$active = "active";
					} else {
						$active = "";
					}
				}
				$tabs_html .= '<button class="tablinks dp--show-'.$key.'-box  dp-monaco-box '.$active.'" onclick="openSCORGTab(event, \''.$key.'\')">'.$val.'</button>';
			}
			$meta_boxes[$index_to_add]['fields'][] = array(
			    'type'    => 'custom_html',
			    'id' => "{$prefix}tab_header",
			    'std'  => '<div class="tab">'.$tabs_html.'</div>',
			);
			$meta_boxes[$index_to_add]['fields'][] = array(
				'name'    => esc_html__('Header Mode', 'scorg'),
			    'id'      => "{$prefix}header_mode",
			    'type'    => 'select',
			    // Array of 'value' => 'Label' pairs
			    'options'         => array(
			        'html'       => 'HTML',
			        'css' 		=> 'CSS',
			        'scss'    => 'SCSS',
			        'javascript'     => 'Javascript',
			    ),
			    'std' => 'html',
			    // Allow to select multiple value?
			    'multiple'        => false,
			    // Placeholder text
			    //'placeholder'     => 'Select mode',
			    // Display "Select All / None" button?
			    'select_all_none' => false,
			    'class' => 'dp--select-advanced dp--header-mode',
			    // select2 configuration. See https://select2.org/configuration
			    /*'js_options'      => array(
			        'containerCssClass' => 'my-custom-class',
			    ),*/
			);
			$header_mode = get_post_meta($post_id, 'SCORG_header_mode', true);
			$show_header_file = (!empty($header_mode) && $header_mode != "html") ? '' : 'dp--hidden';
			$meta_boxes[$index_to_add]['fields'][] = array(
				'name'    => esc_html__('Create file', 'scorg'),
			    'id'      => "{$prefix}header_file",
			    'type'    => 'checkbox',
			    // Show choices in the same line?
			    'inline' => true,
			    'std' => 0,
			    'class' => 'dp--file-checkbox header-file ' . $show_header_file,
			);
			
			if ( SCORG_color_picker_option() ) {
				$meta_boxes[$index_to_add]['fields'][] = array(
					'type'    => 'custom_html',
					'std' => SCORG_oxy_colors(),
					'class' => 'oxy-color-picker header-color-picker',
				);
			}
			$meta_boxes[$index_to_add]['fields'][] = array(
				'type'    => 'custom_html',
				'std' => SCORG_css_variables_picker(),
				'class' => 'css-variables-picker header-variable-picker',
			);
			$meta_boxes[$index_to_add]['fields'][] = array(
			    'id' => "{$prefix}header_before_color_picker",
			    'type'    => 'custom_html',
			    'std'  => '	<div id="header_script" class="dp--header-script monaco-wrap" style="height:100%;"></div>
			    ',
			);
			$meta_boxes[$index_to_add]['fields'][] = array(
				'name'    => esc_html__('Footer Mode', 'scorg'),
			    'id'      => "{$prefix}footer_mode",
			    'type'    => 'select',
			    // Array of 'value' => 'Label' pairs
			    'options'         => array(
			        'html'       => 'HTML',
			        'css' 		=> 'CSS',
			        'scss'    => 'SCSS',
			        'javascript'     => 'Javascript',
			    ),
			    'std' => 'html',
			    // Allow to select multiple value?
			    'multiple'        => false,
			    // Placeholder text
			    //'placeholder'     => 'Select mode',
			    // Display "Select All / None" button?
			    'select_all_none' => false,
			    'class' => 'dp--select-advanced dp--footer-mode',
			    // select2 configuration. See https://select2.org/configuration
			    /*'js_options'      => array(
			        'containerCssClass' => 'my-custom-class',
			    ),*/
			);
			$footer_mode = get_post_meta($post_id, 'SCORG_footer_mode', true);
			$show_footer_file = (!empty($footer_mode) && $footer_mode != "html") ? '' : 'dp--hidden';
			$meta_boxes[$index_to_add]['fields'][] = array(
				'name'    => esc_html__('Create file', 'scorg'),
			    'id'      => "{$prefix}footer_file",
			    'type'    => 'checkbox',
			    // Show choices in the same line?
			    'inline' => true,
			    'std' => 0,
			    'class' => 'dp--file-checkbox footer-file '. $show_footer_file,
			);
			if ( SCORG_color_picker_option() ) {
				$meta_boxes[$index_to_add]['fields'][] = array(
					'type'    => 'custom_html',
					'std' => SCORG_oxy_colors(),
					'class' => 'dp--color-picker footer-color-picker',
				);
			}
			$meta_boxes[$index_to_add]['fields'][] = array(
				'type'    => 'custom_html',
				'std' => SCORG_css_variables_picker(),
				'class' => 'css-variables-picker footer-variable-picker',
			);
			$meta_boxes[$index_to_add]['fields'][] = array(
			    'type'    => 'custom_html',
				'id' => "{$prefix}footer_before_color_picker",
			    'std'  => '	<div id="footer_script" class="monaco-wrap"></div>
			    ',
			);
			$meta_boxes[$index_to_add]['fields'][] = array(
			    'type'    => 'custom_html',
			    'id' => "{$prefix}php_close",
			    'std'  => '<div id="php_script" class=""></div>
			    ',
			);
			$meta_boxes[$index_to_add]['fields'][] = array(
			    'name'    => esc_html__('', 'scorg'),
			    'id'      => "{$prefix}view",
			    'type'    => 'text',
			    'sanitize_callback' => 'none',
			    'class' => 'dp--hidden',
			);
			$meta_boxes[$index_to_add]['fields'][] = array(
			    'name'    => esc_html__('', 'scorg'),
			    'id'      => "{$prefix}toggle_sidebar",
			    'type'    => 'text',
			    'sanitize_callback' => 'none',
			    'class' => 'dp--hidden',
			);
			$meta_boxes[$index_to_add]['fields'][] = array(
			    'name'    => esc_html__('', 'scorg'),
			    'id'      => "{$prefix}active_tab",
			    'type'    => 'text',
			    'sanitize_callback' => 'none',
			    'class' => 'dp--hidden',
			);
			$meta_boxes[$index_to_add]['fields'][] = array(
			    'name'    => esc_html__('', 'scorg'),
			    'id'      => "{$prefix}last_field",
			    'type'    => 'textarea',
			    'sanitize_callback' => 'none',
			    'class' => 'dp--textarea dp--code-editor dp--hidden',
			);

			//echo "<pre>"; print_r($meta_boxes); "</pre>"; exit;

			return $meta_boxes;
		}

		public function save_php_script($post_id, $php_script){
			$php_script_file = SCORG_UPLOADS_DIR.'/'.$post_id.'.php';
			$fh = fopen($php_script_file, 'wa+');
			fwrite($fh, $php_script."\n");
			fclose($fh);
		}

		public function SCORG_set_custom_edit_scorg_columns($columns) {
			$new_columns = array();
		    $new_columns['cb'] = $columns['cb'];
		    $new_columns['new_title'] = '';
		    $new_columns['title'] = $columns['title'];
		    $new_columns['scorg_id'] = __('ID', 'scorg');
		    $new_columns['script_description'] = __( 'Description', 'scorg' );
		    $scorg_show_shortcode = get_option('scorg_show_shortcode');
		    if($scorg_show_shortcode == "yes"){
		    	$new_columns['shortcode'] = __( 'Shortcode', 'scorg' );
		    }
		    $new_columns['taxonomy-scorg_tags'] = $columns['taxonomy-scorg_tags'];
		    $new_columns['author'] = __( 'Author', 'scorg' );
		    $new_columns['date'] = $columns['date'];
		    //$new_columns['enable_script'] = __( 'Enable/Disable Script', 'scorg' );

		    return $new_columns;
		}

		// Add the data to the custom columns for the scorg post type:

		public function SCORG_custom_scorg_column( $column, $post_id ) {
			$SCORG_enable_script = get_post_meta($post_id, 'SCORG_enable_script', true);
			$trigger_location = get_post_meta($post_id, 'trigger_location', true);
			$SCORG_script_type = get_post_meta($post_id, 'SCORG_script_type');
			$SCORG_script_description = get_post_meta($post_id, 'SCORG_script_description', true);
			$checked = "";
			if($SCORG_enable_script){
				$checked = "checked";
			}
		    switch ( $column ) {
		        case 'new_title' :
		        echo '<div class="switch enable_script">
				    <div class="rwmb-input">
				        <label class="rwmb-switch-label rwmb-switch-label--rounded">
				            <input value="1" type="checkbox" '.$checked.' size="30" class="rwmb-switch">
				            <div class="rwmb-switch-status">
				                <span class="rwmb-switch-slider"></span>
				                <span class="rwmb-switch-on">On</span>
				                <span class="rwmb-switch-off"></span>
				            </div>
				        </label>
				    </div>
				</div>';
		        break;

		        case 'script_description' :
		            if(!empty($SCORG_script_description)){
		            	echo $SCORG_script_description;
		            } else {
		            	echo '';
		            }
		        break;

		        case 'scorg_id' :
		            echo $post_id;
		        break;
		        case 'author' :
					$author_id = get_post_field ('post_author', $post_id);
		            echo get_the_author_meta( 'display_name', $author_id );
		        break;
		        case 'shortcode' :
		            if($trigger_location != "everywhere" && !empty($SCORG_script_type) && in_array('shortcode', $SCORG_script_type)){
		            	echo '[scorg_shortcode id="'.$post_id.'"]';
		            } else {
		            	echo '';
		            }
		        break;
		    }
		}

		// make columns sortable
		public function SCORG_set_sortable_columns($columns){
			$columns['title'] = 'Title';
			$columns['scorg_id'] = 'ID';
		    return $columns;
		}


		// set query to sort
		public function SCORG_sort_custom_column_query($query){
		    if ( is_admin() && $query->is_main_query() && ($query->get('post_type') == "scorg" || $query->get('post_type') == "scorg_scss") && isset($_GET['orderby']) && $_GET['orderby'] == "Title" ) {
		        $query->set( 'orderby', 'title' );
		    }
		}


		public function SCORG_saveAction_func(){
			check_ajax_referer('ajax-nonce', 'verify_nonce');
			$id = esc_attr($_POST['id']);
			if(isset($_POST['script_action'])){
				$script_action = esc_attr($_POST['script_action']);
				update_post_meta($id, 'SCORG_enable_script', $script_action);
			}
			if(isset($_POST['frontend_only'])){
				$frontend_only = esc_attr($_POST['frontend_only']);
				update_post_meta($id, 'SCORG_only_frontend', $frontend_only);
			}
			if(isset($_POST['SCORG_view'])){
				$SCORG_view = esc_attr($_POST['SCORG_view']);
				update_post_meta($id, 'SCORG_view', $SCORG_view);
			}
			if(isset($_POST['focus_mode'])){
				$focus_mode = esc_attr($_POST['focus_mode']);
				update_post_meta($id, 'SCORG_focus_mode', $focus_mode);
			}
			if(isset($_POST['sidebar_settings'])){
				$sidebar_settings = esc_attr($_POST['sidebar_settings']);
				update_post_meta($id, 'SCORG_sidebar_settings', $sidebar_settings);
			}
			if(isset($_POST['page_settings'])){
				$page_settings = esc_attr($_POST['page_settings']);
				update_post_meta($id, 'SCORG_page_settings', $page_settings);
			}
			if(isset($_POST['scorg_darkmode'])){
				$scorg_darkmode = esc_attr($_POST['scorg_darkmode']);
				update_option('scorg_darkmode', $scorg_darkmode);
			}	
			
			echo "updated";
			wp_die();
		}

		public function SCORG_checkphp_for_errors(){
			$screen = get_current_screen();
			if($screen->id == "scorg" || $screen->id == "edit-scorg"){ ?>
				<style>
					.dp--on-load__hide{	display: none;}
					.dp--on-load__opacity{ opacity: 0; }
					.dp--on-load__opacity label{ opacity: 0; }
					.dp--hidden{	display: none !important;	}
				</style>
				<?php
				if(isset($_GET['scorg_checkphp'])){
					$post_id = false;
					if ( isset( $_GET['post'] ) ) {
					    $post_id = intval( $_GET['post'] );
					} elseif ( isset( $_POST['post_ID'] ) ) {
					    $post_id = intval( $_POST['post_ID'] );
					}
					$php_script = SCORG_is_base64(get_post_meta($post_id, 'SCORG_php_script', true));
					$SCORG_can_include_php = SCORG_can_include_php();
					if(!empty($php_script) && $SCORG_can_include_php == "no"){
						$php_script_file = SCORG_UPLOADS_DIR.'/'.$post_id.'.php';
							ob_start();
							// php code goes here
							try {
								$post_edit_link = get_edit_post_link($post_id);
		    					$go_back_url = remove_query_arg('scorg_checkphp', $post_edit_link);
								require_once $php_script_file;
							} catch(Exception $e){
							    //echo $e->getMessage();
							}
							$all_output = ob_get_clean();
					}
				}
			}
		}


		public function SCORG_rwmb_before($rwmb_meta){
			if(!is_admin()){
				return;
			}
			$screen = get_current_screen();
			if($screen->id != "scorg" || $rwmb_meta->meta_box['id'] == "codeblocks"){
				return;
			}
			echo '	<div class="dp--scorg-wrapper">';
		}


		public function SCORG_rwmb_after($rwmb_meta){
			if(!is_admin()){
				return;
			}
			$screen = get_current_screen();
			if($screen->id != "scorg" || $rwmb_meta->meta_box['id'] == "codeblocks"){
				return;
			}
			echo '	</div>';
		}


		public function SCORG_outer_html($outer_html, $field, $meta){
			if(!is_admin()){
				return $outer_html;
			}
			$screen = get_current_screen();
			if($screen->id != "scorg"){
				return $outer_html;
			}
			global $post;
			$SCORG_toggle_sidebar = get_post_meta($post->ID, 'SCORG_toggle_sidebar', true);
			$hide_sidebar = '';
			if($SCORG_toggle_sidebar == "yes"){
				$hide_sidebar = 'hide-sidebar';
			}
			$SCORG_view = get_post_meta($post->ID, 'SCORG_view', true);
			$editor_main_class = "";
			$columns_direction_class = "";
			if($SCORG_view == "yes"){
				$editor_main_class = "column-active";
				$columns_direction_class = "active";
			}
			$new_outer_html = "";
			if($field['id'] == "SCORG_enable_script"){
				$new_outer_html = '<div class="dp--scorg-column dp--settings-sidebar '.$hide_sidebar.'"><div class="toggle-sidebar">Toggle sidebar</div>';
			}
			if($field['id'] == "SCORG_header_script"){
				$new_outer_html = '</div><div class="wrap-editor-and-iframe"><div class="dp--scorg-column dp--editor-main panel-left '.$editor_main_class.'">
				<a href="#" class="top__btn preview" id="dp--show-hide-preview-full-view">Preview</a>';
			}
			if($field['id'] == "SCORG_tab_header"){
				$new_outer_html = '<div class="tabheader">';
			}
			if($field['id'] == "SCORG_last_field"){
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
			if($field['id'] == "SCORG_header_mode"){
				$new_outer_html = '</div><div id="header" class="tabcontent dp--show-header-box dp-monaco-box" style="display:none;"><div class="dp--header-wrap">';
			}
			if($field['id'] == "SCORG_header_before_color_picker" || $field['id'] == "SCORG_footer_before_color_picker"){
				$new_outer_html = '</div>';
			}
			if($field['id'] == "SCORG_footer_mode"){
				$new_outer_html = '</div><div id="footer" class="tabcontent dp--show-footer-box dp-monaco-box" style="display:none;"><div class="dp--footer-wrap">';
			}
			if($field['id'] == "SCORG_php_close"){
				$new_outer_html = '</div><div id="php" class="tabcontent dp--show-php-box dp-monaco-box" style="display:none;">';
			}
			if($field['id'] == "SCORG_view"){
				$new_outer_html = '</div>';
			}
			$new_outer_html .= $outer_html;

			return $new_outer_html;
		}

		public function get_all_scripts_grouped_by_type(){
			global $wpdb;
			$return_data = array();
			$return_data['register'] = array();
			$return_data['enqueue'] = array();
			$swiss_knife_scripts = $wpdb->prefix . 'swiss_knife_scripts';
			if(class_exists('SCORG_scripts_manager')){
				$SCORG_scripts_manager = new SCORG_scripts_manager();
				if($SCORG_scripts_manager->check_table_exists()){
					$swiss_knife_scripts = $wpdb->prefix . 'swiss_knife_scripts';
					$all_scripts = $wpdb->get_results( "SELECT id, script_name, script_include_type FROM $swiss_knife_scripts ORDER BY script_order ASC" );
					if(!empty($all_scripts)){
						foreach($all_scripts as $script){
							if($script->script_include_type == "register"){
								$return_data['register'][$script->id] = $script->script_name;
							} else {
								$return_data['enqueue'][$script->id] = $script->script_name;
							}
						}
					}
				}
			}

			return $return_data;
		}


		/**
		 * Adds one or more classes to the body tag in the scripts dashboard.
		*/
		public function SCORG_admin_body_class( $classes ) {
			$screen = get_current_screen();
			$classes_append = "";
			
			if($screen->id == "scorg"){
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
			if($screen->id == "scorg"){
				$classes_append .= " scorg-edit-scripts ";
			}

			$classes .= " $classes_append";

			return $classes;
		}

		public function filter_html( $safe_text, $text ){
			return $text;
		}
	}
	new SCORG_Post();
}