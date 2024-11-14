<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Predis\Client;

class CoreJwt {
    private $redis;

    public function __construct() {
        $this->redis = new Client(); // Connect to Redis
    }

    // Generate a JWT
    public function generateToken($UN,$Groups) {
        $payload = [
          'iat' => time(), // Issued at
          'exp' => time() + (86400 * 30), // Expiration time (30 days)
          'username' => $UN,
          'name' => null,
          'groups' => $Groups
        ];
        writeLog("Authentication","Issued JWT token","debug",$payload);
        return JWT::encode($payload, getConfig()['Security']['salt'], 'HS256');
    }

    // Revoke a token
    public function revokeToken($token) {
        $decoded = JWT::decode($token, new Key(getConfig()['Security']['salt'], 'HS256'));
        $this->redis->set($token, json_encode($decoded), 'EX', (86400 * 30)); // Store token with expiration
        writeLog("Authentication","Revoked JWT token","debug",$decoded);
    }

    // Check if a token is revoked
    public function isRevoked($token) {
        return $this->redis->exists($token);
    }
}

class Auth {
  private $db;

  public function __construct($dbFile) {
    // Create or open the SQLite database
    $this->db = new PDO("sqlite:$dbFile");
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->createUsersTable();
  }

  private function createUsersTable() {
    // Create users table if it doesn't exist
    $this->db->exec("CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT UNIQUE,
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

  public function newUser($username, $password, $groups = '') {
    if ($this->isPasswordComplex($password)) {
      // Hash the password for security
      $pepper = $this->hashAndSalt($password);
      // Get current date/time
      $currentDateTime = date('Y-m-d H:i:s');
      $passwordExpiryDate = new DateTime();
      $passwordExpiryDate->modify('+90 days');
      $passwordExpires = $passwordExpiryDate->format('Y-m-d H:i:s');

      $stmt = $this->db->prepare("INSERT INTO users (username, password, salt, groups, created, passwordexpires) VALUES (:username, :password, :salt, :groups, :created, :passwordexpires)");
      
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
        $stmt->execute([':username' => $username, ':password' => $pepper['hash'], ':salt' => $pepper['salt'], ':groups' => $groups, ':created' => $currentDateTime, ':passwordexpires' => $passwordExpires]);
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
      $stmt = $this->db->prepare("SELECT id, username, groups, created, lastlogin, passwordexpires FROM users WHERE id = :id");
      $stmt->execute([':id' => $id]);
    } else if ($username != null) {
      $stmt = $this->db->prepare("SELECT id, username, groups, created, lastlogin, passwordexpires FROM users WHERE username = :username");
      $stmt->execute([':username' => $username]);
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
      return $user;
    } else {
      return false;
    }
  }

  public function updateUser($id,$username,$password,$groups) {
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
    $stmt = $this->db->prepare("SELECT id, username, groups, created, lastlogin, passwordexpires FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!is_array($users)) {
        $usermap = array(
          'id' => $users['id'],
          'username' => $users['username'],
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
    $stmt = $this->db->prepare("SELECT id, password, salt, groups FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($user['salt'].$password, $user['password'])) { // Login Successful
      // Update last login
      $currentDateTime = date('Y-m-d H:i:s');
      $stmt = $this->db->prepare("UPDATE users SET lastlogin = :lastlogin WHERE id = :id");
      $stmt->execute([':id' => $user['id'], ':lastlogin' => $currentDateTime]);

      // Generate JWT token
      $CoreJwt = new CoreJwt();
      $jwt = $CoreJwt->generateToken($username,explode(',',$user['groups']));
      // Set JWT as a cookie
      setcookie('jwt', $jwt, time() + (86400 * 30), "/"); // 30 days

      $Arr = array(
        'Status' => 'Success',
        'Location' => '/'
      );
      writeLog("Authentication",$username." successfully logged in","info",$Arr);
      return $Arr;
    } else { // Login failed
      $Arr = array(
        'Status' => 'Error',
        'Message' => 'Invalid Credentials'
      );
      writeLog("Authentication",$username." failed to log in","warning",$Arr);
      return $Arr;
    }
  }

  public function logout() {
    $CoreJwt = new CoreJwt();
    $CoreJwt->revokeToken($_COOKIE['jwt']);
    return $this->getAuth();
  }
  
  public function getAuth() {
    $CoreJwt = new CoreJwt();
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $IPAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
      $IPAddress = $_SERVER['REMOTE_ADDR'];
    } else {
      $IPAddress = "N/A";
    }
    $IPAddress = explode(':',$IPAddress)[0];
    
    if (isset($_COOKIE['jwt'])) {
      $secretKey = getConfig()['Security']['salt']; // Change this to a secure key
      if ($CoreJwt->isRevoked($_COOKIE['jwt']) == true) {
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
  
        if (isset($decodedJWT->name)) {
          $Name = $decodedJWT->name;
        } else {
          $Name = null;
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
          'DisplayName' => $Name,
          'EmailAddress' => $Email,
          'IPAddress' => $IPAddress,
          'Groups' => $Groups
        );
      } else {
        $AuthResult = array(
          'Authenticated' => false,
          'IPAddress' => $IPAddress,
          'Groups' => 'everyone'
        );
      }
    } else {
      $AuthResult = array(
        'Authenticated' => false,
        'IPAddress' => $IPAddress,
        'Groups' => 'everyone'
      );
    }
    return $AuthResult;
  }

  public function checkAccess($User,$Service = null,$Menu = null) {
    if ($User == null) {
      $User = $this->getAuth();
    }
    if (isset($User['Authenticated'])) {
      $rbacJson = file_get_contents(__DIR__.'/../'.getConfig("System","rbacjson"));
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