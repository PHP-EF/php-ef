<?php
class TemplateConfig {
    private $db;
    private $core;

    public function __construct($core,$db) {
        // Create or open the SQLite database
        $this->db = $db;
        $this->core = $core;
        $this->createSecurityAssessmentTemplateTable();
    }

    private function createSecurityAssessmentTemplateTable() {
        // Create template table if it doesn't exist
        $this->db->exec("CREATE TABLE IF NOT EXISTS templates (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          Status TEXT,
          FileName TEXT,
          TemplateName TEXT,
          Description TEXT,
          ThreatActorSlide INTEGER,
          Created DATE
        )");
    }

    public function getTemplateConfigs() {
        $stmt = $this->db->prepare("SELECT * FROM templates");
        $stmt->execute();
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $templates;
    }

    public function getTemplateConfigById($id) {
        $stmt = $this->db->prepare("SELECT * FROM templates WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $templates = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($templates) {
          return $templates;
        } else {
          return false;
        }
    }

    public function getActiveTemplate() {
        $stmt = $this->db->prepare("SELECT * FROM templates WHERE Status = :Status");
        $stmt->execute([':Status' => 'Active']);

        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($template) {
          return $template;
        } else {
          return false;
        }
    }

    public function newTemplateConfig($Status,$FileName,$TemplateName,$Description,$ThreatActorSlide) {
        try {
            // Check if filename already exists
            $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM templates WHERE FileName = :FileName OR TemplateName = :TemplateName");
            $checkStmt->execute([':FileName' => $FileName, ':TemplateName' => $TemplateName]);
            if ($checkStmt->fetchColumn() > 0) {
                return array(
                    'Status' => 'Error',
                    'Message' => 'Template Name already exists'
                );
            }
        } catch (PDOException $e) {
            return array(
                'Status' => 'Error',
                'Message' => $e
            );
        }
        $stmt = $this->db->prepare("INSERT INTO templates (Status, FileName, TemplateName, Description, ThreatActorSlide, Created) VALUES (:Status, :FileName, :TemplateName, :Description, :ThreatActorSlide, :Created)");
        try {
            $CurrentDate = new DateTime();
            $stmt->execute([':Status' => urldecode($Status), ':FileName' => urldecode($FileName), ':TemplateName' => urldecode($TemplateName), ':Description' => urldecode($Description), ':ThreatActorSlide' => urldecode($ThreatActorSlide), ':Created' => $CurrentDate->format('Y-m-d H:i:s')]);
            $id = $this->db->lastInsertId();
            if ($Status == 'Active') {
                $statusStmt = $this->db->prepare("SELECT id FROM templates WHERE Status == :Status");
                $statusStmt->execute([':Status' => 'Active']);
                $ActiveTemplates = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($ActiveTemplates as $AT) {
                    $setStatusStmt = $this->db->prepare("UPDATE templates SET Status = :Status WHERE id == :id AND id != :thisid");
                    $setStatusStmt->execute([':Status' => 'Inactive',':id' => $AT['id'],':thisid' => $id]);
                }
            }
            $this->core->logging->writeLog("Templates","Created New Security Assessment Template","info");
            return array(
                'Status' => 'Success',
                'Message' => 'Template added successfully'
            );
        } catch (PDOException $e) {
            return array(
                'Status' => 'Error',
                'Message' => $e
            );
        }
    }

