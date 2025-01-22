<?php
// Define Custom HTML Widgets
class CustomHTML implements WidgetInterface {
    private $phpef;
    private $widgetConfig;

    public function __construct($phpef) {
        $this->phpef = $phpef;
        $this->widgetConfig = $this->phpef->config->get('Widgets','Custom HTML');
    }

    public function settings() {
        $customHTMLQty = 5;
        $SettingsArr = [];
        $SettingsArr['info'] = [
            'name' => 'Custom HTML',
            'description' => 'Enables adding Custom HTML to Dashboards',
			'image' => ''
        ];
        for ($i = 1; $i <= $customHTMLQty; $i++) {
			$i = sprintf('%02d', $i);
            $htmlVal = $this->widgetConfig['CustomHTML' . $i] ?? '';
			$SettingsArr['Settings']['Custom HTML ' . $i] = array(
				$this->phpef->settingsOption('enable', 'CustomHTML' . $i . 'Enabled'),
				$this->phpef->settingsOption('auth', 'CustomHTML' . $i . 'Auth', ['label' => 'Role Required']),
				$this->phpef->settingsOption('code-editor', 'CustomHTML' . $i, ['label' => 'Custom HTML Code', 'mode' => 'html', 'value' => $htmlVal]),
			);
		}
        return $SettingsArr;
    }

    public function render() {
        $Config = $this->phpef->config->get('Widgets','CustomHTML');
        $customHTMLQty = 5;
        $return = '';
        for ($i = 1; $i <= $customHTMLQty; $i++) {
			$i = sprintf('%02d', $i);
            $instance = 'CustomHTML'.$i;
            if (isset($this->widgetConfig[$instance])) {
                $Auth = $this->widgetConfig[$instance.'Auth'] ?? null;
                $Enabled = $this->widgetConfig[$instance.'Enabled'] ?? false;
                if ($this->phpef->auth->checkAccess($Auth) !== false && $Enabled) {
                    $return .= $this->widgetConfig[$instance];
                }
            }
		}
        return $return;
    }
}

// Register Custom HTML Widgets
$phpef->dashboard->registerWidget('Custom HTML', new CustomHTML($phpef));