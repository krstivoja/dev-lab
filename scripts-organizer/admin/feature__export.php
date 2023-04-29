<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_scripts_export')){
	class SCORG_scripts_export {
		public function __construct(){
			add_action( 'init', array($this, 'export_scripts_boot'), 20 );
            add_filter( 'handle_bulk_actions-edit-scorg', array($this, 'bulk_action_handler'), 10, 3 );
            add_filter( 'handle_bulk_actions-edit-scorg_scss', array($this, 'bulk_action_handler'), 10, 3 );
            add_filter( 'handle_bulk_actions-edit-scorg_ga', array($this, 'bulk_action_handler'), 10, 3 );
		}

        function bulk_action_handler( $redirect, $doaction, $object_ids ) {
            if(!empty($object_ids) && isset($_REQUEST['action2']) && $_REQUEST['action2'] == "scss_bulk"){
                $this->scss_partials_export($object_ids);
            }
            
            if(!empty($object_ids) && isset($_REQUEST['action2']) && $_REQUEST['action2'] == "scorg_ga_bulk"){
                $this->scorg_ga_export($object_ids);
            }
            
            if(!empty($object_ids) && isset($_REQUEST['action2']) && $_REQUEST['action2'] == "scorg_ga_disable"){
                $this->enable_disable_blocks("SCORG_GACF_enable_script", "scorg_ga", "disable", $object_ids);
            }
            
            if(!empty($object_ids) && isset($_REQUEST['action2']) && $_REQUEST['action2'] == "scorg_ga_enable"){
                $this->enable_disable_blocks("SCORG_GACF_enable_script", "scorg_ga", "enable", $object_ids);
            }
            
            if(!empty($object_ids) && isset($_REQUEST['action2']) && $_REQUEST['action2'] == "scorg_bulk"){
                $this->code_blocks_export($object_ids);
            }
            if(!empty($object_ids) && isset($_REQUEST['action2']) && $_REQUEST['action2'] == "scorg_disable_bulk"){
                $this->enable_disable_blocks("SCORG_enable_script", "scorg", "disable", $object_ids);
            }
            if(!empty($object_ids) && isset($_REQUEST['action2']) && $_REQUEST['action2'] == "scorg_enable_bulk"){
                $this->enable_disable_blocks("SCORG_enable_script", "scorg", "enable", $object_ids);
            }
        }

		public function export_scripts_boot() {
		    if (isset( $_REQUEST["scorg_export"] )
		        || isset( $_REQUEST["scss_export"] )
                || isset( $_REQUEST["scorg_ga_export"] )) {
		        $this->export_scripts();
		    }
		}

        public function export_scripts(){
            if(isset($_REQUEST['scorg_export']) && $_REQUEST['scorg_export'] == "all"){
                $this->code_blocks_export();
            }
            
            if(isset($_REQUEST['scorg_export']) && $_REQUEST['scorg_export'] == "single"){
                $this->code_blocks_export(array(sanitize_text_field($_GET['id'])));
            }
            
            if(isset($_REQUEST['scorg_ga_export']) && $_REQUEST['scorg_ga_export'] == "all"){
                $this->scorg_ga_export();
            }
            
            if(isset($_REQUEST['scorg_ga_export']) && $_REQUEST['scorg_ga_export'] == "single"){
                $this->scorg_ga_export(array(sanitize_text_field($_GET['id'])));
            }
            
            if(isset($_REQUEST['scss_export']) && $_REQUEST['scss_export'] == "all"){
                $this->scss_partials_export();
            }
            
            if(isset($_REQUEST['scss_export']) && $_REQUEST['scss_export'] == "single"){
                $this->scss_partials_export(array(sanitize_text_field($_GET['id'])));
            }
        }

        public function code_blocks_export($object_ids = array()){
            global $wpdb;
            $posts = $wpdb->posts;
            $postmeta = $wpdb->postmeta;
            $selected_query = '';
            if(!empty($object_ids)){
                $object_ids = join("','",$object_ids);
                $selected_query = " AND p.ID IN ('$object_ids')";
            }
            $all_code_blocks = $wpdb->get_results("SELECT p.ID, p.post_title FROM $posts as p
            WHERE p.post_type = 'scorg' AND post_status IN('draft', 'publish') $selected_query", OBJECT);
            if(!empty($all_code_blocks)){
                $export_data = array();
                $export_data['post_type'] = "scorg";
                foreach($all_code_blocks as $code_block){
                    $export_data[$code_block->ID]['title'] = $code_block->post_title;
                    $scorg_tags = wp_get_post_terms( $code_block->ID, 'scorg_tags', array('fields' => 'all') );
                    if(!is_wp_error($scorg_tags) && !empty($scorg_tags)){
                        foreach($scorg_tags as $scorg_tag){
                            $export_data[$code_block->ID]['scorg_tags'][$scorg_tag->slug] = $scorg_tag->name;
                        }
                    }
                    $code_block_meta = $wpdb->get_results("SELECT meta_key, meta_value FROM $postmeta as pm
                    WHERE pm.post_id = '$code_block->ID' AND meta_key LIKE 'SCORG%'", OBJECT);
                    if(!empty($code_block_meta)){
                        foreach($code_block_meta as $meta){
                            switch ($meta->meta_key) {
                                case 'SCORG_header_script':
                                    $export_data[$code_block->ID][$meta->meta_key] = $meta->meta_value;
                                    break;
                                
                                case 'SCORG_footer_script':
                                    $export_data[$code_block->ID][$meta->meta_key] = $meta->meta_value;
                                    break;
                                
                                case 'SCORG_php_script':
                                    $export_data[$code_block->ID][$meta->meta_key] = $meta->meta_value;
                                    break;
                                
                                case 'SCORG_script_type':
                                    $export_data[$code_block->ID][$meta->meta_key][] = $meta->meta_value;
                                    break;
                                
                                case 'SCORG_partials':
                                    $export_data[$code_block->ID][$meta->meta_key][] = $meta->meta_value;
                                    break;
                                
                                default:
                                    $export_data[$code_block->ID][$meta->meta_key] = $meta->meta_value;
                                    break;
                            }
                        }
                    }
                }
                if(!empty($export_data)){
                    $filename = 'scorg_export_' . date( 'Y-m-d' );
                    $this->export_file($filename, $export_data);
                }
            }
        }
        
        public function enable_disable_blocks($meta_key, $post_type, $disable_enable, $object_ids = array()){
            global $wpdb;
            $postmeta = $wpdb->postmeta;
            $selected_query = '';
            if(!empty($object_ids)){
                $object_ids = join("','",$object_ids);
                $selected_query = " AND post_id IN ('$object_ids')";
            }
            $enable_or_disable = ($disable_enable == 'enable') ? '1' : 0;
            $wpdb->query("UPDATE $postmeta SET meta_value = '".$enable_or_disable."' WHERE meta_key = '".$meta_key."' $selected_query");
            wp_redirect(admin_url().'edit.php?post_type='.$post_type);
            exit;
        }
        
        public function scorg_ga_export($object_ids = array()){
            global $wpdb;
            $posts = $wpdb->posts;
            $postmeta = $wpdb->postmeta;
            $selected_query = '';
            if(!empty($object_ids)){
                $object_ids = join("','",$object_ids);
                $selected_query = " AND p.ID IN ('$object_ids')";
            }
            $all_acf_blocks = $wpdb->get_results("SELECT p.ID, p.post_title FROM $posts as p
            WHERE p.post_type = 'scorg_ga' AND post_status IN('draft', 'publish') $selected_query", OBJECT);
            if(!empty($all_acf_blocks)){
                $export_data = array();
                $export_data['post_type'] = "scorg_ga";
                foreach($all_acf_blocks as $acf_block){
                    $export_data[$acf_block->ID]['title'] = $acf_block->post_title;
                    $scorg_tags = wp_get_post_terms( $acf_block->ID, 'scorg_tags', array('fields' => 'all') );
                    if(!is_wp_error($scorg_tags) && !empty($scorg_tags)){
                        foreach($scorg_tags as $scorg_tag){
                            $export_data[$acf_block->ID]['scorg_tags'][$scorg_tag->slug] = $scorg_tag->name;
                        }
                    }
                    $acf_block_meta = $wpdb->get_results("SELECT meta_key, meta_value FROM $postmeta as pm
                    WHERE pm.post_id = '$acf_block->ID' AND meta_key LIKE 'SCORG%'", OBJECT);
                    if(!empty($acf_block_meta)){
                        foreach($acf_block_meta as $meta){
                            switch ($meta->meta_key) {
                                case SCORG_GACF_PREFIX.'php_script':
                                    $export_data[$acf_block->ID][$meta->meta_key] = $meta->meta_value;
                                    break;
                                
                                case SCORG_GACF_PREFIX.'header_script':
                                    $export_data[$acf_block->ID][$meta->meta_key] = $meta->meta_value;
                                    break;

                                case SCORG_GACF_PREFIX.'footer_script':
                                    $export_data[$acf_block->ID][$meta->meta_key] = $meta->meta_value;
                                    break;
                                
                                case SCORG_GACF_PREFIX.'script_styles':
                                    $export_data[$acf_block->ID][$meta->meta_key][] = $meta->meta_value;
                                    break;
                                
                                default:
                                    $export_data[$acf_block->ID][$meta->meta_key] = $meta->meta_value;
                                    break;
                            }
                        }
                    }
                }
                if(!empty($export_data)){
                    $filename = 'scorg_ga_export_' . date( 'Y-m-d' );
                    $this->export_file($filename, $export_data);
                }
            }
        }

        public function scss_partials_export($object_ids = array()){
            global $wpdb;
            $posts = $wpdb->posts;
            $postmeta = $wpdb->postmeta;
            $selected_query = '';
            if(!empty($object_ids)){
                $object_ids = join("','",$object_ids);
                $selected_query = " AND p.ID IN ('$object_ids')";
            }
            $all_partials = $wpdb->get_results("SELECT p.ID, p.post_title FROM $posts as p
            WHERE p.post_type = 'scorg_scss' AND post_status IN('draft', 'publish') $selected_query", OBJECT);
            if(!empty($all_partials)){
                $export_data = array();
                $export_data['post_type'] = "scorg_scss";
                foreach($all_partials as $partial){
                    $export_data[$partial->ID]['title'] = $partial->post_title;
                    $scorg_tags = wp_get_post_terms( $partial->ID, 'scorg_tags', array('fields' => 'all') );
                    if(!is_wp_error($scorg_tags) && !empty($scorg_tags)){
                        foreach($scorg_tags as $scorg_tag){
                            $export_data[$partial->ID]['scorg_tags'][$scorg_tag->slug] = $scorg_tag->name;
                        }
                    }
                    $partial_meta = $wpdb->get_results("SELECT meta_key, meta_value FROM $postmeta as pm
                    WHERE pm.post_id = '$partial->ID' AND meta_key LIKE 'SCSS%'", OBJECT);
                    if(!empty($partial_meta)){
                        foreach($partial_meta as $meta){
                            switch ($meta->meta_key) {
                                case 'SCSS_scss_scripts':
                                    $export_data[$partial->ID][$meta->meta_key] = $meta->meta_value;
                                    break;
                                
                                default:
                                    $export_data[$partial->ID][$meta->meta_key] = $meta->meta_value;
                                    break;
                            }
                        }
                    }
                }
                if(!empty($export_data)){
                    $filename = 'partials_export_' . date( 'Y-m-d' );
                    $this->export_file($filename, $export_data);
                }
            }
        }

        public function export_file($filename, $export_data){
            header("Content-type: application/vnd.ms-excel");
            header("Content-Type: application/force-download");
            header("Content-Type: application/download");
            header("Content-disposition: " . $filename . ".json");
            header("Content-disposition: filename=" . $filename . ".json");
            echo json_encode($export_data);
            exit;
        }
	}
	new SCORG_scripts_export();
}