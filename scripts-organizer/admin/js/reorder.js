jQuery(document).ready(function($){
  /* reorder */
  $( "#reorder-code-blocks" ).sortable({
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
  /* reorder */
});