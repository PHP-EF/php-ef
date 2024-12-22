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
        return JWT::encode($payload, $this->config->get()['Security']['salt'], 'HS256');
    }

    // Revoke a token
    public function revokeToken($token) {
        $decoded = JWT::decode($token, new Key($this->config->get()['Security']['salt'], 'HS256'));
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
  private $api;

  public function __construct($core,$db,$api) {
    // Set Config
    $this->config = $core->config;
    $this->logging = $core->logging;

    // CoreJwt
    $this->CoreJwt = new CoreJwt($core);

    // SSO
    $this->sso = new OneLogin\Saml2\Auth($this->config->get("SAML"));

    // SQL
    $this->db = $db;
    $this->createUsersTable();
    $this->createRBACTable();
    $this->createRBACResourcesDefinitionsTable();

    // API
    $this->api = $api;
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
    if ($type == 'SSO' | $type == 'LDAP') {
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
            $this->api->setAPIResponse('Error','Username or Email already exists');
            return false;
          }
      } catch (PDOException $e) {
        $this->api->setAPIResponse('Error',$e);
      }

      try {
        $stmt->execute([':username' => $username, ':firstname' => $firstname, ':surname' => $surname, ':email' => $email, ':password' => $pepper['hash'], ':salt' => $pepper['salt'], ':groups' => $groups, ':created' => $currentDateTime, ':passwordexpires' => $passwordExpires, ':type' => $type]);
          $this->api->setAPIResponseMessage('User created successfully');  
          return true;
      } catch (PDOException $e) {
        $this->api->setAPIResponse('Error',$e);
        return false;
      }
    } else {
      $this->api->setAPIResponse('Error','Password does not meet the complexity requirements');
      return false;
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
          $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE (username = :username OR email = :email) AND id != :id");
          $checkStmt->execute([':username' => $username, ':email' => $email, ':id' => $id]);
          if ($checkStmt->fetchColumn() > 0) {
            $this->api->setAPIResponse('Error','Username or Email already exists');
          }
        } catch (PDOException $e) {
            $this->api->setAPIResponse('Error',$e);
        }
      }

      $prepare = [];
      $execute = [];
      $execute[':id'] = $id;
      if (!empty($password)) {
        // Hash & salt the password for security
        $pepper = $this->hashAndSalt($password);
        $prepare[] = 'password = :password';
        $prepare[] = 'salt = :salt';
        $execute[':password'] = $pepper['hash'];
        $execute[':salt'] = $pepper['salt'];
      }
      if (!empty($username)) {
        $prepare[] = 'username = :username';
        $execute[':username'] = $username;
      }
      if (!empty($firstname)) {
        $prepare[] = 'firstname = :firstname';
        $execute[':firstname'] = $firstname;
      }
      if (!empty($surname)) {
        $prepare[] = 'surname = :surname';
        $execute[':surname'] = $surname;
      }
      if (!empty($email)) {
        $prepare[] = 'email = :email';
        $execute[':email'] = $email;
      }
      if (!empty($groups) || $groups == "") {
        $prepare[] = 'groups = :groups';
        $execute[':groups'] = $groups;
      }
      $stmt = $this->db->prepare('UPDATE users SET '.implode(", ",$prepare).' WHERE id = :id');
      $stmt->execute($execute);
      $this->api->setAPIResponseMessage('User updated successfully');
    } else {
      $this->api->setAPIResponse('Error','User does not exist');
    }
  }

  public function removeUser($id) {
    if ($this->getUserById($id)) {
      $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
      $stmt->execute([':id' => $id]);
      if ($this->getUserById($id)) {
        $this->api->setAPIResponse('Error','Failed to delete user');
      } else {
        $this->api->setAPIResponseMessage('User deleted successfully');
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
              $this->api->setAPIResponseMessage('Password reset successfully');
              return true;
            } catch (PDOException $e) {
              $this->api->setAPIResponse('Error',$e);
              return false;
            }
          } else {
            $this->api->setAPIResponse('Error','New password does not meet the complexity requirements');
            return false;
          }
        } else {
          $this->api->setAPIResponse('Error','Cannot reset password for SSO Account');
          return false;
        }
      } else {
        $this->api->setAPIResponse('Error','Failed to retrieve user information');
        return false;
      }
    } else { // Verify failed
      $this->logging->writeLog("Authentication",$username." failed to reset password","warning");
      $this->api->setAPIResponse('Error','The submitted current password is invalid');
      return false;
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
              $this->api->setAPIResponseMessage('Password reset successfully');
            } catch (PDOException $e) {
              $this->api->setAPIResponse('Error',$e);
              return false;
            }
          } else {
            $this->api->setAPIResponse('Error','New password does not meet the complexity requirements');
            return false;
          }
        } else {
          $this->api->setAPIResponse('Error','Cannot reset password for SSO Account');
          return false;
        }
      } else {
        $this->api->setAPIResponse('Error','Failed to retrieve user information');
        return false;
      }
    } else {
      $this->api->setAPIResponse('Error','Not Authenticated',401);
      return false;
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

  public function login($request) {
    if (isset($request['un']) && isset($request['pw'])) {
      $username = $request['un'];
      $password = $request['pw'];

      // Try LDAP authentication first if enabled
      if ($this->config->get('LDAP','enabled')) {
        $LDAPAuth = $this->ldapAuthenticate($username, $password);
        if ($LDAPAuth) {
          // LDAP authentication successful
          $user = $this->getUserByUsernameOrEmail($username, $username, true);
          if ($user) {
              return $this->handleSuccessfulLogin($user);
          } else {
            $AttributeMap = [];
            $AttributeMap['Username'] = $LDAPAuth['Username'] ?? null;
            $AttributeMap['FirstName'] = $LDAPAuth['FirstName'] ?? null;
            $AttributeMap['LastName'] = $LDAPAuth['LastName'] ?? null;
            $AttributeMap['Email'] = $LDAPAuth['Email'] ?? null;
            $AttributeMap['Groups'] = implode(",",$LDAPAuth['Groups']) ?? null;
            if ($this->createUserIfNotExists($AttributeMap,"LDAP",$this->config->get('LDAP','AutoCreateUsers'))) {
              return true;
            } else {
              return false;
            };
          }
        }
      }

      // Fallback to local authentication
      $user = $this->getUserByUsernameOrEmail($username, $username, true);
      if ($user && password_verify($user['salt'].$password, $user['password'])) {
          return $this->handleSuccessfulLogin($user);
      } else {
          $this->api->setAPIResponse('Error', 'Invalid Credentials');
          $this->logging->writeLog("Authentication", $username." failed to log in", "warning");
          return false;
      }
    } else {
        $this->api->setAPIResponse('Error', 'Invalid Credentials');
        return false;
    }
  }

  private function ldapAuthenticate($username, $password) {
    $config = $this->config->get('LDAP');
    $ldapconn = ldap_connect($config['ldap_server']);
    if ($ldapconn) {
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

        // Authenticate as service account
        $service_bind = @ldap_bind($ldapconn, $config['service_dn'], $config['service_password']);
        if (!$service_bind) {
            ldap_unbind($ldapconn);
            return false;
        }

        if ($service_bind) {
            // Search for user details
            $filter = "(".$config['attributes']['Username']."=$username)";
            $result = ldap_search($ldapconn, $config['base_dn'], $filter, [$config['attributes']['DN'], $config['attributes']['Groups'], $config['attributes']['FirstName'], $config['attributes']['Username'], $config['attributes']['LastName'], $config['attributes']['Email']]);
            $entries = ldap_get_entries($ldapconn, $result);

            $userDetails = [];
            if ($entries['count'] > 0) {
                // Validate User Creds
                $ldaprdn = $entries[0][strtolower(strtolower($config['attributes']['DN']))][0] ?? null;
                if ($ldaprdn) {
                  $user_bind = @ldap_bind($ldapconn, $ldaprdn, $password);
                  if (!$user_bind) {
                    ldap_unbind($ldapconn);
                    return false;
                  }
                } else {
                  return false;
                }
                $userDetails['Groups'] = [];
                if (isset($entries[0][strtolower($config['attributes']['Groups'])])) {
                    $rbacgroups = $this->getRBACGroups();
                    foreach ($entries[0][strtolower($config['attributes']['Groups'])] as $groupKey => $groupVal) {
                        if ($groupKey !== 'count') {
                            if (in_array($groupVal,$rbacgroups)) {
                              $userDetails['Groups'][] = $this->extractCN($groupVal);
                            }
                        }
                    }
                }
                $userDetails['Username'] = $entries[0][strtolower($config['attributes']['Username'])][0] ?? null;
                $userDetails['FirstName'] = $entries[0][strtolower($config['attributes']['FirstName'])][0] ?? null;
                $userDetails['LastName'] = $entries[0][strtolower($config['attributes']['LastName'])][0] ?? null;
                $userDetails['Email'] = $entries[0][strtolower($config['attributes']['Email'])][0] ?? null;
            }
            ldap_unbind($ldapconn);
            return $userDetails;
        } else {
            ldap_unbind($ldapconn);
            return false;
        }
    }
    return false;
  }

  private function extractCN($group) {
    preg_match('/CN=([^,]+)/', $group, $matches);
    return $matches[1];
  }

  private function handleSuccessfulLogin($user) {
      $now = new DateTime();
      $expires = new DateTime($user['passwordexpires']);
      if ($expires < $now) {
          $this->api->setAPIResponse('Expired', 'Password Expired');
          return false;
      }

      // Update last login
      $this->updateLastLogin($user['id']);

      // Generate JWT token
      $jwt = $this->CoreJwt->generateToken($user['username'], $user['firstname'], $user['surname'], $user['email'], explode(',', $user['groups']), $user['type']);
      // Set JWT as a cookie
      setcookie('jwt', $jwt, time() + (86400 * 30), "/"); // 30 days

      $this->logging->writeLog("Authentication", $user['username']." successfully logged in", "info");
      $this->api->setAPIResponseMessage('Successfully logged in');
      return true;
  }

  private function createUserIfNotExists($AttributeMap,$Source,$AutoCreate = false) {
    // Check if matching user exists
    $user = $this->getUserByUsernameOrEmail($AttributeMap['Username'],$AttributeMap['Email']);

    if ($user) {
      // Update last login
      $this->updateLastLogin($user['id']);
      // Update user info from External Auth Source
      $stmt = $this->db->prepare("UPDATE users SET username = :username, firstname = :firstname, surname = :surname, email = :email, groups = :groups WHERE id = :id");
      $stmt->execute([':id' => $user['id'], ':username' => $AttributeMap['Username'], ':firstname' => $AttributeMap['FirstName'], ':surname' => $AttributeMap['LastName'], ':email' => $AttributeMap['Email'], ':groups' => $AttributeMap['Groups']]);
      // Set Login to True
      $Login = true;
      $this->logging->writeLog("Authentication",$AttributeMap['Username']." successfully logged in with ".$Source,"info");
    } else if ($AutoCreate) {
      // User does not exist and will be created
      $NewUser = $this->newUser($AttributeMap['Username'], null, $AttributeMap['FirstName'], $AttributeMap['LastName'], $AttributeMap['Email'], $AttributeMap['Groups'], $type = $Source);
      if ($NewUser) {
        // Update last login
        $this->updateLastLogin($this->getUserByUsernameOrEmail($AttributeMap['Username'],$AttributeMap['Email'])['id']);
        // Set Login to True
        $Login = true;
        $this->logging->writeLog("Authentication",$AttributeMap['Username']." successfully logged in with ".$Source." and new user was created","info");
      } else {
        $this->logging->writeLog("Authentication","Failed to create new user: ".$AttributeMap['Username']." from ".$Source.".","info");
        $this->api->setAPIResponse('Error','Failed to create new user');
        return false;
      }
    } else {
      // User does not exist and won't be created
      $this->logging->writeLog("Authentication",$AttributeMap['Username']." successfully logged in with ".$Source.", but user does not exist","warning");
      $this->api->setAPIResponse('Error','Successfully logged in, but user not found and automatic user creation is disabled.');
      return false;
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
      $this->api->setAPIResponseMessage('Successfully logged in');
      return true;
    }
  }

  public function logout() {
    $this->CoreJwt->revokeToken($_COOKIE['jwt']);
    $this->api->setAPIResponseData($this->getAuth());
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
        if ($this->config->get('SAML','attributes')['Username'] && isset($SAMLArr['samlUserdata'][$this->config->get('SAML','attributes')['Username']])) {
          $AttributeMap['Username'] = $SAMLArr['samlUserdata'][$this->config->get('SAML','attributes')['Username']][0];
        } else {
          $AttributeMap['Username'] = null;
        }
        if ($this->config->get('SAML','attributes')['FirstName'] && isset($SAMLArr['samlUserdata'][$this->config->get('SAML','attributes')['FirstName']])) {
          $AttributeMap['FirstName'] = ucwords($SAMLArr['samlUserdata'][$this->config->get('SAML','attributes')['FirstName']][0]);
        } else {
          $AttributeMap['FirstName'] = null;
        }
        if ($this->config->get('SAML','attributes')['LastName'] && isset($SAMLArr['samlUserdata'][$this->config->get('SAML','attributes')['LastName']])) {
          $AttributeMap['LastName'] = ucwords($SAMLArr['samlUserdata'][$this->config->get('SAML','attributes')['LastName']][0]);
        } else {
          $AttributeMap['LastName'] = null;
        }
        if ($this->config->get('SAML','attributes')['Email'] && isset($SAMLArr['samlUserdata'][$this->config->get('SAML','attributes')['Email']])) {
          $AttributeMap['Email'] = $SAMLArr['samlUserdata'][$this->config->get('SAML','attributes')['Email']][0];
        } else {
          $AttributeMap['Email'] = null;
        }
        if ($this->config->get('SAML','attributes')['Groups'] && isset($SAMLArr['samlUserdata'][$this->config->get('SAML','attributes')['Groups']])) {
            $AttributeMap['Groups'] = implode(',',$SAMLArr['samlUserdata'][$this->config->get('SAML','attributes')['Groups']]);
        } else {
          $AttributeMap['Groups'] = '';
        }
        // Add SAML assertion to redis to prevent re-use
        if ($this->CoreJwt->isRevoked($this->sso->getLastAssertionId())) {
          $this->logging->writeLog("Authentication",$AttributeMap['Username']." attempted a potential replay attack.","warning",$SAMLArr);
          return array(
            'Status' => 'Error',
            'Message' => 'SAML Assertion has been revoked'
          );
        } else {
          $this->CoreJwt->revokeAssertion($this->sso->getLastAssertionId(), $this->sso->getNameId(), 3600); // Store SAML Assertion for 1 hour to allow for natural expiry
          if (!$this->createUserIfNotExists($AttributeMap,"SSO",$this->config->get('SAML','AutoCreateUsers'))) {
            return false;
          }
        }
    } else { // Login failed
        $this->logging->writeLog("Authentication","User failed to log in with SSO","warning");
        return array(
          'Status' => 'Error',
          'Message' => 'SSO Authentication Failed'
        );
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
      $secretKey = $this->config->get()['Security']['salt']; // Change this to a secure key
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

  // Function to check if a role exists
  private function roleExists($roleName) {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM rbac WHERE Name = :name");
    $stmt->execute([':name' => $roleName]);
    return $stmt->fetchColumn() > 0;
  }

  private function createRBACTable() {
    // Create users table if it doesn't exist
    $this->db->exec("CREATE TABLE IF NOT EXISTS rbac (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      Name TEXT,
      Description TEXT,
      PermittedResources TEXT,
      Protected BOOLEAN
    )");

    // Insert roles if they don't exist
    $roles = [
      ['Authenticated', 'This group applies to any authenticated user', true],
      ['Everyone', 'This group applies to any user, regardless of if they are logged in or not', true],
      ['Administrators', 'System Administrators', 'ADMIN-RBAC,ADMIN-USERS,ADMIN-CONFIG,ADMIN-LOGS,ADMIN-PAGES,REPORT-TRACKING', true]
    ];

    foreach ($roles as $role) {
      if (!$this->roleExists($role[0])) {
        $stmt = $this->db->prepare("INSERT INTO rbac (Name, Description, PermittedResources, Protected) VALUES (:Name, :Description, :PermittedResources, :Protected)");
        $stmt->execute([':Name' => $role[0],':Description' => $role[1], ':PermittedResources' => $role[2], ':Protected' => $role[3]]);
      }
    }
  }

  // Function to check if a resource exists
  private function resourceExists($db, $resourceName) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM rbac_resources WHERE name = :name");
    $stmt->execute([':name' => $resourceName]);
    return $stmt->fetchColumn() > 0;
  }

  private function createRBACResourcesDefinitionsTable() {
    // Create users table if it doesn't exist
    $this->db->exec("CREATE TABLE IF NOT EXISTS rbac_resources (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT UNIQUE,
      description TEXT,
      Protected BOOLEAN
    )");



    // Insert roles if they don't exist
    $resources = [
      // Built-In Roles
      ['ADMIN-RBAC', 'Grants the ability to view and manage Role Based Access', true],
      ['ADMIN-LOGS', 'Grants access to view Logs', true],
      ['ADMIN-CONFIG', 'Grants access to manage the PHP-EF Configuration', true],
      ['ADMIN-USERS', 'Grants access to view and manage users & groups', true],
      ['ADMIN-PAGES', 'Grants the ability to view and manage Pages', true],
      ['REPORT-TRACKING', 'Grants the ability to view the Web Tracking Reports', true]
    ];

    foreach ($resources as $resource) {
      if (!$this->resourceExists($this->db, $resource[0])) {
        $stmt = $this->db->prepare("INSERT INTO rbac_resources (name, description, Protected) VALUES (:Name, :Description, :Protected)");
        $stmt->execute([':Name' => $resource[0],':Description' => $resource[1], ':Protected' => $resource[2]]);
      }
    }
  }

  public function getRBACGroups($protected = false, $configurable = false) {
    $prepare = 'SELECT * FROM rbac';
    $where = [];
    if ($protected) {
      $where[] = '(Protected = 0 OR Protected IS NULL)';
    }
    if ($configurable) {
      $where[] = 'Name NOT IN ("Everyone","Authenticated")';
    }
    if (!empty($where)) {
      $prepare .= ' WHERE '.implode(' AND ',$where);
    }
    $stmt = $this->db->prepare($prepare);
    $stmt->execute();
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $groups;
  }

  public function getRBACGroupByID($GroupID) {
    $stmt = $this->db->prepare('SELECT * FROM rbac WHERE id = :GroupID');
    $stmt->execute([':GroupID' => $GroupID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getRBACGroupByName($GroupName) {
    $stmt = $this->db->prepare('SELECT * FROM rbac WHERE LOWER(Name) = LOWER(:GroupName)');
    $stmt->execute([':GroupName' => $GroupName]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function updateRBACGroup($GroupID,$GroupName,$Description = null,$Role = null,$Value = null) {
    $rbac = $this->getRBACGroupByID($GroupID)[0];
    $roles = $this->getRBACRoles();
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
          if ($Value == "enabled") {
            ## Add Key to Array
            if (in_array($Role,$PermittedResources)) {
              $this->logging->writeLog("RBAC","$Role is already assigned to ".$rbac['Name'],"debug",$rbac);
              $this->api->setAPIResponseData('Error',$Role.' is already assigned to: '.$rbac['Name']);
              return false;
            } else {
              $PermittedResources[] = $Role;
              $prepare[] = 'PermittedResources = :PermittedResources';
              $execute[':PermittedResources'] = implode(',',$PermittedResources);
              $this->logging->writeLog("RBAC","Added $Role to ".$rbac['Name'],"warning",$rbac);
            }
          } else if ($Value == "disabled") {
            ## Remove Key from Array
            if (in_array($Role,$PermittedResources)) {
              $ArrKey = array_search($Role, $PermittedResources);
              unset($PermittedResources[$ArrKey]);
              $prepare[] = 'PermittedResources = :PermittedResources';
              $execute[':PermittedResources'] = implode(',',$PermittedResources);
              $this->logging->writeLog("RBAC","Removed $Role from ".$rbac['Name'],"warning",$rbac);
            } else {
              $this->logging->writeLog("RBAC","$Role is not assigned to ".$rbac['Name'],"error",$rbac);
              $this->api->setAPIResponseData('Error',$Role.' is not assigned to: '.$rbac['Name']);
              return false;
            }
          }
        } else {
          $this->api->setAPIResponseData('Error','Invalid RBAC Option specified: "'.$Role.'"');
          return false;
        }
      }
      $stmt = $this->db->prepare('UPDATE rbac SET '.implode(", ",$prepare).' WHERE id = :id');
      $stmt->execute($execute);
      $this->api->setAPIResponseMessage('RBAC Group updated successfully');
    } else {
      $this->api->setAPIResponseData('Error','RBAC Group does not exist');
      return false;
    }
  }

  public function newRBACGroup($Name,$Description) {
    if (!empty($this->getRBACGroupByName($Name))) {
      $this->api->setAPIResponseData('Error','RBAC Group already exists with the name: '.$Name);
    } else {
      $stmt = $this->db->prepare("INSERT INTO rbac (Name, Description) VALUES (:Name, :Description)");
      $stmt->execute([':Name' => $Name, ':Description' => $Description]);      
      $this->api->setAPIResponseMessage('RBAC Group successfully created: '.$Name);
    }
  }

  public function deleteRBACGroup($GroupID) {
    if ($this->getRBACGroupByID($GroupID)) {
      $stmt = $this->db->prepare("DELETE FROM rbac WHERE id = :id");
      $stmt->execute([':id' => $GroupID]);
      $this->logging->writeLog("RBAC","Deleted RBAC Group: $GroupID","debug",$_REQUEST);
      $this->api->setAPIResponseMessage('RBAC Group deleted successfully');  
    } else {
      $this->logging->writeLog("RBAC","Error deleting RBAC Group. The Group does not exist.","error",$_REQUEST);
      $this->api->setAPIResponse('Error','Unable to delete RBAC Group. The Group does not exist.');
    }
  }

  public function getRBACRoles() {
    $stmt = $this->db->prepare('SELECT * FROM rbac_resources');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getRBACRoleByID($RoleID) {
    $stmt = $this->db->prepare('SELECT * FROM rbac_resources WHERE id = :RoleID');
    $stmt->execute([':RoleID' => $RoleID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getRBACRoleByName($RoleName) {
    $stmt = $this->db->prepare('SELECT * FROM rbac_resources WHERE LOWER(name) = LOWER(:RoleName)');
    $stmt->execute([':RoleName' => $RoleName]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function newRBACRole($Name,$Description) {
    if (!empty($this->getRBACRoleByName($Name))) {
      $this->api->setAPIResponse('Error','RBAC Role already exists with the name: '.$Name);
    } else {
      $stmt = $this->db->prepare("INSERT INTO rbac_resources (name, description) VALUES (:Name, :Description)");
      $stmt->execute([':Name' => $Name, ':Description' => $Description]);      
      $this->api->setAPIResponseMessage('RBAC Role successfully created: '.$Name);
    }
  }

  public function updateRBACRole($id,$roleName,$roleDescription) {
    if ($this->getRBACRoleByID($id)) {
      $prepare = [];
      $execute = [];
      $execute[':id'] = $id;
      if ($roleName !== null) {
        $prepare[] = 'name = :name';
        $execute[':name'] = $roleName;
      }
      if ($roleDescription !== null) {
        $prepare[] = 'description = :description';
        $execute[':description'] = $roleDescription;
      }
      $stmt = $this->db->prepare('UPDATE rbac_resources SET '.implode(", ",$prepare).' WHERE id = :id');
      $stmt->execute($execute);
      $this->api->setAPIResponseMessage('RBAC Role updated successfully');
    } else {
      $this->api->setAPIResponseData('Error','RBAC Role does not exist.');
    }
  }

  public function deleteRBACRole($RoleID) {
    if ($this->getRBACRoleByID($RoleID)) {
      $this->logging->writeLog("RBAC","Deleted RBAC Role: $RoleID","debug",$_REQUEST);
      $stmt = $this->db->prepare("DELETE FROM rbac_resources WHERE id = :id");
      $stmt->execute([':id' => $RoleID]);
      $this->api->setAPIResponseMessage('RBAC Role deleted successfully');  
    } else {
      $this->logging->writeLog("RBAC","Error deleting RBAC Role. The role does not exist.","error",$_REQUEST);
      $this->api->setAPIResponse('Error','Unable to delete RBAC Role. The Role does not exist.');
    }
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
    $User = $this->getAuth();
    if (isset($User['Authenticated'])) {
      $groupsArr = array_map(function($value) {
        return "'" . $value . "'";
      }, $User['Groups']);
      $stmt = $this->db->prepare('SELECT * FROM rbac WHERE Name IN ('.implode(',',$groupsArr).')');
      $stmt->execute();
      $rbac = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if ($Service != null) {
        if ($this->isResourcePermitted($rbac,$Service)) {
          return true;
        } else {
          $this->api->setAPIResponse('Error','Unauthorized','401');
          return false;
        }
      }
    } else {
      return false;
    }
    return false;
  }
}