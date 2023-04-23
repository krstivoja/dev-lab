jQuery(document).ready(function($) {
    // AJAX request to fetch the list of files
    $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: { action: 'coder_fetch_files_list' },
        success: function(response) {
            if (response.success) {
                var files = response.data;
                var list = $('#coder-files-list');

                // Populate the list element with the list of files
                $.each(files, function(index, file) {
                    var item = $('<li>', {
                        text: file
                    }).appendTo(list);

                    // Add click event listener to the file list item
                    item.click(function() {
                        // Remove active class from all list items
                        list.find('li').removeClass('active');

                        // Add active class to the clicked list item
                        item.addClass('active');

                        // AJAX request to fetch the file content
                        $.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: { action: 'coder_fetch_file_content', file: file },
                            success: function(response) {
                                if (response.success) {
                                    var content = response.data;
                                    $('#coder-file-content').text(content);
                                } else {
                                    alert(response.data);
                                }
                            },
                            error: function() {
                                alert('Oops! Something went wrong.');
                            }
                        });
                    });
                });

                // Highlight the first file in the list
                list.find('li:first').addClass('active').trigger('click');
            } else {
                alert(response.data);
            }
        },
        error: function() {
            alert('Oops! Something went wrong.');
        }
    });
});
