<?php
trait Images {
    public function getImagesDir() {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR;
    }
    
    public function getImageUrlPath() {
        return DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR;
    }

    public function getImages() {
        $ignore = array(".", "..", "._.DS_Store", ".DS_Store", "index.php");
        $path = $this->getImageUrlPath();
        $images = scandir($this->getImagesDir());
        $result = [];
        foreach ($images as $image) {
            if (!in_array($image, $ignore)) {
                $result[] = $path . $image;
            }
        }
        return $result;
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

    public function getAllImages() {
        $images = $this->getImages();
        $pluginImages = $this->getPluginImages();
        foreach ($pluginImages as $pluginImageKey => $pluginImageVal) {
            $imageName = basename($pluginImageVal);
            $imageNameWithoutExtension = pathinfo($imageName, PATHINFO_FILENAME);
            $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
            $pluginName = basename(dirname(dirname($pluginImageVal))); // Assuming plugin directory structure
            $pluginImages[$pluginImageKey] = "/api/image/plugin/{$pluginName}/{$imageNameWithoutExtension}/{$imageExtension}";
        }
        return array_merge($images,$pluginImages);
    }

    public function getAllImagesForSelect() {
        $allIconsPrep = array();
        $ignore = array(".", "..", "._.DS_Store", ".DS_Store", "index.php");
        $path = $this->getImageUrlPath();
        
        // Get core images
        $images = scandir($this->getImagesDir());
        foreach ($images as $image) {
            if (!in_array($image, $ignore)) {
                $allIconsPrep[$image] = array(
                    'val' => $path . $image,
                    'name' => $image,
                    'type' => 'native'
                );
            }
        }
    
        // Get plugin images
        $pluginImages = $this->getPluginImages();
        foreach ($pluginImages as $pluginImage) {
            $imageName = basename($pluginImage);
            $imageNameWithoutExtension = pathinfo($imageName, PATHINFO_FILENAME);
            $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
            $pluginName = basename(dirname(dirname($pluginImage))); // Assuming plugin directory structure
    
            if (!in_array($imageName, $ignore)) {
                $allIconsPrep[$pluginImage] = array(
                    'val' => "/api/image/plugin/{$pluginName}/{$imageNameWithoutExtension}/{$imageExtension}",
                    'name' => $imageName,
                    'type' => $pluginName
                );
            }
        }
       
        // Generate HTML options
        $options = '<option value="">None</option>';
        foreach ($allIconsPrep as $image) {
            if ($image['type'] == 'native') {
                $options .= '<option value="' . htmlspecialchars($image['val']) . '" data-img="' . $image['val'] . '" data-type="native">' . htmlspecialchars($image['name']) . '</option>';
            } else {
                $options .= '<option value="' . htmlspecialchars($image['val']) . '" data-img="' . $image['val'] . '" data-type="'.$image['type'].'"> ' . htmlspecialchars($image['name']) . '</option>';
            }
        }
        return $options;
    }

    public function getImageOrIcon($stub) {
        if (strpos($stub,"/assets/images") !== false || strpos($stub,"/api/image/plugin") !== false) {
            return '<img src="'.$stub.'"></img>';
        } else {
            return '<i class="'.$stub.'"></i>';
        }
    }
}