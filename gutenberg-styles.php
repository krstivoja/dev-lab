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
          <div id="text-blocks" class="blocks-group">
              <h2 class="group-title">Text Blocks</h2>
              <div class="blocks-list">
                <div class="block">Paragraph</div>
                <div class="block">Heading</div>
                <div class="block">List</div>
                <div class="block">Quote</div>
                <div class="block">Classic</div>
                <div class="block">Code</div>
                <div class="block">Preformatted</div>
                <div class="block">Pullquote</div>
                <div class="block">Table</div>
                <div class="block">Verse</div>
              </div>
          </div>

          <div id="media-blocks" class="blocks-group">
              <h2 class="group-title">Media Blocks</h2>
              <div class="blocks-list">
              <div class="block">Image</div>
              <div class="block">Gallery</div>
              <div class="block">Audio</div>
              <div class="block">Cover</div>
              <div class="block">File</div>
              <div class="block">Media & Text</div>
              <div class="block">Video</div>
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