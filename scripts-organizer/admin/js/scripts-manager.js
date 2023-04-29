var mediaUploader, add_file_url, add_script_file;
// copy scripts manager enqueue code
function copyToClipboardSCORG(element) {
    var sel, range;
    var el = jQuery(element)[0];
    if (window.getSelection && document.createRange) { //Browser compatibility
      sel = window.getSelection();
      if(sel.toString() == ''){ //no text selection
         window.setTimeout(function(){
            range = document.createRange(); //range object
            range.selectNodeContents(el); //sets Range
            sel.removeAllRanges(); //remove all ranges from selection
            sel.addRange(range);//add Range to a Selection.
        },1);
      }
    }else if (document.selection) { //older ie
        sel = document.selection.createRange();
        if(sel.text == ''){ //no text selection
            range = document.body.createTextRange();//Creates TextRange object
            range.moveToElementText(el);//sets Range
            range.select(); //make selection.
        }
    }
    var $temp = jQuery("<input>");
    jQuery("body").append($temp);
    $temp.val(jQuery(element).text()).select();
    document.execCommand("copy");
    $temp.remove();
}

function initiate_sortable(){
    if (jQuery( "#reorder-code-blocks" ).hasClass('ui-sortable')){
        // Remove the sortable feature to prevent bad state caused by unbinding all
        jQuery( "#reorder-code-blocks" ).sortable('destroy');
        // Unbind all event handlers!
        jQuery( "#reorder-code-blocks" ).unbind();
    }

    // Initialization of the sortable feature
    jQuery( "#reorder-code-blocks" ).sortable({
        handle: '.move',
        change: function(event, ui) {
        ui.placeholder.css({visibility: 'visible', border : '1px solid yellow'});
        },
        placeholder: "highlight",
        start: function (event, ui) {
                ui.item.toggleClass("highlight");
        },
        stop: function (event, ui) {
                ui.item.toggleClass("highlight");
        }
    });
}

jQuery(document).ready(function($){
    // scripts manager js
    $( document ).on("click", ".script-info__edit", function() {
        $( this ).parent().parent().toggleClass( "active" );
    });

    $(document).on("click", ".script-file-upload", function (e) {
        e.preventDefault();
        $(this);
        var t = $(this).parents(".file-upload");
        (add_script_file = wp.media.frames.file_frame = wp.media({ title: "Choose Images", button: { text: "Choose Images" }, multiple: !1 })).on("open", function () {
            var e = add_script_file.state().get("selection");
            (ids = $(t).find('.script-file').val().split(",")),
                ids.forEach(function (t) {
                    (attachment = wp.media.attachment(t)), attachment.fetch(), e.add(attachment ? [attachment] : []);
                });
        }),
            add_script_file.on("select", function () {
                var e = add_script_file.state().get("selection").toJSON();
                $(t).find('.script-file').val(""),
                    e.forEach(function (e) {
                        $(t).find('.script-file').val(e.url);
                    });
            }),
            add_script_file.open();
    }),
    $(document).on("click", ".swk-new-script", function(e){
        e.preventDefault();
        $(".scripts-rows ul").append("<li><span class='move'></span>"+$(".script-copy").html()+"</li>");
        $(".scripts-manager #Scripts").show();
        initiate_sortable();
    }),
    $(document).on("click", ".swk-save-scripts", function(e){
        e.preventDefault();
        $("#save-scripts").trigger("click");
    }),
    $(document).on("click", ".script-frontend-only", function () {
        var e = $(this).parents(".of-input");
        $(this).is(":checked") ? $(e).find("input[name='script_frontend_only[]']").val("1") : $(e).find("input[name='script_frontend_only[]']").val("0");
    }),
    $(document).on("change", "select[name='script_type[]']", function(){
        var currObj_parent = $(this).parents(".script-row");
        var script_name = $(currObj_parent).find("input[name='script_name[]']").val().toLowerCase().replace(/ /g, '-');
        if($(this).val() == "js"){
            $(currObj_parent).find(".script-type-wrap > svg").removeClass("css-icon--svg");
            $(currObj_parent).find(".script-type-wrap > svg").removeClass("js-icon--svg");
            $(currObj_parent).find(".script-type-wrap > svg").addClass("js-icon--svg");
            $(currObj_parent).find(".script-type-wrap > svg > use").attr("href", "#js-icon");
            $(currObj_parent).find(".reg-enq").html("wp_enqueue_script('"+script_name+"');");
        } else {
            $(currObj_parent).find(".script-type-wrap > svg").removeClass("css-icon--svg");
            $(currObj_parent).find(".script-type-wrap > svg").removeClass("js-icon--svg");
            $(currObj_parent).find(".script-type-wrap > svg").addClass("css-icon--svg");
            $(currObj_parent).find(".script-type-wrap > svg > use").attr("href", "#css-icon");
            $(currObj_parent).find(".reg-enq").html("wp_enqueue_style('"+script_name+"');");
        }
    }),
    $(document).on("keyup", "input[name='script_name[]']", function(){
        var currObj_parent = $(this).parents(".script-row");
        var script_name = $(this).val().toLowerCase().replace(/ /g, '-');
        var script_type = $(currObj_parent).find("select[name='script_type[]']").val();
        if(script_type == "js"){
            $(currObj_parent).find(".reg-enq").html("wp_enqueue_script('"+script_name+"');");
        } else {
            $(currObj_parent).find(".reg-enq").html("wp_enqueue_style('"+script_name+"');");
        }
    });
    $(document).on("click", ".swk-copy-code", function(e){
        var currObj_main_parent = $(this).parents(".script-row.edit-style");
        var currObj_parent = $(this).parents(".script-row__info");
        $(currObj_main_parent).find(".reg-shortcode").trigger("click"); 
        $(currObj_parent).find("span").html("Copied");
         setTimeout(function(){
             $(currObj_parent).find("span").html("Click to copy");
         }, 3000);
    });
    $(document).on("click", ".reg-shortcode", function(){
        var currObj = $(this);
        copyToClipboardSCORG($(currObj).find('.reg-enq'));
        $(currObj).find("span").html("Copied");
        setTimeout(function(){
            $(currObj).find("span").html("Click to copy");
        }, 3000);
    });
    $(document).on("click", ".swk-delete-script", function(e){
        e.preventDefault();
        var currObj_parent = $(this).parents("li");
        var curr_id = $(this).data("id");
        if(confirm("Are you sure you want to delete this script?")){
            if(curr_id != ""){
                $.ajax({
                    url: SCORG_ajax.ajaxurl,
                    type: "post",
					async: false,
                    data: { action: "deleteSCORGScript", script_id: curr_id, verify_nonce: SCORG_ajax.SCORG_nonce, },
                    success: function (e) {
                        $(currObj_parent).remove();
                        SCORG_hide_save_button();
                        initiate_sortable();
                    },
                });
            } else {
                $(currObj_parent).remove();
                SCORG_hide_save_button();
                initiate_sortable();
            }
        }
    });
    /* reorder */
    initiate_sortable();
    /* reorder */
});