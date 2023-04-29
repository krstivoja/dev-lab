<?php global $post; $prefix = "SCORG_";?>
<!-- Top Bar -->
<div id="scorg__topbar">
    <!-- Left top bar -->
    <span class="topbar--left">
        <a id="top__wp" href="<?php echo (($screen->id == "scorg") ? admin_url().'edit.php?post_type=scorg' : admin_url().'edit.php?post_type=scorg_scss'); ?>">
            <svg height="36px" width="36px"><use xlink:href="#wordpress" /></svg>
        </a>	

        <a href="<?php echo admin_url().'post-new.php?post_type=scorg'; ?>" class="top__btn">
            + Block
        </a>
        <a href="<?php echo admin_url().'post-new.php?post_type=scorg_scss'; ?>" class="top__btn">
            + Partial
        </a>
        <?php if( is_plugin_active( 'scripts-organizer--gutenberg-acf/scripts-organizer--gutenberg-acf.php' )){ ?>
        <a href="<?php echo admin_url().'post-new.php?post_type=scorg_ga'; ?>" class="top__btn">
            + Element
        </a>
        <?php } ?>
    </span>
    <!-- End of Left top bar -->
    
    <!-- Center top bar -->
    <span class="topbar--center">
        <?php if($post->post_type == "scorg"){ ?>
            <div class="dp--scorg-loader-main" style="display:none;">
                <div class="dp--scorg-loader">
                    <div class="ball"></div>
                    <p>LOADING</p>							
                </div>
            </div>
            <div class="dp--scorg-loader-scss" style="display:none;">
                <div class="dp--scorg-loader"></div>
                <div class="close-scss-error-box">Close</div>
            </div>
        <?php } else { ?>
            <div class="dp--scorg-loader-main" style="display:none;">
				<div class="dp--scorg-loader">
					<div class="ball"></div>
					<p>LOADING</p>							
				</div>
			</div>
        <?php } ?>
    </span>
    <!-- Center of Left top bar -->

    <?php
    $php_script = get_post_meta($post->ID, $prefix.'php_script', true);
    $SCORG_can_include_php = SCORG_can_include_php();
    ?>
    <!-- Right top bar -->
    <span class="topbar--right">
        <?php
        $post_edit_link = get_edit_post_link($post->ID);
        if($SCORG_can_include_php == "no"){
            $safe_mode_disable_url = add_query_arg('scorg_checkphp', "yes", $post_edit_link);
            $php_script_check_html = "";
            if(isset($_GET['scorg_checkphp'])){
                echo '<div class="top__btn" id="no-php-errors">	    			
                <svg width="16px" height="16px" viewBox="0 0 16 16" fill="currentColor">        
                <path d="M8.11111111,0 C10.0532484,1.32312 12.3717101,2.11936 15.2222222,2.16944 C15.1163489,2.94124 15.0828058,3.7254563 15.0675728,4.5127973 L15.0450727,6.11204977 L15.0350353,6.52851808 L15.0115112,7.11690655 L15.0115112,7.11690655 L14.9875429,7.50773527 L14.9875429,7.50773527 L14.9551955,7.89711866 C14.9490226,7.96188111 14.9424381,8.02657258 14.9354107,8.0911877 L14.887681,8.47791916 C14.4950192,11.3065998 13.1238296,13.9666405 8.11111111,16 C2.80351695,13.8469333 1.57823827,10.9913496 1.27457792,7.97696878 L1.23858537,7.56498363 L1.23858537,7.56498363 L1.21223683,7.1513363 L1.21223683,7.1513363 L1.19375129,6.73633316 L1.19375129,6.73633316 L1.17687015,6.11195653 L1.17687015,6.11195653 L1.15304316,4.44327225 L1.15304316,4.44327225 L1.14225505,4.02692438 L1.14225505,4.02692438 L1.11521401,3.40426463 L1.11521401,3.40426463 L1.0873501,2.99084631 L1.0873501,2.99084631 L1.0495391,2.57913597 C1.03507969,2.4422179 1.0186656,2.30563556 1,2.16944 C3.85051211,2.11944 6.16897381,1.323184 8.11111111,0 Z M10.8822917,5.181248 L6.93427435,9.095648 L5.337,7.52 L4.18339461,8.670592 L6.94063249,11.390592 L12.0427974,6.331872 L10.8822917,5.181248 Z"></path>
                </svg>    	
                No syntax errors found        
                </div>';
            }
            echo '<a class="top__btn dp--checkphp" id="checkphp" href="'.$safe_mode_disable_url.'" >
            <svg height="16px" width="16px"><use xlink:href="#info16" /></svg>
            Read error log
            </a>';
        }
        
        if($SCORG_can_include_php == "yes"){
            $safe_mode_disable_url = add_query_arg('scorg_safemode', "no", $post_edit_link);
            echo '<a href="'.esc_url($safe_mode_disable_url).'" class="top__btn" id="safe_mode">
            <span></span>Disable Safe Mode
            </a>';
        }
        ?>
        
        <a href="#" class="top__btn sync" id="dp--sync">Sync</a>
        <a href="#" class="top__btn preview" id="dp--show-hide-preview">Preview</a>
        
        <a href="#" class="top__btn" id="save">
            <?php echo ((isset($post->post_status) && $post->post_status == "publish") ? 'Update' : 'Publish'); ?>
        </a>
        
        <div class="top__btn" id="screen_options">
            <svg height="24px" width="24px"><use xlink:href="#more" /></svg>
        </div>  
    
