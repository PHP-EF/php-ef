<?php
class Plugins {
    private $api;
    private $core;
    private $db;

    public function __construct($api,$core,$db) {
        $this->api = $api;
        $this->core = $core;
        $this->db = $db;
    }

    public function getInstalledPlugins() {
        $list = [];
        foreach ($GLOBALS['plugins'] as $key => $value) {
            $list[] = $value;
        }
        return $list;
    }

    public function getOnlinePlugins() {
        $installedPlugins = $this->getInstalledPlugins();
        $list = $this->getPluginRepositories();
        $results = [];
        $warnings = [];
        foreach ($list as $l) {
            $stubArr = explode('https://github.com/', $l);
            $branchArr = explode(':',$stubArr[1]);
            $branch = $branchArr[1] ?? "main";
            $stub = $branchArr[0];
            $url = 'https://raw.githubusercontent.com/' . $stub . '/refs/heads/' . $branch . '/plugin.json';
            $response = $this->api->query->get($url);
            if ($response === false) {
                $warnings[] = 'Plugin.json invalid or not found<hr><small>'.$url.'</small>';
            } else if (is_array($response)) {
                foreach ($response as $r) {
                    $r['branch'] = $branch;
                    $results[] = $r;
                }
            }
        }

        return array(
            "results" => $results,
            "warnings" => $warnings
        );
    }

    public function getPluginRepositories() {
        return $this->core->config->get('PluginRepositories');
    }

    public function getAvailablePlugins() {
        $installedPlugins = $this->getInstalledPlugins();
        $onlinePluginsData = $this->getOnlinePlugins();
        $onlinePlugins = $onlinePluginsData['results'];
        $onlinePluginsWarnings = $onlinePluginsData['warnings'];
        $allPlugins = array_merge($onlinePlugins, $installedPlugins);

        // Flatten the array if there are nested arrays
        $flattenedPlugins = [];
        foreach ($allPlugins as $plugin) {
            if (is_array($plugin) && isset($plugin['name'])) {
                $flattenedPlugins[] = $plugin;
            } elseif (is_array($plugin) && isset($plugin['name'])) {
                $flattenedPlugins[] = $plugin;
            }
        }
    
        // Remove duplicates based on 'name' and mark status, source, and version
        $uniquePlugins = [];
        $installedPluginNames = array_column($installedPlugins, 'name');
        $onlinePluginNames = array_column(array_merge($onlinePlugins), 'name'); // Flatten online plugins
    
        foreach ($flattenedPlugins as $plugin) {
            if (!isset($uniquePlugins[$plugin['name']])) {
                $plugin['status'] = in_array($plugin['name'], $installedPluginNames) ? 'Installed' : 'Available';
                if (in_array($plugin['name'], $installedPluginNames) && in_array($plugin['name'], $onlinePluginNames)) {
                    $plugin['source'] = 'Online';
                    // Merge online and local plugin details
                    $onlinePlugin = current(array_filter($onlinePlugins, function($p) use ($plugin) {
                        return isset($p['name']) && $p['name'] === $plugin['name'];
                    }));
                    $plugin = array_merge($plugin, $onlinePlugin);
                    $plugin['online_version'] = $onlinePlugin['version'];
                } elseif (in_array($plugin['name'], $installedPluginNames)) {
                    $plugin['source'] = 'Local';
                } else {
                    $plugin['source'] = 'Online';
                    $plugin['online_version'] = $plugin['version'];
                }
                $uniquePlugins[$plugin['name']] = $plugin;
            } else {
                // Merge details if the plugin is already in the uniquePlugins array
                $uniquePlugins[$plugin['name']] = array_merge($uniquePlugins[$plugin['name']], $plugin);
            }
        }
    
        // Convert back to a list
        $result = array_values($uniquePlugins);
    
        return array(
            "results" => $result,
            "warnings" => $onlinePluginsWarnings
        );
    }

    public function install($data) {
        $git = new git();
        if (isset($data['name']) && isset($data['repo'])) {
            $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $data['name'];
            if (!file_exists($dir)) {
                try {
                    $branch = $data['branch'] ?? 'main';
                    $repo = $git->cloneRepository($data['repo'], $dir, ['--branch' => $branch]);
                    if ($repo === false) {
                        $this->api->setAPIResponse('Error', 'Failed to clone repository: ' . $data['repo']);
                    } else {
                        if (file_exists($dir)) {
                            $this->api->setAPIResponseMessage('Successfully installed plugin');
                            return true;
                        } else {
                            $this->api->setAPIResponseMessage('Failed to install plugin into '. $dir);
                        }
                    }
                } catch (Exception $e) {
                    $this->api->setAPIResponse('Error', 'Exception occurred while cloning repository: ' . $e->getMessage());
                }
            } else {
                $this->api->setAPIResponse('Error', 'Plugin directory: ' . $dir . ' already exists');
            }
        } else {
            $this->api->setAPIResponse('Error', 'Name and Repository are required');
        }
        return false;
    }

    public function uninstall($data) {
        if (isset($data['name'])) {
            $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $data['name'];
            if (file_exists($dir)) {
                try {
                    if (rmdirRecursive($dir)) {
                        $this->api->setAPIResponseMessage('Successfully uninstalled plugin');
                        return true;
                    } else {
                        $this->api->setAPIResponse('Error', 'Failed to remove plugin directory: ' . $dir);
                    };
                } catch (Exception $e) {
                    $this->api->setAPIResponse('Error', 'Exception occurred while removing plugin: ' . $e->getMessage());
                }
            } else {
                $this->api->setAPIResponse('Error', 'Plugin directory: ' . $dir . ' does not exist');
            }
        } else {
            $this->api->setAPIResponse('Error', 'Plugin name is required');
        }
        return false;
    }

    public function reinstall($data) {
        if ($this->uninstall($data)) {
            if ($this->install($data)) {
                $this->api->setAPIResponseMessage('Successfully reinstalled plugin');
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}