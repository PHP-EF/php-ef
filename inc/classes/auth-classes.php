<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Predis\Client;

class CoreJwt {
    private $redis;
    private $config;
    private $logging;

    public function __construct($core) {
        $this->redis = new Client(); // Connect to Redis
        $this->config = $core->config;
        $this->logging = $core->logging;
    }

    // Generate a JWT
    public function generateToken($UN,$FN,$SN,$EM,$Groups) {
        $payload = [
          'iat' => time(), // Issued at
          'exp' => time() + (86400 * 30), // Expiration time (30 days)
          'username' => $UN,
          'firstname' => $FN,
          'surname' => $SN,
          'email' => $EM,
          'fullname' => $FN.' '.$SN,
          'groups' => $Groups
        ];
        $this->logging->writeLog("Authentication","Issued JWT token","debug",$payload);
        return JWT::encode($payload, $this->config->getConfig()['Security']['salt'], 'HS256');
    }

    // Revoke a token
    public function revokeToken($token) {
        $decoded = JWT::decode($token, new Key($this->config->getConfig()['Security']['salt'], 'HS256'));
        $this->redis->set($token, json_encode($decoded), 'EX', (86400 * 30)); // Store token with expiration
        $this->logging->writeLog("Authentication","Revoked JWT token","debug",$decoded);
    }

    // Check if a token is revoked
    public function isRevoked($token) {
        return $this->redis->exists($token);
    }
}

class Auth {
  private $db;
  private $config;
  private $logging;
  private $CoreJwt;
  private $sso;

  public function __construct($core,$dbFile) {
    // Create or open the SQLite database
    $this->db = new PDO("sqlite:$dbFile");
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->createUsersTable();

    // Set Config
    $this->config = $core->config;
    $this->logging = $core->logging;

    // CoreJwt
    $this->CoreJwt = new CoreJwt($core);

    // SSO
    $this->sso = new OneLogin\Saml2\Auth($this->config->getConfig("SAML"));
  }

  private function createUsersTable() {
    // Create users table if it doesn't exist
    $this->db->exec("CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT UNIQUE,
      firstname TEXT,
      surname TEXT,
      email TEXT,
      password TEXT,
      salt TEXT,
      groups TEXT,
      created DATE,
      lastlogin DATE,
      passwordexpires DATE
    )");
  }

  private function hashAndSalt($password) {
    // Generate a random salt
    $salt = bin2hex(random_bytes(16)); // Generates a 32-character hexadecimal salt
    // Concatenate the salt with the password
    $saltedPassword = $salt . $password;
    // Hash the salted password
    $hashedPassword = password_hash($saltedPassword, PASSWORD_DEFAULT);
    return array(
      'hash' => $hashedPassword,
      'salt' => $salt
    );
  }

  private function isPasswordComplex($password) {
    // Define the complexity criteria
    $length = strlen($password) >= 8; // Minimum length of 8
    $uppercase = preg_match('/[A-Z]/', $password); // At least one uppercase letter
    $lowercase = preg_match('/[a-z]/', $password); // At least one lowercase letter
    $number = preg_match('/[0-9]/', $password); // At least one number
    $specialChar = preg_match('/[\W_]/', $password); // At least one special character

    // Check if all criteria are met
    if ($length && $uppercase && $lowercase && $number && $specialChar) {
        return true; // Password is complex
    } else {
        return false; // Password is not complex
    }
  }

