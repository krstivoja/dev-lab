<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_scripts_trash')){
	class SCORG_scripts_trash {
		public function __construct(){
            add_filter( 'handle_bulk_actions-edit-scorg', array($this, 'bulk_action_handler'), 10, 3 );
            add_filter( 'handle_bulk_actions-edit-scorg_scss', array($this, 'bulk_action_handler'), 10, 3 );
		}

        function bulk_action_handler( $redirect, $doaction, $object_ids ) {
            if(!empty($object_ids) && isset($_REQUEST['action2']) && $_REQUEST['action2'] == "scorg_bulk_delete"){
                foreach($object_ids as $object_id){
                    update_post_meta($object_id, 'SCORG_enable_script', 0);
                    wp_trash_post($object_id);
                }
                wp_redirect(admin_url().'edit.php?post_type=scorg');
                exit;
            }
            
            if(!empty($object_ids) && isset($_REQUEST['action2']) && $_REQUEST['action2'] == "scss_bulk_delete"){
                foreach($object_ids as $object_id){
                    wp_trash_post($object_id);
                }
                wp_redirect(admin_url().'edit.php?post_type=scorg_scss');
                exit;
            }
        }
	}
	new SCORG_scripts_trash();
}