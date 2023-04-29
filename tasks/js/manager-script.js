jQuery(document).ready(function($) {
    // Select all the tasks groups and make them sortable
    $(".tasks-group .tasks-list").each(function() {
      new Sortable(this, {
        group: "tasks",
        animation: 150,
        onEnd: function(evt) {
          // The task has been moved to a new position
          var post_id = $(evt.item).data("post_id");
          var term_slug = $(evt.to).data("term_slug");
          var new_index = evt.newIndex;
  
          // Make an AJAX call to update the post's terms
          wp.apiRequest({
            method: "PUT",
            path: "/wp/v2/tasks/" + post_id,
            data: {
              progress: term_slug
            }
          });
        }
      });
    });
  });
  