<?php
$Dashboards = $phpef->dashboard->getDashboards();
$DashboardCount = count($Dashboards);

if ($DashboardCount == 1) {
    $DashboardKey = array_keys($Dashboards)[0];
    $Content = '<div class="container-fluid mt--20">';
    $Content .= $phpef->dashboard->buildDashboard($DashboardKey);
    $Content .= '</div>';
} else {
    $TabList = '<ul class="nav nav-tabs mb-2" role="tablist" style="margin-top: -20px;">';
    $TabContent = '<div class="tab-content">';

    $DashboardBuiltCount = 0;
    foreach ($Dashboards as $DashboardKey => $DashboardVal) {
        if ($DashboardBuiltCount == 0) {
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
                <div class="row grid">
                    '.$phpef->dashboard->buildDashboard($DashboardKey).'
                </div>
            </div>
        ';

        $DashboardBuiltCount++;
    }

    $TabList .= '</ul>';
    $TabContent .= '</div>';
    $Content = $TabList . $TabContent;
}

$Content .= '
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var elem = document.querySelector(".grid");
        var msnry = new Masonry(elem, {
            itemSelector: ".grid-item",
            percentPosition: true
        });
    });
</script>';

return $Content;