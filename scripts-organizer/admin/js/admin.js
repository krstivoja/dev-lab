// Script Manager
// tabs
document.documentElement.setAttribute("data-theme", SCORG_ajax.vs_theme);
localStorage.setItem("theme", SCORG_ajax.vs_theme);
var SCORG_active_tab;
var scorg_css_variables = JSON.parse(SCORG_ajax.scorg_css_variables);
const functions = JSON.parse(SCORG_ajax.functions_json);

function openSCORGTab(evt, tabName) {
	evt.preventDefault();
    // Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(tabName).style.display = "block";
    jQuery("#SCORG_active_tab").val(tabName);
    evt.currentTarget.className += " active";
}

function show_hide_exclude_options(){
	var SCORG_page_post = jQuery("input[name='SCORG_page_post']:checked").val();
	if(jQuery("#SCORG_exclude_script").is(":checked")){
		if(SCORG_page_post == "all"){
			jQuery(".dp--exclude, .exclude-message").show();
		} else if(SCORG_page_post == "specific_page_post"){
			jQuery(".dp--exclude, .exclude-message").hide();
		} else if(SCORG_page_post == "specific_post_type"){
			jQuery(".dp--exclude-pt, .dp--exclude-t, .dp--exclude-tx").hide();
			jQuery(".dp--exclude-pp, .exclude-message").show();
		} else if(SCORG_page_post == "custom"){
			jQuery(".dp--exclude-pp, .dp--exclude-pt, .dp--exclude-t, .dp--exclude-tx").hide();
			jQuery(".dp-switch-exclude").hide();
		} else {
			jQuery(".dp--exclude-t, .exclude-message").show();
			jQuery(".dp--exclude-pt, .dp--exclude-pp, .dp--exclude-tx").hide();
		}
	} else {
		jQuery(".dp--exclude").hide();
	}
}

function dp_show_preview(){
	var url = jQuery("#dp--preview-url").val();
	if(url != "" && jQuery(".wrap-editor-and-iframe").hasClass("active")){
		jQuery("#dp--preview-iframe iframe").attr("src", url);
	}
}

function show_hide_exclude_text(){
	show_hide_exclude_options();
}


function SCORG_escapeRegExp(string){
    return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

function SCORG_replaceAll(str, term, replacement) {
    return str.replace(new RegExp(SCORG_escapeRegExp(term), 'g'), replacement);
}

function changeActiveTabValue(){
	var SCORG_active_tab = jQuery("#SCORG_active_tab").val();
	var SCORG_trigger_location = jQuery("input[name='SCORG_trigger_location']:checked").val();
	var current_active = [];
	var i = 0;
	var item = '';
	var SCORG_script_type;
	var currobj;
	jQuery(".dp--shortcode").hide();
	jQuery("input[name='SCORG_script_type[]']").each(function(){
		currobj = jQuery(this);
		SCORG_script_type = jQuery(currobj).val();
		if(SCORG_script_type == "shortcode"){
			if(jQuery(currobj).is(":checked")){
				current_active.push(SCORG_script_type);
			}
			if((SCORG_trigger_location != "everywhere" && SCORG_trigger_location != "admin_only")){
				jQuery(".dp--shortcode").show();
			}
			SCORG_script_type = "php";
		}
		if(jQuery(currobj).is(":checked")){
			current_active.push(SCORG_script_type);
		} else {
			jQuery(".tablinks.dp--show-"+SCORG_script_type+"-box").hide();	
			jQuery(".tabcontent.dp--show-"+SCORG_script_type+"-box").hide();	
		}
		i++;
	}).promise().done(function(){
		jQuery(".tablinks").removeClass("active");
		if(jQuery.inArray( "shortcode", current_active ) < 0){
			jQuery(".dp--shortcode").hide();
		} else {
			jQuery(".dp--shortcode").show();
		}
		var remove_Item = 'shortcode';
		current_active = jQuery.grep(current_active, function(value) {
		  	return value != remove_Item;
		});
		//console.log(current_active);
		if(SCORG_trigger_location != "everywhere" && SCORG_trigger_location != "admin_only"){
			if(jQuery(current_active).length > 0){
				current_active.forEach(function(item) {
					jQuery(".tablinks.dp--show-"+item+"-box").show();
					jQuery(".dp--show-"+item+"-box").parents(".rwmb-custom_html-wrapper").show();
				});
			}
			if(jQuery.inArray(SCORG_active_tab, current_active) > 0){
				jQuery(".tablinks.dp--show-"+SCORG_active_tab+"-box").addClass("active");
				jQuery(".tabcontent.dp--show-"+SCORG_active_tab+"-box").show();
			} else {
				jQuery("#SCORG_active_tab").val(current_active[0]);
				jQuery(".tablinks.dp--show-"+current_active[0]+"-box").addClass("active");
				jQuery(".tabcontent.dp--show-"+current_active[0]+"-box").show();
			}
			jQuery(".hide-in-admin-and-everywhere").removeClass("admin-only-everywhere");
			jQuery(".action-hook").hide();
			jQuery(".action-hook").hide();
		} else if(SCORG_trigger_location == "admin_only"){
			if(jQuery(current_active).length > 0){
				current_active.forEach(function(item) {
					jQuery(".tablinks.dp--show-"+item+"-box").show();
					jQuery(".dp--show-"+item+"-box").parents(".rwmb-custom_html-wrapper").show();
				});
			}
			if(jQuery.inArray(SCORG_active_tab, current_active) > 0){
				jQuery(".tablinks.dp--show-"+SCORG_active_tab+"-box").addClass("active");
				jQuery(".tabcontent.dp--show-"+SCORG_active_tab+"-box").show();
			} else {
				jQuery("#SCORG_active_tab").val(current_active[0]);
				jQuery(".tablinks.dp--show-"+current_active[0]+"-box").addClass("active");
				jQuery(".tabcontent.dp--show-"+current_active[0]+"-box").show();
			}
			jQuery(".hide-in-admin-and-everywhere").addClass("admin-only-everywhere");
			jQuery(".script-location").removeClass("admin-only-everywhere");
			jQuery(".action-hook").hide();
			jQuery(".action-hook").hide();
		} else {
			jQuery(".tablinks, .tabcontent").hide();
			jQuery(".hide-in-admin-and-everywhere").addClass("admin-only-everywhere");
			jQuery(".tablinks.dp--show-php-box").addClass("active").show();
			jQuery(".tabcontent.dp--show-php-box").show();
			jQuery(".action-hook").removeClass("admin-only-everywhere").show();
			jQuery(".action-hook").removeClass("dp--on-load__hide").show();
			jQuery('input[name="SCORG_script_type[]"]').prop('checked', false);
			jQuery('input[name="SCORG_script_type[]"][value=php]').prop('checked', true);
		}
	});
}

function show_hide_header_footer_file_option(){
	$ = jQuery;
	var header_mode = $("#SCORG_header_mode").val();
	var footer_mode = $("#SCORG_footer_mode").val();
	if(header_mode == "html"){
		if(!$(".dp--file-checkbox.header-file").hasClass("dp--hidden")){
			$(".dp--file-checkbox.header-file").addClass("dp--hidden");
		}
		$("#SCORG_header_file").prop("checked", false);
	} else {
		$(".dp--file-checkbox.header-file").removeClass("dp--hidden");
	}

	if(footer_mode == "html"){
		if(!$(".dp--file-checkbox.footer-file").hasClass("dp--hidden")){
			$(".dp--file-checkbox.footer-file").addClass("dp--hidden");
		}
		$("#SCORG_footer_file").prop("checked", false);
	} else {
		$(".dp--file-checkbox.footer-file").removeClass("dp--hidden");
	}
	show_hide_oxy_color_picker(header_mode, footer_mode);
}

function show_hide_oxy_color_picker(header_mode, footer_mode){
	$ = jQuery;
	if(header_mode == "html" || header_mode == "javascript"){
		if(!$(".oxy-color-picker.header-color-picker").hasClass("dp--hidden")){
			$(".oxy-color-picker.header-color-picker").addClass("dp--hidden");
		}
		if(!$(".css-variables-picker.header-variable-picker").hasClass("dp--hidden")){
			$(".css-variables-picker.header-variable-picker").addClass("dp--hidden");
		}
	} else {
		$(".oxy-color-picker.header-color-picker, .css-variables-picker.header-variable-picker").removeClass("dp--hidden");
	}

	if(footer_mode == "html" || footer_mode == "javascript"){
		if(!$(".dp--color-picker.footer-color-picker").hasClass("dp--hidden")){
			$(".dp--color-picker.footer-color-picker").addClass("dp--hidden");
		}
		if(!$(".css-variables-picker.footer-variable-picker").hasClass("dp--hidden")){
			$(".css-variables-picker.footer-variable-picker").addClass("dp--hidden");
		}
	} else {
		$(".dp--color-picker.footer-color-picker, .css-variables-picker.footer-variable-picker").removeClass("dp--hidden");
	}
}

// Function that will switch the theme based on the if the theme toggle is checked or not
function switchTheme(e) {
	if (e.target.checked) {
		document.documentElement.setAttribute("data-theme", "dark");
		localStorage.setItem("theme", "dark");
		if(jQuery(".scorg_css-edit-scripts").length != 0){
			editor_scss.updateOptions({
				'theme': "vs-dark"
			});
		} else {
			header_script.updateOptions({
				'theme': "vs-dark"
			});
			footer_script.updateOptions({
				'theme': "vs-dark"
			});
			php_script.updateOptions({
				'theme': "vs-dark"
			});
		}
	} else {
		document.documentElement.setAttribute("data-theme", "light");
		localStorage.setItem("theme", "light");
		if(jQuery(".scorg_css-edit-scripts").length != 0){
			editor_scss.updateOptions({
				'theme': "vs-light"
			});
		} else {
			header_script.updateOptions({
				'theme': "vs-light"
			});
			footer_script.updateOptions({
				'theme': "vs-light"
			});
			php_script.updateOptions({
				'theme': "vs-light"
			});
		}
	}
	edit_script_save_ajax();
}

function SCORG_resizer(){
	var split = Split(['.panel-left', '.panel-right'])
}

function addCommandsToMonaco($, m_editor, monaco){
	m_editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_1, function() {
		$("#page-settings").trigger("click");
	});
	m_editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_2, function() {
		$("#global-settings").trigger("click");
	});
	m_editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_3, function() {
		$("#codeblock-list").trigger("click");
	});
	m_editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_4, function() {
		$("#partials-list").trigger("click");
	});
	m_editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_7, function() {
		$("#gutenberg-blocks-list").trigger("click");
	});
	m_editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_5, function() {
		if($("body").hasClass("sidebar__focus--active")){
			$("#focus-settings2").trigger("click");
		} else {
			$("#focus-settings").trigger("click");
		}
	});
	m_editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_6, function() {
		$("#dp--show-hide-preview").trigger("click");
	});
}
function createDependencyProposals(range) {
	// returning a static list of proposals, not even looking at the prefix (filtering is done by the Monaco editor),
	// here you could do a server side lookup
	return filterByProperty(functions, range);
}

