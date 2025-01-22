<?php
class db {
    public $db;
    private $logging;
    private $version;
  
    public function __construct($dbFile,$core,$version) {
      // Create or open the SQLite database
      $this->db = new PDO("sqlite:$dbFile");
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->logging = $core->logging;
      $this->version = $version;
      $this->createOptionsTable();
    }
  
    private function createOptionsTable() {
      // Create options table if it doesn't exist
      $dbHelper = new dbHelper($this->db);
      if (!$dbHelper->tableExists("options")) {
        if ($this->db->query("CREATE TABLE IF NOT EXISTS options (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          Key TEXT,
          Value TEXT
        )")) {
          $this->db->exec('INSERT INTO options (key,value) VALUES ("dbVersion","'.$this->version.'");');
        };
      }
    }
}

class dbHelper {
  private $pdo;

  public function __construct($pdo) {
    $this->pdo = $pdo;
  }

  public function tableExists($tableName) {
    $stmt = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=:table");
    $stmt->execute([':table' => $tableName]);
    return $stmt->fetch() !== false;
  }

  public function getDatabaseVersion() {
    $stmt = $this->pdo->prepare("SELECT * FROM options WHERE Key = 'dbVersion'");
    $stmt->execute();
    return $stmt->fetch()['Value'];
  }
  
  public function updateDatabaseVersion($newVersion) {
    $stmt = $this->pdo->prepare("UPDATE options SET Value = :dbVersion WHERE Key = 'dbVersion'");
    if ($stmt->execute([':dbVersion' => $newVersion])) {
      return true;
    };
  }

