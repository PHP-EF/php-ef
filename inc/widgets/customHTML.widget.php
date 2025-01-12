<?php
// Define Custom HTML Widgets
class CustomHTML implements WidgetInterface {
    protected $Instance;

    public function __construct($Instance) {
        $this->Instance = $Instance;
    }

    public function render() {
        global $phpef;
        return $phpef->config->get('Dashboards', 'Widgets')[$this->Instance] ?? '';
    }
}

// Register Custom HTML Widgets
$widgetNames = ['customHTML1', 'customHTML2', 'customHTML3', 'customHTML4', 'customHTML5'];
foreach ($widgetNames as $name) {
    $phpef->dashboard->registerWidget($name, new CustomHTML($name));
}