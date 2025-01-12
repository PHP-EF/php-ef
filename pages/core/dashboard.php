<?php
// Load Class for Simplification
$dashboard = $phpef->dashboard;

// Dashboard Name to load from Configuration
$dashboardName = 'Test';

// Grab Dashboard Configuration
$configuration = $phpef->config->get('Dashboards','Configured')[$dashboardName] ?? [];

// Return dashboard & configured widgets
return '
<div class="container">
    <div class="row">
        '.$dashboard->render($configuration['Widgets']).'
    </div>
</div>
</body>
</html>
';