  public function newUser($username, $password, $firstname = '', $surname = '', $email = '', $groups = '') {
    if ($this->isPasswordComplex($password)) {
      // Hash the password for security
      $pepper = $this->hashAndSalt($password);
      // Get current date/time
      $currentDateTime = date('Y-m-d H:i:s');
      $passwordExpiryDate = new DateTime();
      $passwordExpiryDate->modify('+90 days');
      $passwordExpires = $passwordExpiryDate->format('Y-m-d H:i:s');

      $stmt = $this->db->prepare("INSERT INTO users (username, firstname, surname, email, password, salt, groups, created, passwordexpires) VALUES (:username, :firstname, :surname, :email, :password, :salt, :groups, :created, :passwordexpires)");
      
      try {
          // Check if username already exists
          $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
          $checkStmt->execute([':username' => $username]);
          if ($checkStmt->fetchColumn() > 0) {
              return array(
                  'Status' => 'Error',
                  'Message' => 'Username already exists'
              );
          }
      } catch (PDOException $e) {
          return array(
              'Status' => 'Error',
              'Message' => $e
          );
      }

      try {
        $stmt->execute([':username' => $username, ':firstname' => $firstname, ':surname' => $surname, ':email' => $email, ':password' => $pepper['hash'], ':salt' => $pepper['salt'], ':groups' => $groups, ':created' => $currentDateTime, ':passwordexpires' => $passwordExpires]);
          return array(
              'Status' => 'Success',
              'Message' => 'Created user successfully'
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
        'Message' => 'Password does not meet the complexity requirements'
      );
    }
  }

  public function getUser($id = null, $username = null) {
    if ($id != null) {
      $stmt = $this->db->prepare("SELECT id, username, firstname, surname, email, groups, created, lastlogin, passwordexpires FROM users WHERE id = :id");
      $stmt->execute([':id' => $id]);
    } else if ($username != null) {
      $stmt = $this->db->prepare("SELECT id, username, firstname, surname, email, groups, created, lastlogin, passwordexpires FROM users WHERE username = :username");
      $stmt->execute([':username' => $username]);
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
      return $user;
    } else {
      return false;
    }
  }

  public function updateUser($id,$username,$password,$firstname,$surname,$email,$groups) {
    if ($this->getUser($id)) {
      // Hash the password for security
      $prepare = [];
      $execute = [];
      $execute[':id'] = $id;
      if ($password !== null) {
        $pepper = $this->hashAndSalt($password);
        $prepare[] = 'password = :password';
        $prepare[] = 'salt = :salt';
        $execute[':password'] = $pepper['hash'];
        $execute[':salt'] = $pepper['salt'];
      }
      if ($username !== null) {
        $prepare[] = 'username = :username';
        $execute[':username'] = $username;
      }
      if ($firstname !== null) {
        $prepare[] = 'firstname = :firstname';
        $execute[':firstname'] = $firstname;
      }
      if ($surname !== null) {
        $prepare[] = 'surname = :surname';
        $execute[':surname'] = $surname;
      }
      if ($email !== null) {
        $prepare[] = 'email = :email';
        $execute[':email'] = $email;
      }
      if ($groups !== null) {
        $prepare[] = 'groups = :groups';
        $execute[':groups'] = $groups;
      }
      $stmt = $this->db->prepare('UPDATE users SET '.implode(", ",$prepare).' WHERE id = :id');
      $stmt->execute($execute);
      return array(
        'Status' => 'Success',
        'Message' => 'User updated successfully'
      );
    } else {
      return array(
        'Status' => 'Error',
        'Message' => 'User does not exist'
      );
    }
  }

  public function removeUser($id) {
    if ($this->getUser($id)) {
      $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
      $stmt->execute([':id' => $id]);
      if ($this->getUser($id)) {
        return array(
          'Status' => 'Error',
          'Message' => 'Failed to delete user'
        );
      } else {
        return array(
          'Status' => 'Success',
          'Message' => 'User deleted successfully'
        );
      }
    }
  }

  public function getAllUsers() {
    $stmt = $this->db->prepare("SELECT id, username, firstname, surname, email, groups, created, lastlogin, passwordexpires FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!is_array($users)) {
        $usermap = array(
          'id' => $users['id'],
          'username' => $users['username'],
          'firstname' => $users['firstname'],
          'surname' => $users['surname'],
          'email' => $users['email'],
          'groups' => explode(',',$users['groups']),
          'created' => $users['created'],
          'lastlogin' => $users['lastlogin'],
          'passwordexpires' => $users['passwordexpires'],
        );
    } else {
      foreach ($users as $user) {
        $usermap[] = array(
          'id' => $user['id'],
          'username' => $user['username'],
          'firstname' => $user['firstname'],
          'surname' => $user['surname'],
          'email' => $user['email'],
          'groups' => explode(',',$user['groups']),
          'created' => $user['created'],
          'lastlogin' => $user['lastlogin'],
          'passwordexpires' => $user['passwordexpires'],
        );
      }
    }
    return $usermap;
  }

  public function login($username, $password) {
    $stmt = $this->db->prepare("SELECT id, username, password, salt, firstname, surname, email, groups FROM users WHERE username = :username OR email = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($user['salt'].$password, $user['password'])) { // Login Successful
      // Update last login
      $currentDateTime = date('Y-m-d H:i:s');
      $stmt = $this->db->prepare("UPDATE users SET lastlogin = :lastlogin WHERE id = :id");
      $stmt->execute([':id' => $user['id'], ':lastlogin' => $currentDateTime]);

      // Generate JWT token
      $jwt = $this->CoreJwt->generateToken($user['username'],$user['firstname'],$user['surname'],$user['email'],explode(',',$user['groups']));
      // Set JWT as a cookie
      setcookie('jwt', $jwt, time() + (86400 * 30), "/"); // 30 days

      $Arr = array(
        'Status' => 'Success',
        'Location' => '/'
      );
      $this->logging->writeLog("Authentication",$username." successfully logged in","info",$Arr);
      return $Arr;
    } else { // Login failed
      $Arr = array(
        'Status' => 'Error',
        'Message' => 'Invalid Credentials'
      );
      $this->logging->writeLog("Authentication",$username." failed to log in","warning",$Arr);
      return $Arr;
    }
  }

  public function logout() {
    $this->CoreJwt->revokeToken($_COOKIE['jwt']);
    return $this->getAuth();
  }

  public function sso() {
    $this->sso->login();
  }

  public function slo() {
    $callback = function () {
      $this->logout();
    };
    $this->sso->processSLO(false, null, false, $callback);
  }

  public function acs() {
    // Implement Redis for expired assertions
    $this->sso->processResponse();

    if ($this->sso->isAuthenticated()) {
        // User is authenticated
        echo json_encode(array(
          'samlUserdata' => $this->sso->getAttributes(),
          'samlNameId' => $this->sso->getNameId(),
          'samlNameIdFormat' => $this->sso->getNameIdFormat(),
          'samlNameidNameQualifier' => $this->sso->getNameIdNameQualifier(),
          'samlNameidSPNameQualifier' => $this->sso->getNameIdSPNameQualifier(),
          'samlSessionIndex' => $this->sso->getSessionIndex()
        ));
    } else {
        // Authentication failed
        echo "Authentication failed.";
    }
  }
  
  public function getAuth() {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $IPAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
      $IPAddress = $_SERVER['REMOTE_ADDR'];
    } else {
      $IPAddress = "N/A";
    }
    $IPAddress = explode(':',$IPAddress)[0];
    
    if (isset($_COOKIE['jwt'])) {
      $secretKey = $this->config->getConfig()['Security']['salt']; // Change this to a secure key
      if ($this->CoreJwt->isRevoked($_COOKIE['jwt']) == true) {
        // Token is invalid
        $AuthResult = array(
          'Authenticated' => false,
          'IPAddress' => $IPAddress,
          'Groups' => [
            'everyone'
          ]
        );
        return $AuthResult;
      } else {
        try {
          $decodedJWT = JWT::decode($_COOKIE['jwt'], new Key($secretKey, 'HS256'));
        } catch (Exception $e) {
          return array(
            'Status' => 'Error',
            'Message' => $e->getMessage()
          );
        }
      }
 
      if ($decodedJWT) {
        if (isset($decodedJWT->username)) {
          $Username = $decodedJWT->username;
        } else {
          $Username = null;
        }
  
        $FullNameArr = [];
        
        if (isset($decodedJWT->firstname)) {
          $Firstname = $decodedJWT->firstname;
          $FullNameArr[] = $Firstname;
        } else {
          $Firstname = null;
        }

        if (isset($decodedJWT->surname)) {
          $Surname = $decodedJWT->surname;
          $FullNameArr[] = $Surname;
        } else {
          $Surname = null;
        }

        if (isset($FullNameArr)) {
          $FullName = implode(' ',$FullNameArr);
        }

        if (isset($decodedJWT->email)) {
          $Email = $decodedJWT->email;
        } else {
          $Email = null;
        }
  
        if (isset($decodedJWT->groups[0]) && $decodedJWT->groups[0] != "") {
          $decodedJWT->groups[] = 'authenticated';
          $decodedJWT->groups[] = 'everyone';
          $Groups = $decodedJWT->groups;
        } else {
          $Groups = [
            'authenticated',
            'everyone'
          ];
        }
  
        if (isset($decodedJWT->email)) {
          $Email = $decodedJWT->email;
        } else {
          $Email = null;
        }
  
        $AuthResult = array(
          'Authenticated' => true,
          'Username' => $Username,
          'Firstname' => $Firstname,
          'Surname' => $Surname,
          'Email' => $Email,
          'DisplayName' => $FullName,
          'EmailAddress' => $Email,
          'IPAddress' => $IPAddress,
          'Groups' => $Groups
        );
      } else {
        $AuthResult = array(
          'Authenticated' => false,
          'IPAddress' => $IPAddress,
          'Groups' => ['everyone']
        );
      }
    } else {
      $AuthResult = array(
        'Authenticated' => false,
        'IPAddress' => $IPAddress,
        'Groups' => ['everyone']
      );
    }
    return $AuthResult;
  }

