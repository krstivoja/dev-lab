<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*====================================================
=            Create Options page and Menu            =
====================================================*/

if(!class_exists('SCORG_oxy_stylesheets')){
	class SCORG_oxy_stylesheets {
		public function __construct(){
			if (defined('CT_VERSION')) {
				add_action( 'admin_menu', array($this, 'oxy_stylesheets_page') );
				add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_script'), 1000 );
				add_action( 'wp_ajax_saveOxyST', array($this, 'save_oxy_stylesheet') );
				add_action( 'wp_ajax_saveOxyStylesheetOptions', array($this, 'save_oxy_stylesheet_options') );
			}
		}

        public function oxy_stylesheets_page() {
			$status  = get_option( 'scorg_license_status' );
			if( $status !== false && $status == 'valid' ) {
				add_submenu_page( SCORG_SAMPLE_PLUGIN_LICENSE_PAGE, 'Oxygen Stylesheets', 'Oxygen Stylesheets', 'manage_options', 'scorg_oxy_stylesheet', array($this, 'oxygen_stylesheets_html'), 2 );
			}
		}

		public function enqueue_admin_script() {
			$screen = get_current_screen();
			if($screen->id == "scripts-organizer_page_scorg_oxy_stylesheet"){
				wp_enqueue_style( 'SCORG-oxy-stylesheet', SCORG_URL . '/admin/css/oxygen_stylesheets.css', array(), time());
				$vs_theme = "light";
				$vs_font_size = "14";
				$scorg_darkmode = get_option('scorg_darkmode');
				$scorg_fontsize = get_option('scorg_fontsize');
				if($scorg_darkmode == "yes"){
					//wp_enqueue_style( 'SCORG_darkmode', SCORG_URL . 'admin/css/darkmode.css', array(), time());
					$vs_theme = "dark";
				}
				if($scorg_fontsize == "yes"){
					$vs_font_size = get_option('scorg_font_value');
				}
				wp_enqueue_script( 'mousetrap_scripts', SCORG_URL . 'admin/js/mousetrap.min.js', array(), time() , true );
				wp_enqueue_style( 'monaco', SCORG_URL . 'admin/js/node_modules/monaco-editor/min/vs/editor/editor.main.css', array(), time());
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( "monaco-loader", SCORG_URL.'admin/js/node_modules/monaco-editor/min/vs/loader.js', array('jquery'), time(), true );
				wp_enqueue_script( "monaco-editor-nls", SCORG_URL.'admin/js/node_modules/monaco-editor/min/vs/editor/editor.main.nls.js', array('jquery'), time(), true );
				wp_enqueue_script( "monaco-editor", SCORG_URL.'admin/js/node_modules/monaco-editor/min/vs/editor/editor.main.js', array('jquery'), time(), true );
				wp_enqueue_script( "monaco-emmet", SCORG_URL.'admin/js/node_modules/emmet-monaco-es/dist/emmet-monaco.min.js', array('monaco-editor'), time(), true );
				wp_enqueue_script( "SCORG_oxy_ajax", SCORG_URL.'admin/js/oxy-stylesheets.js', array('jquery'), time() );
				wp_localize_script( 'SCORG_oxy_ajax', 'SCORG_oxy_ajax', array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' ), 
					'scorg_vs' => SCORG_URL.'admin/js/node_modules/monaco-editor/min/vs',
					'vs_theme' => $vs_theme,
					'vs_font_size' => $vs_font_size,
					'oxy_st_nonce' => wp_create_nonce('ajax-nonce'),
				));
			}
		}

        public function oxygen_stylesheets_html(){
            $status  = get_option( 'scorg_license_status' );
			if( $status !== false && $status == 'valid' ) { 
				// check Oxygen plugin state and user access
				if (function_exists('oxygen_vsb_current_user_can_full_access') || oxygen_vsb_current_user_can_full_access()) {
					$stylesheets = get_option('ct_style_sheets');
					//echo "<pre>"; print_r($stylesheets); "</pre>";
					$st_code = '';
					$enabled_stylesheets = array();
					$enabled_stylesheets['s-0']['name'] = 'Uncategorized';
					$disabled_stylesheets = array();
					if(!empty($stylesheets)){
						foreach($stylesheets as $stylesheet){
							if(array_key_exists('folder', $stylesheet)){
								$s_id = 's-'.$stylesheet['id'];
								if($stylesheet['status']){
									$enabled_stylesheets[$s_id]['name'] = $stylesheet['name'];
									$enabled_stylesheets[$s_id]['stylesheets'] = array();
								} else {
									$disabled_stylesheets[$s_id]['name'] = $stylesheet['name'];
									$disabled_stylesheets[$s_id]['stylesheets'] = array();
								}
							}
						}
						foreach($stylesheets as $stylesheet){
							if(!array_key_exists('folder', $stylesheet)){
								$s_id = 's-'.$stylesheet['parent'];
								if(array_key_exists($s_id, $enabled_stylesheets)){
									$enabled_stylesheets[$s_id]['stylesheets'][] = array(
										'id' => $stylesheet['id'],
										'name' => $stylesheet['name'],
									);
								} else {
									$disabled_stylesheets[$s_id]['stylesheets'][] = array(
										'id' => $stylesheet['id'],
										'name' => $stylesheet['name'],
									);
								}
								if(isset($_GET['st_id']) && $_GET['st_id'] == $stylesheet['id']){
									$st_code = base64_decode($stylesheet['css']);
								}
							}
						}
					}

					$html = '<div id="header">
						<div id="success-message" style="display:none;"></div>
						<div id="saving-st" style="display:none;">Saving...</div>';
						if(isset($_GET['st_id'])){
							$html .= '<a href="#" data-stid="'.$_GET['st_id'].'" id="save-st" class="button-primary">Save</a>';
						}
					$html .= '</div>
					<div id="layout"><div id="sidebar">';

						if(!empty($enabled_stylesheets)){
							$html .= '<div id="enabled"><h2 class="header">Enabled</h2>';
							foreach($enabled_stylesheets as $key => $es){
								if(!empty($es['stylesheets'])){
									$html .= '<h4>'.$es['name'].'</h4><ul>';
									foreach($es['stylesheets'] as $stylesheet){
										$html .= '<li><a href="'.esc_url(admin_url().'admin.php?page=scorg_oxy_stylesheet&st_id='.$stylesheet['id']).'">'.$stylesheet['name'].'</a></li>';
									}
									$html .= '</ul>';
								}
							}
							$html .= '</div>';
						}
						if(!empty($disabled_stylesheets)){
							$html .= '<div id="disabled"><h2 class="header" style="margin-top: 30px">Disabled</h2>';
							foreach($disabled_stylesheets as $key => $ds){
								if(!empty($ds['stylesheets'])){
									$html .= '<h4>'.$ds['name'].'</h4><ul>';
									foreach($ds['stylesheets'] as $stylesheet){
										$html .= '<li><a href="'.esc_url(admin_url().'admin.php?page=scorg_oxy_stylesheet&st_id='.$stylesheet['id']).'">'.$stylesheet['name'].'</a></li>';
									}
									$html .= '</ul>';
								}
							}
							$html .= '</div>';
						}
					$html .= '<label class="sidebar__btn tooltip-wrap" id="theme-settings">
					<input type="checkbox" name="theme" value="theme">
						<svg height="36px" width="36px"><use xlink:href="#theme" /></svg>
						<span class="tooltip tooltip-right">Toggle Light/Dark Theme</span> 
					</label>
					</div>';
					if(isset($_GET['st_id'])){
						$html .= '<div id="editor">
							'.SCORG_oxy_colors().'
						</div>';
					} else {
						$html .= '<div id="blank-canvas">
							<div class="content">
								<svg width="225px" height="194px" viewBox="0 0 225 194" fill="currentColor">
									<g id="Page-1" stroke="none">
										<g id="Artboard" transform="translate(-136.000000, -101.000000)">
											<path d="M354.021696,101.44898 C357.795875,101.44898 360.869524,104.455001 360.995954,108.209027 L361,108.44964 L361,287.448319 C361,291.233154 358.00498,294.318025 354.261628,294.444919 L354.021696,294.44898 L142.980951,294.44898 C139.20418,294.44898 136.130478,291.442959 136.004046,287.688932 L136,287.448319 L136,108.44964 C136,104.664806 138.99502,101.579934 142.740857,101.45304 L142.980951,101.44898 L354.021696,101.44898 Z M347.236646,151.889838 L149.763354,151.889838 L149.763354,280.646768 L347.236646,280.646768 L347.236646,151.889838 Z M309,250.44898 L309,263.44898 L242,263.44898 L242,250.44898 L309,250.44898 Z M194.957346,164.44898 L244,213.450391 L194.957195,262.44898 L185,252.499705 L224.08228,213.447301 L185,174.397952 L194.957346,164.44898 Z M163.529356,117.64846 C158.504765,117.64846 154.533803,121.888147 154.533803,126.669409 C154.533803,131.450671 158.504765,135.700978 163.529356,135.690398 C168.556594,135.690398 172.527556,131.708185 172.527556,126.669409 C172.527556,121.630633 168.556594,117.64846 163.529356,117.64846 Z M190.539839,117.64846 C185.504659,117.64846 181.533697,121.630633 181.544244,126.669409 C181.544244,131.708185 185.515248,135.690358 190.539839,135.690358 C195.567077,135.690358 199.538039,131.708185 199.538039,126.669409 C199.538039,121.630633 195.567077,117.64846 190.539839,117.64846 Z M217.539733,117.64846 C212.504553,117.64846 208.533591,121.630633 208.54151,126.669409 C208.54151,131.708185 212.512495,135.690358 217.539733,135.690358 C222.564324,135.690358 226.535286,131.708185 226.535286,126.669409 C226.535286,121.630633 222.564324,117.64846 217.539733,117.64846 Z" id="logo"></path>
										</g>
									</g>
								</svg>
						
								Please choose one of the Stylesheets from the left to start editing.
								
							</div>
						</div>';
					}
					
					$html .= '</div>
					<textarea id="editor-code" style="display:none;">'.$st_code.'</textarea>';
					echo $html;
				
					include 'icons.php';
					
				}
			} else {
				echo '<h2 class="warning-licence-not-active">Activate licence to see the oxygen stylesheets.</h2>';
			}
        }

		public function save_oxy_stylesheet(){
			check_ajax_referer('ajax-nonce', 'verify_nonce');
			$return_data = array();
			$id = $_POST['id'];
			$code = urldecode(base64_decode($_POST['code']));

			$stylesheets = get_option('ct_style_sheets');
			$save_stylesheets = $stylesheets;
			$i = 0;
			foreach($stylesheets as $stylesheet){
				if(!array_key_exists('folder', $stylesheet) && $stylesheet['id'] == $id){
					$save_stylesheets[$i]['css'] = base64_encode($code);
					update_option('ct_style_sheets', $save_stylesheets);
					$return_data['status'] = 'success';
					// regenerate universal cache and show Oxygen's result message
					$result = oxygen_vsb_cache_universal_css();
					if ($result){
						$return_data['msg'] = 'Universal CSS cache generated successfully';
					} else {
						$return_data['msg'] = 'Universal CSS cache not generated.';
					}
				}
				$i++;
			}
	
			echo json_encode($return_data);
			
			wp_die();
		}
		
		public function save_oxy_stylesheet_options(){
			check_ajax_referer('ajax-nonce', 'verify_nonce');
			
			if(isset($_POST['scorg_darkmode'])){
				$scorg_darkmode = esc_attr($_POST['scorg_darkmode']);
				update_option('scorg_darkmode', $scorg_darkmode);
			}	
			
			echo "updated";
			
			wp_die();
		}
	}

	new SCORG_oxy_stylesheets();
}
