<form id="of-form">
	
	<div class="swk_admin_card">

		<div class="swk_admin_body dp--checkbox">

			<h3>Activate Features</h3>

			<ul class="features--list">
				
				<label class="of-input of-input--main"><input type="checkbox" id="select-all" /> <strong>SELECT/UNSELECT ALL</strong></label>				

				<?php
					$SCORG_features = new SCORG_features();
					$scorg_options = $SCORG_features->get_scorg_options();
					$priority_options = $SCORG_features->priority_options();
					$priority_options_html = '';
					$checked = "";
					$selected = "";
					foreach($scorg_options as $key => $val){
						$option_value = get_option($key);
						if($option_value == "yes"){
							$checked = "checked";
						} else {
							$checked = "";
						}

						if(!in_array($key, $priority_options)){
							echo '<label class="of-input"><input '.$checked.' type="checkbox" class="of-checkboxes" name="'.$key.'" value="yes" /> '.$val[0];
								if($key == "scorg_fontsize"){
									echo ' <input type="number" class="of-checkboxes" placeholder="14" name="scorg_font_value" value="'.get_option('scorg_font_value').'" /> px';
								}
							echo '</label>';
						} else {
							$db_val = get_option($key);
							$priority_options_html .= '<label class="of-input reverse">'.$val[0];
								$priority_options_html .= ' <input type="number" min="1" class="of-checkboxes bigger" placeholder="14" name="'.$key.'" value="'.((!empty($db_val)) ? $db_val : $val['value']).'" />';
							$priority_options_html .= '</label>';
						}
					}
					echo '<label class="of-input double-input">Files Path in root directory <span>' . ABSPATH . '</span>';
						echo ' <input type="text" class="of-checkboxes" name="scorg_files_path" value="'.get_option('scorg_files_path').'" />';
					echo '</label>';
				?>
			</ul>

					

		</div><!-- End of swk_admin_body -->


	</div><!-- End of swk_admin_card -->

	<div class="swk_admin_card">

		<div class="swk_admin_body dp--checkbox">

			<h3>Scripts Priority</h3>

			<ul class="features--list">
				<?php echo $priority_options_html; ?>
			</ul>

					

		</div><!-- End of swk_admin_body -->


	</div><!-- End of swk_admin_card -->


	<a href="#" class="of-save button-primary">Save Changes <span class="spinner" ></span></a>
	<a href="#" class="of-sync-all button-secondary" style="margin-left: 10px">Regenerate Files <span class="spinner" ></span></a>

</form>