var header_script;
var footer_script;
var php_script;
var editor_scss;
var curr_mode = '';

/* broadcast channel */
const scorg_channel = new BroadcastChannel('scorg_channel');
/* broadcast channel */

function filterByProperty(functions, range){
    var filtered = [];
	for (let key in functions) {
		filtered.push({
			label: '"'+functions[key].prefix+'"',
			kind: monaco.languages.CompletionItemKind.Function,
			documentation: functions[key].description,
			insertText: functions[key].body,
			insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
			range: range
		});
    }

    return filtered;

}

jQuery(document).ready(function($){
	/* variables */
	$(".select-variable__trigger").click(function(){
		$(".variable-picker").toggleClass("active");
	});
	if(!$.isArray(scorg_css_variables)){
		scorg_css_variables = $.map(scorg_css_variables, function(value, index) {
			return [value];
		});
	}

	/* purge varaibles */
	$(".purge-variables-btn").click(function(){
		scorg_css_variables = [];
		jQuery.ajax({
			url: SCORG_ajax.ajaxurl,
			type: 'post',
			data: {
				action: 'purgeVariables',
				scorg_css_variables: scorg_css_variables.join(","),
				verify_nonce: SCORG_ajax.SCORG_nonce,
			},
			dataType: 'json',
			success: function( data ) {
				jQuery(".variable-picker ul .variables-list").html(data.variables_html);
			}
		});
	});
	/* purge varaibles */

	/* search for variables */
	$(document).on("keyup", ".scorg-var-search", function() {
		var searched_val = $(this).val().toLowerCase();
		var parentObj = $(this).parents(".variable-picker");
		$("li", parentObj).filter(function() {
			$(this).toggle($(this).text().toLowerCase().indexOf(searched_val) > -1);
		});
	});
	/* search for variables */
	
	/* search for code blocks */
	$(".scorg-search").on("keyup", function() {
		var searched_val = $(this).val().toLowerCase();
		var parentObj = $(this).parents(".dp--code-blocks");
		$(".scorg-tag", parentObj).removeClass("active");
		$("li", parentObj).filter(function() {
			$(this).toggle($(this).text().toLowerCase().indexOf(searched_val) > -1);
		});
	});
	/* search for code blocks */

	/* top bar save */
	$(".topbar--right #save").click(function(e){
		e.preventDefault();
		$("#publishing-action input[type='submit']").trigger("click");
	});
	/* top bar save */

    if(jQuery(".scorg-edit-scripts").length != 0){
    	/* close scss error box */
		jQuery(".close-scss-error-box").click(function(){
			jQuery(".dp--scorg-loader-scss").hide(300);
		});
		/* close scss error box */

		/* sync partial from file */
		$(".top__btn.sync").click(function(e){
			e.preventDefault();
			$(this).prop("disabled", true);
			var post_id = jQuery("#post_ID").val();
			$(".dp--scorg-loader-main").show();
			jQuery.ajax({
				url: SCORG_ajax.ajaxurl,
				type: 'post',
				data: {
					action: 'syncFromFileSCORG',
					id: post_id,
					verify_nonce: SCORG_ajax.SCORG_nonce,
				},
				success: function( data ) {
					location.reload();
				}
			});
		});
		/* sync partial from file */
		
    	var editor_font_size = SCORG_ajax.vs_font_size;
    	Mousetrap.bind(['ctrl+s', 'command+s'], function(e) {
		    if (e.preventDefault) {
		        e.preventDefault();
		    } else {
		        // internet explorer
		        e.returnValue = false;
		    }
		    saveScorgScript(php_script.getValue(), header_script.getValue(), footer_script.getValue());
		});

		/* monaco editor */
		require.config({ paths: { vs: SCORG_ajax.scorg_vs } });
		emmetMonaco.emmetHTML(monaco, ['html', 'php']);
		emmetMonaco.emmetCSS(monaco, ['scss', 'css']);
		emmetMonaco.emmetJSX();
		monaco.languages.registerCompletionItemProvider('php', {
			provideCompletionItems: function (model, position) {
				// find out if we are completing a property in the 'dependencies' object.
				var textUntilPosition = model.getValueInRange({
					startLineNumber: 1,
					startColumn: 1,
					endLineNumber: position.lineNumber,
					endColumn: position.column
				});
				var match = textUntilPosition.match("");
				if (!match) {
					return { suggestions: [] };
				}
				var word = model.getWordUntilPosition(position);
				var range = {
					startLineNumber: position.lineNumber,
					endLineNumber: position.lineNumber,
					startColumn: word.startColumn,
					endColumn: word.endColumn
				};
				return {
					suggestions: createDependencyProposals(range)
				};
			}
		});
		require(['vs/editor/editor.main'], function () {
			header_script = monaco.editor.create(document.getElementById('header_script'), {
				value: $("#SCORG_header_script").val(),
				language: 'html',
		        lineNumbers: true,
		        roundedSelection: false,
		        scrollBeyondLastLine: false,
		        readOnly: false,
		        glyphMargin: false,
		        vertical: 'auto',
		        horizontal: 'auto',
		        verticalScrollbarSize: 10,
		        horizontalScrollbarSize: 10,
		        theme: 'vs-'+SCORG_ajax.vs_theme,
		        wordWrap: 'wordWrapColumn',
		        minimap: { enabled: false },
		        wordWrapColumn: 120,
		        wordWrapMinified: true,
		        wrappingIndent: "indent",
		        automaticLayout: true,
		        lineHeight: editor_font_size * 1.45,
		        fontSize: editor_font_size,
				"autoIndent": true,
				"formatOnPaste": true,
				"formatOnType": true,
				'bracketPairColorization.enabled': true,
			});
			// $("#header_script").height(200);
			//window['emmet-monaco'].enableEmmet(header_script, window.emmet);
			header_script.onKeyUp(() => {
				$("#SCORG_header_script").val(header_script.getValue());
			});
			header_script.getModel().onDidChangeContent((event) => {
				$("#SCORG_header_script").val(header_script.getValue());
			});
			header_script.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_S, function() {
			    saveScorgScript(php_script.getValue(), header_script.getValue(), footer_script.getValue());
			});	
			addCommandsToMonaco($, header_script, monaco);
			jQuery( window ).resize(function() {
				header_script.layout({});
			});

			footer_script = monaco.editor.create(document.getElementById('footer_script'), {
				value: $("#SCORG_footer_script").val(),
				language: 'html',
		        lineNumbers: true,
		        roundedSelection: false,
		        scrollBeyondLastLine: false,
		        readOnly: false,
		        glyphMargin: false,
		        vertical: 'auto',
		        horizontal: 'auto',
		        verticalScrollbarSize: 10,
		        horizontalScrollbarSize: 10,
		        theme: 'vs-'+SCORG_ajax.vs_theme,
		        wordWrap: 'wordWrapColumn',
		        minimap: { enabled: false },
		        wordWrapColumn: 120,
		        wordWrapMinified: true,
		        wrappingIndent: "indent",
		        automaticLayout: true,
		        lineHeight: editor_font_size * 1.45,
		        fontSize: editor_font_size,
				"autoIndent": true,
				"formatOnPaste": true,
				"formatOnType": true,
				'bracketPairColorization.enabled': true,
			});
			// $("#footer_script").height(200);
			//emmetMonaco.emmetHTML(footer_script);
			//window['emmet-monaco'].enableEmmet(footer_script, window.emmet);
			footer_script.onKeyUp(() => {
				$("#SCORG_footer_script").val(footer_script.getValue());
			});
			footer_script.getModel().onDidChangeContent((event) => {
				$("#SCORG_footer_script").val(footer_script.getValue());
			});
			footer_script.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_S, function() {
			    saveScorgScript(php_script.getValue(), header_script.getValue(), footer_script.getValue());
			});
			addCommandsToMonaco($, footer_script, monaco);
			jQuery( window ).resize(function() {
				footer_script.layout({});
			});

			php_script = monaco.editor.create(document.getElementById('php_script'), {
				value: SCORG_replaceAll($("#SCORG_php_script").val(), 'REPLACE_BACKSLASH', '\\'),
				language: 'php',
		        lineNumbers: true,
		        roundedSelection: false,
		        scrollBeyondLastLine: false,
		        readOnly: false,
		        glyphMargin: false,
		        vertical: 'auto',
		        horizontal: 'auto',
		        verticalScrollbarSize: 10,
		        horizontalScrollbarSize: 10,
		        theme: 'vs-'+SCORG_ajax.vs_theme,
		        wordWrap: 'wordWrapColumn',
		        minimap: { enabled: false },
		        wordWrapColumn: 120,
		        wordWrapMinified: true,
		        wrappingIndent: "indent",
		        automaticLayout: true,
		        lineHeight: editor_font_size * 1.45,
		        fontSize: editor_font_size,
				"autoIndent": true,
				"formatOnPaste": true,
				"formatOnType": true,
				'bracketPairColorization.enabled': true,
			});
			// $("#php_script").height(200);
			//window['emmet-monaco'].enableEmmet(php_script, window.emmet);
			php_script.onKeyUp(() => {
				$("#SCORG_php_script").val(php_script.getValue());
			});
			php_script.getModel().onDidChangeContent((event) => {
				$("#SCORG_php_script").val(php_script.getValue());
			});
			php_script.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_S, function() {
			    saveScorgScript(php_script.getValue(), header_script.getValue(), footer_script.getValue());
			});
			addCommandsToMonaco($, php_script, monaco);
			jQuery( window ).resize(function() {
				php_script.layout({});
			});
		});

		// oxygen color picker
		$(".insert-color").click(function(){
			var oxy_color_id = $(this).data("id");
			if($("button.dp--show-header-box").hasClass("active")){
				header_script.trigger('keyboard', 'type', {text: oxy_color_id});
				$("#SCORG_header_script").val(header_script.getValue());
			}
			if($("button.dp--show-footer-box").hasClass("active")){
				footer_script.trigger('keyboard', 'type', {text: oxy_color_id});
				$("#SCORG_footer_script").val(footer_script.getValue());
			}
		});
		
		// variables picker
		$(document).on("click", ".insert-variable", function(){
			var variable_id = $(this).data("id");
			if($("button.dp--show-header-box").hasClass("active")){
				header_script.trigger('keyboard', 'type', {text: variable_id});
				$("#SCORG_header_script").val(header_script.getValue());
			}
			if($("button.dp--show-footer-box").hasClass("active")){
				footer_script.trigger('keyboard', 'type', {text: variable_id});
				$("#SCORG_footer_script").val(footer_script.getValue());
			}
		});

		$("#SCORG_header_mode, #SCORG_footer_mode").change(function(){
			curr_mode = $(this).val();
			if(curr_mode == ""){
				curr_mode = 'html';
			}
			if($(this).attr("id") == "SCORG_header_mode"){
				monaco.editor.setModelLanguage(header_script.getModel(), curr_mode);
			} else {
				monaco.editor.setModelLanguage(footer_script.getModel(), curr_mode);
			}
			show_hide_header_footer_file_option();
		});

		curr_mode = $("#SCORG_header_mode").val();
		if(curr_mode == ""){
			curr_mode = 'html';
		}
		monaco.editor.setModelLanguage(header_script.getModel(), curr_mode);

		curr_mode = $("#SCORG_footer_mode").val();
		if(curr_mode == ""){
			curr_mode = 'html';
		}
		monaco.editor.setModelLanguage(footer_script.getModel(), curr_mode);
		/* monaco editor */
    }
});

