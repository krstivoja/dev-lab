var oxy_stylesheet;
document.documentElement.setAttribute("data-theme", SCORG_oxy_ajax.vs_theme);
localStorage.setItem("theme", SCORG_oxy_ajax.vs_theme);
jQuery(document).ready(function($){
    // Get the theme toggle input
	const themeToggle = document.querySelector(
		'#theme-settings input[type="checkbox"]'
	);

	// Get the current theme from local storage
	const currentTheme = SCORG_oxy_ajax.vs_theme;

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

    var editor_font_size = SCORG_oxy_ajax.vs_font_size;
    Mousetrap.bind(['ctrl+s', 'command+s'], function(e) {
        if (e.preventDefault) {
            e.preventDefault();
        } else {
            // internet explorer
            e.returnValue = false;
        }
        oxy_st_save();
    });

    /* monaco editor */
    if($("#editor").length != 0){
        require.config({ paths: { vs: SCORG_oxy_ajax.scorg_vs } });
        emmetMonaco.emmetCSS(monaco, ['scss', 'css']);
        require(['vs/editor/editor.main'], function () {
            oxy_stylesheet = monaco.editor.create(document.getElementById('editor'), {
                value: $("#editor-code").val(),
                language: 'css',
                lineNumbers: true,
                roundedSelection: false,
                scrollBeyondLastLine: false,
                readOnly: false,
                glyphMargin: false,
                vertical: 'auto',
                horizontal: 'auto',
                verticalScrollbarSize: 10,
                horizontalScrollbarSize: 10,
                theme: 'vs-'+SCORG_oxy_ajax.vs_theme,
                wordWrap: 'wordWrapColumn',
                minimap: { enabled: false },
                wordWrapColumn: 120,
                wordWrapMinified: true,
                wrappingIndent: "indent",
                automaticLayout: true,
                lineHeight: 19,
                fontSize: editor_font_size,
                "autoIndent": true,
				"formatOnPaste": true,
				"formatOnType": true,
				'bracketPairColorization.enabled': true,
            });
            $("#editor").height(200);
            oxy_stylesheet.onKeyUp(() => {
                $("#editor-code").val(oxy_stylesheet.getValue());
            });
            oxy_stylesheet.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_S, function() {
                oxy_st_save();
            });
        });

        // oxygen color picker
        $(".insert-color").click(function(){
            var oxy_color_id = $(this).data("id");
            oxy_stylesheet.trigger('keyboard', 'type', {text: oxy_color_id.replace("oxycolor", "color")});
            $("#editor-code").val(oxy_stylesheet.getValue());
        });

        monaco.editor.setModelLanguage(oxy_stylesheet.getModel(), 'css');
    }
    /* monaco editor */

    /* save */
    $("#save-st").click(function(e){
        e.preventDefault();
        oxy_st_save();
    });
    /* save */
});

// Function that will switch the theme based on the if the theme toggle is checked or not
function switchTheme(e) {
	if (e.target.checked) {
		document.documentElement.setAttribute("data-theme", "dark");
		localStorage.setItem("theme", "dark");
        if(jQuery("#editor").length != 0){
            oxy_stylesheet.updateOptions({
                'theme': "vs-dark"
            });
        }
	} else {
		document.documentElement.setAttribute("data-theme", "light");
		localStorage.setItem("theme", "light");
        if(jQuery("#editor").length != 0){
            oxy_stylesheet.updateOptions({
                'theme': "vs-light"
            });
        }
	}
    save_st_options();
}

function save_st_options(){
	var theme = document.documentElement.getAttribute("data-theme");
    if(theme == "dark"){
        theme = "yes";
    }

    jQuery.ajax({
        url: SCORG_oxy_ajax.ajaxurl,
        type: 'post',
        data: {
            action: 'saveOxyStylesheetOptions',
            scorg_darkmode: theme,
            verify_nonce: SCORG_oxy_ajax.oxy_st_nonce,
        },
        async: false,
        success: function( data ) {
            //console.log(data);
        }
    });
}

function oxy_st_save(){
    $ = jQuery;
    $("#saving-st").show();
    $.ajax({
        url: SCORG_oxy_ajax.ajaxurl,
        type: "post",
        data: { 
            action: "saveOxyST" ,
            verify_nonce: SCORG_oxy_ajax.oxy_st_nonce,
            id: $("#save-st").data("stid"),
            code: btoa(encodeURIComponent(oxy_stylesheet.getValue())),
        },
        dataType: 'json',
        success: function (data) {
            if(data.status == "success"){
                $("#saving-st").hide();
                $("#success-message").html(data.msg).show();
                setTimeout(function(){
                    $("#success-message").hide();
                }, 3000);
            }
        },
    });
}