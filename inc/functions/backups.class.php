<?php
trait Backups {
    public function getBackupConfig() {
        return $this->config->get('System','backup');
    }

    public function getBackupLocation() {
        return $this->getBackupConfig['directory'] ?? $this->config->configDir . DIRECTORY_SEPARATOR . 'backups';
    }

    public function backup($manual = false) {
        $enabled = $this->getBackupConfig()['enabled'] ?? true;
        $includeOtherData = $this->getBackupConfig()['includeOtherData'] ?? false;
        $includeCustomImages = $this->getBackupConfig()['includeImages'] ?? false;
        $qtyToKeep = $this->getBackupConfig()['qtyToKeep'] ?? 10;
        $backupLocation = $this->getBackupLocation();

        if (!file_exists($backupLocation)) {
            mkdir($backupLocation, 0777, true);
            $this->logging->writeLog('Backup', 'Created backup directory: ' . $backupLocation, 'Info');
        }
        
        if (!$enabled && !$manual) {
            $this->logging->writeLog('Backup', 'Backup is disabled.', 'Info');
            return;
        }
    
        $filesToBackup = [];
    
        if ($includeOtherData) {
            $filesToBackup = array_merge($filesToBackup, glob($this->config->configDir . '/*'));
        } else {
            $filesToBackup[] = $this->configFilePath;
            $filesToBackup[] = $this->dbPath;
        }
    
        if ($includeCustomImages) {
            $filesToBackup = array_merge($filesToBackup, glob($this->getImagesDir() . '/*'));
        }

        // Exclude index.php
        $filesToBackup = array_filter($filesToBackup, function($file) {
            return basename($file) !== 'index.php';
        });
    
        $zip = new ZipArchive();
        $backupFile = $backupLocation . '/backup_' . date('Ymd_His') . '.zip';
    
        if ($zip->open($backupFile, ZipArchive::CREATE) === TRUE) {
            foreach ($filesToBackup as $file) {
                if (is_file($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
    
            if ($includeCustomImages) {
                $imageFiles = glob($this->getImagesDir() . '/*');
                foreach ($imageFiles as $file) {
                    if (is_file($file) && basename($file) !== "index.php") {
                        $zip->addFile($file, 'custom_images/' . basename($file));
                    }
                }
            }
    
            $zip->close();
            $this->logging->writeLog('Backup', 'Backup created successfully: ' . $backupFile, 'Info');
            $this->api->setAPIResponseMessage('Backup created successfully: ' . $backupFile);
        } else {
            $this->logging->writeLog('Backup', 'Failed to create backup zip file.', 'Warning');
            $this->api->setAPIResponse('Error','Failed to create backup zip file.');
            throw new Exception('Failed to create backup zip file.');
        }
    
        $backups = glob($backupLocation . '/backup_*.zip');
        if (count($backups) > $qtyToKeep) {
            usort($backups, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            while (count($backups) > $qtyToKeep) {
                $oldBackup = array_shift($backups);
                unlink($oldBackup);
                $this->logging->writeLog('Backup', 'Deleted old backup: ' . $oldBackup, 'Info');
            }
        }
    }

    public function getBackups() {
        $backupLocation = $this->getBackupLocation();
    
        if (!file_exists($backupLocation)) {
            $this->logging->writeLog('Backup', 'Backup directory does not exist: ' . $backupLocation, 'Warning');
            return [];
        }
    
        $backups = glob($backupLocation . '/backup_*.zip');
        usort($backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
    
        $backupList = [];
        foreach ($backups as $backup) {
            $backupList[] = [
                'filename' => basename($backup),
                'full_path' => $backup,
                'date_created' => date('Y-m-d H:i:s', filemtime($backup))
            ];
        }
    
        $this->logging->writeLog('Backup', 'Retrieved list of backups.', 'Info');
        $this->api->setAPIResponseData($backupList);
        return $backupList;
    }

    public function downloadBackup($filename) {
        $backupLocation = $this->getBackupLocation();
        $filePath = $backupLocation . DIRECTORY_SEPARATOR . $filename;
    
        if (!file_exists($filePath)) {
            $this->writeLog('Backup', 'Backup file does not exist: ' . $filePath, 'Warning');
            $this->api->setAPIResponse('Error','Backup file does not exist: ' . $filePath);
            return false;
        }

        return $filePath;
    }

    public function deleteBackup($filename) {
        $backupLocation = $this->getBackupLocation();
        $filePath = $backupLocation . DIRECTORY_SEPARATOR . $filename;
    
        if (!file_exists($filePath)) {
            $this->logging->writeLog('Backup', 'Backup file does not exist: ' . $filePath, 'Warning');
            $this->api->setAPIResponseMessage('Backup file does not exist: ' . $filePath);
            return false;
        }
    
        if (unlink($filePath)) {
            $this->logging->writeLog('Backup', 'Deleted backup file: ' . $filePath, 'Info');
            $this->api->setAPIResponseMessage('Deleted backup file: ' . $filePath);
            return true;
        } else {
            $this->logging->writeLog('Backup', 'Failed to delete backup file: ' . $filePath, 'Error');
            $this->api->setAPIResponse('Error','Failed to delete backup file: ' . $filePath);
            return false;
        }
    }
}