</div>

    </span>
    <!-- End of Right top bar -->

</div>
<!-- End of Top Bar -->


<!-- Side Bar -->

<div id="scorg__sidebar">

    <!-- Top part -->
    <span>
        <div class="sidebar__btn tooltip-wrap" id="page-settings">
            <label>
                <input type="radio" name="settings" value="page-settings" checked="checked" />
                <svg height="36px" width="36px"><use xlink:href="#config" /></svg>
            </label>
            <span class="tooltip tooltip-right">Script Settings</span> 
        </div>
        <div class="sidebar__btn tooltip-wrap" id="global-settings">
            <label>
                <input type="radio" name="settings" value="global-settings" />
                <svg height="36px" width="36px"><use xlink:href="#gear" /></svg>
            </label>
            
            <span class="tooltip tooltip-right">Page Settings</span> 
        </div>

        <div class="sidebar__btn tooltip-wrap" id="codeblock-list">
            <label>
                <input type="radio" name="settings" value="codeblock-list" />
                <svg height="36px" width="36px"><use xlink:href="#page" /></svg>
            </label>
            
            <span class="tooltip tooltip-right">Code Blocks</span> 
        </div>

        <div class="sidebar__btn tooltip-wrap" id="partials-list">
            <label>
                <input type="radio" name="settings" value="partials-list" />
                <svg height="36px" width="36px"><use xlink:href="#partials" /></svg>
            </label>
            
            <span class="tooltip tooltip-right">Partials</span> 
        </div>
        
        <?php if( is_plugin_active( 'scripts-organizer--gutenberg-acf/scripts-organizer--gutenberg-acf.php' )){ ?>
        <div class="sidebar__btn tooltip-wrap" id="gutenberg-blocks-list">
            <label>
                <input type="radio" name="settings" value="gutenberg-blocks" />
                <svg height="36px" width="36px"><use xlink:href="#gutenberg-blocks" /></svg>
            </label>
            
            <span class="tooltip tooltip-right">Gutenberg Blocks</span> 
        </div>
        <?php } ?>

    </span>
    <!-- End of Top part -->

    <!-- Bottom par -->
    <span>
        
        <label class="sidebar__btn tooltip-wrap" id="theme-settings">
            <input type="checkbox" name="theme" value="theme" />
            <svg height="36px" width="36px"><use xlink:href="#theme" /></svg>
            <span class="tooltip tooltip-right">Toggle Light/Dark Theme</span> 
        </label>        
            
        <label class="sidebar__btn tooltip-wrap" id="focus-settings">
            <input type="radio" name="settings" value="focus" />
            <svg height="36px" width="36px"><use xlink:href="#focus" /></svg>
            <span class="tooltip tooltip-right">In Focus Mode</span> 
        </label>            
            
    </span>	
    <!-- End of Bottom par -->

