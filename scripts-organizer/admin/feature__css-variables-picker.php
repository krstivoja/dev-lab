<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function SCORG_css_variables_picker(){
    $variables_html = '';
    $variables_html =  '<div class="variable-picker">
    <div class="select-variable__trigger">
        <svg height="24px" width="24px"><use xlink:href="#variablepicker" /></svg>
        CSS Variables
    </div>
    <ul><div class="variables-list"';
        $variables_html .= SCORG_css_variables_picker_lis();
    $variables_html .= '</div><div id="purge-variables" class="purge-variables-btn">Clear All</div></ul>';
    $variables_html .= '</div>';
    
    return $variables_html;
}

function SCORG_css_variables_picker_lis(){
    $variables_html = '';
    $variables = get_option('scorg_css_variables');
    if(!empty($variables)){
        $variables_html .= '<input class="scorg-var-search" type="text" placeholder="Search..">';
        foreach($variables as $variable){
            $variables_html .= '<li class="insert-variable" data-id="var('.$variable.')" title="'.$variable.'">'.$variable.'</li>';
        }
    }
    return $variables_html;
}

function purgeVariables_func(){
    check_ajax_referer('ajax-nonce', 'verify_nonce');
    $scorg_css_variables = !empty($_POST['scorg_css_variables']) ? explode(",", sanitize_text_field($_POST['scorg_css_variables'])) : array();
    $scorg_css_variables = array_unique($scorg_css_variables);
    update_option('scorg_css_variables', $scorg_css_variables);
    echo json_encode(
        array(
            'message' => '',
            'variables_html' => SCORG_css_variables_picker_lis()
        )
    );
    wp_die();
}
add_action( 'wp_ajax_purgeVariables', 'purgeVariables_func' );