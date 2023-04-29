<form id="scripts-form" method="post" action="">
	<div class="scripts-rows">
		<?php 
			global $wpdb;
			$swiss_knife_scripts = $wpdb->prefix . 'swiss_knife_scripts';
			$html = '';
			$html .= '<ul id="reorder-code-blocks">';
			$all_scripts = $wpdb->get_results( "SELECT * FROM $swiss_knife_scripts ORDER BY script_order ASC" );
			if(!empty($all_scripts)){
				$FrontEnd_only = "";
				$selected = "";
				foreach($all_scripts as $script){
					if($script->script_frontend_only == "1"){
						$FrontEnd_only = "FrontEnd Only";
					} else {
						$FrontEnd_only = "";
					}

					$html .= '<li><span class="move"></span><div class="script-row edit-style">


						<div class="script-row__info">
							<div class="script-info__name">
								<div class="script-type-wrap">
									<svg width="24" height="24"  class="'.$script->script_type.'-icon--svg">
										<use xlink:href="#'.$script->script_type.'-icon"/>
									</svg>
								</div>
								<h3>'.$script->script_name.'</h3>
							</div>
							
							<div class="script-info__settings">

								<div class="script-info__reg-type">
									'.$FrontEnd_only.'
								</div>

								<div class="script-info__location">
									'.ucfirst($script->script_location).'
								</div>
								
								<div class="script-info__exclude-builder">
									'.ucfirst($script->script_include_type).'
								</div>

							</div>



							<div class="action-svg-icon swk-copy-code">
								<span>Click to copy</span>
								<svg width="24" height="24" >
	    							<use xlink:href="#copy-icon"/>
	  							</svg>
							</div>

					
							<div class="script-info__edit btn__edit">
								Edit
							</div>


							<a href="#" class="action-svg-icon swk-delete-script" data-id="'.$script->id.'">
								<svg width="24" height="24" >
	    							<use xlink:href="#delete-icon"/>
	  							</svg>
							</a>


						</div>


						<div class="script-row__edit">

							<div class="font-field swk-field">
								<label>Script name</label>
								<input type="text" name="script_name[]" value="'.$script->script_name.'">
							</div>

							
							<div class="swk-field reg-shortcode">
								<div class="reg-enq">';
								if($script->script_type == "js"){
									$html .= 'wp_enqueue_script(\''.strtolower(str_replace(" ", "-", $script->script_name)).'\');';
								} else {
									$html .= 'wp_enqueue_style(\''.strtolower(str_replace(" ", "-", $script->script_name)).'\');';
								}
								$html .= '
								</div>
								<span>Click to copy</span>
							</div>
							
							


							<div class="font-field swk-field dp--select">
								
									<label for="script_type">Script Type</label>
									<select name="script_type[]"> ';
									$script_types = array(
										'js' => 'JavaScript',
										'css' => 'CSS',
									);
									foreach($script_types as $key => $val){
										if($key == $script->script_type){
											$selected = "selected";
										} else {
											$selected = "";
										}
										$html .= '<option '.$selected.' value="'.$key.'">'.$val.'</option>';
									}
								$html .= '</select>
							</div>


							<div class="font-field swk-field dp--select">
									<label for="script_location">Location</label>
									<select name="script_location[]">';
										$script_locations = array(
											'header' => 'Header',
											'footer' => 'Footer',
										);
										foreach($script_locations as $key => $val){
											if($key == $script->script_location){
												$selected = "selected";
											} else {
												$selected = "";
											}
											$html .= '<option '.$selected.' value="'.$key.'">'.$val.'</option>';
										}
									$html .= '</select>			
							</div>


							<div class="font-field swk-field dp--select">
									<label for="script_include_type">Include Type</label>
									<select name="script_include_type[]">';
										$script_include_types = array(
											'register' => 'Register',
											'enqueue' => 'Enqueue',
										);
										foreach($script_include_types as $key => $val){
											if($key == $script->script_include_type){
												$selected = "selected";
											} else {
												$selected = "";
											}
											$html .= '<option '.$selected.' value="'.$key.'">'.$val.'</option>';
										}
									$html .= '</select>	
							</div>

							</br>

							<div class="rwmb-switch-wrapper dp--switch dp--switch_move-in-header swk-field of-input">
								<div class="rwmb-label">
									<label for="script_frontend_only">FrontEnd Only</label>
								</div>

								<div class="rwmb-input">
									<label class="rwmb-switch-label rwmb-switch-label--rounded">';
									if($script->script_frontend_only == "1"){
										$html .= '<input checked type="checkbox" class="of-checkboxes script-frontend-only"> <input type="hidden" name="script_frontend_only[]" value="1">';
									} else {
										$html .= '<input type="checkbox" class="of-checkboxes script-frontend-only"> <input type="hidden" name="script_frontend_only[]" value="0">';
									}
									$html .= '<div class="rwmb-switch-status">
										<span class="rwmb-switch-slider"></span>
										<span class="rwmb-switch-on">On</span>
										<span class="rwmb-switch-off"></span>
									</div>
									</label>
								</div>
							</div>

							<div class="font-field swk-field file-upload">
								<label>Upload file or paste CDN link</label>
								<span>
									<input type="text" name="script_file[]" class="script-file" value="'.$script->script_file.'">
									<a href="#" class="script-file-upload swk-file-upload action-svg-icon">
										<svg width="24" height="24" >
											<use xlink:href="#upload-icon"></use>
										</svg>
										 File Upload
									</a>
								<span>
							</div>

							</br>


						</div>		
					</div></li>';
				}
			}
			$html .= '</ul>';
			echo $html;
		?>
	</div>
	<input type="submit" style="display: none;" name="save_scripts" value="save" id="save-scripts">
