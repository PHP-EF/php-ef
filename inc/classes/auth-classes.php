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
    public function generateToken($UN,$FN,$SN,$EM,$Groups,$Type) {
        $payload = [
          'iat' => time(), // Issued at
          'exp' => time() + (86400 * 30), // Expiration time (30 days)
          'username' => $UN,
          'firstname' => $FN,
          'surname' => $SN,
          'email' => $EM,
          'fullname' => $FN.' '.$SN,
          'groups' => $Groups,
          'type' => $Type
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

    // Revoke a SAML Assertion
    public function revokeAssertion($assertion,$userid,$seconds) {
      $this->redis->set($assertion, $userid, 'EX', $seconds); // Store assertion with expiration
      $RevokeArr = array(
        'assertion' => $assertion,
        'userid' => $userid,
        'seconds' => $seconds
      );
      $this->logging->writeLog("Authentication","Revoked SAML Assertion","debug",$RevokeArr);
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

  public function __construct($core,$db) {
    $this->db = $db;
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
      email TEXT UNIQUE,
      password TEXT,
      salt TEXT,
      groups TEXT,
      created DATE,
      lastlogin DATE,
      passwordexpires DATE,
      type TEXT
    )");

    // Check if the users table is empty and define default admin account if it is
    $result = $this->db->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    if ($row['count'] == 0) {
        $this->newUser('admin', 'Admin123!', 'Admin', 'User', '', 'Administrators', 'Local', 'true');
    }
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

  private function random_password($length) {
    // Define the characters to use in the password
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!-.[]?*()';
    $password = '';
    $characterListLength = mb_strlen($characters, '8bit') - 1;
    // Generate the password
    foreach (range(1, $length) as $i) {
        $password .= $characters[random_int(0, $characterListLength)];
    }
    return $password;
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

  private function updateLastLogin($id) {
    // Update last login
    $currentDateTime = date('Y-m-d H:i:s');
    $stmt = $this->db->prepare("UPDATE users SET lastlogin = :lastlogin WHERE id = :id");
    $stmt->execute([':id' => $id, ':lastlogin' => $currentDateTime]);
  }

  public function newUser($username, $password, $firstname = '', $surname = '', $email = '', $groups = '', $type = 'Local', $expire = 'false') {
    // Set random password for SSO accounts
    if ($type == 'SSO') {
      $password = $this->random_password(32);
    }
    if ($this->isPasswordComplex($password)) {
      // Hash the password for security
      $pepper = $this->hashAndSalt($password);
      // Get current date/time
      $currentDateTime = date('Y-m-d H:i:s');
      $passwordExpiryDate = new DateTime();
      if ($expire == 'true') {
        $passwordExpiryDate->modify('-1 days');
      } else {
        $passwordExpiryDate->modify('+90 days');
      }
      $passwordExpires = $passwordExpiryDate->format('Y-m-d H:i:s');

      $stmt = $this->db->prepare("INSERT INTO users (username, firstname, surname, email, password, salt, groups, created, passwordexpires, type) VALUES (:username, :firstname, :surname, :email, :password, :salt, :groups, :created, :passwordexpires, :type)");

      try {
          // Check if username or email already exists
          $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
          $checkStmt->execute([':username' => $username, ':email' => $email]);
          if ($checkStmt->fetchColumn() > 0) {
              return array(
                  'Status' => 'Error',
                  'Message' => 'Username or Email already exists'
              );
          }
      } catch (PDOException $e) {
          return array(
              'Status' => 'Error',
              'Message' => $e
          );
      }

      try {
        $stmt->execute([':username' => $username, ':firstname' => $firstname, ':surname' => $surname, ':email' => $email, ':password' => $pepper['hash'], ':salt' => $pepper['salt'], ':groups' => $groups, ':created' => $currentDateTime, ':passwordexpires' => $passwordExpires, ':type' => $type]);
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

  public function getUserById($id,$AllColumns = false) {
    if ($AllColumns) {
      $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
    } else {
      $stmt = $this->db->prepare("SELECT id, username, firstname, surname, email, groups, created, lastlogin, passwordexpires, type FROM users WHERE id = :id");
    }
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
      return $user;
    } else {
      return false;
    }
  }

  public function getUserByUsername($username,$AllColumns = false) {
    if ($AllColumns) {
      $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
    } else {
      $stmt = $this->db->prepare("SELECT id, username, firstname, surname, email, groups, created, lastlogin, passwordexpires, type FROM users WHERE username = :username");
    }
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
      return $user;
    } else {
      return false;
    }
  }

  public function getUserByUsernameOrEmail($username,$email,$AllColumns = false) {
    if ($AllColumns) {
      $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
    } else {
      $stmt = $this->db->prepare("SELECT id, username, firstname, surname, email, groups, created, lastlogin, passwordexpires, type FROM users WHERE username = :username OR email = :email");
    }
    $stmt->execute([':username' => $username,':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
      return $user;
    } else {
      return false;
    }
  }

  public function updateUser($id,$username,$password,$firstname,$surname,$email,$groups) {
    if ($this->getUserById($id)) {
      if ($username || $password) {
        try {
          // Check if username or email already exists
          $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE (username = :username OR email = :email) AND id = :id");
          $checkStmt->execute([':username' => $username, ':email' => $email, ':id' => $id]);
          if ($checkStmt->fetchColumn() > 0) {
              return array(
                  'Status' => 'Error',
                  'Message' => 'Username or Email already exists'
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
      if ($password !== null) {
        // Hash & salt the password for security
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
    if ($this->getUserById($id)) {
      $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
      $stmt->execute([':id' => $id]);
      if ($this->getUserById($id)) {
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


  public function resetExpiredPassword($username,$currentPassword,$newPassword) {
    $user = $this->getUserByUsernameOrEmail($username,$username,true);
    if ($user && password_verify($user['salt'].$currentPassword, $user['password'])) { // Login Successful
      $this->logging->writeLog("Authentication",$username." successfully reset password","info");

      if (isset($user['username'])) {
        if ($user['type'] != 'SSO') {
          if ($this->isPasswordComplex($newPassword)) {
            // Hash the password for security
            $pepper = $this->hashAndSalt($newPassword);

            $currentDateTime = date('Y-m-d H:i:s');
            $passwordExpiryDate = new DateTime();
            $passwordExpiryDate->modify('+90 days');
            $passwordExpires = $passwordExpiryDate->format('Y-m-d H:i:s');

            try {
              $stmt = $this->db->prepare("UPDATE users SET passwordexpires = :passwordexpires, password = :password, salt = :salt WHERE id = :id");
              $stmt->execute([':id' => $user['id'], ':password' => $pepper['hash'], ':salt' => $pepper['salt'], ':passwordexpires' => $passwordExpires]);
              return array(
                'Status' => 'Success',
                'Message' => 'Password reset successfully'
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
              'Message' => 'New password does not meet the complexity requirements'
            );
          }
        } else {
          return array(
            'Status' => 'Error',
            'Message' => 'Cannot reset password for SSO Account'
          );
        }
      } else {
        return array(
          'Status' => 'Error',
          'Message' => 'Failed to retrieve user information'
        );
      }
    } else { // Verify failed
      $this->logging->writeLog("Authentication",$username." failed to reset password","warning");
      return array(
        'Status' => 'Error',
        'Message' => 'The submitted current password is invalid'
      );
    }
  }

  public function resetPassword($password) {
    if ($this->getAuth()['Authenticated']) {
      $CurrentAuth = $this->getAuth();
      $CurrentUser = $this->getUserByUsername($CurrentAuth['Username']);
      if (isset($CurrentUser['username'])) {
        if ($CurrentUser['type'] != 'SSO') {
          if ($this->isPasswordComplex($password)) {
            // Hash the password for security
            $pepper = $this->hashAndSalt($password);

            $currentDateTime = date('Y-m-d H:i:s');
            $passwordExpiryDate = new DateTime();
            $passwordExpiryDate->modify('+90 days');
            $passwordExpires = $passwordExpiryDate->format('Y-m-d H:i:s');

            try {
              $stmt = $this->db->prepare("UPDATE users SET passwordexpires = :passwordexpires, password = :password, salt = :salt WHERE id = :id");
              $stmt->execute([':id' => $CurrentUser['id'], ':password' => $pepper['hash'], ':salt' => $pepper['salt'], ':passwordexpires' => $passwordExpires]);
              return array(
                'Status' => 'Success',
                'Message' => 'Password reset successfully'
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
              'Message' => 'New password does not meet the complexity requirements'
            );
          }
        } else {
          return array(
            'Status' => 'Error',
            'Message' => 'Cannot reset password for SSO Account'
          );
        }
      } else {
        return array(
          'Status' => 'Error',
          'Message' => 'Failed to retrieve user information'
        );
      }
    } else {
      return array(
        'Status' => 'Error',
        'Message' => 'Not Authenticated'
      );
    }
  }

  public function getAllUsers() {
    $stmt = $this->db->prepare("SELECT id, username, firstname, surname, email, groups, created, lastlogin, passwordexpires, type FROM users");
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
          'type' => $users['type']
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
          'type' => $user['type']
        );
      }
    }
    return $usermap;
  }

  public function login($username, $password) {
    $user = $this->getUserByUsernameOrEmail($username,$username,true);
    if ($user && password_verify($user['salt'].$password, $user['password'])) { // Login Successful
      $now = new DateTime();
      $expires = new DateTime($user['passwordexpires']);
      if ($expires < $now)  {
        return array(
          'Status' => 'Expired',
          'Message' => 'Password Expired'
        );
      }
      // Update last login
      $this->updateLastLogin($user['id']);

      // Generate JWT token
      $jwt = $this->CoreJwt->generateToken($user['username'],$user['firstname'],$user['surname'],$user['email'],explode(',',$user['groups']),$user['type']);
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
    $this->sso->processResponse();
    $Login = false;

    if ($this->sso->isAuthenticated()) {
        // User is Authenticated
        $SAMLArr = array(
          'samlUserdata' => $this->sso->getAttributes(),
          'samlNameId' => $this->sso->getNameId(),
          'samlNameIdFormat' => $this->sso->getNameIdFormat(),
          'samlNameidNameQualifier' => $this->sso->getNameIdNameQualifier(),
          'samlNameidSPNameQualifier' => $this->sso->getNameIdSPNameQualifier(),
          'samlSessionIndex' => $this->sso->getSessionIndex()
        );
        $AttributeMap = [];
        if ($this->config->getConfig('SAML','attributes')['Username'] && isset($SAMLArr['samlUserdata'][$this->config->getConfig('SAML','attributes')['Username']])) {
          $AttributeMap['Username'] = $SAMLArr['samlUserdata'][$this->config->getConfig('SAML','attributes')['Username']][0];
        } else {
          $AttributeMap['Username'] = null;
        }
        if ($this->config->getConfig('SAML','attributes')['FirstName'] && isset($SAMLArr['samlUserdata'][$this->config->getConfig('SAML','attributes')['FirstName']])) {
          $AttributeMap['FirstName'] = ucwords($SAMLArr['samlUserdata'][$this->config->getConfig('SAML','attributes')['FirstName']][0]);
        } else {
          $AttributeMap['FirstName'] = null;
        }
        if ($this->config->getConfig('SAML','attributes')['LastName'] && isset($SAMLArr['samlUserdata'][$this->config->getConfig('SAML','attributes')['LastName']])) {
          $AttributeMap['LastName'] = ucwords($SAMLArr['samlUserdata'][$this->config->getConfig('SAML','attributes')['LastName']][0]);
        } else {
          $AttributeMap['LastName'] = null;
        }
        if ($this->config->getConfig('SAML','attributes')['Email'] && isset($SAMLArr['samlUserdata'][$this->config->getConfig('SAML','attributes')['Email']])) {
          $AttributeMap['Email'] = $SAMLArr['samlUserdata'][$this->config->getConfig('SAML','attributes')['Email']][0];
        } else {
          $AttributeMap['Email'] = null;
        }
        if ($this->config->getConfig('SAML','attributes')['Groups'] && isset($SAMLArr['samlUserdata'][$this->config->getConfig('SAML','attributes')['Groups']])) {
            $AttributeMap['Groups'] = implode(',',$SAMLArr['samlUserdata'][$this->config->getConfig('SAML','attributes')['Groups']]);
        } else {
          $AttributeMap['Groups'] = '';
        }
        // Add SAML assertion to redis to prevent re-use
        if ($this->CoreJwt->isRevoked($this->sso->getLastAssertionId())) {
          $Arr = array(
            'Status' => 'Error',
            'Message' => 'SAML Assertion has been revoked'
          );
          $this->logging->writeLog("Authentication",$AttributeMap['Username']." attempted a potential replay attack.","warning",$SAMLArr);
        } else {
          $this->CoreJwt->revokeAssertion($this->sso->getLastAssertionId(), $this->sso->getNameId(), 3600); // Store SAML Assertion for 1 hour to allow for natural expiry

          // Check if matching user exists
          $user = $this->getUserByUsernameOrEmail($AttributeMap['Username'],$AttributeMap['Email']);

          if ($user) {
            // Update last login
            $this->updateLastLogin($user['id']);
            // Update user info from IdP
            $stmt = $this->db->prepare("UPDATE users SET username = :username, firstname = :firstname, surname = :surname, email = :email, groups = :groups WHERE id = :id");
            $stmt->execute([':id' => $user['id'], ':username' => $AttributeMap['Username'], ':firstname' => $AttributeMap['FirstName'], ':surname' => $AttributeMap['LastName'], ':email' => $AttributeMap['Email'], ':groups' => $AttributeMap['Groups']]);
            // Set Login to True
            $Login = true;
            $this->logging->writeLog("Authentication",$AttributeMap['Username']." successfully logged in with SSO","info",$SAMLArr);
            $Arr = array(
              'Status' => 'Success',
              'Message' => 'User logged in'
            );
          } else if ($this->config->getConfig('SAML','AutoCreateUsers')) {
            // User does not exist and will be created
            $NewUser = $this->newUser($AttributeMap['Username'], null, $AttributeMap['FirstName'], $AttributeMap['LastName'], $AttributeMap['Email'], $AttributeMap['Groups'], $type = 'SSO');
            if ($NewUser['Status'] == 'Success') {
              // Update last login
              $this->updateLastLogin($this->getUserByUsernameOrEmail($AttributeMap['Username'],$AttributeMap['Email'])['id']);
              // Set Login to True
              $Login = true;
              $this->logging->writeLog("Authentication",$AttributeMap['Username']." successfully logged in with SSO and new user was created","info",$SAMLArr);
              $Arr = array(
                'Status' => 'Success',
                'Message' => 'User created'
              );
            } else {
              return $NewUser;
            }
          } else {
            // User does not exist and won't be created
            $this->logging->writeLog("Authentication",$AttributeMap['Username']." successfully logged in with SSO, but user does not exist","warning",$SAMLArr);
            $Arr = array(
              'Status' => 'Error',
              'Message' => 'User does not exist'
            );
          }

          if ($Login) {
            // Get latest user info
            $userinfo = $this->getUserByUsernameOrEmail($AttributeMap['Username'],$AttributeMap['Email']);
            // Set Username to Email if Username is not present as an attribute
            if ($userinfo['username'] == "" && $userinfo['email'] != "") {
              $Username = $userinfo['email'];
            } else {
              $Username = $userinfo['username'];
            }

            $LoginArr = array(
              'Username' => $Username,
              'FirstName' => $userinfo['firstname'],
              'LastName' => $userinfo['surname'],
              'Email' => $userinfo['email'],
              'Groups' => explode(',',$userinfo['groups']),
              'Type' => $userinfo['type']
            );

            // Generate JWT token
            $jwt = $this->CoreJwt->generateToken($LoginArr['Username'],$LoginArr['FirstName'],$LoginArr['LastName'],$LoginArr['Email'],$LoginArr['Groups'],$LoginArr['Type']);
            // Set JWT as a cookie
            setcookie('jwt', $jwt, time() + (86400 * 30), "/"); // 30 days
            // Redirect
            header('Location: /');
          }
        }
        return $Arr;
    } else { // Login failed
        $Arr = array(
          'Status' => 'Error',
          'Message' => 'SSO Authentication Failed'
        );
        $this->logging->writeLog("Authentication","User failed to log in with SSO","warning");
        return $Arr;
      }
  }

  public function getSamlMetadata() {
    try {
      $settings = $this->sso->getSettings();
      $metadata = $settings->getSPMetadata();
      $errors = $settings->validateMetadata($metadata);
      if (empty($errors)) {
          header('Content-Type: text/xml');
          return $metadata;
      } else {
          throw new OneLogin_Saml2_Error(
              'Invalid SP metadata: '.implode(', ', $errors),
              OneLogin_Saml2_Error::METADATA_SP_INVALID
          );
      }
    } catch (Exception $e) {
        return $e->getMessage();
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
            'Everyone'
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
          $decodedJWT->groups[] = 'Authenticated';
          $decodedJWT->groups[] = 'Everyone';
          $Groups = $decodedJWT->groups;
        } else {
          $Groups = [
            'Authenticated',
            'Everyone'
          ];
        }

        if (isset($decodedJWT->type)) {
          $Type = $decodedJWT->type;
        } else {
          $Type = null;
        }

        $AuthResult = array(
          'Authenticated' => true,
          'Username' => $Username,
          'Firstname' => $Firstname,
          'Surname' => $Surname,
          'Email' => $Email,
          'DisplayName' => $FullName,
          'IPAddress' => $IPAddress,
          'Groups' => $Groups,
          'Type' => $Type
        );
      } else {
        $AuthResult = array(
          'Authenticated' => false,
          'IPAddress' => $IPAddress,
          'Groups' => ['Everyone']
        );
      }
    } else {
      $AuthResult = array(
        'Authenticated' => false,
        'IPAddress' => $IPAddress,
        'Groups' => ['Everyone']
      );
    }
    return $AuthResult;
  }

  public function signinRedirect() {
    if ($this->getAuth()['Authenticated']) {
    } else {
      echo '<script>top.window.location = "/login.php?redirect_uri="+parent.window.location.href.replace("#","?")</script>';
    }
  }
}

class RBAC {
  private $config;
  private $logging;
  private $db;
  private $auth;

  public function __construct($core,$db,$auth) {
    // Set Config
    $this->config = $core->config;
    $this->logging = $core->logging;

    // Define Auth
    $this->auth = $auth;

    // SQL
    $this->db = $db;
    $this->createRBACTable();
    $this->createRBACResourcesDefinitionsTable();
  }

  private function createRBACTable() {
    // Create users table if it doesn't exist
    $this->db->exec("CREATE TABLE IF NOT EXISTS rbac (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      Name TEXT,
      Description TEXT,
      PermittedResources TEXT
    )");

    // Function to check if a role exists
    function roleExists($db, $roleName) {
      $stmt = $db->prepare("SELECT COUNT(*) FROM rbac WHERE Name = :name");
      $stmt->execute([':name' => $roleName]);
      return $stmt->fetchColumn() > 0;
    }

    // Insert roles if they don't exist
    $roles = [
      ['Authenticated', 'This group applies to any authenticated user', ''],
      ['Everyone', 'This group applies to any user, regardless of if they are logged in or not', ''],
      ['Administrators', 'System Administrators', 'ADMIN-RBAC,ADMIN-USERS,ADMIN-CONFIG,ADMIN-LOGS']
    ];

    foreach ($roles as $role) {
      if (!roleExists($this->db, $role[0])) {
        $stmt = $this->db->prepare("INSERT INTO rbac (Name, Description, PermittedResources) VALUES (:Name, :Description, :PermittedResources)");
        $stmt->execute([':Name' => $role[0],':Description' => $role[1], ':PermittedResources' => $role[2]]);
      }
    }
  }

  private function createRBACResourcesDefinitionsTable() {
    // Create users table if it doesn't exist
    $this->db->exec("CREATE TABLE IF NOT EXISTS rbac_resources (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT UNIQUE,
      description TEXT
    )");

    // Function to check if a resource exists
    function resourceExists($db, $resourceName) {
      $stmt = $db->prepare("SELECT COUNT(*) FROM rbac_resources WHERE name = :name");
      $stmt->execute([':name' => $resourceName]);
      return $stmt->fetchColumn() > 0;
    }

    // Insert roles if they don't exist
    // This needs to be re-done when modularisation comes in
    $resources = [
      // Default Admin Roles
      ['ADMIN-RBAC', 'Grants the ability to view and manage Role Based Access'],
      ['ADMIN-LOGS', 'Grants access to view Logs'],
      ['ADMIN-CONFIG', 'Grants access to manage the Infoblox SA Tools Configuration'],
      ['ADMIN-USERS', 'Grants access to view and manage users & groups'],
      ['REPORT-TRACKING', 'Grants the ability to view the Web Tracking Reports'],
      // Other Stuff
      ['DNS-TOOLBOX', 'Grants the ability to use the DNS Toolbox'],
      ['B1-SECURITY-ASSESSMENT', 'Grants the ability to use the Security Assessment Tool'],
      ['B1-THREAT-ACTORS', 'Grants the ability to use the Threat Actors Tool'],
      ['B1-LICENSE-USAGE', 'Grants the ability to use the License Usage Tool'],
      ['ADMIN-SECASS', 'Grants the ability to view and manage the Security Assessment Generator Configuration'],
      ['REPORT-ASSESSMENTS', 'Grants the ability to view the Assessment Reports']
    ];

    foreach ($resources as $resource) {
      if (!resourceExists($this->db, $resource[0])) {
        $stmt = $this->db->prepare("INSERT INTO rbac_resources (name, description) VALUES (:Name, :Description)");
        $stmt->execute([':Name' => $resource[0],':Description' => $resource[1]]);
      }
    }
  }

  public function getRBACGroups() {
    $stmt = $this->db->prepare('SELECT * FROM rbac');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getRBACByGroupID($GroupID) {
    $stmt = $this->db->prepare('SELECT * FROM rbac WHERE id = :GroupID');
    $stmt->execute([':GroupID' => $GroupID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getRBACByGroupName($GroupName) {
    $stmt = $this->db->prepare('SELECT * FROM rbac WHERE LOWER(Name) = LOWER(:GroupName)');
    $stmt->execute([':GroupName' => $GroupName]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getRBAC($GroupID = null,$Action = null) {
    $this->logging->writeLog("RBAC","Queried RBAC List","debug",$_REQUEST);
    if ($GroupID != null) {
      $rbac = $this->getRBACByGroupID($GroupID);
    } else {
      $rbac = $this->getRBACGroups();
    }
    switch ($Action) {
      case 'listgroups':
        $splat = array();
        foreach ($rbac as $group) {
          $newArr = array(
            "id" => $group['id'],
            "Group" => $group['Name'],
            "Description" => $group['Description']
          );
          array_push($splat,$newArr);
        }
        return $splat;
      case 'listconfigurablegroups':
        $splat = array();
        foreach ($rbac as $group) {
          // Exclude SYSTEM Groups
          if ($group['Name'] != 'Authenticated' && $group['Name'] != 'Everyone') {
            $newArr = array(
              "id" => $group['id'],
              "Group" => $group['Name'],
              "Description" => $group['Description']
            );
            array_push($splat,$newArr);
          }
        }
        return $splat;
      case 'listroles':
        $stmt = $this->db->prepare('SELECT * FROM rbac_resources');
        $stmt->execute();
        $rbac = $stmt->fetchAll(PDO::FETCH_ASSOC);
      default:
        return $rbac;
    }
  }

  public function setRBAC($GroupID,$GroupName,$Description = null,$Role = null,$Value = null) {
    $rbac = $this->getRBAC($GroupID)[0];
    $roles = $this->getRBAC(null,"listroles");
    $prepare = [];
    $execute = [];
    $execute[':id'] = $GroupID;
    if ($rbac) {
      if ($Description != null) {
        $rbac['Description'] = $Description;
        $prepare[] = 'Description = :Description';
        $execute[':Description'] = $Description;
        $this->logging->writeLog("RBAC","Updated description for: ".$rbac['Name'],"info",$rbac);
      }
      if ($Role != null) {
        // Check if role exists in definitions
        if (in_array($Role, array_column($roles, 'name'))) {
          if ($rbac['PermittedResources'] != "") {
            $PermittedResources = explode(',',$rbac['PermittedResources']);
          } else {
            $PermittedResources = [];
          }
          if ($Value == "true") {
            ## Add Key to Array
            if (in_array($Role,$PermittedResources)) {
              return array(
                'Status' => 'Error',
                'Message' => $Role.' is already assigned to: '.$rbac['Name']
              );
              $this->logging->writeLog("RBAC","$Role is already assigned to ".$rbac['Name'],"debug",$rbac);
            } else {
              $PermittedResources[] = $Role;
              $prepare[] = 'PermittedResources = :PermittedResources';
              $execute[':PermittedResources'] = implode(',',$PermittedResources);
              $this->logging->writeLog("RBAC","Added $Role to ".$rbac['Name'],"warning",$rbac);
            }
          } else if ($Value == "false") {
            ## Remove Key from Array
            if (in_array($Role,$PermittedResources)) {
              $ArrKey = array_search($Role, $PermittedResources);
              unset($PermittedResources[$ArrKey]);
              $prepare[] = 'PermittedResources = :PermittedResources';
              $execute[':PermittedResources'] = implode(',',$PermittedResources);
              $this->logging->writeLog("RBAC","Removed $Role from ".$rbac['Name'],"warning",$rbac);
            } else {
              $this->logging->writeLog("RBAC","$Role is not assigned to ".$rbac['Name'],"error",$rbac);
              return array(
                'Status' => 'Error',
                'Message' => $Role.' is not assigned to: '.$rbac['Name']
              );
            }
          }
        } else {
          return array(
            'Status' => 'Error',
            'Message' => 'Invalid RBAC Option specified: "'.$Role.'"'
          );
        }
      }
      $stmt = $this->db->prepare('UPDATE rbac SET '.implode(", ",$prepare).' WHERE id = :id');
      $stmt->execute($execute);
      return array(
        'Status' => 'Success',
        'Message' => 'RBAC Group updated successfully'
      );
    } else {
      return array(
        'Status' => 'Error',
        'Message' => 'RBAC Group does not exist'
      );
    }
    return $rbac;
  }

  public function newRBAC($Name,$Description) {
    if (!empty($this->getRBACByGroupName($Name))) {
      return array(
        'Status' => 'Error',
        'Message' => 'RBAC Group already exists with the name: '.$Name
      );
    } else {
      $stmt = $this->db->prepare("INSERT INTO rbac (Name, Description) VALUES (:Name, :Description)");
      $stmt->execute([':Name' => $Name, ':Description' => $Description]);      
      return array(
        'Status' => 'Success',
        'Message' => 'RBAC Group successfully created: '.$Name
      );
    }
  }

  public function deleteRBAC($GroupID) {
    if ($this->getRBACByGroupID($GroupID)) {
      $this->logging->writeLog("RBAC","Deleted RBAC Group: $GroupID","debug",$_REQUEST);
      $stmt = $this->db->prepare("DELETE FROM rbac WHERE id = :id");
      $stmt->execute([':id' => $GroupID]);
    } else {
      $this->logging->writeLog("RBAC","Error deleting RBAC Group. The group does not exist.","error",$_REQUEST);
    }
    return array(
      'Status' => 'Success',
      'Message' => 'RBAC Group deleted successfully'
    );
  }

  private function isResourcePermitted($rbac, $resource) {
    foreach ($rbac as $group) {
      $resources = explode(',', $group['PermittedResources']);
      if (in_array($resource,$resources)) {
          return true;
      } else {
      }
    }
    return false;
  }

  public function checkAccess($Service = null) {
    $User = $this->auth->getAuth();
    if (isset($User['Authenticated'])) {
      $groupsArr = array_map(function($value) {
        return "'" . $value . "'";
      }, $User['Groups']);
      $stmt = $this->db->prepare('SELECT * FROM rbac WHERE Name IN ('.implode(',',$groupsArr).')');
      $stmt->execute();
      $rbac = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if ($Service != null) {
        return $this->isResourcePermitted($rbac,$Service);
      } else {
      }
    } else {
      return false;
    }
    return false;
  }
}