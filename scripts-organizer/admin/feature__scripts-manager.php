<?php
	global $wpdb;
	$swiss_knife_scripts = $wpdb->prefix . 'swiss_knife_scripts';
	if(isset($_POST['save_scripts']) && isset($_POST['script_name']) && !empty($_POST['script_name'])){
		//echo "<pre>"; print_r($_POST); "</pre>"; exit;
		$insert_query = array();
		$i = 0;
		$style = "";
		foreach($_POST['script_name'] as $script_name){
			$insert_array[] = "('".sanitize_text_field($_POST['script_name'][$i])."', '".sanitize_text_field($_POST['script_type'][$i])."', '".sanitize_text_field($_POST['script_location'][$i])."', '".sanitize_text_field($_POST['script_include_type'][$i])."', '".sanitize_text_field($_POST['script_file'][$i])."', '".sanitize_text_field($_POST['script_frontend_only'][$i])."', '".$i."')";
			$i++;
		}
		$insert_query = "INSERT INTO ".$swiss_knife_scripts." (script_name, script_type, script_location, script_include_type, script_file, script_frontend_only, script_order) VALUES ";
	    $insert_query .= implode(', ', $insert_array);
	    $wpdb->query("TRUNCATE TABLE $swiss_knife_scripts");
	    $wpdb->query($insert_query);
	}
?>
<div id="wpwrap">
	<div class="wrap scripts-manager">
		
		<h1 class="wp-heading-inline">Scripts Manager</h1>
		<!-- Add and Save buttons -->
		<a href="#" class="swk-new-script bth_add page-title-action">
			+ Add New Script
			<span class="spinner"></span>
		</a>	
	

		<div id="Scripts" >

		<div class="swk_admin_card">

			<div class="swk_admin_body">
			
				<?php include 'feature__scripts-manager--script-row.php'; ?>

			</div><!-- End of swk_admin_body -->

		</div><!-- End of swk_admin_card -->


		<a href="#" class="swk-save-scripts button-primary">
			Save
			<span class="spinner"></span>
		</a>
		<!-- End of Add and Save buttons -->
		



	</div> <!-- End of wrap -->
</div> <!-- End of wpwrap -->



<svg display="none">
	

	<symbol width="24" height="24" viewBox="0 0 24 24" id="upload-icon"  fill="currentColor">
		<path d="M0 0h24v24H0z" fill="none"></path><path d="M5 4v2h14V4H5zm0 10h4v6h6v-6h4l-7-7-7 7z"></path>
	</symbol>


	<symbol width="24" height="24" viewBox="0 0 24 24" id="delete-icon"  fill="currentColor">
		<path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"></path>
	</symbol>

	<symbol width="24" height="24" viewBox="0 0 24 24" id="copy-icon"  fill="currentColor">
		<path d="M0 0h24v24H0z" fill="none"/>
		<path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
	</symbol>

	<symbol width="24" height="24" viewBox="0 0 128 128" id="css-icon"  fill="currentColor">
		<path fill="#1572B6" d="M18.814 114.123l-10.054-112.771h110.48l-10.064 112.754-45.243 12.543-45.119-12.526z"/>
		<path fill="#33A9DC" d="M64.001 117.062l36.559-10.136 8.601-96.354h-45.16v106.49z"/>
		<path fill="#fff" d="M64.001 51.429h18.302l1.264-14.163h-19.566v-13.831h34.681999999999995l-.332 3.711-3.4 38.114h-30.95v-13.831z"/>
		<path fill="#EBEBEB" d="M64.083 87.349l-.061.018-15.403-4.159-.985-11.031h-13.882l1.937 21.717 28.331 7.863.063-.018v-14.39z"/>
		<path fill="#fff" d="M81.127 64.675l-1.666 18.522-15.426 4.164v14.39l28.354-7.858.208-2.337 2.406-26.881h-13.876z"/>
		<path fill="#EBEBEB" d="M64.048 23.435v13.831000000000001h-33.407999999999994l-.277-3.108-.63-7.012-.331-3.711h34.646zM64.001 51.431v13.831000000000001h-15.209l-.277-3.108-.631-7.012-.33-3.711h16.447z"/>
	</symbol>

	<symbol width="24" height="24" viewBox="0 0 128 128" id="js-icon"  fill="currentColor">
		<path fill="#F0DB4F" d="M1.408 1.408h125.184v125.185h-125.184z"/>
		<path fill="#323330" d="M116.347 96.736c-.917-5.711-4.641-10.508-15.672-14.981-3.832-1.761-8.104-3.022-9.377-5.926-.452-1.69-.512-2.642-.226-3.665.821-3.32 4.784-4.355 7.925-3.403 2.023.678 3.938 2.237 5.093 4.724 5.402-3.498 5.391-3.475 9.163-5.879-1.381-2.141-2.118-3.129-3.022-4.045-3.249-3.629-7.676-5.498-14.756-5.355l-3.688.477c-3.534.893-6.902 2.748-8.877 5.235-5.926 6.724-4.236 18.492 2.975 23.335 7.104 5.332 17.54 6.545 18.873 11.531 1.297 6.104-4.486 8.08-10.234 7.378-4.236-.881-6.592-3.034-9.139-6.949-4.688 2.713-4.688 2.713-9.508 5.485 1.143 2.499 2.344 3.63 4.26 5.795 9.068 9.198 31.76 8.746 35.83-5.176.165-.478 1.261-3.666.38-8.581zm-46.885-37.793h-11.709l-.048 30.272c0 6.438.333 12.34-.714 14.149-1.713 3.558-6.152 3.117-8.175 2.427-2.059-1.012-3.106-2.451-4.319-4.485-.333-.584-.583-1.036-.667-1.071l-9.52 5.83c1.583 3.249 3.915 6.069 6.902 7.901 4.462 2.678 10.459 3.499 16.731 2.059 4.082-1.189 7.604-3.652 9.448-7.401 2.666-4.915 2.094-10.864 2.07-17.444.06-10.735.001-21.468.001-32.237z"/>
	</svg>

</svg>