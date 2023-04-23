jQuery(document).ready(function($) {
    var activeFile = '';

    // Function to fetch the list of files from the server
    function coder_fetch_files_list() {
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

                            // Set the active file variable
                            activeFile = file;

                            // AJAX request to fetch the file content
                            coder_fetch_file_content();
                        });
                    });

                    // Highlight the first file in the list
                    var firstItem = list.find('li:first');
                    firstItem.addClass('active');

                    // Set the active file variable to the first file
                    activeFile = firstItem.text();

                    // AJAX request to fetch the content of the first file
                    coder_fetch_file_content();

                    // Initialize the text editor
                    initEditor();
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('Oops! Something went wrong.');
            }
        });
    }


    // Function to fetch the content of a file from the server
    function coder_fetch_file_content() {
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: { action: 'coder_fetch_file_content', file: activeFile },
            success: function(response) {
                if (response.success) {
                    var content = response.data;
                    $('#coder-file-content').val(content);
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('Oops! Something went wrong.');
            }
        });
    }

    // Function to save the content of a file to the server
    function coder_save_file_content() {
        var content = $('#coder-file-content').val();
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: { action: 'coder_save_file_content', file: activeFile, content: content },
            success: function(response) {
                if (response.success) {
                    alert('File saved successfully.');
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('Oops! Something went wrong.');
            }
        });
    }

    // Function to initialize the text editor
    function initEditor() {
        $('#coder-file-content').on('input', function() {
            $('#coder-save-file').prop('disabled', false);
        });
    }

    // Register AJAX actions
    $(document).on('click', '#coder-files-list li', function() {
        coder_fetch_file_content();
    });
    $(document).on('submit', '#coder-file-form', function(event) {
        event.preventDefault();
        coder_save_file_content();
        $('#coder-save-file').prop('disabled', true);
    });
    $(document).on('coderFetchFilesList', function() {
        coder_fetch_files_list();
    });
    $(document).trigger('coderFetchFilesList');
});