    public function setTemplateConfig($id,$Status,$FileName,$TemplateName,$Description,$ThreatActorSlide) {
        $templateConfig = $this->getTemplateConfigById($id);
        if ($templateConfig) {
            if ($FileName !== null || $TemplateName !== null) {
                try {
                    // Check if new filename/template name already exists
                    $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM templates WHERE (FileName = :FileName OR TemplateName = :TemplateName) AND id != :id");
                    $checkStmt->execute([':FileName' => $FileName, ':TemplateName' => $TemplateName, ':id' => $id]);
                    if ($checkStmt->fetchColumn() > 0) {
                        return array(
                            'Status' => 'Error',
                            'Message' => 'Template Name already exists'
                        );
                    }
                } catch (PDOException $e) {
                    return array(
                        'Status' => 'Error',
                        'Message' => $e
                    );
                }
            }

            $prepare = [];
            $execute = [];
            $execute[':id'] = $id;
            if ($Status !== null) {
                if ($Status == 'Active') {
                    $statusStmt = $this->db->prepare("SELECT id FROM templates WHERE Status == :Status");
                    $statusStmt->execute([':Status' => 'Active']);
                    $ActiveTemplates = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($ActiveTemplates as $AT) {
                        $setStatusStmt = $this->db->prepare("UPDATE templates SET Status = :Status WHERE id == :id AND id != :thisid");
                        $setStatusStmt->execute([':Status' => 'Inactive',':id' => $AT['id'],':thisid' => $id]);
                    }
                }
                $prepare[] = 'Status = :Status';
                $execute[':Status'] = urldecode($Status);
            }
            if ($FileName !== null) {
                $prepare[] = 'FileName = :FileName';
                $execute[':FileName'] = urldecode($FileName);
            }
            if ($TemplateName !== null) {
                $prepare[] = 'TemplateName = :TemplateName';
                $execute[':TemplateName'] = urldecode($TemplateName);
            }
            if ($Description !== null) {
                $prepare[] = 'Description = :Description';
                $execute[':Description'] = urldecode($Description);
            }
            if ($ThreatActorSlide !== null) {
                $prepare[] = 'ThreatActorSlide = :ThreatActorSlide';
                $execute[':ThreatActorSlide'] = urldecode($ThreatActorSlide);
            }
            $stmt = $this->db->prepare('UPDATE templates SET '.implode(", ",$prepare).' WHERE id = :id');
            $stmt->execute($execute);
            if ($FileName !== null) {
                $uploadDir = __DIR__.'/../../files/templates/';
                if ($templateConfig['FileName']) {
                    if (file_exists($uploadDir.$templateConfig['FileName'])) {
                        if (!unlink($uploadDir.$templateConfig['FileName'])) {
                            return array(
                                'Status' => 'Error',
                                'Message' => 'Failed to delete old template file'
                            );
                        }
                    }
                }
            }
            $this->core->logging->writeLog("Templates","Updated Security Assessment Template: ".$TemplateName,"info");
            return array(
                'Status' => 'Success',
                'Message' => 'Template updated successfully'
            );
        } else {
            return array(
                'Status' => 'Error',
                'Message' => 'Template does not exist'
            );
        }
    }

    public function removeTemplateConfig($id) {
        $templateConfig = $this->getTemplateConfigById($id);
        if ($templateConfig) {
          $uploadDir = __DIR__.'/../../files/templates/';
          if ($templateConfig['FileName']) {
            if (file_exists($uploadDir.$templateConfig['FileName'])) {
                if (!unlink($uploadDir.$templateConfig['FileName'])) {
                    return array(
                        'Status' => 'Error',
                        'Message' => 'Failed to delete template file'
                    );
                }
            }
          }
          $stmt = $this->db->prepare("DELETE FROM templates WHERE id = :id");
          $stmt->execute([':id' => $id]);
          if ($this->getTemplateConfigById($id)) {
            return array(
              'Status' => 'Error',
              'Message' => 'Failed to delete template'
            );
          } else {
            $this->core->logging->writeLog("Templates","Removed Security Assessment Template: ".$id,"warning");
            return array(
              'Status' => 'Success',
              'Message' => 'Template deleted successfully'
            );
          }
        }
    }
}

class ThreatActorConfig {
    private $db;
    private $core;

    public function __construct($core,$db) {
        // Create or open the SQLite database
        $this->db = $db;
        $this->core = $core;
        $this->createThreatActorTable();
    }

    private function createThreatActorTable() {
        // Create users table if it doesn't exist
        $this->db->exec("CREATE TABLE IF NOT EXISTS threat_actors (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          Name TEXT UNIQUE,
          SVG TEXT,
          PNG TEXT,
          URLStub TEXT
        )");
    }

    public function getThreatActorConfigById($id) {
        $stmt = $this->db->prepare("SELECT * FROM threat_actors WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $threatActors = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($threatActors) {
          return $threatActors;
        } else {
          return false;
        }
    }

