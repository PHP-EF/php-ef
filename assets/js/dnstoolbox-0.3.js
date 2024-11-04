$(document).ready(function(){
    $("#domain").keyup(function(event){
	event.preventDefault();
    });
});
window.onload = function() {
//Counts the number of requests in this session
    var requestNum = 0;
    //Choose the correct script to run based on dropdown selection
    $("#submit").on('click', function(event) {
      event.preventDefault();
      if (document.getElementById("domain").value.endsWith(".")) {
        var domain = document.getElementById("domain").value;
      } else {
        var domain = document.getElementById("domain").value+".";
      }
      returnDnsDetails(document.getElementById("domain").value, document.getElementById("file").value, document.getElementById("port").value, $('#source').val());
    });

    if ($('#domain').val() != "") {
      $("#submit").click();
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

    //Get DNS Details
    function returnDnsDetails(domain, callType, port, source) {
        //checks for valid input
        if (domain.length == 0) {
            document.getElementById("txtHint").innerHTML = " Please enter a valid domain";
            return;
        } else {
            var xmlhttp = new XMLHttpRequest();
            
            xmlhttp.onreadystatechange = function () {
                var date = new Date();
                if (this.readyState == 4 && this.status == 200) {
                    //Clears the hint field
                    document.getElementById("txtHint").innerHTML = "";
                    document.getElementById("loading").innerHTML= '';
                    //parse the response into a JS Object
                    dnsResp = JSON.parse(this.responseText);        
                    buildTable(dnsResp, callType, source);
                }
            }
            document.getElementById("loading").innerHTML = '<div class="sk-three-bounce"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div>'
            xmlhttp.open("GET", "/api/dnstoolbox/?domain=" + domain + "&request=" + callType + "&port=" + port + "&source=" + source, true);
            xmlhttp.send();
            
        }
    }

    function buildTable(jsonResp, callType, source){
        var requestNum = Date.now();
        if (jsonResp.length == 0) {
            $(".responseTable").prepend("<div class = 'responseRow" + requestNum + "'><table></table></div>");
            $(".responseRow" + requestNum + " Table").append("<tr><td colspan='2' class='thead'>" + requestTitle(callType) + " (" + requestSource(source) + ")</td></tr>");
            $(".responseRow" + requestNum + " Table").append("<tr><td colspan='2' style='text-align:center'>NO DATA FOUND</td></tr>");
        } else {

            //creates thes the table to store the response details each table has a unique class
            $(".responseTable").prepend("<div class = 'responseRow" + requestNum + "'><table></table></div>");
            //Creates title bar
            $(".responseRow" + requestNum + " Table").append("<tr><td colspan='2' class='thead'>" + requestTitle(callType) + " (" + requestSource(source) + ")</td></tr>");

            for (i = 0, len = jsonResp.length; i < len; i++) {
                var jsonData = jsonResp[i];

                if (i != 0) {$(".responseRow" + (requestNum-1)).append("<Div class = 'responseRow" + requestNum + "'><table></table></div>");}
                //iterates through object keys
                if(callType === "blacklist.php"){
                    for (j = 0, len2 = Object.keys(jsonData).length; j < len2; j++) {  
                        $(".responseRow" + requestNum + " Table").append("<tr class='twoCol'><td class='left-row'>" + Object.getOwnPropertyNames(jsonData)[j] + ":</td><td>" + jsonData[Object.keys(jsonData)[j]] + "</td></tr>");
                    }
                    
                } else {
                    for (j = 0, len2 = Object.keys(jsonData).length; j < len2; j++) {  
                        $(".responseRow" + requestNum + " Table").append("<tr class='twoCol'><td class='left-row'>" + Object.getOwnPropertyNames(jsonData)[j] + ":</td><td>" + cleanString(jsonData[Object.keys(jsonData)[j]].toString()) + "</td></tr>");
                    }
                }
                requestNum++;
            }

        }
    }

    function cleanString(data) {
        return data
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
     }

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
