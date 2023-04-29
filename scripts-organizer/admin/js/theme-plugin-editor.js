// Script Manager
// tabs
document.documentElement.setAttribute("data-theme", SCORG_them_plugin.vs_theme);
localStorage.setItem("theme", SCORG_them_plugin.vs_theme);
jQuery(document).ready(function($){
    require.config({ paths: { vs: SCORG_them_plugin.scorg_vs } });
    emmetMonaco.emmetHTML();
    emmetMonaco.emmetCSS(monaco, ['css']);
    emmetMonaco.emmetJSX();

    // Get the main content element.
    var contentElement = document.getElementById('newcontent');

    // Create a new div element to hold the Monaco editor.
    var monacoEditorElement = document.createElement('div');

    // Set the new div element properties.
    monacoEditorElement.id    = 'monaco-editor';
    monacoEditorElement.style = 'height: 500px;';

    // Insert the new Monaco editor element after the content element.
    contentElement.parentNode.insertBefore(monacoEditorElement, contentElement.nextSibling);

    var fileName      = document.getElementsByName("file")[0].value;
    var fileNameSplit = fileName.split('.');
    var fileExtension = fileNameSplit[fileNameSplit.length - 1];
    if(fileExtension == "js"){
        fileExtension = "javascript";
    }

    // Initialize the Monaco element.
    var theme_plugin_editor = monaco.editor.create(monacoEditorElement, {
        value: contentElement.value,
        language: fileExtension,
        lineNumbers: true,
        roundedSelection: false,
        scrollBeyondLastLine: false,
        readOnly: false,
        glyphMargin: false,
        vertical: 'auto',
        horizontal: 'auto',
        verticalScrollbarSize: 10,
        horizontalScrollbarSize: 10,
        minimap: { enabled: false },
        theme: 'vs-'+SCORG_them_plugin.vs_theme,
        wordWrap: 'wordWrapColumn',
        wordWrapColumn: 120,
        wordWrapMinified: true,
        wrappingIndent: "indent",
        roundedSelection: false,
        scrollBeyondLastLine: false,
        automaticLayout: true,
        lineHeight: 19,
        fontSize: SCORG_them_plugin.editor_font_size,
        "autoIndent": true,
        "formatOnPaste": true,
        "formatOnType": true,
        'bracketPairColorization.enabled': true,
    });

    theme_plugin_editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_S, function() {
        $("#submit").trigger("click");
    });

	// When the Monaco editor value changes, also set the value of the default content editor.
	theme_plugin_editor.onKeyUp(() => {
		var value = theme_plugin_editor.getValue();

		document.getElementById('newcontent').value = value;
	});

    // Always hide the content element by pushing it off screen.
    contentElement.style.position = 'fixed';
    contentElement.style.left     = '-9999px';

    // save changes on keyup
    theme_plugin_editor.onKeyUp(() => {
        document.querySelector('.CodeMirror').CodeMirror.setValue(theme_plugin_editor.getValue());
    });

    // mousetrap
    Mousetrap.bind(['ctrl+s', 'command+s'], function(e) {
        if (e.preventDefault) {
            e.preventDefault();
        } else {
            // internet explorer
            e.returnValue = false;
        }
        $("#submit").trigger("click");
    });
});