</form>
<!-- for copy only don't edit  -->
<div class="script-copy" style="display: none;">
	<div class="script-row edit-style active">
		<div class="script-row__info">
			<div class="script-info__name">
				<div class="script-type-wrap">
					<svg width="24" height="24"  class="js-icon--svg">
						<use xlink:href="#js-icon"></use>
					</svg>
				</div>
				<h3></h3>
			</div>
			
			<div class="script-info__settings">
				<div class="script-info__location">
					Footer
				</div>
				

				<div class="script-info__exclude-builder">
					Register
				</div>


				<div class="script-info__reg-type">
					
				</div>
			</div>



			<div class="action-svg-icon swk-copy-code">
				<span>Click to copy</span>
				<svg width="24" height="24" >
					<use xlink:href="#copy-icon"></use>
					</svg>
			</div>


			<div class="script-info__edit btn__edit">
				Edit
			</div>


			<a href="#" class="action-svg-icon swk-delete-script" data-id="">
				<svg width="24" height="24" >
					<use xlink:href="#delete-icon"></use>
					</svg>
			</a>


		</div>


		<div class="script-row__edit">

			<div class="font-field swk-field">
				<label>Script name</label>
				<input type="text" name="script_name[]" value="">
			</div>

			
			<div class="swk-field reg-shortcode">
				<div class="reg-enq"></div>
				<span>Click to copy</span>
			</div>
			
			


			<div class="font-field swk-field dp--select">
				
					<label for="script_type">Script Type</label>
					<select name="script_type[]"> <option selected="" value="js">JavaScript</option><option value="css">CSS</option></select>
			</div>


			<div class="font-field swk-field dp--select">
					<label for="script_location">Location</label>
					<select name="script_location[]"><option value="header">Header</option><option selected="" value="footer">Footer</option></select>			
			</div>


			<div class="font-field swk-field dp--select">
					<label for="script_include_type">Include Type</label>
					<select name="script_include_type[]"><option selected="" value="register">Register</option><option value="enqueue">Enqueue</option></select>	
			</div>

			<br>

			<div class="rwmb-switch-wrapper dp--switch dp--switch_move-in-header swk-field of-input">
				<div class="rwmb-label">
					<label for="script_frontend_only">FrontEnd Only</label>
				</div>

				<div class="rwmb-input">
					<label class="rwmb-switch-label rwmb-switch-label--rounded"><input type="checkbox" class="of-checkboxes script-frontend-only"> <input type="hidden" name="script_frontend_only[]" value="0"><div class="rwmb-switch-status">
						<span class="rwmb-switch-slider"></span>
						<span class="rwmb-switch-on">On</span>
						<span class="rwmb-switch-off"></span>
					</div>
					</label>
				</div>
			</div>

			<div class="font-field swk-field file-upload">
				<label>Upload file or paste CDN link</label>
				<span>
					<input type="text" name="script_file[]" class="script-file" value="">
					<a href="#" class="script-file-upload swk-file-upload action-svg-icon">
						<svg width="24" height="24" >
							<use xlink:href="#upload-icon"></use>
						</svg>
							File Upload
					</a>
				<span>
			</div>
			<br>
		</div>		
	</div>
</div>
<!-- for copy only don't edit  -->