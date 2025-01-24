<?php
trait Common {
    public function settingsOption($type, $name = null, $extras = null) {
        $type = strtolower(str_replace('-', '', $type));
        $setting = [
            'name' => $name,
            'value' => ''
        ];
        switch ($type) {
            case 'auth':
                $settingMerge = [
                    'type' => 'select',
                    'options' => $this->auth->getRBACRolesForMenu()
                ];
                break;
            case 'authgroup':
                $settingMerge = [
                    'type' => 'select',
                    'options' => $this->auth->getRBACGroupsForMenu(false,true)
                ];
                break;
            case 'enable':
                $settingMerge = [
                    'type' => 'switch',
                    'label' => 'Enable',
                ];
                break;
            case 'test':
                $Method = $extras['Method'] ?? 'GET';
                $settingMerge = [
                    'type' => 'button',
                    'label' => 'Test',
                    'icon' => 'fa fa-flask',
                    'class' => 'pull-right',
                    'text' => 'Test',
                    'attr' => 'onclick="testAPI(\'' . $Method . '\',\'' . $name . '\')"',
                    'help' => 'Remember! Please save before using the test button!'
                ];
                break;
            case 'url':
                $settingMerge = [
                    'type' => 'input',
                    'label' => 'URL',
                    'help' => 'Please make sure to use local IP address and port - You also may use local dns name too.',
                    'placeholder' => 'http(s)://hostname:port'
                ];
                break;
            case 'cron':
                $settingMerge = [
                    'type' => 'input',
                    'label' => 'Cron Schedule',
                    // 'help' => 'You may use either Cron format or - @hourly, @daily, @monthly',
                    'placeholder' => '* * * * *'
                ];
                break;
            case 'folder':
                $settingMerge = [
                    'type' => 'folder',
                    'label' => 'Save Path',
                    'help' => 'Folder path',
                    'placeholder' => '/path/to/folder'
                ];
                break;
            case 'username':
                $settingMerge = [
                    'type' => 'input',
                    'label' => 'Username',
                ];
                break;
            case 'password':
                $settingMerge = [
                    'type' => 'password',
                    'label' => 'Password',
                    'class' => 'encrypted'
                ];
                break;
            case 'passwordalt':
                $settingMerge = [
                    'type' => 'password-alt',
                    'label' => 'Password',
                ];
                break;
            case 'passwordaltcopy':
            $settingMerge = [
                'type' => 'password-alt-copy',
                'label' => 'Password',
            ];
            break;
            case 'apikey':
            case 'token':
                $settingMerge = [
                    'type' => 'password',
                    'label' => 'API Key/Token',
                    'class' => 'encrypted'
                ];
                break;
            case 'notice':
                $settingMerge = [
                    'type' => 'html',
                    'width' => 12,
                    'label' => '',
                    'html' => '
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="panel panel-' . ($extras['notice'] ?? 'info') . '">
                                    <div class="panel-heading">
                                        <span lang="en">' . ($extras['title'] ?? 'Attention') . '</span>
                                    </div>
                                    <div class="panel-wrapper" aria-expanded="true">
                                        <div class="panel-body">
                                            <span lang="en">' . ($extras['body'] ?? '') . '</span>
                                            <span>' . ($extras['bodyHTML'] ?? '') . '</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        '
                ];
                break;
            case 'about':
                $settingMerge = [
                    'type' => 'html',
                    'width' => 12,
                    'label' => '',
                    'html' => '
                        <div class="panel panel-default">
                            <div class="panel-wrapper collapse in">
                                <div class="panel-body">
                                    <h3 lang="en">' . ucwords($name) . ' Homepage Item</h3>
                                    <p lang="en">' . $extras["about"] . '</p>
                                </div>
                            </div>
                        </div>'
                ];
                break;
            case 'limit':
                $settingMerge = [
                    'type' => 'number',
                    'label' => 'Item Limit',
                ];
                break;
            case 'blank':
                $settingMerge = [
                    'type' => 'blank',
                    'label' => '',
                ];
                break;
            case 'precodeeditor':
                $settingMerge = [
                    'type' => 'textbox',
                    'class' => 'hidden ' . $name . 'Textarea',
                    'label' => '',
                ];
                break;
            case 'codeeditor':
                $mode = strtolower($extras['mode'] ?? 'css');
                switch ($mode) {
                    case 'html':
                    case 'javascript':
                        $mode = 'ace/mode/' . $mode;
                        break;
                    case 'js':
                        $mode = 'ace/mode/javascript';
                        break;
                    default:
                        $mode = 'ace/mode/css';
                        break;
                }
                $sanitized_name = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
                $value = $extras['value'] ?? '';
                $settingMerge = [
                    'type' => 'html',
                    'width' => 12,
                    'label' => 'Custom Code',
                    'html' => '
                        <textarea class="form-control info-field ' . $name . 'Textarea" name="' . $name . '" data-type="textbox" data-label="' . $name . '" hidden></textarea>
                        <div id="' . $sanitized_name . 'Editor" style="height:300px"></div>
                        <script>
                            var ' . $sanitized_name . ' = ace.edit("' . $sanitized_name . 'Editor");
                            ' . $sanitized_name . '.session.setMode("' . $mode . '");
                            ' . $sanitized_name . '.setTheme("ace/theme/idle_fingers");
                            ' . $sanitized_name . '.setShowPrintMargin(false);
                            
                            ' . $sanitized_name . '.setValue(`' . $value .'`, -1); // -1 to move cursor to the start
                            
                            ' . $sanitized_name . '.session.on("change", function(delta) {
                                $(`[name="' . $name .'"]`).val(' . $sanitized_name . '.getValue());
                                $(`[name="' . $name .'"]`).trigger("change");
                            });
                
                            // Update Ace editor when textarea value changes
                            $(`[name="' . $name .'"]`).on("input", function() {
                                ' . $sanitized_name . '.setValue($(this).val(), -1); // -1 to move cursor to the start
                            });
                        </script>
                    '
                ];
                break;
            default:
                $settingMerge = [
                    'type' => strtolower($type),
                    'label' => ''
                ];
                break;
        }
        $setting = array_merge($settingMerge, $setting);
        if ($extras) {
            if (gettype($extras) == 'array') {
                $setting = array_merge($setting, $extras);
            }
        }
        return $setting;
    }

