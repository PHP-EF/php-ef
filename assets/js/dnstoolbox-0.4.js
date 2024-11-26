$(document).ready(function(){
    $("#domain").keyup(function(event){
	event.preventDefault();
    });
});
window.onload = function() {
    //Counts the number of requests in this session
    var requestNum = 0;
    //Choose the correct script to run based on dropdown selection
    if ($('#domain').val() != "") {
        $("#submit").click();
    }
}

function requestSource(source){
    switch (source){
    case 'google':
        return "Google DNS";
    case 'cloudflare':
        return "Cloudflare DNS";
    }
}

function showLoading() {
  document.querySelector('.loading-icon').style.display = 'block';
  document.querySelector('.loading-div').style.display = 'block';
}
    
function hideLoading() {
  document.querySelector('.loading-icon').style.display = 'none';
  document.querySelector('.loading-div').style.display = 'none';
}
    
$("#submit").on('click', function(event) {
    event.preventDefault();
    showLoading();
    var type = document.getElementById("file").value;
    if (document.getElementById("domain").value.endsWith(".") || type == "reverse") {
      var domain = document.getElementById("domain").value;
    } else {
      var domain = document.getElementById("domain").value+".";
    }
    returnDnsDetails(domain, type, document.getElementById("port").value, $('#source').val());
});

//Get DNS Details
function returnDnsDetails(domain, callType, port, source) {
    $('#txtHint, .info').html('');
  
    $.get( "/api?f=DNSToolbox&domain=" + domain + "&request=" + callType + "&port=" + port + "&source=" + source).done(function( data ) {
        if (data['Status'] == 'Error') {
            toast(data['Status'],"",data['Message'],"danger","30000");
            hideLoading();
        } else {
            const columns = [];
            switch(callType) {
                case 'port':
                    columns.push({
                        field: 'hostname',
                        title: 'Hostname',
                        sortable: true
                    },{
                        field: 'port',
                        title: 'Port',
                        sortable: true
                    },
                    {
                        field: 'result',
                        title: 'Status',
                        sortable: true
                    });
                    break;
                case 'reverse':
                    columns.push({
                        field: 'ip',
                        title: 'IP Address',
                        sortable: true
                    },{
                        field: 'hostname',
                        title: 'Hostname',
                        sortable: true
                    });
                    break;
                default:
                    columns.push({
                        field: 'hostname',
                        title: 'Hostname',
                        sortable: true
                    },{
                        field: 'type',
                        title: 'Type',
                        sortable: true
                    },
                    {
                        field: 'TTL',
                        title: 'TTL',
                        sortable: true
                    },
                    {
                        field: 'class',
                        title: 'Class',
                        sortable: true
                    });
                    break;
            }


            if ((["a","aaaa","all"]).includes(callType)) {
                columns.push({
                    field: 'IPAddress',
                    title: 'IP Address',
                    sortable: true
                })
            }
            if ((["mx","txt","dmarc","all","nameserver","soa","cname"]).includes(callType)) {
                columns.push({
                    field: 'data',
                    title: 'Data',
                    sortable: true
                })
            }

            $('#dnsResponseTable').bootstrapTable('destroy');
            $('#dnsResponseTable').bootstrapTable({
                data: data,
                sortable: true,
                pagination: true,
                search: true,
                showExport: true,
                exportTypes: ['json', 'xml', 'csv', 'txt', 'excel', 'sql'],
                showColumns: true,
                columns: columns
            });
            hideLoading();
            return false;
        }
    }).fail(function() {
        toast("API Error","","Unknown API Error","danger","30000");
    }).always(function() {
        hideLoading();
    });
}

function cleanString(data) {
    return data
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function showAdditionalFields() {
  var file = $("#file");
  var port = $("#port-container");
  var source = document.getElementById("source");
  if(file.val() === 'port') {
    port.css('visibility','visible');
    source.disabled = true;
  } else if (file.val() === 'reverseLookup') {
    port.css('visibility','hidden');
    $('#port').val('');
    source.disabled = true;
  } else {
    port.css('visibility','hidden');
    $('#port').val('');
    source.disabled = false;
  }
}

$('#copyLink').on('click',function(elem) {
  elem.preventDefault();
  var domain = $('#domain').val();
  var file = $('#file').val();
  var source = $('#source').val();

  var text = "https://ib-sa-report.azurewebsites.net/pages/tools/dnstoolbox.php?domain="+domain+"&type="+file+"&location="+source;

   // Copy the text inside the text field
  navigator.clipboard.writeText(text);

  // Alert the copied text
  toast("Info","","Copied link to clipboard","primary");
});
