<?php
class customWidget implements WidgetInterface {
    public function render() {
        return '<div>Some other content</div>';
    }
}
$phpef->dashboard->registerWidget('customWidget', new customWidget());