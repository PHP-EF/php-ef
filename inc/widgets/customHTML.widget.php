<?php
// Define Custom HTML Widgets
class CustomHTML implements WidgetInterface {
    private $phpef;

    public function __construct($phpef) {
        $this->phpef = $phpef;
    }

    public function settings() {
        $customHTMLQty = 5;
        $SettingsArr = [];
        $SettingsArr['info'] = [
            'name' => 'CustomHTML',
            'description' => 'Enables adding Custom HTML to Dashboards',
			'image' => ''
        ];
        for ($i = 1; $i <= $customHTMLQty; $i++) {
			$i = sprintf('%02d', $i);
			$SettingsArr['Settings']['Custom HTML ' . $i] = array(
				$this->phpef->settingsOption('enable', 'CustomHTML' . $i . 'Enabled'),
				$this->phpef->settingsOption('auth', 'CustomHTML' . $i . 'Auth', ['label' => 'Role Required']),
				$this->phpef->settingsOption('code-editor', 'CustomHTML' . $i, ['label' => 'Custom HTML Code', 'mode' => 'html']),
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
            if (isset($Config[$instance])) {
                $Auth = $Config[$instance.'Auth'] ?? null;
                $Enabled = $Config[$instance.'Enabled'] ?? false;
                if ($this->phpef->auth->checkAccess($Auth) !== false && $Enabled) {
                    $return .= $Config[$instance];
                }
            }
		}
        return $return;
    }
}

// Register Custom HTML Widgets
$phpef->dashboard->registerWidget('CustomHTML', new CustomHTML($phpef));