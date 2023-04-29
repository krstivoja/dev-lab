<div id="wpwrap">
    <div class="wrap shortcuts-manager">
        <h1 class="wp-heading-inline">Import Scripts</h1>
        <div style="margin-top:20px">

            <div class="import swk_admin_card">
                <div class="swk_admin_header">
                    <h2>Scripts Organizer Import</h2>
                </div>
                <div class="swk_admin_body">
                    <form method="post" enctype="multipart/form-data" class="wp-upload-form">
                        <input type="file" class="swk-json" id="jsonfile-scorg" name="jsonfile" accept=".json" required>
                        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('scripts_organizer'); ?>">
                        <input type="hidden" name="scripts_organizer_action" value="import">
                        <input type="submit" class="button button-primary" value="Import JSON">
                    </form>
                </div>
            </div>
            
            <?php if( is_plugin_active( 'scripts-organizer--gutenberg-acf/scripts-organizer--gutenberg-acf.php' )){ ?>
            <div class="import swk_admin_card">
                <div class="swk_admin_header">
                    <h2>Scripts Organizer - ACF Gutenberg Addon</h2>
                </div>
                <div class="swk_admin_body">
                    <form method="post" enctype="multipart/form-data" class="wp-upload-form">
                        <input type="file" class="swk-json" id="jsonfile-scorg-ga" name="jsonfile" accept=".json" required>
                        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('scripts_organizer_ga'); ?>">
                        <input type="hidden" name="scripts_organizer_ga_action" value="import">
                        <input type="submit" class="button button-primary" value="Import JSON">
                    </form>
                </div>
            </div>
            <?php } ?>
        
            <div class="import swk_admin_card">
                <div class="swk_admin_header">
                    <h2>Code Snippets Import</h2>
                </div>
                <div class="swk_admin_body">
                    <form method="post" enctype="multipart/form-data" class="wp-upload-form">
                        <input type="file" class="swk-json" id="jsonfile-cs" name="jsonfile" accept=".json" required>
                        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('code_snippet'); ?>">
                        <input type="hidden" name="code_snippet_action" value="import">
                        <input type="submit" class="button button-primary" value="Import JSON">
                    </form>
                </div>
            </div>

            <div class="import swk_admin_card">
                <div class="swk_admin_header">
                <h2>Advanced Scripts Import</h2>
                </div>
                <div class="swk_admin_body">
                    <form method="post" enctype="multipart/form-data" class="wp-upload-form">
                        <input type="file" class="swk-json" id="jsonfile-as" name="jsonfile" accept=".json" required>
                        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('advanced_scripts'); ?>">
                        <input type="hidden" name="advanced_scripts_action" value="import">
                        <input type="submit" class="button button-primary" value="Import JSON">
                    </form>
                </div>
            </div>
        </div><!-- End of Sticky wrap -->
    </div> <!-- End of wrap -->
</div> <!-- End of wpwrap -->