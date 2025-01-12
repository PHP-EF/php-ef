<?php
// Define Widget Interface
interface WidgetInterface {
    public function render();
}

// Define Dashboard Class
class Dashboard {
    protected $widgets = [];

    public function __construct() {
    }

    public function render($widgetList = []) {
        $output = '';
        foreach ($this->getWidgets() as $widgetName => $widget) {
            if (empty($widgetList) || in_array($widgetName,$widgetList)) {
                $output .= $widget->render();
                $output .= '<div class="m-1"></div>';
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

    public function getWidgetsByName($name) {
        return $this->widgets[$name] ?? [];
    }
}