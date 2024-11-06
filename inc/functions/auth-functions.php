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
    public function generateToken($UN) {
        $payload = [
          'iat' => time(), // Issued at
          'exp' => time() + (86400 * 30), // Expiration time (30 days)
          'username' => $UN,
          'name' => null,
          'groups' => array()
        ];
        if ($UN === 'admin') {
          $payload['groups'][] = 'Administrators';
        }
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

function NewAuth($UN,$PW) {
  $CoreJwt = new CoreJwt();
  // Secret key for JWT
  $secretKey = getConfig()['Security']['salt']; // Change this to a secure key

  // Function to validate user credentials
  function validateCredentials($username, $password) {
      // Replace this with actual user validation logic
      if ($username === 'admin' && $password === getConfig()['Security']['AdminPassword']) {
        return true;
      } else if ($username === 'user' && $password === getConfig()['Security']['AdminPassword']) {
        return true;
      };
  }

  if (validateCredentials($UN, $PW)) {
    // Generate JWT token
    $jwt = $CoreJwt->generateToken($UN);
    // Set JWT as a cookie
    setcookie('jwt', $jwt, time() + (86400 * 30), "/"); // 30 days

    $Arr = array(
      'Status' => 'Success',
      'Location' => '/'
    );
    writeLog("Authentication",$UN." successfully logged in","info",$Arr);
    return $Arr;
  } else {
    $Arr = array(
      'Status' => 'Error',
      'Message' => 'Invalid Credentials'
    );
    writeLog("Authentication",$UN." failed to log in","warning",$Arr);
    return $Arr;
  }
}

function InvalidateAuth() {
  $CoreJwt = new CoreJwt();
  $CoreJwt->revokeToken($_COOKIE['jwt']);
  return GetAuth();
}

function GetAuth() {
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
        'Groups' => 'Everyone'
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

      if (isset($decodedJWT->groups)) {
        $decodedJWT->groups[] = 'Authenticated|Everyone';
        $Groups = join("|",$decodedJWT->groups);
      } else {
        $Groups = "Authenticated|Everyone";
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
        'Groups' => 'Everyone'
      );
    }
  } else {
    $AuthResult = array(
      'Authenticated' => false,
      'IPAddress' => $IPAddress,
      'Groups' => 'Everyone'
    );
  }
  return $AuthResult;
}

function signinRedirect() {
  if (GetAuth()['Authenticated']) {
  } else {
    echo '<script>top.window.location = "/login.php?redirect_uri="+parent.window.location.href.replace("#","?")</script>';
  }
}

