
<div class="wrap">
    <h1>Coder Settings</h1>

    <form id="coder-form">
        <label for="files-list">Files:</label>
        <select id="files-list" name="files-list"></select>
        <br>
        <button id="coder-view-btn" class="button">View</button>
    </form>

    <div id="coder-view-content"></div>
</div>

<script>
    jQuery(document).ready(function($) {
        // AJAX request to fetch the list of files
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: { action: 'coder_fetch_files_list' },
            success: function(response) {
                if (response.success) {
                    var files = response.data;
                    var select = $('#files-list');

                    // Populate the select element with the list of files
                    $.each(files, function(index, file) {
                        select.append($('<option>', {
                            value: file,
                            text: file
                        }));
                    });
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('Oops! Something went wrong.');
            }
        });

        // AJAX request to fetch the file content
        $('#coder-view-btn').click(function(e) {
            e.preventDefault();

            var file = $('#files-list').val();

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: { action: 'coder_fetch_file_content', file: file },
                success: function(response) {
                    if (response.success) {
                        var content = response.data;
                        $('#coder-view-content').text(content);
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
</script>

