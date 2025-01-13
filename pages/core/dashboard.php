<?php
$Dashboards = $phpef->dashboard->getDashboards();

$TabList = '<ul class="nav nav-tabs" role="tablist" style="margin-top: -20px;">';
$TabContent = '<div class="tab-content">';

$DashboardCount = 0;
foreach ($Dashboards as $DashboardKey => $DashboardVal) {
    if ($DashboardCount == 0) {
        $active = ' active';
    } else {
        $active = '';
    }
    $TabList .= '
    <li class="nav-item">
        <a class="nav-link'.$active.'" id="'.$DashboardKey.'-tab" data-bs-toggle="tab" href="#'.$DashboardKey.'" role="tab" aria-controls="'.$DashboardKey.'" aria-selected="true">'.$DashboardKey.'</a>
    </li>
    ';

    $TabContent .= '
        <div class="tab-pane'.$active.'" id="'.$DashboardKey.'" role="tabpanel" aria-labelledby="'.$DashboardKey.'-tab">
            '.$phpef->dashboard->buildDashboard($DashboardKey).'
        </div>
    ';

    $DashboardCount++;
}

$TabList .= '</ul>';
$TabContent .= '</div>';
$Content = $TabList . $TabContent;

return $Content;