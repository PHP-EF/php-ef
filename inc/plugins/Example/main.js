function saveAPIKey(key) {
    queryAPI("POST", "/api/v2/auth/crypt", {key: key}).done(function( data, status ) {
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