<?php
// Define Widget Interface
interface WidgetInterface {
    public function render();
    public function settings();
}

// Define Dashboard Class
class Dashboard {
    protected $widgets = [];
    private $config;

    public function __construct($core) {
        $this->config = $core->config;
    }

    public function render($widgetList = []) {
        $output = '';
        foreach ($widgetList as $widgetName => $options) {
            if (isset($this->widgets[$widgetName])) {
                $size = $options['size'] ?? 'col-12'; // Default size
                $class = $options['class'] ?? ''; // Additional classes
                $output .= '<div class="' . $size . ' ' . $class . '">';
                $output .= $this->widgets[$widgetName]['widget']->render();
                $output .= '</div>';
            }
        }
        return $output;
    }

    public function registerWidget($widgetName, WidgetInterface $widget) {
        $this->widgets[$widgetName] = [
            "widget" => $widget,
            "info" => $widget->settings()['info']
        ];
    }

    public function getWidgets() {
        return $this->widgets;
    }

    public function getWidgetSettings($widgetName) {
        return $this->widgets[$widgetName]['widget']->settings();
    }

    public function getWidgetByName($name) {
        return $this->widgets[$name] ?? [];
    }

    public function getDashboards() {
        return $this->config->get('Dashboards') ?? [];
    }

    public function buildDashboard($name) {
        $widgets = $this->config->get('Dashboards',$name)['Widgets'] ?? [];
      
        // Return dashboard & configured widgets
        return $this->render($widgets);
    }

    public function getDashboardSettings() {
        global $phpef;

        $AppendNone = array(
            [
                "name" => 'None',
                "value" => ''
            ]
        );
        $WidgetList = array_merge($AppendNone,array_map(function($item) {
            return [
                "name" => $item['info']['name'],
                "value" => $item['info']['name']
            ];
        }, $this->getWidgets()));

        return array(
            'Settings' => array(
                $phpef->settingsOption('input', 'Name', ['label' => 'Dashboard Name']),
                $phpef->settingsOption('input', 'Description', ['label' => 'Dashboard Description']),
                $phpef->settingsOption('auth', 'Auth', ['label' => 'Role Required']),
                $phpef->settingsOption('enable', 'Enabled')
            ),
            'Widgets' => array(
                $phpef->settingsOption('selectwithtable', 'Widgets', ['label' => 'Enabled Widgets', 'options' => $WidgetList, 'class' => 'widgetSelect select-multiple', 'override' => 'col-md-8', 'id' => 'widgetSelect'])
            )
	    );
    }
}