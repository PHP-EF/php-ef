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

function saveAPIKey(key) {
  $.post( "../api?function=crypt", {key: key}).done(function( data, status ) {
      setCookie('crypt',data,7);
      checkAPIKey();
      toast("Success","","Saved API Key.","success","30000");
  }).fail(function( data, status ) {
      toast("API Error","","Unable to save API Key.","danger","30000");
  })
}

function checkAPIKey() {
  if (getCookie('crypt')) {
    $('#APIKey').prop('disabled',true).attr('placeholder','== Using Saved API Key ==').val('');
    $("#saveBtn").removeClass('fa-save').addClass('fa-trash')
    checkInput('saved');
  } else {
    $('#APIKey').prop('disabled',false).attr('placeholder','Enter API Key');
    $("#saveBtn").removeClass('fa-trash').addClass('fa-save')
  }
}

function removeAPIKey() {
  setCookie('crypt',null,-1);
  checkAPIKey();
  toast("Success","","Removed API Key.","success","30000");
}

function checkInput(text) {
  if (text) {
      $("#saveBtn").addClass("show");
  } else {
      $("#saveBtn").removeClass("show");
  }
}

// END

document.addEventListener('DOMContentLoaded', function() {
  const maxDaysApart = 30;
  const today = new Date();
  const maxPastDate = new Date(today);
  maxPastDate.setDate(today.getDate() - maxDaysApart);

  flatpickr("#startDate", {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    minDate: maxPastDate,
    maxDate: today,
    onChange: function(selectedDates, dateStr, instance) {
      const endDatePicker = document.getElementById('endDate')._flatpickr;
      const maxEndDate = new Date(selectedDates[0]);
      maxEndDate.setDate(maxEndDate.getDate() + maxDaysApart);
      endDatePicker.set('minDate', dateStr);
      endDatePicker.set('maxDate', maxEndDate > today ? today : maxEndDate);
    }
  });

  flatpickr("#endDate", {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    minDate: maxPastDate,
    maxDate: today,
    onChange: function(selectedDates, dateStr, instance) {
      const startDatePicker = document.getElementById('startDate')._flatpickr;
      const minStartDate = new Date(selectedDates[0]);
      minStartDate.setDate(minStartDate.getDate() - maxDaysApart);
      startDatePicker.set('maxDate', dateStr);
      startDatePicker.set('minDate', minStartDate < maxPastDate ? maxPastDate : minStartDate);
    }
  });
});

$( document ).ready(function() {
  applyTheme();
  checkAPIKey();
  $('#saveBtn').click(function(){
    if ($('#saveBtn').hasClass('fa-save')) {
      saveAPIKey($('#APIKey').val());
    } else if ($('#saveBtn').hasClass('fa-trash')) {
      removeAPIKey();
    }
  });
});