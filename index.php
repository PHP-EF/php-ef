<?php
  require_once(__DIR__.'/scripts/inc/inc.php');
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
                <li>
                    <a href="#page=home" class="toggleFrame" id="home">
                        <i class="ti-home"></i>
                        <p>Home</p>
                    </a>
                </li>
                <li>
                    <a href="#page=security-assessment" class="toggleFrame" id="security-assessment">
                        <i class="ti-pie-chart"></i>
                        <p>Security Assessment</p>
                    </a>
                </li>
                <li class="toggleThemeBtn-li">
                    <a href="#" class="toggleThemeBtn preventDefault">
                        <i class="fa fa-toggle-off" id="themeToggle"></i>
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
</div>


</body>

</html>

<script>
loadiFrame();

$('.toggleThemeBtn').on('click', function () {
    toggleTheme();
});

$(document).ready(function() {
    $('.toggleFrame').click(function(element) {
        $('li.active').removeClass('active');
        $(this).parent().addClass('active');
        loadiFrame(element.currentTarget.href);
    });
});
</script>