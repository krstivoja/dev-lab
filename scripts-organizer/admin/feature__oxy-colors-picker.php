<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function SCORG_oxy_colors(){
    $colors_html = '';
    if(SCORG_color_picker_option()){
        $colors_html =  '<div id="color-picker">
            <div class="select-color__trigger">
                <svg height="24px" width="24px"><use xlink:href="#colorpicker" /></svg>
                Color Picker
            </div>
            <div class="palette-wrapper">';

        $colors = SCORG_get_oxygen_colors();
        if(!empty($colors)){
            $colors_html .= '<div class="select-color__pallete page-builder">Oxygen</div>';
            foreach($colors['sets'] as $key => $set){
                if(!empty($set)){
                    $colors_html .= '<div class="select-color__pallete">
                    <div class="color-group__headline">'.ucwords(str_replace("-", " ", $key)).'</div>';
                    foreach($set as $color){
                        $colors_html .= '<div class="insert-color__bg">
                            <div class="insert-color" data-id="oxycolor('.$color['id'].')" title="'.$color['name'].'" style="background-color: '.$color['value'].';"></div>
                        </div>';
                    }
                    $colors_html .= '</div>';
                }
            }
        }
        $elementor_colors = SCORG_elementor_colors();
        if(!empty($elementor_colors)){
            $colors_html .= '<div class="select-color__pallete page-builder">Elementor</div>';
            foreach($elementor_colors['sets'] as $key => $set){
                if(!empty($set)){
                    $colors_html .= '<div class="select-color__pallete">
                    <div class="color-group__headline">'.ucwords(str_replace("-", " ", $key)).'</div>';
                    foreach($set as $color){
                        $colors_html .= '<div class="insert-color__bg">
                            <div class="insert-color" data-id="elementor('.$color['_id'].')" title="'.$color['title'].'" style="background-color: '.$color['color'].';"></div>
                        </div>';
                    }
                    $colors_html .= '</div>';
                }
            }
        }

        $bricks_colors = SCORG_bricks_colors();
        if(!empty($bricks_colors['colors'])){
            $colors_html .= '<div class="select-color__pallete page-builder">Bricks</div>';
            foreach($bricks_colors['sets'] as $key => $set){
                if(!empty($set)){
                    $colors_html .= '<div class="select-color__pallete">
                    <div class="color-group__headline">'.ucfirst(ucwords(str_replace("-", " ", $key))).'</div>';
                    foreach($set as $color){
                        $colors_html .= '<div class="insert-color__bg">
                            <div class="insert-color" data-id="bricks('.$color['_id'].')" title="'.$color['title'].'" style="background-color: '.$color['color'].';"></div>
                        </div>';
                    }
                    $colors_html .= '</div>';
                }
            }
        }

        $colors_html .= '</div></div>';
    }
    
    return $colors_html;
}