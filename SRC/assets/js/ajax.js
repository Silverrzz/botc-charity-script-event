window.active_ajax_count = 0;

function ajax(action, payload, callback) {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '/ajax', true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4) {
      //check if response is json or html

      window.active_ajax_count--;

      try {
        let response = JSON.parse(xhr.responseText);
        if (response.status == 'success') {
            if (callback != null) {
                return callback(response);
            }
        } else {
            error(response.data);
        }
      } catch (e) {
        
        callback.innerHTML = xhr.responseText;
        return;
      }
      
    }
  };
  window.active_ajax_count++;
  xhr.send(JSON.stringify({ action: action, payload: payload }));
}