    public function cookie($type, $name, $value = '', $days = -1, $http = true, $path = '/') {
        $days = ($days > 365) ? 365 : $days;
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https") {
            $Secure = true;
            $HTTPOnly = true;
        } elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['HTTPS'] !== '') {
            $Secure = true;
            $HTTPOnly = true;
        } else {
            $Secure = false;
            $HTTPOnly = false;
        }
        if (!$http) {
            $HTTPOnly = false;
        }
        $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? '';
        $Domain = $this->parseDomain($_SERVER['HTTP_HOST']);
        $DomainTest = $this->parseDomain($_SERVER['HTTP_HOST'], true);
        if ($type == 'set') {
            $_COOKIE[$name] = $value;
            header('Set-Cookie: ' . rawurlencode($name) . '=' . rawurlencode($value)
                . (empty($days) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', time() + (86400 * $days)) . ' GMT')
                . (empty($path) ? '' : '; path=' . $path)
                . (empty($Domain) ? '' : '; domain=' . $Domain)
                . (!$Secure ? '' : '; SameSite=None; Secure')
                . (!$HTTPOnly ? '' : '; HttpOnly'), false);
            header('Set-Cookie: ' . rawurlencode($name) . '=' . rawurlencode($value)
                . (empty($days) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', time() + (86400 * $days)) . ' GMT')
                . (empty($path) ? '' : '; path=' . $path)
                . (empty($Domain) ? '' : '; domain=' . $DomainTest)
                . (!$Secure ? '' : '; SameSite=None; Secure')
                . (!$HTTPOnly ? '' : '; HttpOnly'), false);
        } elseif ($type == 'delete') {
            unset($_COOKIE[$name]);
            header('Set-Cookie: ' . rawurlencode($name) . '=' . rawurlencode($value)
                . (empty($days) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', time() - 3600) . ' GMT')
                . (empty($path) ? '' : '; path=' . $path)
                . (empty($Domain) ? '' : '; domain=' . $Domain)
                . (!$Secure ? '' : '; SameSite=None; Secure')
                . (!$HTTPOnly ? '' : '; HttpOnly'), false);
            header('Set-Cookie: ' . rawurlencode($name) . '=' . rawurlencode($value)
                . (empty($days) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', time() - 3600) . ' GMT')
                . (empty($path) ? '' : '; path=' . $path)
                . (empty($Domain) ? '' : '; domain=' . $DomainTest)
                . (!$Secure ? '' : '; SameSite=None; Secure')
                . (!$HTTPOnly ? '' : '; HttpOnly'), false);
        }
    }

    public function parseDomain($value, $force = false) {
        $Domain = $value;
        $Port = strpos($Domain, ':');
        if ($Port !== false) {
            $Domain = substr($Domain, 0, $Port);
            $value = $Domain;
        }
        $check = substr_count($Domain, '.');
        if ($check >= 3) {
            if (is_numeric($Domain[0])) {
                $Domain = '';
            } else {
                $Domain = '.' . explode('.', $Domain)[1] . '.' . explode('.', $Domain)[2] . '.' . explode('.', $Domain)[3];
            }
        } elseif ($check == 2) {
            if (explode('.', $Domain)[0] == 'www') {
                $Domain = '.' . explode('.', $Domain)[1] . '.' . explode('.', $Domain)[2];
            } elseif (explode('.', $Domain)[1] == 'co') {
                $Domain = '.' . explode('.', $Domain)[0] . '.' . explode('.', $Domain)[1] . '.' . explode('.', $Domain)[2];
            } else {
                $Domain = '.' . explode('.', $Domain)[1] . '.' . explode('.', $Domain)[2];
            }
        } elseif ($check == 1) {
            $Domain = '.' . $Domain;
        } else {
            $Domain = '';
        }
        return ($force) ? $value : $Domain;
    }

    public function getUserIP() {
		if (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		} elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_FORWARDED'])) {
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		} elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		} else {
			$ipaddress = '127.0.0.1';
		}
		if (strpos($ipaddress, ',') !== false) {
			list($first, $last) = explode(",", $ipaddress);
			unset($last);
			return $first;
		} else {
			return $ipaddress;
		}
	}
}