<?php
trait Cron {
    function updateCronStatus($source, $jobName, $status, $message = null) {
        // Check if the record exists
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM cron WHERE name = :name");
        $stmt->execute([':name' => $jobName]);
        $exists = $stmt->fetchColumn();
    
        if ($exists) {
            // Update the existing record
            $stmt = $this->db->prepare("
                UPDATE cron 
                SET source = :source, last_run = CURRENT_TIMESTAMP, status = :status, message = :message 
                WHERE name = :name
            ");
        } else {
            // Insert a new record
            $stmt = $this->db->prepare("
                INSERT INTO cron (name, source, last_run, status, message) 
                VALUES (:name, :source, CURRENT_TIMESTAMP, :status, :message)
            ");
        }
    
        $stmt->execute([
            ':name' => $jobName,
            ':source' => $source,
            ':status' => $status,
            ':message' => $message
        ]);
    }

    function getCronStatus($type = 'active') {
        switch($type) {
            case 'active': // Only retrieves System & jobs matching installed plugins
                $list = ['System'];
                foreach ($GLOBALS['plugins'] as $key => $value) {
                    $list[] = $value['name'];
                }

                $placeholders = implode(',', array_fill(0, count($list)+1, '?'));

                $stmt = $this->db->prepare("SELECT * FROM cron WHERE source IN ($placeholders);");
                $stmt->execute($list);
                $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'all':
                $stmt = $this->db->prepare("SELECT * FROM cron;");
                $stmt->execute();
                $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
        }

        return $jobs;
    }
}