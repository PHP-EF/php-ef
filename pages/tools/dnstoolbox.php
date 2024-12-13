<?php
header("Cache-control: no-cache, max-age=0");
header("Expires: 0");
header("Expires: Tue, 01 Jan 1980 1:00:00 GMT");
header("Pragma: no-cache");

require_once(__DIR__.'/../../inc/inc.php');
if ($ib->rbac->checkAccess("DNS-TOOLBOX") == false) {
  die();
}
$return = '

<link href="/assets/css/dnstoolbox-0.3.css" rel="stylesheet">

<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <center>
            <H1 class="logo"><Span class = "logo-style1">DNS</Span>Toolbox</H1>
            <p>The DNS Toolbox is here to be an easy way for users to query various information from DNS. The available options are listed out below.</p>
          </center>
        </div>
      </div>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-md-12">
                <div class="row g-3 toolsMenu">
                  <div class="col-auto">
                    <label for="domain" class="visually-hidden">Domain</label>
                    <input type="text" class="form-control" id="domain" placeholder="domain.com">
                  </div>
                  <div class="col-auto">
                    <label for="file" class="visually-hidden">Domain</label>
                    <select onchange="showAdditionalFields()" id="file" class="form-select">
                      <option value="a">A Record</option>
                      <option value="aaaa">AAAA Record</option>
                      <option value="cname">CNAME Record</option>
                      <option value="mx">MX Record</option>
                      <option value="txt">SPF/TXT Record</option>
                      <option value="dmarc">DMARC Record</option>
                     <!-- <option value="blacklist">Blacklist Check</option> -->
                     <!-- <option value="whois">Whois</option> -->
                     <!-- <option value="hinfo">Hinfo/Get Hardware Information</option> -->
                      <option value="nameserver">NS Record</option>
                      <option value="soa">SOA Record</option>
                      <option value="all">Query All DNS Records</option>
                      <option value="reverse">IP/Reverse DNS Lookup</option>
                      <option value="port">Check If Port Open</option>
                    </select>
                  </div>
                  <div class="col-auto">
                    <select id="source" class="form-select">
                      <option value="google">Google DNS</option>
                      <option value="cloudflare">Cloudflare DNS</option>
                    </select>
                  </div>
                  <div class="col-auto">
                    <button class="btn btn-success" id="submit">Search</button>
                  </div>
                  <div class="col-auto">
                    <button id="copyLink" class="form-control btn btn-success" title="Copy link to clipboard">
                      <span class="fas fa-link" style="padding-top:4px;padding-bottom:4px;"/>
                    </button>
                  </div>
                  <div class="col-auto">
                    <div style="visibility: hidden" id="port-container">
                      <input type="text" name="port" id="port" class="form-control" placeholder="Port number(s) (i.e 22,80)">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 col-md-offset-2">
                <span id="txtHint" style="color: red;"></span>
                <div class="row loading-div">
                  <div class="loading-icon">
                    <div id="spinner-container">
                      <div class="spinner-bounce">
                        <div class="spinner-child spinner-bounce1"></div>
                        <div class="spinner-child spinner-bounce2"></div>
                        <div class="spinner-child spinner-bounce3"></div>
                      </div>
                    </div>
                    <p class="progressAction" id="progressAction"></p>
                  </div>
                </div>
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
                    <td>A Record</td>
                    <td>An A Record is used to associate a domain name with an IP(v4) address. This query returns any A records associated with the queried domain.</td>
                  </tr>
                  <tr>
                    <td>AAAA Record</td>
                    <td>An AAAA Record is used to associate a domain name with an IP(v6) address. This query returns any AAAA records associated with the queried domain.</td>
                  </tr>
                  <tr>
                    <td>CNAME Record</td>
                    <td>An CNAME Record is used to alias a domain name with another domain name. This query returns any CNAME records associated with the queried domain.</td>
                  </tr>
                  <tr>
                    <td>MX Record</td>
                    <td>MX is a Mail Exchange record type. This is used to identify the mail server(s) used which are authoritative for the queried domain.</td>
                  </tr>
                  <tr>
                    <td>SPF/TXT Record</td>
                    <td>An TXT record is used to store text based information within DNS for various services. SPF specifically is for authentication of public email servers and identifies which mail servers are permitted to send mail on the queried domain\'s behalf. This query will return associated TXT records.</td>
                  </tr>
                  <tr>
                    <td>DMARC Record</td>
                    <td>A DMARC Record is used to authenticate email addresses and defines how and where report both authorized and unauthorized mail.</td>
                  </tr>
  <!--                <tr>
                    <td>Whois</td>
                    <td>This queries the public Whois database(s) to identify registrar level information about the queried domain.</td>
                  </tr>-->
                  <tr>
                    <td>NS Records</td>
                    <td>The query will do a query a list of Authoritative Nameservers for the queried domain.</td>
                  </tr>
                  <tr>
                    <td>SOA Records</td>
                    <td>Start of Authority (SOA) records contains administrative information about the zone, primarily useful for identifying the master server(s) for DNS Zone Transfers.</td>
                  </tr>
                  <tr>
                    <td>IP/Reverse DNS Lookup</td>
                    <td>The query will do a reverse DNS lookup based on the IP Address queried.</td>
                  </tr>
                  <tr>
                    <td>Query All DNS Records</td>
                    <td>This query <u>attempts</u> to request all available information for the specified domain.</td>
                  </tr>
                  <tr>
                    <td>Open Port Check</td>
                    <td>Identify if the specified port is open. Multiple ports can be entered separated by commas. If you do not specify a port, a default check against these ports will occur: 22(SSH), 25(SMTP), 53(DNS), 80(HTTP), 443(HTTPS), 445(SMB), 3389(RDP)</td>
                  </tr>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 col-md-offset-2">
                <div id="responseArea" class="col-md-12">
                  <table id="dnsResponseTable" class="table table-striped rounded"></table>
                </div>
                <footer>
                  <div class="row text-center">
                    <div class="col-md-12"></div>
                  </div>
                </footer>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src ="/assets/js/dnstoolbox-0.4.js"></script>
<script>';
if (isset($_REQUEST['domain'])) {
  $return .= '$("#domain").val("'.$_REQUEST['domain'].'");';
}
if (isset($_REQUEST['type'])) {
  $return .= '$("#file").val("'.$_REQUEST['type'].'");';
}
if (isset($_REQUEST['location'])) {
  $return .= '$("#source").val("'.$_REQUEST['location'].'");';
}
return $return;
?>
</script>