    public function getThreatActorConfigByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM threat_actors WHERE LOWER(Name) = LOWER(:name)");
        $stmt->execute([':name' => $name]);
        $threatActors = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($threatActors) {
          return $threatActors;
        } else {
          return false;
        }
    }

    public function getThreatActorConfigs() {
        $stmt = $this->db->prepare("SELECT * FROM threat_actors");
        $stmt->execute();
        $threatActors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $threatActors;
    }

    public function newThreatActorConfig($Name,$SVG,$PNG,$URLStub) {
        if ($Name != "") {
            $ThreatActorConfig = $this->getThreatActorConfigs();
            try {
                // Check if filename already exists
                $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM threat_actors WHERE Name = :Name");
                $checkStmt->execute([':Name' => urldecode($Name)]);
                if ($checkStmt->fetchColumn() > 0) {
                    return array(
                        'Status' => 'Error',
                        'Message' => 'Threat Actor already exists'
                    );
                }
            } catch (PDOException $e) {
                return array(
                    'Status' => 'Error',
                    'Message' => $e
                );
            }
            $stmt = $this->db->prepare("INSERT INTO threat_actors (Name, SVG, PNG, URLStub) VALUES (:Name, :SVG, :PNG, :URLStub)");
            try {
                $stmt->execute([':Name' => urldecode($Name), ':SVG' => urldecode($SVG), ':PNG' => urldecode($PNG), ':URLStub' => urldecode($URLStub)]);
                $this->core->logging->writeLog("ThreatActors","Created new Threat Actor: ".$Name,"info");
                return array(
                    'Status' => 'Success',
                    'Message' => 'Threat Actor added successfully'
                );
            } catch (PDOException $e) {
                return array(
                    'Status' => 'Error',
                    'Message' => $e
                );
            }
        } else {
            return array(
                'Status' => 'Error',
                'Message' => 'Threat Actor name missing'
            );
        }
    }

    public function setThreatActorConfig($id,$Name,$SVG,$PNG,$URLStub) {
        if ($Name != "") {
            $ThreatActorConfig = $this->getThreatActorConfigById($id);
            if ($ThreatActorConfig) {
                $prepare = [];
                $execute = [];
                $execute[':id'] = $id;
                if ($Name !== null) {
                    try {
                        // Check if new filename/template name already exists
                        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM threat_actors WHERE Name = :Name AND id != :id");
                        $checkStmt->execute([':Name' => $Name, ':id' => $id]);
                        if ($checkStmt->fetchColumn() > 0) {
                            return array(
                                'Status' => 'Error',
                                'Message' => 'Threat Actor Name already exists'
                            );
                        }
                    } catch (PDOException $e) {
                        return array(
                            'Status' => 'Error',
                            'Message' => $e
                        );
                    }
                    $prepare[] = 'Name = :Name';
                    $execute[':Name'] = urldecode($Name);
                }
                if ($SVG !== null) {
                    $prepare[] = 'SVG = :SVG';
                    $execute[':SVG'] = urldecode($SVG);
                }
                if ($PNG !== null) {
                    $prepare[] = 'PNG = :PNG';
                    $execute[':PNG'] = urldecode($PNG);
                }
                if ($URLStub !== null) {
                    $prepare[] = 'URLStub = :URLStub';
                    $execute[':URLStub'] = urldecode($URLStub);
                }
                $stmt = $this->db->prepare('UPDATE threat_actors SET '.implode(", ",$prepare).' WHERE id = :id');
                $stmt->execute($execute);
                $this->core->logging->writeLog("ThreatActors","Updated Threat Actor: ".$Name,"info");
                return array(
                    'Status' => 'Success',
                    'Message' => 'Threat Actor updated successfully'
                );
            } else {
                return array(
                    'Status' => 'Error',
                    'Message' => 'Threat Actor does not exist'
                );
            }
        } else {
            return array(
                'Status' => 'Error',
                'Message' => 'Threat Actor name missing'
            );
        }
    }

    public function removeThreatActorConfig($id) {
        $ThreatActorConfig = $this->getThreatActorConfigById($id);
        if ($ThreatActorConfig) {
          $uploadDir = __DIR__.'/../../assets/images/Threat Actors/Uploads/';
          if ($ThreatActorConfig['PNG']) {
            if (file_exists($uploadDir.$ThreatActorConfig['PNG'])) {
                if (!unlink($uploadDir.$ThreatActorConfig['PNG'])) {
                    return array(
                        'Status' => 'Error',
                        'Message' => 'Failed to delete PNG file'
                    );
                }
            }
          }
          if ($ThreatActorConfig['SVG']) {
            if (file_exists($uploadDir.$ThreatActorConfig['SVG'])) {
                if (!unlink($uploadDir.$ThreatActorConfig['SVG'])) {
                    return array(
                        'Status' => 'Error',
                        'Message' => 'Failed to delete SVG file'
                    );
                }
            }
          }
          $stmt = $this->db->prepare("DELETE FROM threat_actors WHERE id = :id");
          $stmt->execute([':id' => $id]);
          if ($this->getThreatActorConfigById($id)) {
            return array(
              'Status' => 'Error',
              'Message' => 'Failed to delete Threat Actor'
            );
          } else {
            $this->core->logging->writeLog("ThreatActors","Removed Threat Actor: ".$id,"warning");
            return array(
              'Status' => 'Success',
              'Message' => 'Template deleted Threat Actor'
            );
          }
        }
    }
}