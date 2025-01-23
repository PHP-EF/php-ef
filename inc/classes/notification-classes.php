<?php

class Notifications {
    private $db;
    private $core;
    private $api;
  
    public function __construct($core,$db,$api) {
      $this->db = $db;
      $this->core = $core;
      $this->api = $api;
      $this->initializeNewsDatabase();
    }

    private function initializeNewsDatabase() {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS news (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                content TEXT NOT NULL,
                created TEXT NOT NULL,
                updated TEXT NOT NULL
            )
        ");
    }

    public function getNews() {
        $stmt = $this->db->prepare("SELECT * FROM news");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function newNews($data) {
        $prepare = ['created'];
        $execute = [];
        $dateTime = new DateTime('now');
        $execute[':created'] = $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
  
        if (isset($data['newsTitle'])) {
          $prepare[] = 'title';
          $execute[':title'] = $data['newsTitle'];
        }
        if (isset($data['newsContent'])) {
          $prepare[] = 'content';
          $execute[':content'] = $data['newsContent'];
        }
        
        $valueArray = array_map(function($value) {
          return ':' . $value;
        }, $prepare);

        $stmt = $this->db->prepare("INSERT INTO news (".implode(", ",$prepare).") VALUES (".implode(', ', $valueArray).")");
        if ($stmt->execute($execute)) {
            $this->api->setAPIResponseMessage('Created news item successfully.');
        } else {
            $this->api->setAPIResponse('Error','Failed to create news item.');
        }
    }

    public function updateNews($id,$data) {
        if ($this->getNewsById($id)) {
            $prepare = ['updated = :updated'];
            $execute = [];
            $dateTime = new DateTime('now');
            $execute[':updated'] = $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
            $execute[':id'] = $id;
            if (isset($data['newsTitle'])) {
              $prepare[] = 'title = :title';
              $execute[':title'] = $data['newsTitle'];
            }
            if (isset($data['newsContent'])) {
              $prepare[] = 'content = :content';
              $execute[':content'] = $data['newsContent'];
            }
            if (!empty($prepare)) {
                $stmt = $this->db->prepare('UPDATE news SET '.implode(", ",$prepare).' WHERE id = :id');
                $stmt->execute($execute);
                $this->api->setAPIResponseMessage('News item updated successfully');
            } else {
                $this->api->setAPIResponseMessage('Nothing to update');
            }
        } else {
            $this->api->setAPIResponse('Error','News item does not exist');
        }
    }

    public function removeNews($id) {
      if ($this->getNewsById($id)) {
        $stmt = $this->db->prepare("DELETE FROM news WHERE id = :id");
        $stmt->execute([':id' => $id]);
        if ($this->getNewsById($id)) {
          $this->api->setAPIResponse('Error','Failed to delete news item');
        } else {
          $this->api->setAPIResponseMessage('News item deleted successfully');
        }
      }
    }

    public function getNewsById($id) {
        $stmt = $this->db->prepare("SELECT * FROM news WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getNewsItems($limit) {
        $newsOrderByLastUpdated = $this->core->config->get('Widgets','News Feed')['newsOrderByLastUpdated'] ?? false;
        $orderBy = $newsOrderByLastUpdated ? "updated" : "created";
        $stmt = $this->db->prepare("SELECT title, content, created, updated FROM news ORDER BY ".$orderBy." DESC LIMIT :limit");
        $stmt->execute([':limit' => $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}