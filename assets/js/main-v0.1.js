function getCookie(name) {
    var cookieArr = document.cookie.split(";");
    for(var i = 0; i < cookieArr.length; i++) {
        var cookiePair = cookieArr[i].split("=");
        if(name == cookiePair[0].trim()) {
            return decodeURIComponent(cookiePair[1]);
        }
    }
    return null;
}
function setCookie(cName, cValue, expDays) {
    let date = new Date();
    date.setTime(date.getTime() + (expDays * 24 * 60 * 60 * 1000));
    const expires = "expires=" + date.toUTCString();
    document.cookie = cName + "=" + cValue + "; " + expires + "; path=/";
}

function loadiFrame(element = null) {
    if (element != null) {
      var hashsplit = element.split('#page=');
      if (hashsplit[1].startsWith('prx')) {
        var prxsplit = hashsplit[1].split('prx');
        window.parent.document.getElementById('mainFrame').src = '/pages/'+prxsplit[1];
        $('#' + prxsplit[1]).parent().addClass('active');
      } else {
        window.parent.document.getElementById('mainFrame').src = '/pages/'+hashsplit[1]+".php";
        $('#' + hashsplit[1]).parent().addClass('active');
      }
    } else if (window.parent.location.hash) {
      var hashsplit = window.parent.location.hash.split('#page=');
      if (hashsplit[1].startsWith('prx')) {
        var prxsplit = hashsplit[1].split('prx');
        window.parent.document.getElementById('mainFrame').src = '/pages/'+prxsplit[1];
        $('#' + prxsplit[1]).parent().addClass('active');
      } else {
        window.parent.document.getElementById('mainFrame').src = '/pages/'+hashsplit[1]+".php";
        $('#' + hashsplit[1]).parent().addClass('active');
      }
    }
}

function toggleTheme() {
  var cookie = getCookie('theme')
  if (cookie == "dark") {
      setCookie('theme','light',365);
      applyTheme();
      location.reload();
  } else {
      setCookie('theme','dark',365);
      applyTheme();
      location.reload();
  }
}

function applyTheme() {
  var cookie = getCookie('theme');
  if (cookie == "dark") {
      $('.sidebar').attr('data-background-color','black');
      $('.main-panel-theme').attr('data-background-color','black');
      $('#themeToggle').removeClass('fa-toggle-on').addClass('fa-toggle-off');
  } else {
      $('.sidebar').attr('data-background-color','white');
      $('.main-panel-theme').attr('data-background-color','white');
      $('#themeToggle').removeClass('fa-toggle-off').addClass('fa-toggle-on');
  }
}

function toast(title,note,body,theme,delay = "8000") {
  $('#toastContainer').append(`
      <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="`+delay+`">
        <div class="toast-header">
          <img class="bg-`+theme+` p-2 rounded-2">&nbsp;
          <strong class="me-auto">`+title+`</strong>
          <small class="text-muted">`+note+`</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          `+body+`
        </div>
      </div>
  `);
  $('.toast').toast('show').on('hidden.bs.toast', function (elem) {
    $(elem.target).remove();
  })
};

// END

$( document ).ready(function() {
  applyTheme();
});