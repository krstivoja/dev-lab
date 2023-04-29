jQuery(document).ready(function($){
    /* enable/disable script */
	if($('.enable_script').length != 0) {
		var script_action;
		var post_id;
		$(".rwmb-switch-status").click(function(){
			post_id = $(this).parents("tr").attr("id").split("-")[1];
			if($(this).parent().find("input").prop("checked") == true){
				script_action = 0;
				$(this).parents("tr").removeClass("script-active");
			} else {
				script_action = 1;
				$(this).parents("tr").addClass("script-active");
			}

			$.ajax({
                url: SCORG_ajax.ajaxurl,
                type: 'post',
                data: {
                    action: 'saveAction',
                    id: post_id,
                    script_action: script_action,
					verify_nonce: SCORG_ajax.SCORG_nonce,
                },
				async: true,
                success: function( data ) {
                	//console.log(data);
                }
            });
		});
	}
	/* enable/disable script */
});