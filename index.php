<?php
  require_once(__DIR__.'/inc/inc.php');
?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

	<title>Infoblox SA Tools</title>

	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />
</head>
<body>

<div class="wrapper">
	<div class="sidebar" data-background-color="white" data-active-color="danger">

    <!--
		Tip 1: you can change the color of the sidebar's background using: data-background-color="white | black"
		Tip 2: you can change the color of the active button using the data-active-color="primary | info | success | warning | danger"
	-->

    	<div class="sidebar-wrapper">
            <div class="logo">
                <a href="http://ib-sa-report.azurewebsites.net" class="simple-text">
                    Infoblox SA Tools
                </a>
            </div>

            <ul class="nav">
                <!-- <li>
                    <a href="#page=home" class="toggleFrame" id="home">
                        <i class="fa fa-home"></i>
                        <p>Home</p>
                    </a>
                </li> -->
                <li>
                    <a href="#page=security-assessment" class="toggleFrame" id="security-assessment">
                        <i class="fa fa-magnifying-glass-chart"></i>
                        <p>Security Assessment</p>
                    </a>
                </li>
                <li>
                    <a href="#page=threat-actors" class="toggleFrame" id="threat-actors">
                        <i class="fa fa-skull"></i>
                        <p>Threat Actors</p>
                    </a>
                </li>
                <!-- <li>
                    <a href="#page=license-usage" class="toggleFrame" id="license-usage">
                        <i class="fa fa-pie-chart"></i>
                        <p>License Usage</p>
                    </a>
                </li> -->
                <li class="toggleThemeBtn-li">
                    <a href="#" class="toggleThemeBtn preventDefault">
                        <i class="fa fa-toggle-off" id="themeToggle"></i>
                    </a>
                </li>
                <li class="infoBtn-li">
                    <a href="#" class="infoBtn preventDefault">
                        <i class="fa fa-info" id="infoBtn"></i>
                    </a>
                </li>
            </ul>
    	</div>
    </div>

    <div class="main-panel main-panel-theme" data-background-color="white">
		<!-- <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="#">Home</a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-right">
                    </ul>
                </div>
            </div>
        </nav> -->


        <div class="mainContainer">
            <div class="container-fluid iframeWrapper">
                <?php
                if (isset($iframe)) {
                    echo '<iframe id="mainFrame" name="mainFrame" height="100%" width="100%" frameborder="0" src="'.$iframe.'"></iframe>';
                } else {
                    echo '<iframe id="mainFrame" name="mainFrame" height="100%" width="100%" frameborder="0" src="pages/security-assessment.php"></iframe>';
                }
                ?>
            </div>
        </div>

        <footer class="footer">
            <div class="container-fluid">
                <div class="copyright pull-right">
                    &copy; <script>document.write(new Date().getFullYear())</script>, made with <i class="fa fa-heart heart"></i> by <a href="https://github.com/TehMuffinMoo" target="_blank">Mat Cox</a>
                </div>
            </div>
        </footer>

    </div>

    <!-- Info Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">General Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body" id="infoModelBody">
                    <div class="lg-12 col-sm-12 pb-6">
                        <div class="card h-100">
                            <div class="card-block">
                                <!--tabs-->
                                <ul id="tabsJustified" class="nav nav-tabs info-nav">
                                    <li class="nav-item">
                                        <a href="" data-bs-target="#about" data-bs-toggle="tab" class="nav-link small text-uppercase active">About</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="" data-bs-target="#support" data-bs-toggle="tab" class="nav-link small text-uppercase">Support</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="" data-bs-target="#license" data-bs-toggle="tab" class="nav-link small text-uppercase">License</a>
                                    </li>
                                </ul>
                                <!--/tabs-->
                                <div id="tabsJustifiedContent" class="tab-content">
                                    <div class="tab-pane fade active show p-1" id="about">
                                        <p>The Infoblox SA Tools Portal offers a place for the Infoblox SA Team to leverage some web based tools.</p>
                                        <p>Designed by <i class="fa fa-code" style="color:red"></i> by - <a target="_blank" rel="noopener noreferrer" href="https://github.com/TehMuffinMoo">Mat Cox</a></p>
                                    </div>
                                    <div class="tab-pane fade" id="support">
                                        <br>
                                        <p>Issues and Feature Requests can be raised via Github issues page by clicking <a href="https://github.com/TehMuffinMoo/ib-sa-report/issues" target="_blank">here</a>.</p>
                                    </div>
                                    <div class="tab-pane fade" id="license">
                                        <p>MIT License</p>
                                        <p>Copyright &copy; 2024 <a target="_blank" rel="noopener noreferrer" href="https://github.com/TehMuffinMoo">Mat Cox</a></p>
                                        <p>
                                            Permission is hereby granted, free of charge, to any person obtaining a copy
                                            of this software and associated documentation files (the "Software"), to deal
                                            in the Software without restriction, including without limitation the rights
                                            to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
                                            copies of the Software, and to permit persons to whom the Software is
                                            furnished to do so, subject to the following conditions:
                                        </p><p>	
                                            The above copyright notice and this permission notice shall be included in all
                                            copies or substantial portions of the Software.
                                        </p><p>
                                            THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
                                            IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
                                            FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
                                            AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
                                            LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
                                            OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
                                            SOFTWARE.
                                        </p>
                                    </div>
                                </div>
                            <!--/tabs content-->
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>


</body>

</html>

<script>
loadiFrame();

$('.toggleThemeBtn').on('click', function () {
    toggleTheme();
});

$('.infoBtn').on('click', function () {
    $('#infoModal').modal('show');
});

$(document).ready(function() {
    $('.toggleFrame').click(function(element) {
        $('li.active').removeClass('active');
        $(this).parent().addClass('active');
        loadiFrame(element.currentTarget.href);
    });
});
</script>