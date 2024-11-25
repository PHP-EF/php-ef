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
        break;
    case 'cloudflare':
        return "Cloudflare DNS";
        break;
    }
}

function requestTitle(callType){
    switch(callType){
        case "txt":
            return "SPF/TXT Lookup";
            break;
        case "mx":
            return "MX Lookup";
            break;
        case "dmarc":
            return "DMARC";
            break;
        case "a":
            return "IP Lookup";
            break;
        case "all":
            return "All available DNS records";
            break;
        case "aaaa":
            return "IPV6 Lookup";
            break;
        case "whois":
            return "Who Is Lookup";
            break;
        case "hinfo":
            return "H Info Lookup";
            break;
        case "blacklist":
            return "Blacklist Lookup";
            break;
        case "port":
            return "Ports Lookup";
            break;
        case "reverseLookup":
            return "Host Lookup";
            break;
        case "nameserverLookup":
            return "Authoritative Nameserver Lookup";
            break;
    case "soa":
    return "Start of Authority Lookup";
    break;
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
    if (document.getElementById("domain").value.endsWith(".")) {
      var domain = document.getElementById("domain").value;
    } else {
      var domain = document.getElementById("domain").value+".";
    }
    returnDnsDetails(domain, document.getElementById("file").value, document.getElementById("port").value, $('#source').val());
});

//Get DNS Details
function returnDnsDetails(domain, callType, port, source) {
    $('#txtHint, .info').html('');
  
    $.get( "/api?f=DNSToolbox&domain=" + domain + "&request=" + callType + "&port=" + port + "&source=" + source).done(function( data, status ) {
        if (data['Status'] == 'Error') {
            toast(data['Status'],"",data['Error'],"danger","30000");
            hideLoading();
        } else if (data['error']) {
            toast('Error',"",data['error'][0]['message'],"danger","30000");
            hideLoading();
        } else {
            const columns = [
                {
                    field: 'hostname',
                    title: 'Hostname',
                    sortable: true
                }
            ];
            if (callType != "port") {
                columns.push({
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
                    }
                );
            } else {
                columns.push({
                        field: 'port',
                        title: 'Port',
                        sortable: true
                    },
                    {
                        field: 'result',
                        title: 'Status',
                        sortable: true
                    }
                );
            }

            if ((["a","aaaa","all"]).includes(callType)) {
                columns.push({
                    field: 'IPAddress',
                    title: 'IP Address',
                    sortable: true
                })
            }
            if ((["mx","txt","dmarc","all","nameserver","soa"]).includes(callType)) {
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
    }).fail(function( data, status ) {
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
  var file = document.getElementById("file");
  var port = document.getElementById("port-container")
  var source = document.getElementById("source");
  if(file.value === 'port') {
    port.style.visibility="visible";
    source.disabled = true;
    source.value = "private";
  } else if (file.value === 'reverseLookup') {
    port.style.visibility="hidden";
    source.disabled = true;
    source.value = "private";
  } else {
    port.style.visibility="hidden";
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