jQuery(document).ready(function($){
	/* toggles and buttons */
	/*=============================================
	=                   Shortcuts                 =
	=============================================*/

	// exit to WP
	/* Mousetrap.bind(['ctrl+e', 'command+e'], function(e) {
		if (e.preventDefault) {
			e.preventDefault();
		} else {
			// internet explorer
			e.returnValue = false;
		}
		$("#top__wp").trigger("click");
	}); */
	
	// page settings trigger
	Mousetrap.bind(['ctrl+1', 'command+1'], function(e) {
		if (e.preventDefault) {
			e.preventDefault();
		} else {
			// internet explorer
			e.returnValue = false;
		}
		$("#page-settings").trigger("click");
	});
	
	// global settings trigger
	Mousetrap.bind(['ctrl+2', 'command+2'], function(e) {
		if (e.preventDefault) {
			e.preventDefault();
		} else {
			// internet explorer
			e.returnValue = false;
		}
		$("#global-settings").trigger("click");
	});
	
	// code block list trigger
	Mousetrap.bind(['ctrl+3', 'command+3'], function(e) {
		if (e.preventDefault) {
			e.preventDefault();
		} else {
			// internet explorer
			e.returnValue = false;
		}
		$("#codeblock-list").trigger("click");
	});
	
	// partials list trigger
	Mousetrap.bind(['ctrl+4', 'command+4'], function(e) {
		if (e.preventDefault) {
			e.preventDefault();
		} else {
			// internet explorer
			e.returnValue = false;
		}
		$("#partials-list").trigger("click");
	});

	// gutenberg blocks list trigger
	Mousetrap.bind(['ctrl+7', 'command+7'], function(e) {
		if (e.preventDefault) {
			e.preventDefault();
		} else {
			// internet explorer
			e.returnValue = false;
		}
		$("#gutenberg-blocks-list").trigger("click");
	});

	// Focus mode
	Mousetrap.bind(['ctrl+5', 'command+5'], function(e) {
		if (e.preventDefault) {
			e.preventDefault();
		} else {
			// internet explorer
			e.returnValue = false;
		}
		if($("body").hasClass("sidebar__focus--active")){
			$("#focus-settings2").trigger("click");
		} else {
			$("#focus-settings").trigger("click");
		}
	});
	
	// Toggle Preview
	Mousetrap.bind(['ctrl+6', 'command+6'], function(e) {
		if (e.preventDefault) {
			e.preventDefault();
		} else {
			// internet explorer
			e.returnValue = false;
		}
		$("#dp--show-hide-preview").trigger("click");
	});

	$('label[for="codeblocks-hide"], label[for="scss_parials-hide"]').remove();

	// Screen Settings
	$( "#screen_options" ).click(function() {
		$( "#screen-options-wrap" ).slideToggle( "slow" );
	});

	// Single Page Toggle Logic    
	
	$( "#page-settings" ).click(function() {
		$( ".wp-admin" ).addClass( "sidebar__pages--active" );
		$( ".wp-admin" ).removeClass( "sidebar__settings--active" );
		$( ".wp-admin" ).removeClass( "sidebar__scorg--active" );
		$( ".wp-admin" ).removeClass( "sidebar__scss--active" );
		$( ".wp-admin" ).removeClass( "sidebar__gutenberg--active" );
		edit_script_save_ajax();
	});
	
	$( "#global-settings" ).click(function() {
		$( ".wp-admin" ).addClass( "sidebar__settings--active" );
		$( ".wp-admin" ).removeClass( "sidebar__pages--active" );
		$( ".wp-admin" ).removeClass( "sidebar__scorg--active" );
		$( ".wp-admin" ).removeClass( "sidebar__scss--active" );
		$( ".wp-admin" ).removeClass( "sidebar__gutenberg--active" );
		edit_script_save_ajax();
	});
	
	$( "#codeblock-list" ).click(function() {
		$( ".wp-admin" ).removeClass( "sidebar__settings--active" );
		$( ".wp-admin" ).removeClass( "sidebar__pages--active" );
		$( ".wp-admin" ).addClass( "sidebar__scorg--active" );
		$( ".wp-admin" ).removeClass( "sidebar__scss--active" );
		$( ".wp-admin" ).removeClass( "sidebar__gutenberg--active" );
	});
	
	$( "#partials-list" ).click(function() {
		$( ".wp-admin" ).removeClass( "sidebar__settings--active" );
		$( ".wp-admin" ).removeClass( "sidebar__pages--active" );
		$( ".wp-admin" ).removeClass( "sidebar__scorg--active" );
		$( ".wp-admin" ).removeClass( "sidebar__gutenberg--active" );
		$( ".wp-admin" ).addClass( "sidebar__scss--active" );
	});
	
	$( "#gutenberg-blocks-list" ).click(function() {
		$( ".wp-admin" ).removeClass( "sidebar__settings--active" );
		$( ".wp-admin" ).removeClass( "sidebar__pages--active" );
		$( ".wp-admin" ).removeClass( "sidebar__scorg--active" );
		$( ".wp-admin" ).removeClass( "sidebar__scss--active" );
		$( ".wp-admin" ).addClass( "sidebar__gutenberg--active" );
	});
	
	$( "#focus-settings" ).click(function() {
		$( ".wp-admin" ).addClass( "sidebar__focus--active" );
		$( ".wp-admin" ).removeClass( "sidebar__pages--active" );
		$( ".wp-admin" ).removeClass( "sidebar__settings--active" );
		edit_script_save_ajax();
	});

	$( "#focus-settings2" ).click(function() {
		$( ".wp-admin" ).removeClass( "sidebar__focus--active" );
		edit_script_save_ajax();
	});

	// filter based on tags
	$(".scorg-tag").click(function(e){
		e.preventDefault();
		var currObj = $(this);
		var currObjParent = $(this).parents(".rwmb-custom_html-wrapper");
		var clicked_tag = $(currObj).data("tag");
		$(".scorg-tag").removeClass("active");
		$(currObj).addClass("active");
		$(currObjParent).find("li").hide();
		if(clicked_tag == "all"){
			$(currObjParent).find("li").show();
		} else {
			$(currObjParent).find("li."+clicked_tag).show();
		}
	});
	/* toggles and buttons */


	if(jQuery(".scripts-organizer_page_scorg_scripts_manager").length != 0){
		SCORG_hide_save_button();		
	}
});