  public function checkAccess($User,$Service = null,$Menu = null) {
    if ($User == null) {
      $User = $this->getAuth();
    }
    if (isset($User['Authenticated'])) {
      $rbacJson = file_get_contents(__DIR__.'/../'.$this->config->getConfig("System","rbacjson"));
      $rbac = json_decode($rbacJson, true);
      if (isset($User['Groups'])) {
        $usergroups = $User['Groups'];
        if ($Service != null) {
          $Services = explode(',',$Service);
          foreach ($Services as $ServiceToCheck) {
            foreach ($usergroups as $usergroup) {
              if (isset($rbac[$usergroup])) {
                if (in_array($ServiceToCheck,$rbac[$usergroup]['PermittedResources'])) {
                  return true;
                }
              }
            }
          }
        }
        if ($Menu != null) {
          foreach ($usergroups as $usergroup) {
            if (isset($rbac[$usergroup])) {
              if (in_array($Menu,$rbac[$usergroup]['PermittedMenus'])) {
                return true;
              }
            }
          }
        }
      } else {
        return false;
      }
    } else {
      return false;
    }
    return false;
  }

  public function signinRedirect() {
    if ($this->getAuth()['Authenticated']) {
    } else {
      echo '<script>top.window.location = "/login.php?redirect_uri="+parent.window.location.href.replace("#","?")</script>';
    }
  }
}