function CheckAccess($User,$Service = null,$Menu = null) {
  if ($User == null) {
    $User = GetAuth();
  }
  if (isset($User['Authenticated'])) {
    $rbacJson = file_get_contents(__DIR__.'/../'.getConfig("System","rbacjson"));
    $rbac = json_decode($rbacJson, true);
    if (isset($User['Groups'])) {
      $usergroups = explode('|',$User['Groups']);
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

function getRBAC($Group = null,$Action = null) {
  writeLog("RBAC","Queried RBAC List","debug",$_REQUEST);
  $rbacJson = file_get_contents(__DIR__.'/../'.getConfig("System","rbacjson"));
  $rbac = json_decode($rbacJson, true);
  if ($Action == "listgroups") {
    $splat = array();
    foreach ($rbac as $group => $groupval) {
      $newArr = array(
        "Group" => $group,
        "Description" => $groupval['Description']
      );
      array_push($splat,$newArr);
    }
    return $splat;
  } elseif ($Action == "listroles") {
    $rbacJson = file_get_contents(__DIR__.'/../'.getConfig("System","rbacinfo"));
    $rbac = json_decode($rbacJson, true);
    return $rbac;
  } elseif ($Group != null) {
    return $rbac[$Group];
  } else {
    return $rbac;
  }
}

function setRBAC($Group,$Description = null,$Key = null,$Value = null) {
  $rbac = getRBAC();
  $roles = getRBAC(null,"listroles");
  if (array_key_exists($Group,$rbac)) {
    if ($Description != null) {
      $rbac[$Group]['Description'] = $Description;
      file_put_contents(__DIR__.'/../'.getConfig("System","rbacjson"), json_encode($rbac, JSON_PRETTY_PRINT));
      writeLog("RBAC","Updated description for $Group to: $Description","info",$rbac[$Group]);
    }
    if ($Key != null) {
     if (array_key_exists($Key,$roles['Resources'])) {
      if ($Value == "true") {
        ## Add Key to Array
        if (in_array($Key,$rbac[$Group]['PermittedResources'])) {
          writeLog("RBAC","$Key is already assigned to $Group","error",$Key,$rbac[$Group]);
	  return "Error. ".$Key." is already assigned to: ".$Group;
	} else { 
	  array_push($rbac[$Group]['PermittedResources'],$Key);
	  file_put_contents(__DIR__.'/../'.getConfig("System","rbacjson"), json_encode($rbac, JSON_PRETTY_PRINT));
          writeLog("RBAC","Added $Key to $Group","warning",$rbac[$Group]);	
	}

	## Add Menus to Array
	foreach ($roles['Resources'][$Key]['PermittedMenus'] as $PermittedMenu) {
          if (in_array($PermittedMenu,$rbac[$Group]['PermittedMenus'])) {
//            writeLog("RBAC","$PermittedMenu is already assigned to $Group","error",$rbac[$Group]);
	  } else {
            array_push($rbac[$Group]['PermittedMenus'],$PermittedMenu);
	    file_put_contents(__DIR__.'/../'.getConfig("System","rbacjson"), json_encode($rbac, JSON_PRETTY_PRINT));
	    writeLog("RBAC","Added Menu: $PermittedMenu to $Group","info",$rbac[$Group]);
	  }
	}
      } else if ($Value == "false") {
        ## Remove Key from Array
        if (in_array($Key,$rbac[$Group]['PermittedResources'])) {
          if (($keytoremove = array_search($Key, $rbac[$Group]['PermittedResources'])) !== false) {
            unset($rbac[$Group]['PermittedResources'][$keytoremove]);
	    $rbac[$Group]['PermittedResources'] = array_values($rbac[$Group]['PermittedResources']);
	    file_put_contents(__DIR__.'/../'.getConfig("System","rbacjson"), json_encode($rbac, JSON_PRETTY_PRINT));
            writeLog("RBAC","Removed $Key from $Group","warning",$rbac[$Group]);
          }
        } else { 
          writeLog("RBAC","$Key is not assigned to $Group","error",$Key,$rbac[$Group]);
	  return "Error. ".$Key." is not asssigned to: ".$Group;
	}
	## Remove Menus from Array
        $Needed = false;
	foreach ($roles['Resources'][$Key]['PermittedMenus'] as $PermittedMenu) {
          if (in_array($PermittedMenu,$rbac[$Group]['PermittedMenus'])) {
            if (!empty($rbac[$Group]['PermittedResources'])) {
              foreach ($rbac[$Group]['PermittedResources'] as $PermittedResource) {
                if (in_array($PermittedMenu,$roles['Resources'][$PermittedResource]['PermittedMenus'])) {
                  $Needed = true;
		}
              }
	    } else {
              foreach ($rbac[$Group]['PermittedMenus'] as $PermittedMenu) {
                if (($menutoremove = array_search($PermittedMenu, $rbac[$Group]['PermittedMenus'])) !== false) {;
                  unset($rbac[$Group]['PermittedMenus'][$menutoremove]);
                }
	      }
              $rbac[$Group]['PermittedMenus'] = array_values($rbac[$Group]['PermittedMenus']);
              file_put_contents(__DIR__.'/../'.getConfig("System","rbacjson"), json_encode($rbac, JSON_PRETTY_PRINT));
              writeLog("RBAC","No permitted resources left, removing permitted menus from $Group","debug",$rbac[$Group]);
	    }
	  } else {
//            echo "$PermittedMenu is not assigned to $Group";
	  }
          if (!$Needed) {
            if (($menutoremove = array_search($PermittedMenu, $rbac[$Group]['PermittedMenus'])) !== false) {
              unset($rbac[$Group]['PermittedMenus'][$menutoremove]);
              $rbac[$Group]['PermittedMenus'] = array_values($rbac[$Group]['PermittedMenus']);
              file_put_contents(__DIR__.'/../'.getConfig("System","rbacjson"), json_encode($rbac, JSON_PRETTY_PRINT));
              writeLog("RBAC","Removed Menu: $PermittedMenu from $Group","info",$rbac[$Group]);
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
        "Description" => $Description,
        "PermittedResources" => array(),
        "PermittedMenus" => array()
    );
    $rbac[$Group] = $NewNode;
    file_put_contents(__DIR__.'/../'.getConfig("System","rbacjson"), json_encode($rbac, JSON_PRETTY_PRINT));
  }
  return $rbac;
}

function deleteRBAC($Group) {
  writeLog("RBAC","Deleted RBAC Group: $Group","debug",$_REQUEST);
  $rbacJson = file_get_contents(__DIR__.'/../'.getConfig("System","rbacjson"));
  $rbacArr = json_decode($rbacJson, true);
  if (array_key_exists($Group,$rbacArr)) {
    writeLog("RBAC","Deleted RBAC Group: $Group","debug",$_REQUEST);
    unset($rbacArr[$Group]);
    file_put_contents(__DIR__.'/../'.getConfig("System","rbacjson"), json_encode($rbacArr, JSON_PRETTY_PRINT));
  } else {
    writeLog("RBAC","Error deleting RBAC Group: $Group. The group does not exist.","error",$_REQUEST);
  }
  return $rbacArr;
}

?>