  public function queryDBWithParams($table,$params,$searchColumns) {
    $query = 'SELECT * FROM '.$table.' WHERE 1=1';
    
    // Filtering
    if (!empty($params['filter'])) {
        foreach ($params['filter'] as $field => $value) {
            $query .= ' AND ' . $field . ' LIKE :filter_' . $field;
        }
    }

    // Searching
    if (!empty($params['search'])) {
        $SearchColumnQuery = '';
        $ColumnCount = count($searchColumns);
        $ColumnNo = 0;
        foreach ($searchColumns as $searchColumn) {
          $ColumnNo++;
          $SearchColumnQuery .= $searchColumn . ' LIKE :search';
          if ($ColumnNo != $ColumnCount) {
            $SearchColumnQuery .= ' OR ';
          }
        }
        $query .= ' AND '.$SearchColumnQuery;
    }

    // Ordering
    if (!empty($params['sort']) && !empty($params['order'])) {
        $query .= ' ORDER BY ' . $params['sort'] . ' ' . $params['order'];
    }

    // Paging
    $limit = !empty($params['limit']) ? (int)$params['limit'] : 25;
    $offset = !empty($params['offset']) ? (int)$params['offset'] : 0;
    $query .= ' LIMIT :limit OFFSET :offset';

    $stmt = $this->pdo->prepare($query);

    // Bind parameters
    if (!empty($params['filter'])) {
        foreach ($params['filter'] as $field => $value) {
            $stmt->bindValue(':filter_' . $field, '%' . $value . '%');
        }
    }
    if (!empty($params['search'])) {
        $stmt->bindValue(':search', '%' . $params['search'] . '%');
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
    
  public function updateDatabaseSchema($currentVersion, $newVersion, $schema) {
    $allUpdates = array_merge(['0.0.0' => []], $schema);

    // Add the new version if it's not already in the updates list
    if (!array_key_exists($newVersion, $allUpdates)) {
        $allUpdates[$newVersion] = [];
    }

    // Sort versions to maintain the correct order
    uksort($allUpdates, 'version_compare');

    $versions = array_keys($allUpdates);
    $currentIndex = array_search($currentVersion, $versions);
    if ($currentIndex === false) {
        $currentIndex = 0;
    }
    $newIndex = array_search($newVersion, $versions);

    if ($currentIndex >= $newIndex) {
        echo "Invalid version update path from $currentVersion to $newVersion.";
        die();
    }

    try {
        $this->pdo->beginTransaction();
        for ($i = $currentIndex + 1; $i <= $newIndex; $i++) {
            $version = $versions[$i];
            if (isset($allUpdates[$version])) {
                foreach ($allUpdates[$version] as $query) {
                    // Execute the migration query
                    $this->pdo->exec($query);
                }
            }
        }
        // Commit the transaction
        $this->pdo->commit();
        // Update the database version
        $this->updateDatabaseVersion($newVersion);
        echo "Database schema updated successfully from version $currentVersion to $newVersion. Refresh the page to reload.";
    } catch (Exception $e) {
        // Roll back the transaction in case of an error
        $this->pdo->rollBack();
        echo "Failed to update database schema: " . $e->getMessage();
        die();
    }
  }

  public function migrationScripts() {
    return [
      '0.7.0' => [
        "ALTER TABLE pages ADD COLUMN Weight INTEGER", // Add Weight Column to Pages
        'UPDATE pages
        SET Weight = (
            SELECT COUNT(*)
            FROM pages AS t2
            WHERE t2.Weight <= pages.Weight
        );' // Populate Initial Weights
      ],
      '0.7.1' => [],
      '0.7.2' => [
        "ALTER TABLE pages ADD COLUMN LinkType INTEGER", // Add Weight Column to Pages
        "UPDATE pages SET LinkType = 'Native'"
      ],
      '0.7.3' => [],
      '0.7.4' => [],
      '0.7.5' => [
        "UPDATE pages SET Url = SUBSTR(url, 7) WHERE Url LIKE '#page=%';"
      ],
      '0.7.6' => [
        "DELETE FROM pages WHERE Url IN ('core/users','core/rbac');"
      ],
      '0.7.7' => [
        "DELETE FROM pages WHERE Url = 'core/pages';"
      ]
    ];
  }

	public function replaceStringInDatabase($string) {
		$databaseStringList = [
			'AUTOINCREMENT' => [
				'sqlite3' => 'AUTOINCREMENT',
				'mysqli' => 'AUTO_INCREMENT',
				'postgre' => 'AUTOINCREMENT'
			],
			'COLLATE NOCASE' => [
				'sqlite3' => 'COLLATE NOCASE',
				'mysqli' => '',
				'postgre' => ''
			],
			'INTEGER PRIMARY KEY AUTOINCREMENT' => [
				'sqlite3' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
				'mysqli' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
				'postgre' => 'SERIAL PRIMARY KEY'
			],
			'DATETIME' => [
				'sqlite3' => 'DATETIME',
				'mysqli' => 'DATETIME',
				'postgre' => 'TIMESTAMP'
			],
		];
		if (gettype($string) == 'string') {
			foreach ($databaseStringList as $item => $value) {
				if (stripos($string, $item) !== false) {
					$string = str_ireplace($item, $databaseStringList[$item][$this->config['driver']], $string);
				}
			}
		}
		return $string;
	}

	public function cleanDatabaseQuery($query)
	{
		if (is_array($query)) {
			foreach ($query as $key => $value) {
				$query[$key] = $this->cleanDatabaseQuery($value);
			}
			return $query;
		} else {
			return $this->replaceStringInDatabase($query);
		}
	}

	public function processQueries(array $request) {
    global $phpef;
		$results = array();
		$firstKey = '';
		foreach ($request as $k => $v) {
			try {
				$v['query'] = $this->cleanDatabaseQuery($v['query']);
				$query = $this->pdo->query($v['query']);
				$keyName = (isset($v['key'])) ? $v['key'] : $k;
				$firstKey = (isset($v['key']) && $k == 0) ? $v['key'] : $k;
				switch ($v['function']) {
					case 'fetchAll':
						$results[$keyName] = $query->fetchAll();
						break;
					case 'fetch':
						// PHP 8 Fix?
						// $query->setRowClass(null);
						$results[$keyName] = $query->fetch();
						break;
					case 'getAffectedRows':
						$results[$keyName] = $query->getAffectedRows();
						break;
					case 'getRowCount':
						$results[$keyName] = $query->getRowCount();
						break;
					case 'fetchSingle':
						// PHP 8 Fix?
						// $query->setRowClass(null);
						$results[$keyName] = $query->fetchSingle();
						break;
					case 'query':
						$results[$keyName] = $query;
						break;
					default:
						return false;
				}
			} catch (Exception $e) {
				$phpef->logging->writeLog('Database', $e, 'error', [$v['query']]);
        $phpef->api->setAPIResponse('Error',$e,null,$v['query']);
				return false;
			}
		}
		return count($request) > 1 ? $results : $results[$firstKey];
	}
}
