<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*====================================================
=            Create Options page and Menu            =
====================================================*/

if(!class_exists('SCORG_admin')){
	class SCORG_admin {
		public function __construct(){
			add_filter( 'plugin_action_links_scripts-organizer/scripts-organizer.php', array($this, 'settings_link') );
			add_action( 'admin_menu', array($this, 'SCORG_license_menu_page') );
			add_action( 'init', array($this, 'SCORG_load_php_scripts_file') );
			add_action( 'init', array($this, 'SCORG_load_files_if_license') );
			add_action( 'shutdown', array($this, 'SCORG_shutdown'), -1 );
			add_action( 'admin_init', array($this, 'add_role_caps'), 999 );
		}

		public function settings_link($links){
			// Build and escape the URL.
			$url = esc_url( add_query_arg(
				'page',
				'scorg_features',
				get_admin_url() . 'admin.php'
			) );
			// Create the link.
			$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
			// Adds the link to the end of the array.
			array_unshift(
				$links,
				$settings_link
			);

			$url = esc_url( add_query_arg(
				'post_type',
				'scorg',
				get_admin_url() . 'edit.php'
			) );
			// Create the link.
			$settings_link = "<a href='$url'>" . __( 'Code' ) . '</a>';
			// Adds the link to the end of the array.
			array_unshift(
				$links,
				$settings_link
			);
		
			return $links;
		}

		function add_role_caps() {
			// Add the roles you'd like to administer the custom post types
			$roles = array('administrator');
			
			// Loop through each role and assign capabilities
			foreach($roles as $the_role) { 
				$role = get_role($the_role);
				$role->add_cap( 'read' );
				$role->add_cap( 'read_SCORG_option');
				$role->add_cap( 'read_private_SCORG_options' );
				$role->add_cap( 'edit_SCORG_option' );
				$role->add_cap( 'edit_SCORG_options' );
				$role->add_cap( 'edit_others_SCORG_options' );
				$role->add_cap( 'edit_published_SCORG_options' );
				$role->add_cap( 'publish_SCORG_options' );
				$role->add_cap( 'delete_SCORG_options' );
				$role->add_cap( 'delete_others_SCORG_options' );
				$role->add_cap( 'delete_private_SCORG_options' );
				$role->add_cap( 'delete_published_SCORG_options' );
			}
		}

		public function SCORG_license_menu_page() {
			add_menu_page( 
				'Scripts Organizer', 
				'Scripts Organizer', 
				'manage_options', 
				SCORG_SAMPLE_PLUGIN_LICENSE_PAGE, 
				array($this, 'SCORG_license_page'),
				// 'dashicons-editor-code'
				'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMjJweCIgaGVpZ2h0PSIxOXB4IiB2aWV3Qm94PSIwIDAgMjIgMTkiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+U2hhcGU8L3RpdGxlPgogICAgPGcgaWQ9IlRodW1iIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4KICAgICAgICA8cGF0aCBkPSJNMjEuMzE3Njc3LDAgQzIxLjY2MDI5NzQsMCAyMS45NDM4NzQ5LDAuMjU1MDg3ODAyIDIxLjk5MjYwMjksMC41ODczNjM2NjggTDIyLDAuNjg5MTg0MTcgTDIyLDE4LjMxMDgxNTggQzIyLDE4LjY1Njc1MDMgMjEuNzQ3NTY5OCwxOC45NDMyODY3IDIxLjQxODUxNiwxOC45OTI1MjUzIEwyMS4zMTc2NzcsMTkgTDAuNjgyNTgxODksMTkgQzAuMzM5NzI2MDkyLDE5IDAuMDU2MTI3MjA3NywxOC43NDQ5MTIyIDAuMDA3Mzk3MjkzMiwxOC40MTI2MzYzIEwwLDE4LjMxMDgxNTggTDAsMC42ODkxODQxNyBDMCwwLjM0MzI0OTY4OCAwLjI1MjQzMDE3NSwwLjA1NjcxMzI5NzMgMC41ODE2Nzg0NywwLjAwNzQ3NDczNDMyIEwwLjY4MjU4MTg5LDAgTDIxLjMxNzY3NywwIFogTTIwLDUgTDIsNSBMMiwxNyBMMjAsMTcgTDIwLDUgWiBNNSw2IEwxMCwxMC41IEw1LDE1IEw0LDE0IEw4LDEwLjUgTDQsNyBMNSw2IFogTTE3LDEzIEwxNywxNSBMMTEsMTUgTDExLDEzIEwxNywxMyBaIE0yLjY5MTc1OTIyLDEuNTk0NzY3NDcgQzIuMjAwNDY1OTMsMS41OTQ3Njc0NyAxLjgxMjE5NDA5LDIuMDEyMTQ1OTcgMS44MTIxOTQwOSwyLjQ4Mjg0MDIgQzEuODEyMTk0MDksMi45NTM1MzQ0NCAyLjIwMDQ2NTkzLDMuMzcxOTU4MzUgMi42OTE3NTkyMiwzLjM3MDkxODc4IEMzLjE4MzMxMTM3LDMuMzcwOTE4NzggMy41NzE1ODMyMSwyLjk3ODg4NTU0IDMuNTcxNTgzMjEsMi40ODI4NDAyIEMzLjU3MTU4MzIxLDEuOTg2Nzk0ODcgMy4xODMzMTEzNywxLjU5NDc2NzQ3IDIuNjkxNzU5MjIsMS41OTQ3Njc0NyBaIE01LjMzMjc4NDI2LDEuNTk0NzY3NDcgQzQuODQwNDU1NTcsMS41OTQ3Njc0NyA0LjQ1MjE4MzczLDEuOTg2Nzk0ODcgNC40NTMyMTI5NSwyLjQ4Mjg0MDIgQzQuNDUzMjEyOTUsMi45Nzg4ODU1NCA0Ljg0MTQ5MDk2LDMuMzcwOTEyOTQgNS4zMzI3ODQyNiwzLjM3MDkxMjk0IEM1LjgyNDMzNjQxLDMuMzcwOTEyOTQgNi4yMTI2MDgyNSwyLjk3ODg4NTU0IDYuMjEyNjA4MjUsMi40ODI4NDAyIEM2LjIxMjYwODI1LDEuOTg2Nzk0ODcgNS44MjQzMzY0MSwxLjU5NDc2NzQ3IDUuMzMyNzg0MjYsMS41OTQ3Njc0NyBaIE03Ljk3Mjc3MzkxLDEuNTk0NzY3NDcgQzcuNDgwNDQ1MjIsMS41OTQ3Njc0NyA3LjA5MjE3MzM4LDEuOTg2Nzk0ODcgNy4wOTI5NDY0NSwyLjQ4Mjg0MDIgQzcuMDkyOTQ2NDUsMi45Nzg4ODU1NCA3LjQ4MTIyMTc2LDMuMzcwOTEyOTQgNy45NzI3NzM5MSwzLjM3MDkxMjk0IEM4LjQ2NDA2NzIxLDMuMzcwOTEyOTQgOC44NTIzMzkwNCwyLjk3ODg4NTU0IDguODUyMzM5MDQsMi40ODI4NDAyIEM4Ljg1MjMzOTA0LDEuOTg2Nzk0ODcgOC40NjQwNjcyMSwxLjU5NDc2NzQ3IDcuOTcyNzczOTEsMS41OTQ3Njc0NyBaIiBpZD0iU2hhcGUiIGZpbGw9IiNGRkZGRkYiPjwvcGF0aD4KICAgIDwvZz4KPC9zdmc+'
			);

			$status  = get_option( 'scorg_license_status' );
			if( $status !== false && $status == 'valid' ) {
				
				$scorg_scripts_manager  = get_option( 'scorg_scripts_manager' );
				if($scorg_scripts_manager == "yes"){
					add_submenu_page( SCORG_SAMPLE_PLUGIN_LICENSE_PAGE, 'Scripts Manager', 'Scripts Manager', 'manage_options', 'scorg_scripts_manager', array($this, 'SCORG_scripts_manager_page') );
				}
				
				add_submenu_page( SCORG_SAMPLE_PLUGIN_LICENSE_PAGE, 'Import', 'Import', 'manage_options', 'scorg_import_export', array($this, 'SCORG_import_export') );
				
				add_submenu_page( SCORG_SAMPLE_PLUGIN_LICENSE_PAGE, 'Features', 'Features', 'manage_options', 'scorg_features', array($this, 'SCORG_features_page') );
			}

			add_submenu_page( SCORG_SAMPLE_PLUGIN_LICENSE_PAGE, 'License', 'License & Support', 'manage_options', SCORG_SAMPLE_PLUGIN_LICENSE_PAGE, array($this, 'SCORG_license_page'), 99999 );

			// if metabox plugin is not active 
			if(!is_plugin_active( 'meta-box/meta-box.php' )){
				remove_menu_page( 'meta-box' );
			}
		}

		/*====================================
		=         Scripts Manager page       =
		====================================*/
		public function SCORG_scripts_manager_page() {
			$status  = get_option( 'scorg_license_status' );
			if( $status !== false && $status == 'valid' ) { 
				require_once SCORG_DIR . 'admin/feature__scripts-manager.php';
			} else {
				echo '<h2 class="warning-licence-not-active">Activate licence to see the features.</h2>';
			}
		}

		/*====================================
		=           Import/Export page       =
		====================================*/
		public function SCORG_import_export() {
			$status  = get_option( 'scorg_license_status' );
			if( $status !== false && $status == 'valid' ) { 
				require_once SCORG_DIR . 'admin/feature__import-export.php';
			} else {
				echo '<h2 class="warning-licence-not-active">Activate licence to see the features.</h2>';
			}
		}


		/*====================================
		=         Featurs Manager page       =
		====================================*/
		public function SCORG_features_page() {
			$status  = get_option( 'scorg_license_status' );
			?>
			<div class="wrap">
				<h2><?php _e('Scripts Organizer Features'); ?></h2>
				<div id="Features" class="of-tabs">
					<?php 
						if( $status !== false && $status == 'valid' ) { 
							require_once('admin__feature-form.php'); 
						} else {
							echo '<h2 class="warning-licence-not-active">Activate licence to see the features.</h2>';
						}
					?>
				</div> <!-- End of Settings -->
			<?php
		}

		public function SCORG_license_page(){
			$license = get_option( 'scorg_license_key' );
			$status  = get_option( 'scorg_license_status' );
			?>
			<div class="wrap">
				<h2><?php _e('Scripts Organizer Settings'); ?></h2>
				<div id="License" class="of-tabs">
					<form method="post" action="options.php" id="license-form">

						<?php settings_fields('SCORG_license'); ?>

						<div id="licence-form" class="swk_admin_card">
						
							<div class="swk_admin_header">
								<h3>Scripts Organizer License</h3>
								<h4>To get you started, please <b>activate your license first</b></h4>			
							</div><!-- End of swk_admin_header -->
							<div class="swk_admin_body">
								<label class="description" for="scorg_license_key"><?php _e('Enter your license key'); ?></label>
								<input id="scorg_license_key" name="scorg_license_key" type="password" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />	

								<?php if( $status !== false && $status == 'valid' ) { ?>
									<div class="licence-status">
										Status: <span class="licence-status-active"><?php _e('active'); ?></span>
									</div>

									<?php wp_nonce_field( 'scorg_nonce', 'scorg_nonce' ); ?>
								
									<input type="submit" class="button-secondary " class="scorg_license_deactivate" name="scorg_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
								<?php } else {
									wp_nonce_field( 'scorg_nonce', 'scorg_nonce' );
									submit_button( 'Activate License', 'primary', 'scorg_license_activate', true, array() );
								} ?>	
							</div><!-- End of swk_admin_body -->

						</div><!-- End of swk_admin_card -->

					</form>

					<?php if( is_plugin_active( 'scripts-organizer--gutenberg-acf/scripts-organizer--gutenberg-acf.php' )){
						$license = get_option( 'scorg_ga_license_key' );
						$status  = get_option( 'scorg_ga_license_status' ); ?>
						<form method="post" action="options.php" id="license-form">

							<?php settings_fields('SCORG_ga_license'); ?>

							<div id="licence-ga-form" class="swk_admin_card">
							
								<div class="swk_admin_header">
									<h3>ACF Gutenberg Add on License</h3>
									<h4>To get you started, please <b>activate your license first</b></h4>			
								</div><!-- End of swk_admin_header -->
								<div class="swk_admin_body">
									<label class="description" for="scorg_ga_license_key"><?php _e('Enter your license key'); ?></label>
									<input id="scorg_ga_license_key" name="scorg_ga_license_key" type="password" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />	

									<?php if( $status !== false && $status == 'valid' ) { ?>
										<div class="licence-status">
											Status: <span class="licence-status-active"><?php _e('active'); ?></span>
										</div>

										<?php wp_nonce_field( 'scorg_ga_nonce', 'scorg_ga_nonce' ); ?>
									
										<input type="submit" class="button-secondary " class="scorg_ga_license_deactivate" name="scorg_ga_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
									<?php } else {
										wp_nonce_field( 'scorg_ga_nonce', 'scorg_ga_nonce' );
										submit_button( 'Activate License', 'primary', 'scorg_ga_license_activate', true, array() );
									} ?>	
								</div><!-- End of swk_admin_body -->

							</div><!-- End of swk_admin_card -->

						</form>
					<?php } ?>

					<div class="swk_admin_card">
						
							<div class="swk_admin_header">
								<h3>Admin links</h3>
							</div><!-- End of swk_admin_header -->
							<div class="swk_admin_body">
								<h4>Admin area</h4>
								<p>Access your account, download files, licenses or generate the invoice</p> 
								<a target="_blank" href="https://dplugins.com/login/" class="swk-links">dplugins.com/login/</a>
								<br>
								<br>
								
								<h4>Support</h4> 
								<a target="_blank" href="https://dplugins.com/support/" class="swk-links">dplugins.com/support/</a>						
								<br>
								<br>

								<h4>Changelog</h4> 
								<a target="_blank" href="https://dplugins.com/scripts-organizer-changelog/" class="swk-links">dplugins.com/scripts-organizer-changelog/</a>											
								<br>
								<br>					
							</div><!-- End of swk_admin_body -->

					</div><!-- End of swk_admin_card -->


					<div class="swk_admin_card">
						
							<div class="swk_admin_header">
								<h3>Useful links to keep you with the updates</h3>
							</div><!-- End of swk_admin_header -->
							<div class="swk_admin_body">
								<h4>Facebook group</h4> 
								<a target="_blank" href="https://www.facebook.com/groups/dplugins" class="swk-links">facebook.com/groups/dplugins</a>
								<br>
								<br>
								
								<h4>Newsletter</h4> 
								<a target="_blank" href="https://dplugins.com/newsletter/" class="swk-links">dplugins.com/newsletter/</a>						
								<br>
								<br>

								<h4>Youtube</h4> 
								<a target="_blank" href="https://www.youtube.com/channel/UCTfzTqbcMrdzAR9qRbY8a5w" class="swk-links">youtube.com/channel/UCTfzTqbcMrdzAR9qRbY8a5w</a>
								<br>
								<br>					
							</div><!-- End of swk_admin_body -->

					</div><!-- End of swk_admin_card -->

				</div> <!-- End of Licence -->
			<?php
		}

		public function SCORG_load_files_if_license(){
			/**
			 * Including files if licnese is active
			*/
			$status  = get_option( 'scorg_license_status' );
			if( $status !== false && $status == 'valid' ) {
				if ( !class_exists( 'RWMB_Core' ) ) {
				    require_once SCORG_DIR . 'plugins/meta-box/meta-box.php';
				}

				/* scorg post type */
				register_post_type(
					'scorg',
					array(
					'labels' => array(
						'name' => __('Code Blocks', 'scorg'),
						'singular_name' => __('Code Block', 'scorg'),
						'all_items'           => __( 'Code Blocks', 'scorg' ),
				        'view_item'           => __( 'View Code Block', 'scorg' ),
				        'add_new_item'        => __( 'Add New Code Block', 'scorg' ),
				        'add_new'             => __( 'Add New', 'scorg' ),
				        'edit_item'           => __( 'Edit Code Block Title', 'scorg' ),
				        'update_item'         => __( 'Update Code Block', 'scorg' ),
				        'search_items'        => __( 'Search Code Block', 'scorg' ),
				        'not_found'           => __( 'No code block found.', 'scorg' ),
				        'not_found_in_trash'  => __( 'No code block found in Trash', 'scorg' ),
					),
					'supports' => array( 
						'title', 'revisions', 'page-attributes',
					),
					//'menu_icon' => 'dashicons-admin-generic',
					'public' => true,
					'publicly_queryable' => false,
					'has_archive' => false,
					//'rewrite' => array('slug' => 'hf_scripts'),
					'capability_type'     => array('SCORG_option', 'SCORG_options'),
					'map_meta_cap'        => true,
					'exclude_from_search' => true,
					'show_in_menu' => SCORG_SAMPLE_PLUGIN_LICENSE_PAGE
					)
				);

				/* scorg_scss post type */
				register_post_type(
					'scorg_scss',
					array(
					'labels' => array(
						'name' => __('SCSS Partials', 'scorg'),
						'singular_name' => __('SCSS Partial', 'scorg'),
						'all_items'           => __( 'SCSS Partials', 'scorg' ),
				        'view_item'           => __( 'View SCSS Partial', 'scorg' ),
				        'add_new_item'        => __( 'Add New SCSS Partial', 'scorg' ),
				        'add_new'             => __( 'Add New', 'scorg' ),
				        'edit_item'           => __( 'Edit SCSS Partial Title', 'scorg' ),
				        'update_item'         => __( 'Update SCSS Partial', 'scorg' ),
				        'search_items'        => __( 'Search SCSS Partial', 'scorg' ),
				        'not_found'           => __( 'No code block found.', 'scorg' ),
				        'not_found_in_trash'  => __( 'No code block found in Trash', 'scorg' ),
					),
					'supports' => array( 
						'title', 'revisions',
					),
					//'menu_icon' => 'dashicons-admin-generic',
					'public' => true,
					'publicly_queryable' => false,
					'has_archive' => false,
					//'rewrite' => array('slug' => 'hf_scripts'),
					'capability_type'     => array('SCORG_option', 'SCORG_options'),
					'map_meta_cap'        => true,
					'exclude_from_search' => true,
					'show_in_menu' => SCORG_SAMPLE_PLUGIN_LICENSE_PAGE
					)
				);
				require_once SCORG_DIR . 'admin/admin__features-settings.php';
				require_once SCORG_DIR . 'admin/feature__scripts-manager-functions.php';
				require_once SCORG_DIR . 'includes/scorg-post.php';
				require_once SCORG_DIR . 'includes/scorg-scss.php';
				require_once SCORG_DIR . 'includes/scorg-header-footer-files.php';
				require_once SCORG_DIR . 'includes/scorg-enqueue-dequeue.php';
				require_once SCORG_DIR . 'includes/scorg-script-display.php';
				require_once SCORG_DIR . 'includes/scorg-shortcode.php';

				/**
				* register tags taxonomy
				*/
				$labels = array(
					'name'              => _x( 'Script Tags', 'taxonomy general name', 'scorg' ),
					'singular_name'     => _x( 'Script Tag', 'taxonomy singular name', 'scorg' ),
					'search_items'      => __( 'Search Script Tags', 'scorg' ),
					'all_items'         => __( 'All Script Tags', 'scorg' ),
					'parent_item'       => __( 'Parent Script Tag', 'scorg' ),
					'parent_item_colon' => __( 'Parent Script Tag:', 'scorg' ),
					'edit_item'         => __( 'Edit Script Tag', 'scorg' ),
					'update_item'       => __( 'Update Script Tag', 'scorg' ),
					'add_new_item'      => __( 'Add New Script Tag', 'scorg' ),
					'new_item_name'     => __( 'New Script Tag Name', 'scorg' ),
					'menu_name'         => __( 'Script Tags', 'scorg' ),
				);

				$args = array(
					'hierarchical'      => false,
					'labels'            => $labels,
					'show_ui'           => true,
					'public'           	=> false,
					'publicly_queryable'=> false,
					'show_admin_column' => true,
					'query_var'         => true,
					'rewrite'           => array( 'slug' => 'scorg_tags' ),
				);

				register_taxonomy( 'scorg_tags', array( 'scorg', 'scorg_scss' ), $args );

				/* disable oxygen on scripts */
				update_option('oxygen_vsb_ignore_post_type_scorg', 'true');
				update_option('oxygen_vsb_ignore_post_type_scorg_scss', 'true');
			}
		}


		public function SCORG_load_php_scripts_file(){
			/**
			 * Including php scripts if licnese is active
			*/
			$status  = get_option( 'scorg_license_status' );
			if( $status !== false && $status == 'valid' ) {
				require_once SCORG_DIR . 'includes/scorg-php-scripts.php';
			}
		}

		public function SCORG_shutdown(){
			$scorg_safemode = get_option('scorg_safemode');
			if($scorg_safemode == "yes") return;
			require_once(ABSPATH.'wp-includes/pluggable.php');
			if(!is_user_logged_in()){
				return;
			}
			if(is_user_logged_in() && !current_user_can('administrator')){
				return;
			}
			$error = error_get_last();
			if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_WARNING, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING])) {
			    $error_file = $error['file'];
			    if (strpos($error_file, SCORG_ERROR_LOG_CHECK) !== false) {
			    	global $wp;
					//$current_url = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
					$script_id = basename($error_file);
					$script_id = str_replace(".php", "", $script_id);
					$safe_mode_url = site_url().'/wp-admin/post.php?post='.$script_id.'&action=edit&scorg_safemode=no';
				    $safe_mode_enable_url = add_query_arg('scorg_safemode', "yes", $safe_mode_url);
				    ?>
				    <div id="enable-scorg_safemode">

						<svg viewBox="0 0 100 100">
							<path d="m50 100c-53.82-22.02-39.902-55.797-44.066-86.441 17.664-0.3125 32.031-5.2891 44.066-13.559 12.035 8.2695 26.402 13.246 44.066 13.559-4.168 30.645 9.7539 64.422-44.066 86.441zm-17.191-52.996 9.8984 9.8438 24.465-24.465 7.1914 7.1914-31.617 31.617-17.086-17 7.1523-7.1875z" fill-rule="evenodd" />
						</svg>
						
						<div class="safemode-content">
							<h1><span>Scripts Organizer</span><br>Safe Mode is Not active</h1>
							<p>Your website is broken due to an error in: <span class="safe-mode-code-line"><?= $error_file ?></span></p>
							<?php
								if(!empty($error)){
									echo '<p><strong>Error:</strong> '.$error['message'] . ' at line # ' . $error['line'] .'</p>';
								}
							?>
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
		}
	}

	new SCORG_admin();
}