// hide save button if empty
function SCORG_hide_save_button(){
    if(jQuery(".scripts-rows .script-row").length < 1){
        jQuery(".scripts-manager #Scripts").hide();
    }
}

function SCORG_trigger_location(){
	var SCORG_trigger_location = jQuery("input[name='SCORG_trigger_location']:checked").val();
	if(SCORG_trigger_location != ""){
		jQuery(".trigger-location").hide();
		jQuery(".trigger-location."+SCORG_trigger_location).show();
		var current_li;
		if(SCORG_trigger_location == "everywhere"){
			jQuery(".dp--exclude").addClass("dp--exclude-hide");
			jQuery(".script-location li").each(function(){
				current_li = jQuery(this);
				if(jQuery(current_li).find("input").val() == "php"){
					jQuery(current_li).show();
					jQuery(current_li).find("input[type='checkbox']").prop("checked", true);
				} else {
					jQuery(current_li).hide();
				}
			});

			jQuery(".script-template li").each(function(){
				current_li = jQuery(this);
				if(jQuery(current_li).find("input").val() == "all"){
					jQuery(current_li).show();
				} else {
					jQuery(current_li).hide();
				}
			});
		} else if(SCORG_trigger_location == "admin_only"){
			jQuery(".dp--exclude").addClass("dp--exclude-hide");
			jQuery(".script-location li").each(function(){
				current_li = jQuery(this);
				if(jQuery(current_li).find("input").val() != "shortcode"){
					jQuery(current_li).show();
				} else {
					jQuery(current_li).hide();
				}
			});

			jQuery(".script-template li").each(function(){
				current_li = jQuery(this);
				if(jQuery(current_li).find("input").val() == "all"){
					jQuery(current_li).show();
				} else {
					jQuery(current_li).hide();
				}
			});
		} else {
			jQuery(".dp--exclude").removeClass("dp--exclude-hide");
			jQuery(".script-location li").each(function(){
				jQuery(this).show();
			});
			jQuery(".script-template li").each(function(){
				jQuery(this).show();
			});
		}

		changeActiveTabValue();
		show_hide_header_footer_file_option();
	}
	show_hide_exclude_text();
}

