var maxDaysApart = 31;
var today = new Date();
var maxPastDate = new Date(today);
maxPastDate.setDate(today.getDate() - 31);

flatpickr("#assessmentStartAndEndDate", {
  mode: "range",
  minDate: maxPastDate,
  maxDate: today,
  enableTime: true,
  dateFormat: "Y-m-d H:i",
  onChange: function(selectedDates, dateStr, instance) {
    if (selectedDates.length === 1) {
      const startDate = selectedDates[0];
      const maxEndDate = new Date(startDate.getTime() + 31 * 24 * 60 * 60 * 1000); // 31 days later
      const today = new Date();
      instance.set('maxDate', maxEndDate > today ? today : maxEndDate);
    }
    if (selectedDates.length === 2) {
      const startDate = selectedDates[0];
      const endDate = selectedDates[1];
      const diffInDays = (endDate - startDate) / (1000 * 60 * 60 * 24);
      if (diffInDays > 31) {
        toast("Error","","The start and end date cannot exceed 31 days.","warning");
        instance.clear();
      }
    }
  }
});

flatpickr("#reportingStartAndEndDate", {
  mode: "range",
  maxDate: today,
  enableTime: true,
  dateFormat: "Y-m-d H:i"
});

function saveAPIKey(key) {
    queryAPI("POST", "/api/auth/crypt", {key: key}).done(function( data, status ) {
        if (data.result == 'Success') {
            setCookie('crypt',data.data,7);
            checkAPIKey();
            toast("Success","","Saved API Key.","success","30000");
        } else {
            toast(data.result,"","Unable to save API Key.","danger","30000");
        }
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
        $("#saveBtn").addClass("saveBtnShow");
    } else {
        $("#saveBtn").removeClass("saveBtnShow");
    }
}

checkAPIKey();
$('#saveBtn').click(function(){
  if ($('#saveBtn').hasClass('fa-save')) {
    saveAPIKey($('#APIKey').val());
  } else if ($('#saveBtn').hasClass('fa-trash')) {
    removeAPIKey();
  }
});