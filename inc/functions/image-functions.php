<?php
trait Images {
    public function getImagesDir() {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR;
    }
    
    public function getImageUrlPath() {
        return DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR;
    }

    public function getImages() {
        $allIconsPrep = array();
        $allIcons = array();
        $ignore = array(".", "..", "._.DS_Store", ".DS_Store", "index.php");
        $path = $this->getImageUrlPath();
        $images = scandir($this->getImagesDir());
        foreach ($images as $image) {
            if (!in_array($image, $ignore)) {
                $allIconsPrep[$image] = array(
                    'path' => $path,
                    'name' => $image
                );
            }
        }
        uksort($allIconsPrep, 'strcasecmp');
        foreach ($allIconsPrep as $item) {
            $allIcons[] = $item['path'] . $item['name'];
        }
        return $allIcons;
    }

    public function getPluginImages() {
        $pluginDir = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'plugins';
        $result = [];
    
        // Check if the main plugin directory exists
        if (is_dir($pluginDir)) {
            // Scan the main plugin directory for subdirectories
            $plugins = scandir($pluginDir);
    
            foreach ($plugins as $plugin) {
                if ($plugin !== '.' && $plugin !== '..') {
                    $imagesDir = $pluginDir . '/' . $plugin . '/images';
    
                    // Check if the images directory exists in the plugin
                    if (is_dir($imagesDir)) {
                        // Scan the images directory for files
                        $images = scandir($imagesDir);
    
                        foreach ($images as $image) {
                            if ($image !== '.' && $image !== '..') {
                                $result[] = $imagesDir . '/' . $image;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    public function getImageOrIcon($stub) {
        if (strpos($stub,"fa fa-") !== false || strpos($stub,"fa-") !== false) {
            return '<i class="'.$stub.'"></i>';
        } elseif (strpos($stub,"/assets/images") !== false) {
            return '<img src="'.$stub.'"></img>';
        }
    }
}