function gridToggles(){
	if(jQuery(".columns-direction").hasClass("active")){
		jQuery("#SCORG_view").val("no");
		jQuery(".columns-direction").removeClass("active");
		jQuery(".dp--editor-main").removeClass("column-active");
	} else {
		jQuery(".columns-direction").addClass("active");
		jQuery(".dp--editor-main").addClass("column-active");
		jQuery("#SCORG_view").val("yes");
	}
}

function edit_script_save_ajax(){
	var post_id = jQuery("#post_ID").val();
	var SCORG_view = jQuery("#SCORG_view").val();
    if(post_id != ""){
    	var script_action = 0;
    	var frontend_only = 0;
		if(jQuery(".dp-switch-save #SCORG_enable_script").is(":checked")){
			script_action = 1;
		} else {
			script_action = 0;
		}

		if(jQuery("#SCORG_only_frontend").is(":checked")){
			frontend_only = 1;
		} else {
			frontend_only = 0;
		}

		var theme = document.documentElement.getAttribute("data-theme");
		if(theme == "dark"){
			theme = "yes";
		}

		var focus_mode = '';
		if($("body").hasClass("sidebar__focus--active")){
			focus_mode = "yes";
		}

		var sidebar_settings = '';
		if($("body").hasClass("sidebar__settings--active")){
			sidebar_settings = "yes";
		}
		
		var page_settings = '';
		if($("body").hasClass("sidebar__pages--active")){
			page_settings = "yes";
		}
		jQuery.ajax({
            url: SCORG_ajax.ajaxurl,
            type: 'post',
            data: {
                action: 'saveAction',
                id: post_id,
                script_action: script_action,
                frontend_only: frontend_only,
                SCORG_view: SCORG_view,
				scorg_darkmode: theme,
				focus_mode: focus_mode,
				sidebar_settings: sidebar_settings,
				page_settings: page_settings,
				verify_nonce: SCORG_ajax.SCORG_nonce,
            },
			async: true,
            success: function( data ) {
            	//console.log(data);
            }
        });
    }
}

function saveScorgScript(editor_php, editor_header, editor_footer){
	jQuery(".dp--scorg-loader-main").show();
	var form_data = btoa(encodeURIComponent(jQuery(".post-type-scorg form#post").serialize()));
	var php_script = btoa(encodeURIComponent(editor_php));
	var header_script = btoa(encodeURIComponent(editor_header));
	var footer_script = btoa(encodeURIComponent(editor_footer));
	scorg_css_variables = get_all_css_variables_iframe();
	scorg_css_variables = get_all_css_variables(editor_header);
	scorg_css_variables = get_all_css_variables(editor_footer);
	var SCORG_enable_script = 0;
	if(jQuery("#SCORG_enable_script").is(":checked")){
		SCORG_enable_script = 1;
	}
	jQuery.ajax({
        url: SCORG_ajax.ajaxurl,
        type: "post",
        data: { 
        	action: "saveScript", 
        	form_data: form_data, 
        	php_script: php_script,
        	header_script: header_script,
        	footer_script: footer_script,
        	scorg_css_variables: scorg_css_variables.join(","),
        	SCORG_enable_script: SCORG_enable_script,
			verify_nonce: SCORG_ajax.SCORG_nonce,
        },
		dataType: 'json',
		async: true,
        success: function (e) {
            jQuery(".dp--scorg-loader-main").hide(300);
            if(e.message != ""){
            	jQuery(".dp--scorg-loader-scss").show(300);
            }
			jQuery(".variable-picker ul .variables-list").html(e.variables_html);
			scorg_channel.postMessage('reload');
			dp_show_preview();
        },
    });
}

function get_all_css_variables(editor_css){
	/*Get css styles*/
	var rules = editor_css.substring(editor_css.indexOf("{")+1,editor_css.indexOf("}"));
	rules = rules.split(";");
	for(var j=0;j<rules.length;j++) {
		if(rules[j].trim().startsWith("--") && scorg_css_variables.indexOf(rules[j].trim().split(":")[0]) < 0 && rules[j].trim().split(":")[0] != "") {
			scorg_css_variables.push(rules[j].trim().split(":")[0]);
		}
	}
	return scorg_css_variables;
}

function get_all_css_variables_iframe(){
	var css_content = '';
	var iframe_styles = jQuery("#dp--iframe_body iframe").contents().find("style");
	if(iframe_styles.length > 0){
		jQuery("#dp--iframe_body iframe").contents().find("style").each(function(){
			css_content = jQuery(this).html();
			/*Get css styles*/
			var rules = css_content.substring(css_content.indexOf("{")+1,css_content.indexOf("}"));
			rules = rules.split(";");
			for(var j=0;j<rules.length;j++) {
				if(rules[j].trim().startsWith("--") && scorg_css_variables.indexOf(rules[j].trim().split(":")[0]) < 0 && rules[j].trim().split(":")[0] != "") {
					scorg_css_variables.push(rules[j].trim().split(":")[0]);
				}
			}
		});
	}

	var iframe_styles = jQuery("#dp--iframe_body iframe")[0].contentDocument.styleSheets;
	if(iframe_styles.length > 0){
		for(var i = 0; i < iframe_styles.length; i++){
			// loop stylesheet's cssRules
			try{ // try/catch used because 'hasOwnProperty' doesn't work
				for( var j = 0; j < iframe_styles[i].cssRules.length; j++){
					try{
						// loop stylesheet's cssRules' style (property names)
						for(var k = 0; k < iframe_styles[i].cssRules[j].style.length; k++){
							let name = iframe_styles[i].cssRules[j].style[k];
							// test name for css variable signiture and uniqueness
							if(name.startsWith('--') && scorg_css_variables.indexOf(name) < 0 && name != ""){
								scorg_css_variables.push(name);
							}
						}
					} catch (error) {}
				}
			} catch (error) {}
		}
	}
	return scorg_css_variables;
}

function getAllCSSVariableNamesIframe(styleSheets = document.styleSheets){
	// loop each stylesheet
	for(var i = 0; i < styleSheets.length; i++){
	   // loop stylesheet's cssRules
	   try{ // try/catch used because 'hasOwnProperty' doesn't work
		  for( var j = 0; j < styleSheets[i].cssRules.length; j++){
			 try{
				// loop stylesheet's cssRules' style (property names)
				for(var k = 0; k < styleSheets[i].cssRules[j].style.length; k++){
				   let name = styleSheets[i].cssRules[j].style[k];
				   // test name for css variable signiture and uniqueness
				   if(name.startsWith('--') && scorg_css_variables.indexOf(name) < 1){
						scorg_css_variables.push(name);
				   }
				}
			 } catch (error) {}
		  }
	   } catch (error) {}
	}
	return scorg_css_variables;
}

function saveSCSSScript(editor_scss){
	jQuery(".dp--scorg-loader-main").show();
	var form_data = btoa(encodeURIComponent(jQuery(".post-type-scorg_scss form#post").serialize()));
	scorg_css_variables = get_all_css_variables_iframe();
	scorg_css_variables = get_all_css_variables(editor_scss);
	var editor_scss = btoa(encodeURIComponent(editor_scss));
	jQuery.ajax({
        url: SCORG_ajax.ajaxurl,
        type: "post",
        async: false,
		dataType: 'json',
        data: { 
        	action: "saveSCSSScript", 
        	form_data: form_data, 
        	editor_scss: editor_scss,
        	scorg_css_variables: scorg_css_variables.join(","),
			verify_nonce: SCORG_ajax.SCORG_nonce,
        },
        success: function (e) {
            jQuery(".dp--scorg-loader-main").hide(300);
			jQuery(".variable-picker ul .variables-list").html(e.variables_html);
			scorg_channel.postMessage('reload');
			dp_show_preview();
        },
    });
}