class RBAC {
  private $rbacJson;
  private $rbacInfo;
  private $config;
  private $logging;

  public function __construct($core) {
    // Set Config
    $this->config = $core->config;
    $this->logging = $core->logging;

    // Create or open the RBAC Configuration
    $this->rbacJson = __DIR__.'/../'.$this->config->getConfig("System","rbacjson");
    $this->rbacInfo = __DIR__.'/../'.$this->config->getConfig("System","rbacinfo");
  }

  public function getRBAC($Group = null,$Action = null) {
    $this->logging->writeLog("RBAC","Queried RBAC List","debug",$_REQUEST);
    $rbacJson = file_get_contents($this->rbacJson);
    $rbac = json_decode($rbacJson, true);
    switch ($Action) {
      case 'listgroups':
        $splat = array();
        foreach ($rbac as $group => $groupval) {
          $newArr = array(
            "id" => $group,
            "Group" => $groupval['Name'],
            "Description" => $groupval['Description']
          );
          array_push($splat,$newArr);
        }
        return $splat;
      case 'listconfigurablegroups':
        $splat = array();
        foreach ($rbac as $group => $groupval) {
          // Exclude SYSTEM Groups
          if ($group != 'authenticated' && $group != 'everyone') {
            $newArr = array(
              "id" => $group,
              "Group" => $groupval['Name'],
              "Description" => $groupval['Description']
            );
            array_push($splat,$newArr);
          }
        }
        return $splat;
      case 'listroles':
        $rbacJson = file_get_contents($this->rbacInfo);
        $rbac = json_decode($rbacJson, true);
        return $rbac;
      default:
        if ($Group != null) {
          return $rbac[$Group];
        } else {
          return $rbac;
        }
    }
  }
  
