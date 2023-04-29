jQuery(document).ready(function($) {
  if ($(".tasks-list").length) {
      var sortables = [];

      $(".tasks-list").each(function() {
          var sortable = new Sortable(this, {
              animation: 150,
              group: 'shared',
              onUpdate: function(evt) {
                  var tasksOrder = sortable.toArray();
                  $.ajax({
                      url: ajaxObject.ajaxUrl,
                      method: 'POST',
                      data: {
                          action: 'update_tasks_order',
                          tasks_order: tasksOrder
                      },
                      success: function(response) {
                          console.log(response);
                      }
                  });
              },
              onAdd: function(evt) {
                  var task_id = $(evt.item).data('id');
                  var term_id = $(evt.to).data('term-id');
                  $.ajax({
                      url: ajaxObject.ajaxUrl,
                      method: 'POST',
                      data: {
                          action: 'update_tasks_taxonomy',
                          task_id: task_id,
                          term_id: term_id // <-- Updated this line
                      },
                      success: function(response) {
                          console.log(response);
                      }
                  });
              }
          });
          sortables.push(sortable);
      });
  }
});
