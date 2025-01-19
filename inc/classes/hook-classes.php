<?php
class hooks {
    public $hooks = [];

	public function __construct() {
        $this->collectHooks();
	}

    public function getHooks() {
        if (isset($this->hooks)) {
            return $this->hooks;
        }
    }

    public function collectHooks() {
        /*
        * Include all Plugin Hooks
        */
        if (file_exists(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'plugins')) {
            $folder = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'plugins';
            $directoryIterator = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS);
            $iteratorIterator = new RecursiveIteratorIterator($directoryIterator);
            foreach ($iteratorIterator as $info) {
                if ($info->getFilename() == 'hooks.php') {
                    require $info->getPathname();
                }
            }
        }
    }

    public function addHook($hookName, $callback) {
        if (!isset($this->hooks[$hookName])) {
            $this->hooks[$hookName] = [];
            $GLOBALS['hooks'][$hookName] = [];
        }
        $this->hooks[$hookName][] = $callback;
        $GLOBALS['hooks'][$hookName][] = $callback;
    }

    public function executeHook($hookName, $params = []) {
        if (isset($this->hooks[$hookName])) {
            foreach ($this->hooks[$hookName] as $callback) {
                call_user_func_array($callback, $params);
            }
        }
    }
}