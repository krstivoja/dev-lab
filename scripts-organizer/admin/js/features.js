jQuery(document).ready(function($){
    $(document).on("click", "#select-all", function () {
        $(this).is(":checked") ? $(".of-checkboxes").prop("checked", !0) : $(".of-checkboxes").prop("checked", !1);
    });

    /* save features */
    $(document).on("click", ".of-save", function (e) {
        e.preventDefault();
        var t = $(this),
            n = $("input[name='theme']:checked").val();
        $(t).find(".spinner").addClass("show"),
        $(t).addClass("disabled"),
        $.ajax({
            url: SCORG_ajax.ajaxurl,
            type: "post",
            async: false,
            data: { action: "saveSCORGOptions", form_data: $("#of-form .of-checkboxes").serialize(), theme_option: n, verify_nonce: SCORG_ajax.SCORG_nonce, },
            success: function (e) {
                $(t).find(".spinner").removeClass("show"), $(t).removeClass("disabled");
                location.reload();
            },
        });
    });
    /* save features */
    
    /* regenerate files */
    $(document).on("click", ".of-sync-all", function (e) {
        e.preventDefault();
        var t = $(this);
        $(t).find(".spinner").addClass("show"),
        $(t).addClass("disabled"),
        $.ajax({
            url: SCORG_ajax.ajaxurl,
            type: "post",
            async: false,
            data: { action: "regenerateFiles", verify_nonce: SCORG_ajax.SCORG_nonce, },
            success: function (e) {
                $(t).find(".spinner").removeClass("show"), $(t).removeClass("disabled");
                location.reload();
            },
        });
    });
    /* regenerate files */
});