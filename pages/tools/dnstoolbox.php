<?php
header("Cache-control: no-cache, max-age=0");
header("Expires: 0");
header("Expires: Tue, 01 Jan 1980 1:00:00 GMT");
header("Pragma: no-cache");

require_once(__DIR__.'/../../inc/inc.php');

?>

<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title> DNSToolbox </title>
<meta name="msapplication-TileColor" content="#44c0f0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="DNSToolbox">
<meta name="theme-color" content="#44c0f0">

<link href="/assets/css/dnstoolbox-0.2.css" rel="stylesheet">

<style>
.form-select {
  margin-top:10px;
}
</style>

</head>
<body>
    <div class="container">
        <div class="row" id="top-row">
          <div class="col-md-12">
              <H1 class="logo"><Span class = "logo-style1">DNS</Span>Toolbox</H1>
          </div>
        </div>
        <div class="row">
	        <div class="col-md-12">
            <form class="row g-3">
              <div class="col-auto">
                <label for="domain" class="visually-hidden">Domain</label>
                <input type="text" class="form-control" id="domain" placeholder="domain.com">
		          </div>
              <div class="col-auto">
                <label for="file" class="visually-hidden">Domain</label>
                <select onchange="showAdditionalFields()" id="file" class="form-select">
                  <option value="a">IP/Get A Record</option>
                  <option value="aaaa">IPV6/Get AAAA Record</option>
                  <option value="mx">MX/Get MX Record</option>
                  <option value="txt">SPF/TXT</option>
                  <option value="dmarc">DMARC</option>
<!--                  <option value="blacklist">Blacklist Check</option>-->
<!--                  <option value="whois">Whois</option>-->
                  <option value="port">Check If Port Open</option>
<!--                  <option value="hinfo">Hinfo/Get Hardware Information</option>-->
                  <option value="all">Query All DNS Records</option>
		              <option value="reverseLookup">IP/Reverse DNS Lookup</option>
		              <option value="nameserverLookup">Query Authoritative Nameservers</option>
		              <option value="soa">Query Start of Authority (SOA)</option>
		            </select>
		          </div>
              <div class="col-auto">
                <select id="source" class="form-select">
                  <option value="google">Google DNS</option>
                  <option value="cloudflare">Cloudflare DNS</option>
                </select>
		          </div>
              <div class="col-auto">
		            <input type="submit" id="submit" value="Search" class="form-control btn"/>
		            <button id="copyLink" class="form-control btn" title="Copy link to clipboard">
                  <span class="fas fa-link" style="padding-top:4px;padding-bottom:4px;"/>
		            </button>
              </div>
              <div class="col-auto">
                <div style="visibility: hidden" id="port-container">
                  <span class="form-label">Port:&nbsp;</span><input type="text" name="port" id="port" class="form-control">
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="row">
          <div class="col-md-10 col-md-offset-2">
              <span id="txtHint" style="color: red;"></span>
              <div id="loading">
              <div class="info">
              <h3>Using the DNS Toolbox</h3>
              <p>The DNS Toolbox is here to be an easy way for users to query various information from DNS. The available options are listed out below.</p>
              <br/>
              <table>
                <tr>
                  <th>Query</th>
                  <th>Description</th>
                </tr>
                <tr>
                  <td>IP/Get A Record</td>
                  <td>An A Record is used to associate a domain name with an IP(v4) address. This query returns any A records associated with the queried domain.</td>
                </tr>
                <tr>
                  <td>IPV6/Get AAAA Record</td>
                  <td>An AAAA Record is used to associate a domain name with an IP(v6) address. This query returns any AAAA records associated with the queried domain.</td>
                </tr>
                <tr>
                  <td>MX/Get MX Record</td>
                  <td>MX is a Mail Exchange record type. This is used to identify the mail server(s) used which are authoritative for the queried domain.</td>
                </tr>
                <tr>
                  <td>SPF/TXT</td>
                  <td>An TXT record is used to store text based information within DNS for various services. SPF specifically is for authentication of public email servers and identifies which mail servers are permitted to send mail on the queried domain's behalf. This query will return associated TXT records.</td>
                </tr>
                <tr>
                  <td>DMARC</td>
                  <td>A DMARC Record is used to authenticate email addresses and defines how and where report both authorized and unauthorized mail.</td>
                </tr>
<!--                <tr>
                  <td>Whois</td>
                  <td>This queries the public Whois database(s) to identify registrar level information about the queried domain.</td>
                </tr>-->
                <tr>
                  <td>Open Port Check</td>
                  <td>Identify if the specified port is open. If you do not specify a port, a default check against these ports will occur: 22(SSH), 25(SMTP), 53(DNS), 80(HTTP), 443(HTTPS), 445(SMB), 3389(RDP)</td>
                </tr>
                <tr>
                  <td>Query All DNS Records</td>
                  <td>This query <u>attempts</u> to request all available information for the specified domain.</td>
                </tr>
                <tr>
                  <td>IP/Reverse DNS Lookup</td>
                  <td>The query will do a reverse DNS lookup based on the IP Address queried.</td>
                </tr>
                <tr>
                  <td>Query Authoritative Nameservers</td>
                  <td>The query will do a query a list of Authoritative Nameservers for the queried domain.</td>
                </tr>
                <tr>
                  <td>Query Start of Authority (SOA)</td>
                  <td>Start of Authority (SOA) records contains administrative information about the zone, primarily useful for identifying the master server(s) for DNS Zone Transfers.</td>
                </tr>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-8 col-md-offset-2">
          <div id="responseArea" class="col-md-12">
            <div  class="responseTable"></div>
          </div>
          <footer>
            <div class="row text-center">
              <div class="col-md-12"></div>
            </div>
          </footer>
        </div>
      </div>        
    </div>
  </body>
</html>

<script src ="/assets/js/dnstoolbox-0.3.js"></script>
<script>
<?php
if (isset($_REQUEST['domain'])) {
  echo '$("#domain").val("'.$_REQUEST['domain'].'");';
}
if (isset($_REQUEST['type'])) {
  echo '$("#file").val("'.$_REQUEST['type'].'");';
}
if (isset($_REQUEST['location'])) {
  echo '$("#source").val("'.$_REQUEST['location'].'");';
}
?>
</script>
