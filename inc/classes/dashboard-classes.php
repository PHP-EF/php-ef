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

    public function getWidgetsByName($name) {
        return $this->widgets[$name] ?? [];
    }
}