</div>


<div class="sidebar__btn floating" id="focus-settings2">
    <label>
        <input type="radio" name="settings" value="focus" />
        <svg height="36px" width="36px"><use xlink:href="#focus" /></svg>
    </label>
</div>

<!-- End of Side Bar -->

<!-- Blank Canvas -->

<div id="blank-canvas">
    <div class="content">
        <svg width="225px" height="194px" viewBox="0 0 225 194" fill="currentColor">
            <g id="Page-1" stroke="none">
                <g id="Artboard" transform="translate(-136.000000, -101.000000)">
                    <path d="M354.021696,101.44898 C357.795875,101.44898 360.869524,104.455001 360.995954,108.209027 L361,108.44964 L361,287.448319 C361,291.233154 358.00498,294.318025 354.261628,294.444919 L354.021696,294.44898 L142.980951,294.44898 C139.20418,294.44898 136.130478,291.442959 136.004046,287.688932 L136,287.448319 L136,108.44964 C136,104.664806 138.99502,101.579934 142.740857,101.45304 L142.980951,101.44898 L354.021696,101.44898 Z M347.236646,151.889838 L149.763354,151.889838 L149.763354,280.646768 L347.236646,280.646768 L347.236646,151.889838 Z M309,250.44898 L309,263.44898 L242,263.44898 L242,250.44898 L309,250.44898 Z M194.957346,164.44898 L244,213.450391 L194.957195,262.44898 L185,252.499705 L224.08228,213.447301 L185,174.397952 L194.957346,164.44898 Z M163.529356,117.64846 C158.504765,117.64846 154.533803,121.888147 154.533803,126.669409 C154.533803,131.450671 158.504765,135.700978 163.529356,135.690398 C168.556594,135.690398 172.527556,131.708185 172.527556,126.669409 C172.527556,121.630633 168.556594,117.64846 163.529356,117.64846 Z M190.539839,117.64846 C185.504659,117.64846 181.533697,121.630633 181.544244,126.669409 C181.544244,131.708185 185.515248,135.690358 190.539839,135.690358 C195.567077,135.690358 199.538039,131.708185 199.538039,126.669409 C199.538039,121.630633 195.567077,117.64846 190.539839,117.64846 Z M217.539733,117.64846 C212.504553,117.64846 208.533591,121.630633 208.54151,126.669409 C208.54151,131.708185 212.512495,135.690358 217.539733,135.690358 C222.564324,135.690358 226.535286,131.708185 226.535286,126.669409 C226.535286,121.630633 222.564324,117.64846 217.539733,117.64846 Z" id="logo"></path>
                </g>
            </g>
        </svg>

        Please choose <b><a href="#">Header</a></b>, <b><a href="#">Footer</a></b>, <b><a href="#">PHP</a></b> or <b><a href="#">Shortcode</a></b> </br> in Scripts location to start with coding.

        <ul>
            <li>
                <p>Save</p>
                <span class="kbd-wrap">
                    <kbd>Ctrl</kbd>
                    <kbd>S</kbd>
                </span>	
            </li>
            
            <li>
                <p>Find same next in line</p>
                <span class="kbd-wrap">
                    <kbd>Ctrl</kbd>
                    <kbd>D</kbd>
                </span>	
            </li>
                        
            <li>
                <p>Copy line down</p>
                <span class="kbd-wrap">
                    <kbd>Shift</kbd>
                    <kbd>Alt</kbd>
                    <kbd>Down Arrow</kbd>
                </span>	
            </li>

            <li>
                <p>Shortcuts list</p>
                <span class="kbd-wrap">
                    <kbd>F1</kbd>
                </span>	
            </li>

        </ul>

        <!-- Full list of shortchts can be found <a href="#">here</a>. -->
    </div>
</div>

<!-- End of Blank Canvas -->


<?php include 'icons.php'; ?>

