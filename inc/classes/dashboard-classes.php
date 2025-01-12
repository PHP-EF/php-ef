<?php
// Define Widget Interface
interface WidgetInterface {
    public function render();
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
                $output .= $this->widgets[$widgetName]->render();
                $output .= '</div>';
            }
        }
        return $output;
    }

    public function registerWidget($widgetName, WidgetInterface $widget) {
        $this->widgets[$widgetName] = $widget;
    }

    public function getWidgets() {
        return $this->widgets;
    }

    public function getWidgetByName($name) {
        return $this->widgets[$name] ?? [];
    }

    public function getDashboards() {
        return $this->config->get('Dashboards','Configured') ?? [];
    }

    public function buildDashboard($name) {
        $widgets = $this->config->get('Dashboards','Configured')[$name]['Widgets'] ?? [];
      
        // Return dashboard & configured widgets
        return '
        <div class="container">
            <div class="row">
                '.$this->render($widgets).'
            </div>
        </div>
        </body>
        </html>
        ';
    }
}