function SCSS_functions(){
	$ = jQuery;
	preview_functions();
	if(jQuery(".scorg_css-edit-scripts").length != 0){
		// Get the theme toggle input
		const themeToggle = document.querySelector(
			'#theme-settings input[type="checkbox"]'
		);

		// Get the current theme from local storage
		const currentTheme = SCORG_ajax.vs_theme;

		// If the current local storage item can be found
		if (currentTheme) {
			// Set the body data-theme attribute to match the local storage item
			document.documentElement.setAttribute("data-theme", currentTheme);

			// If the current theme is dark, check the theme toggle
			if (currentTheme === "dark") {
				themeToggle.checked = true;
			}
		}

		// Add an event listener to the theme toggle, which will switch the theme
		themeToggle.addEventListener("change", switchTheme, false);

		/* description auto height */
		jQuery("#SCSS_description").on('input', function () {
	        this.style.height = '';
	        this.style.height = (this.scrollHeight) + 'px';
		});
		/* description auto height */

		/* toggle sidebar */
		jQuery(".toggle-sidebar").click(function(){
			jQuery(".dp--scorg-column.dp--settings-sidebar").toggleClass("hide-sidebar");
			if(jQuery(".dp--settings-sidebar").hasClass("hide-sidebar")){
				jQuery("#SCSS_toggle_sidebar").val("yes");
			} else {
				jQuery("#SCSS_toggle_sidebar").val("no");
			}
		});
		/* toggle sidebar */

		/* sync partial from file */
		$(".top__btn.sync").click(function(e){
			e.preventDefault();
			$(this).prop("disabled", true);
			var post_id = jQuery("#post_ID").val();
			$(".dp--scorg-loader-main").show();
			jQuery.ajax({
				url: SCORG_ajax.ajaxurl,
				type: 'post',
				data: {
					action: 'syncFromFile',
					id: post_id,
					verify_nonce: SCORG_ajax.SCORG_nonce,
				},
				success: function( data ) {
					location.reload();
				}
			});
		});
		/* sync partial from file */

		/* initialize monaco */
		var editor_font_size = SCORG_ajax.vs_font_size;
    	Mousetrap.bind(['ctrl+s', 'command+s'], function(e) {
		    if (e.preventDefault) {
		        e.preventDefault();
		    } else {
		        // internet explorer
		        e.returnValue = false;
		    }
		    saveSCSSScript(editor_scss.getValue());
		});

		/* monaco editor */
		require.config({ paths: { vs: SCORG_ajax.scorg_vs } });
		emmetMonaco.emmetCSS(monaco, ['scss']);
		require(['vs/editor/editor.main'], function () {
			editor_scss = monaco.editor.create(document.getElementById('scss_script'), {
				value: $("#SCSS_scss_scripts").val(),
				language: 'scss',
		        lineNumbers: true,
		        roundedSelection: false,
		        scrollBeyondLastLine: false,
		        readOnly: false,
		        glyphMargin: false,
		        vertical: 'auto',
		        horizontal: 'auto',
		        verticalScrollbarSize: 10,
		        horizontalScrollbarSize: 10,
		        theme: 'vs-'+SCORG_ajax.vs_theme,
		        wordWrap: 'wordWrapColumn',
		        minimap: { enabled: false },
		        wordWrapColumn: 120,
		        wordWrapMinified: true,
		        wrappingIndent: "indent",
		        automaticLayout: true,
		        lineHeight: editor_font_size * 1.45,
		        fontSize: editor_font_size,
				"autoIndent": true,
				"formatOnPaste": true,
				"formatOnType": true,
				'bracketPairColorization.enabled': true,
			});
			// $("#scss_script").height(200);
			//window['emmet-monaco'].enableEmmet(editor_scss, window.emmet);
			editor_scss.onKeyUp(() => {
				$("#SCSS_scss_scripts").val(editor_scss.getValue());
			});
			editor_scss.getModel().onDidChangeContent((event) => {
				$("#SCSS_scss_scripts").val(editor_scss.getValue());
			});
			editor_scss.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_S, function() {
			    saveSCSSScript(editor_scss.getValue());
			});
			addCommandsToMonaco($, editor_scss, monaco);
			jQuery( window ).resize(function() {
				editor_scss.layout({});
			});
		});
		/* initialize monaco */

		// oxygen color picker
		$(".insert-color").click(function(){
			var oxy_color_id = $(this).data("id");
			editor_scss.trigger('keyboard', 'type', {text: oxy_color_id});
			$("#SCSS_scss_scripts").val(editor_scss.getValue());
		});
		
		// variables picker
		$(document).on("click", ".insert-variable", function(){
			var variable_id = $(this).data("id");
			editor_scss.trigger('keyboard', 'type', {text: variable_id});
			$("#SCSS_scss_scripts").val(editor_scss.getValue());
		});
	}
}

function preview_functions(){
	/* hide not needed elements */
	jQuery("#dp--iframe_body iframe").on("load", function(){
		let head = jQuery("#dp--iframe_body iframe").contents().find("head");
		let css = '<style>#wpadminbar,#screen-meta-links{display:none !important;}#wpbody{padding-top:0px;}</style>';
		jQuery(head).append(css);
	});
	/* hide not needed elements */
	
	/* preview */
	jQuery(".top__btn.preview").click(function(e){
		e.preventDefault();
		jQuery( ".wrap-editor-and-iframe" ).toggleClass( "active" );
		jQuery( "#dp--preview-iframe" ).toggle();
		SCORG_resizer();
		dp_show_preview();
	});

	/* preview screen size change */
	jQuery("#preview-size").change(function(){
		var pr_width = jQuery(this).find(":selected").data("width");
		var pr_height = jQuery(this).find(":selected").data("height");
		if(pr_width == ""){
			jQuery("#dp--iframe_body iframe").css({"width": "", "height": ""});
		} else {
			jQuery("#dp--iframe_body iframe").css({"width": pr_width, "height": pr_height});
		}
	});
	/* preview screen size change */

	/* preview zoom */
	jQuery("#preview-zoom").change(function(){
		var pr_zoom = jQuery(this).find(":selected").data("zoom");
		jQuery("#dp--iframe_body iframe").css({"transform": "scale("+pr_zoom+")"});
	});
	/* preview zoom */

	/* preview url enter */
	jQuery(document.body).on("keypress", "#dp--preview-url", function(e){
		if(e.which == 13){
			e.preventDefault();
			dp_show_preview();
		}
	});
	/* preview url enter */

	/* home button */
	jQuery(document.body).on("click", "#open-home", function(e){
		e.preventDefault();
		jQuery("#dp--preview-url").val(jQuery(this).data("url"));
		dp_show_preview();
	});
	/* home button */

	jQuery("#dp--preview-iframe button").click(function(e){
		e.preventDefault();
		dp_show_preview();
	});
	/* preview */
}