  public function setRBAC($GroupID,$GroupName,$Description = null,$Key = null,$Value = null) {
    $rbac = $this->getRBAC();
    $roles = $this->getRBAC(null,"listroles");
    if (array_key_exists($GroupID,$rbac)) {
      if ($Description != null) {
        $rbac[$GroupID]['Description'] = $Description;
        file_put_contents($this->rbacJson, json_encode($rbac, JSON_PRETTY_PRINT));
        $this->logging->writeLog("RBAC","Updated description for: ".$rbac[$GroupID]['Name'],"info",$rbac[$GroupID]);
      }
      if ($Key != null) {
        if (array_key_exists($Key,$roles['Resources'])) {
          if ($Value == "true") {
            ## Add Key to Array
            if (in_array($Key,$rbac[$GroupID]['PermittedResources'])) {
              $this->logging->writeLog("RBAC","$Key is already assigned to ".$rbac[$GroupID]['Name'],"debug",$Key,$rbac[$GroupID]);
            } else {
              array_push($rbac[$GroupID]['PermittedResources'],$Key);
              file_put_contents($this->rbacJson, json_encode($rbac, JSON_PRETTY_PRINT));
              $this->logging->writeLog("RBAC","Added $Key to ".$rbac[$GroupID]['Name'],"warning",$rbac[$GroupID]);
            }
  
            ## Add Menus to Array
            foreach ($roles['Resources'][$Key]['PermittedMenus'] as $PermittedMenu) {
              if (in_array($PermittedMenu,$rbac[$GroupID]['PermittedMenus'])) {
                $this->logging->writeLog("RBAC","$PermittedMenu is already assigned to: ".$rbac[$GroupID]['Name'],"debug",$rbac[$GroupID]);
              } else {
                array_push($rbac[$GroupID]['PermittedMenus'],$PermittedMenu);
                file_put_contents($this->rbacJson, json_encode($rbac, JSON_PRETTY_PRINT));
                $this->logging->writeLog("RBAC","Added Menu: $PermittedMenu to ".$rbac[$GroupID]['Name'],"info",$rbac[$GroupID]);
              }
            }
          } else if ($Value == "false") {
            ## Remove Key from Array
            if (in_array($Key,$rbac[$GroupID]['PermittedResources'])) {
              if (($keytoremove = array_search($Key, $rbac[$GroupID]['PermittedResources'])) !== false) {
                unset($rbac[$GroupID]['PermittedResources'][$keytoremove]);
                $rbac[$GroupID]['PermittedResources'] = array_values($rbac[$GroupID]['PermittedResources']);
                file_put_contents($this->rbacJson, json_encode($rbac, JSON_PRETTY_PRINT));
                $this->logging->writeLog("RBAC","Removed $Key from ".$rbac[$GroupID]['Name'],"warning",$rbac[$GroupID]);
              }
            } else {
              $this->logging->writeLog("RBAC","$Key is not assigned to ".$rbac[$GroupID]['Name'],"error",$Key,$rbac[$GroupID]);
            }
            ## Remove Menus from Array
            $Needed = false;
            foreach ($roles['Resources'][$Key]['PermittedMenus'] as $PermittedMenu) {
              if (in_array($PermittedMenu,$rbac[$GroupID]['PermittedMenus'])) {
                if (!empty($rbac[$GroupID]['PermittedResources'])) {
                  foreach ($rbac[$GroupID]['PermittedResources'] as $PermittedResource) {
                    if (in_array($PermittedMenu,$roles['Resources'][$PermittedResource]['PermittedMenus'])) {
                      $Needed = true;
                    }
                  }
                } else {
                  foreach ($rbac[$GroupID]['PermittedMenus'] as $PermittedMenu) {
                    if (($menutoremove = array_search($PermittedMenu, $rbac[$GroupID]['PermittedMenus'])) !== false) {;
                      unset($rbac[$GroupID]['PermittedMenus'][$menutoremove]);
                    }
                  }
                  $rbac[$GroupID]['PermittedMenus'] = array_values($rbac[$GroupID]['PermittedMenus']);
                  file_put_contents($this->rbacJson, json_encode($rbac, JSON_PRETTY_PRINT));
                  $this->logging->writeLog("RBAC","No permitted resources left, removing permitted menus from ".$rbac[$GroupID]['Name'],"debug",$rbac[$GroupID]);
                }
              } else {
                $this->logging->writeLog("RBAC","$PermittedMenu is not assigned to ".$rbac[$GroupID]['Name'],"error",$Key,$rbac[$GroupID]);
              }
              if (!$Needed) {
                if (($menutoremove = array_search($PermittedMenu, $rbac[$GroupID]['PermittedMenus'])) !== false) {
                  unset($rbac[$GroupID]['PermittedMenus'][$menutoremove]);
                  $rbac[$GroupID]['PermittedMenus'] = array_values($rbac[$GroupID]['PermittedMenus']);
                  file_put_contents($this->rbacJson, json_encode($rbac, JSON_PRETTY_PRINT));
                  $this->logging->writeLog("RBAC","Removed Menu: $PermittedMenu from ".$rbac[$GroupID]['Name'],"info",$rbac[$GroupID]);
                }
              }
            }
          }
        } else {
          return "Error. Invalid RBAC Option specified: ".$Key.".";
        }
      }
    } else {
      $NewNode = array(
          "Name" => $GroupName,
          "Description" => $Description,
          "PermittedResources" => array(),
          "PermittedMenus" => array()
      );
      $rbac[$GroupID] = $NewNode;
      file_put_contents($this->rbacJson, json_encode($rbac, JSON_PRETTY_PRINT));
    }
    return $rbac;
  }
  
  public function deleteRBAC($Group) {
    $this->logging->writeLog("RBAC","Deleted RBAC Group: $Group","debug",$_REQUEST);
    $rbacJson = file_get_contents($this->rbacJson);
    $rbacArr = json_decode($rbacJson, true);
    if (array_key_exists($Group,$rbacArr)) {
      $this->logging->writeLog("RBAC","Deleted RBAC Group: $Group","debug",$_REQUEST);
      unset($rbacArr[$Group]);
      file_put_contents($this->rbacJson, json_encode($rbacArr, JSON_PRETTY_PRINT));
    } else {
      $this->logging->writeLog("RBAC","Error deleting RBAC Group: $Group. The group does not exist.","error",$_REQUEST);
    }
    return $rbacArr;
  }
}