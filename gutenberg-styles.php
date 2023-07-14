<?php

/*
  Plugin Name: Gutenberg Styles
  Version: 1.0
  Author: Your Name
  Author URI: Your Website
*/

function gutenberg_styles_menu() {
  add_theme_page(
      'Gutenberg Styles',         // Page title
      'Gutenberg Styles',         // Menu title
      'manage_options',           // Capability required to access the page
      'gutenberg-styles',         // Menu slug
      'render_gutenberg_styles_admin_page' // Callback function to render the page
  );
}
add_action('admin_menu', 'gutenberg_styles_menu');

// This function will render the output for your Gutenberg Styles page.
function render_gutenberg_styles_admin_page() {
  ?>
 
 <div id="app">
  <aside>
      <input type="search" name="" id="">
      <div id="filter">
          <div class="tag">reusable</div>
          <div class="tag">text</div>
          <div class="tag">layout</div>
          <div class="tag">media</div>
          <div class="tag">theme</div>
          <div class="tag">embed</div>
          <div class="tag">design</div>
          <div class="tag">widgets</div>
      </div>

      <div id="blocks">
          <div id="reusable">
              <h2 class="group-title">design</h2>
              <div class="blocks-list">
                  <div class="block">Buttons</div>
                  <div class="block">Column</div>
                  <div class="block">Columns</div>
                  <div class="block">Group</div>
                  <div class="block">More</div>
                  <div class="block">Page Break</div>
                  <div class="block">Separator</div>
                  <div class="block">Spacer</div>
                  <div class="block">Text Columns (deprecated)</div>
                  <div class="block">Comment Template</div>
                  <div class="block">Home Link</div>
                  <div class="block">Custom Link</div>
                  <div class="block">Submenu</div>
              </div>
          </div>
      </div>
  </aside>
  <main>
      <div class="block-title">Add dinamicaly block title</div>
  </main>
</div> 

  <?php
}