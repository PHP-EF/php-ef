<?php
// ** Define Native Widgets ** //
class CustomHTML implements WidgetInterface {
    public function render() {
        global $phpef;
        $HTML = $phpef->config->get('Dashboard','CustomHTML') ?? '';
        return $HTML;
    }
}
$phpef->dashboard->registerWidget('customHTML',new CustomHTML());