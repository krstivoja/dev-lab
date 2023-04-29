<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*====================================================
=            Create Options page and Menu            =
====================================================*/

if(!class_exists('SCORG_reorder')){
	class SCORG_reorder {
		public function __construct(){
			add_action( 'admin_menu', array($this, 'reorder_page') );
            add_action( 'init', array($this, 'reorder_boot'), 20 );
            add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_script'), 1000 );
            add_action( 'pre_get_posts', array($this, 'scorg_admin_posts_sort') );
		}

        public function scorg_admin_posts_sort( $query ){
            global $pagenow;
            if( is_admin()
                && 'edit.php' == $pagenow
                && !isset( $_GET['orderby'] )
                && isset( $_GET['post_type'] )
                && $_GET['post_type'] == "scorg" ){
                    $query->set( 'orderby', 'menu_order' );
                    $query->set( 'order', 'ASC' );
            }
        }

        public function reorder_boot() {
		    if (
		        $_SERVER['REQUEST_METHOD'] === 'POST'
		        && isset( $_REQUEST["reorder_action"] )
		        && wp_verify_nonce( $_REQUEST["_reorder_nonce"], "reorder_nonce" )
		    ) {
		        $code_block_ids = $_POST['code_block_order'];
                global $wpdb;
                $posts = $wpdb->prefix . 'posts';
                $i = 1;
                foreach($code_block_ids as $code_block_id){
                    $wpdb->update( $posts, 
                        array( 
                            'menu_order' => $i,
                        ),
                        array( 'ID' => $code_block_id )
                    );
                    $i++;
                }
		    }
		}

        public function reorder_page() {
			$status  = get_option( 'scorg_license_status' );
			if( $status !== false && $status == 'valid' ) {
				add_submenu_page( SCORG_SAMPLE_PLUGIN_LICENSE_PAGE, 'Code Blocks Order', 'Code Blocks Order', 'manage_options', 'scorg_reorder_option', array($this, 'reorder_html'), 2 );
			}
		}

		public function enqueue_admin_script() {
			$screen = get_current_screen();
            if($screen->id == "scripts-organizer_page_scorg_reorder_option"){
				wp_enqueue_style( 'SCORG-reorder', SCORG_URL . '/admin/css/reorder.css', array(), time());
                wp_enqueue_style( 'SCORG-reorder-jquery-ui', SCORG_URL . '/admin/css/jquery-ui.css', array(), time());
				wp_enqueue_script( 'SCORG-reorder-jquery-ui', SCORG_URL.'admin/js/jquery-ui.min.js', array('jquery'), time() );
				wp_enqueue_script( 'SCORG-reorder', SCORG_URL.'admin/js/reorder.js', array('jquery'), time() );
			}
		}

        public function reorder_html(){
            $status  = get_option( 'scorg_license_status' );
			if( $status !== false && $status == 'valid' ) { 
				$html = '<div id="layout"><h2>Code Blocks Order</h2>';
                    global $wpdb;
                    $posts = $wpdb->prefix . 'posts';
                    $code_blocks = $wpdb->get_results("SELECT ID, post_title FROM $posts WHERE post_type = 'scorg' AND post_status NOT IN('revision', 'auto-draft', 'trash') ORDER BY menu_order ASC", OBJECT);
                    if($code_blocks){
                        $html .= '<form id="reorder-form" method="post" action=""><ul id="reorder-code-blocks" class="swk_admin_card">';
                        foreach($code_blocks as $code_block){
                            $html .= '<li><span class="move"></span>
                                '.$code_block->post_title.'
                                <input type="hidden" name="code_block_order[]" value="'.$code_block->ID.'" />
                            </li>';
                        }
                        $html .= '</ul>
                            <button class="button-primary" name="reorder_action">Save</button>
                            <input type="hidden" name="_reorder_nonce" value="'.wp_create_nonce('reorder_nonce', false, false).'">
                        </form>';
                    }
                $html .= '</div>';
                echo $html;
			} else {
				echo '<h2 class="warning-licence-not-active">Activate licence to see the oxygen stylesheets.</h2>';
			}
        }
	}

	new SCORG_reorder();
}
