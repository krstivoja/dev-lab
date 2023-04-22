var toggleButton = document.querySelector('.toggle-button');
var currentUrl = window.location.href;
toggleButton.addEventListener('click', function() {
  var xhr = new XMLHttpRequest();
  var body = document.querySelector('body');
  var darkMode = body.classList.contains('dark') ? 0 : 1;
  var url = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";

  
  xhr.open('POST', url);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        var response = JSON.parse(xhr.responseText);
        if (response.success) {
          if (currentUrl.indexOf('theme-toggle') !== -1) { // check if current page is the plugin settings page
            body.classList.toggle('dark');
          }
        }
      } else {
        console.error('Error: ' + xhr.status);
      }
    }
  };
  var data = 'action=themetoggle_update_settings' + '&dark_mode=' + darkMode;
  xhr.send(data);
});
