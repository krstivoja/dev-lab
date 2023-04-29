jQuery(document).ready(function($) {
    $('.tasks-list').each(function() {
      new Sortable(this, {
        group: 'tasks',
        onEnd: function(event) {
          var item = event.item;
  
          // Get the post ID and the new term slug
          var post_id = $(item).data('post_id');
          var new_term_slug = $(item).parent('.tasks-list').siblings('.tasks-group-title').data('term_slug');
  
          // Send AJAX request to update the post term
          $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
              action: 'update_post_term',
              post_id: post_id,
              term_slug: new_term_slug,
            },
            success: function(response) {
              console.log(response);
            }
          });
        }
      });
    });
  });
  