jQuery(document).ready(function(){
	if(jQuery('.scorg_css-edit-scripts').length != 0) {
		SCSS_functions();
	}
	var editor_header;
	var editor_footer;
	var editor_php;
	if(jQuery("#SCORG_header_script").length != 0){
		// Get the theme toggle input
		const themeToggle = document.querySelector(
			'#theme-settings input[type="checkbox"]'
		);

		// Get the current theme from local storage
		const currentTheme = SCORG_ajax.vs_theme;

		// If the current local storage item can be found
		if (currentTheme) {
			// Set the body data-theme attribute to match the local storage item
			document.documentElement.setAttribute("data-theme", currentTheme);

			// If the current theme is dark, check the theme toggle
			if (currentTheme === "dark") {
				themeToggle.checked = true;
			}
		}

		// Add an event listener to the theme toggle, which will switch the theme
		themeToggle.addEventListener("change", switchTheme, false);

		SCORG_active_tab = jQuery("#SCORG_active_tab").val();
		/* description auto height */
		jQuery("#SCORG_script_description").on('input', function () {
	        this.style.height = '';
	        this.style.height = (this.scrollHeight) + 'px';
		});
		/* description auto height */

		/* toggle sidebar */
		jQuery(".toggle-sidebar").click(function(){
			jQuery(".dp--scorg-column.dp--settings-sidebar").toggleClass("hide-sidebar");
			if(jQuery(".dp--settings-sidebar").hasClass("hide-sidebar")){
				jQuery("#SCORG_toggle_sidebar").val("yes");
			} else {
				jQuery("#SCORG_toggle_sidebar").val("no");
			}
		});
		/* toggle sidebar */
	}

	if(jQuery('#scriptsettings').length != 0) {
		/* on page load */
		preview_functions();
		
		SCORG_trigger_location();

		/* scripts manager show/hide */
		var SCORG_scripts_manager = jQuery("input[name='SCORG_scripts_manager']:checked").val();
		if(SCORG_scripts_manager == "show"){
			jQuery("select[name='SCORG_dequeue_scripts[]']").parent().parent().show();
			jQuery("select[name='SCORG_enqueue_scripts[]']").parent().parent().show();
		} else {
			jQuery("select[name='SCORG_dequeue_scripts[]']").parent().parent().hide();
			jQuery("select[name='SCORG_enqueue_scripts[]']").parent().parent().hide();
		}

		/* SCSS partials manager show/hide */
		var SCORG_scss_partial_manager = jQuery("input[name='SCORG_scss_partial_manager']:checked").val();
		if(SCORG_scss_partial_manager == "show"){
			jQuery("select[name='SCORG_partials[]']").parent().parent().show();
		} else {
			jQuery("select[name='SCORG_partials[]']").parent().parent().hide();
		}

		/* page post show hide */
		var SCORG_page_post = jQuery("input[name='SCORG_page_post']:checked").val();
		if(SCORG_page_post == "all"){
    		jQuery("select[name='SCORG_selected_page_post[]']").parent().parent().hide();
    		jQuery("select[name='SCORG_specific_post_type[]']").parent().parent().hide();
    		jQuery("select[name='SCORG_specific_taxonomy[]']").parent().parent().hide();
			jQuery("input[name='SCORG_custom']").parent().parent().hide();
    		/* jQuery("select[name='SCORG_exclude_page_post[]']").parent().parent().show();
    		jQuery("select[name='SCORG_exclude_terms[]']").parent().parent().show();
    		jQuery("select[name='SCORG_exclude_taxonomies[]']").parent().parent().show(); */
    	} else if(SCORG_page_post == "specific_page_post"){
    		jQuery("select[name='SCORG_selected_page_post[]']").parent().parent().show();
    		jQuery("select[name='SCORG_specific_post_type[]']").parent().parent().hide();
    		jQuery("select[name='SCORG_specific_taxonomy[]']").parent().parent().hide();
			jQuery("input[name='SCORG_custom']").parent().parent().hide();
			/* jQuery("select[name='SCORG_exclude_page_post[]']").parent().parent().show();
    		jQuery("select[name='SCORG_exclude_taxonomies[]']").parent().parent().hide();
    		jQuery("select[name='SCORG_exclude_terms[]']").parent().parent().hide(); */
    	} else if(SCORG_page_post == "specific_post_type"){
    		jQuery("select[name='SCORG_selected_page_post[]']").parent().parent().hide();
    		jQuery("select[name='SCORG_specific_post_type[]']").parent().parent().show();
    		jQuery("select[name='SCORG_specific_taxonomy[]']").parent().parent().hide();
			jQuery("input[name='SCORG_custom']").parent().parent().hide();
			/* jQuery("select[name='SCORG_exclude_page_post[]']").parent().parent().show();
    		jQuery("select[name='SCORG_exclude_terms[]']").parent().parent().hide();
    		jQuery("select[name='SCORG_exclude_taxonomies[]']").parent().parent().hide(); */
    	} else if(SCORG_page_post == "custom"){
    		jQuery("select[name='SCORG_selected_page_post[]']").parent().parent().hide();
    		jQuery("select[name='SCORG_specific_post_type[]']").parent().parent().hide();
    		jQuery("select[name='SCORG_specific_taxonomy[]']").parent().parent().hide();
			jQuery("input[name='SCORG_custom']").parent().parent().show();
			/* jQuery("select[name='SCORG_exclude_page_post[]']").parent().parent().show();
    		jQuery("select[name='SCORG_exclude_terms[]']").parent().parent().hide();
    		jQuery("select[name='SCORG_exclude_taxonomies[]']").parent().parent().hide(); */
    	} else {
    		jQuery("select[name='SCORG_selected_page_post[]']").parent().parent().hide();
    		jQuery("select[name='SCORG_specific_post_type[]']").parent().parent().hide();
    		jQuery("select[name='SCORG_specific_taxonomy[]']").parent().parent().show();
			jQuery("input[name='SCORG_custom']").parent().parent().hide();
			/* jQuery("select[name='SCORG_exclude_page_post[]']").parent().parent().hide();
    		jQuery("select[name='SCORG_exclude_terms[]']").parent().parent().show();
    		jQuery("select[name='SCORG_exclude_taxonomies[]']").parent().parent().hide(); */
    	}
		show_hide_exclude_text();

    	/* script schedule show/hide */
    	var SCORG_script_schedule = jQuery("input[name='SCORG_script_schedule']:checked").val();
		if(SCORG_script_schedule == "daily"){
			jQuery("input[name='SCORG_specific_date']").parent().parent().hide();
			jQuery("input[name='SCORG_specific_date_from']").parent().parent().hide();
			jQuery("input[name='SCORG_specific_date_to']").parent().parent().hide();
			jQuery("select[name='SCORG_days[]']").parent().parent().hide();
		} else if (SCORG_script_schedule == "specific_days") {
			jQuery("input[name='SCORG_specific_date']").parent().parent().hide();
			jQuery("select[name='SCORG_days[]']").parent().parent().show();
			jQuery("input[name='SCORG_specific_date_from']").parent().parent().hide();
			jQuery("input[name='SCORG_specific_date_to']").parent().parent().hide();
		} else if (SCORG_script_schedule == "specific_date") {
			jQuery("input[name='SCORG_specific_date']").parent().parent().show();
			jQuery("select[name='SCORG_days[]']").parent().parent().hide();
			jQuery("input[name='SCORG_specific_date_from']").parent().parent().hide();
			jQuery("input[name='SCORG_specific_date_to']").parent().parent().hide();
		} else {
			jQuery("input[name='SCORG_specific_date']").parent().parent().hide();
			jQuery("select[name='SCORG_days[]']").parent().parent().hide();
			jQuery("input[name='SCORG_specific_date_from']").parent().parent().show();
			jQuery("input[name='SCORG_specific_date_to']").parent().parent().show();
		}

		/* script time show/hide */
		var SCORG_script_time = jQuery("input[name='SCORG_script_time']:checked").val();
		if(SCORG_script_time == "all_day"){
			jQuery("input[name='SCORG_specific_time_start']").parent().parent().hide();
			jQuery("input[name='SCORG_specific_time_end']").parent().parent().hide();
		} else {
			jQuery("input[name='SCORG_specific_time_start']").parent().parent().show();
			jQuery("input[name='SCORG_specific_time_end']").parent().parent().show();
		}

		/* page post show hide */
		jQuery("input[name='SCORG_page_post']").click(function(){
			if(jQuery(this).val() == "all"){
	    		jQuery("select[name='SCORG_selected_page_post[]']").parent().parent().hide();
	    		jQuery("select[name='SCORG_specific_post_type[]']").parent().parent().hide();
	    		jQuery("select[name='SCORG_specific_taxonomy[]']").parent().parent().hide();
				jQuery("input[name='SCORG_custom']").parent().parent().hide();
				/* jQuery("select[name='SCORG_exclude_page_post[]']").parent().parent().show();
				jQuery("select[name='SCORG_exclude_terms[]']").parent().parent().show();
				jQuery("select[name='SCORG_exclude_taxonomies[]']").parent().parent().show(); */
	    	} else if(jQuery(this).val() == "specific_page_post"){
	    		jQuery("select[name='SCORG_selected_page_post[]']").parent().parent().show();
	    		jQuery("select[name='SCORG_specific_post_type[]']").parent().parent().hide();
	    		jQuery("select[name='SCORG_specific_taxonomy[]']").parent().parent().hide();
				jQuery("input[name='SCORG_custom']").parent().parent().hide();
				/* jQuery("select[name='SCORG_exclude_page_post[]']").parent().parent().show();
				jQuery("select[name='SCORG_exclude_terms[]']").parent().parent().hide();
				jQuery("select[name='SCORG_exclude_taxonomies[]']").parent().parent().hide(); */
	    	} else if(jQuery(this).val() == "specific_post_type"){
	    		jQuery("select[name='SCORG_selected_page_post[]']").parent().parent().hide();
	    		jQuery("select[name='SCORG_specific_post_type[]']").parent().parent().show();
	    		jQuery("select[name='SCORG_specific_taxonomy[]']").parent().parent().hide();
				jQuery("input[name='SCORG_custom']").parent().parent().hide();
				/* jQuery("select[name='SCORG_exclude_page_post[]']").parent().parent().show();
				jQuery("select[name='SCORG_exclude_terms[]']").parent().parent().hide();
				jQuery("select[name='SCORG_exclude_taxonomies[]']").parent().parent().hide(); */
	    	} else if(jQuery(this).val() == "custom"){
	    		jQuery("select[name='SCORG_selected_page_post[]']").parent().parent().hide();
	    		jQuery("select[name='SCORG_specific_post_type[]']").parent().parent().hide();
	    		jQuery("select[name='SCORG_specific_taxonomy[]']").parent().parent().hide();
	    		jQuery("input[name='SCORG_custom']").parent().parent().show();
				/* jQuery("select[name='SCORG_exclude_page_post[]']").parent().parent().show();
				jQuery("select[name='SCORG_exclude_terms[]']").parent().parent().hide();
				jQuery("select[name='SCORG_exclude_taxonomies[]']").parent().parent().hide(); */
	    	} else {
	    		jQuery("select[name='SCORG_selected_page_post[]']").parent().parent().hide();
	    		jQuery("select[name='SCORG_specific_post_type[]']").parent().parent().hide();
	    		jQuery("select[name='SCORG_specific_taxonomy[]']").parent().parent().show();
				jQuery("input[name='SCORG_custom']").parent().parent().hide();
				/* jQuery("select[name='SCORG_exclude_page_post[]']").parent().parent().hide();
				jQuery("select[name='SCORG_exclude_terms[]']").parent().parent().show();
				jQuery("select[name='SCORG_exclude_taxonomies[]']").parent().parent().hide(); */
	    	}
			show_hide_exclude_text();
		});

		/* show exclude options */
		jQuery("#SCORG_exclude_script").change(function(e){
			e.preventDefault();
			show_hide_exclude_options();
		});
		/* show exclude options */

		/* scripts manager show/hide */
		jQuery("input[name='SCORG_scripts_manager']").click(function(){
			var SCORG_scripts_manager = jQuery("input[name='SCORG_scripts_manager']:checked").val();
			if(SCORG_scripts_manager == "show"){
				jQuery("select[name='SCORG_dequeue_scripts[]']").parent().parent().show();
				jQuery("select[name='SCORG_enqueue_scripts[]']").parent().parent().show();
			} else {
				jQuery("select[name='SCORG_dequeue_scripts[]']").parent().parent().hide();
				jQuery("select[name='SCORG_enqueue_scripts[]']").parent().parent().hide();
			}
		});

		/* scss partials manager show/hide */
		jQuery("input[name='SCORG_scss_partial_manager']").click(function(){
			var SCORG_scss_partial_manager = jQuery("input[name='SCORG_scss_partial_manager']:checked").val();
			if(SCORG_scss_partial_manager == "show"){
				jQuery("select[name='SCORG_partials[]']").parent().parent().show();
			} else {
				jQuery("select[name='SCORG_partials[]']").parent().parent().hide();
			}
		});

		/* script schedule show/hide */
		jQuery("input[name='SCORG_script_schedule']").click(function(){
			if(jQuery(this).val() == "daily"){
				jQuery("input[name='SCORG_specific_date']").parent().parent().hide();
				jQuery("input[name='SCORG_specific_date_from']").parent().parent().hide();
				jQuery("input[name='SCORG_specific_date_to']").parent().parent().hide();
				jQuery("select[name='SCORG_days[]']").parent().parent().hide();
			} else if (jQuery(this).val() == "specific_days") {
				jQuery("input[name='SCORG_specific_date']").parent().parent().hide();
				jQuery("select[name='SCORG_days[]']").parent().parent().show();
				jQuery("input[name='SCORG_specific_date_from']").parent().parent().hide();
				jQuery("input[name='SCORG_specific_date_to']").parent().parent().hide();
			} else if (jQuery(this).val() == "specific_date") {
				jQuery("input[name='SCORG_specific_date']").parent().parent().show();
				jQuery("select[name='SCORG_days[]']").parent().parent().hide();
				jQuery("input[name='SCORG_specific_date_from']").parent().parent().hide();
				jQuery("input[name='SCORG_specific_date_to']").parent().parent().hide();
			} else {
				jQuery("input[name='SCORG_specific_date']").parent().parent().hide();
				jQuery("select[name='SCORG_days[]']").parent().parent().hide();
				jQuery("input[name='SCORG_specific_date_from']").parent().parent().show();
				jQuery("input[name='SCORG_specific_date_to']").parent().parent().show();
			}
		});

		/* script time show/hide */
		jQuery("input[name='SCORG_script_time']").click(function(){
			if(jQuery(this).val() == "all_day"){
				jQuery("input[name='SCORG_specific_time_start']").parent().parent().hide();
				jQuery("input[name='SCORG_specific_time_end']").parent().parent().hide();
			} else {
				jQuery("input[name='SCORG_specific_time_start']").parent().parent().show();
				jQuery("input[name='SCORG_specific_time_end']").parent().parent().show();
			}
		});

		/* script type show/hide */
		jQuery("input[name='SCORG_script_type[]']").click(function(){
			SCORG_trigger_location();
		});

		/* trigger location click */
		jQuery("input[name='SCORG_trigger_location']").click(function(){
			SCORG_trigger_location();
		});
	}
    jQuery(document).on("click", ".radio-box label span", function () {
        var e = jQuery(this).parents("label");
        jQuery(e).find("input").trigger("click");
    });
    jQuery(document).on("change", ".dp-switch-save #SCORG_enable_script, #SCORG_only_frontend", function(){
        edit_script_save_ajax();
    });
    jQuery(document).on("click", ".columns-direction", function(){
        var currObj = jQuery(this);
        edit_script_save_ajax(currObj);
    });
    jQuery(".columns-direction").click(function(){
    	gridToggles();
    });
});