<?php

function GetAuth() {
  if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $IPAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else if (isset($_SERVER['REMOTE_ADDR'])) {
    $IPAddress = $_SERVER['REMOTE_ADDR'];
  } else {
    $IPAddress = "N/A";
  }
  if (isset($_SERVER['HTTP_REMOTE_USER'])) {
    if (isset($_SERVER['HTTP_REMOTE_GROUPS'])) {
      $RemoteGroups = $_SERVER['HTTP_REMOTE_GROUPS']."|EVERYONE";
    } else {
      $RemoteGroups = "EVERYONE";
    }
    if (isset($_SERVER['HTTP_REMOTE_EMAIL'])) {
      $Email = $_SERVER['HTTP_REMOTE_EMAIL'];
    } else {
      $Email = null;
    }
    $AuthResult = array(
      'Authenticated' => true,
      'Username' => $_SERVER['HTTP_REMOTE_USER'],
      'DisplayName' =>  $_SERVER['HTTP_REMOTE_NAME'],
      'EmailAddress' => $Email,
      'IPAddress' => $IPAddress,
      'Groups' => $RemoteGroups
    );
  } else {
    $AuthResult = array(
      'Authenticated' => false,
      'IPAddress' => $IPAddress,
    );
  } 
  return $AuthResult;
}

function signinRedirect() {
  if (GetAuth()['Authenticated']) {
  } else {
    echo '<script>top.window.location = "https://auth.placeholder/?rd="+parent.window.location.href.replace("#","?")</script>';
  }
}

function CheckAccessV1($User,$Service) {
  if ($User == null) {
    $User = GetAuth();
  }
  if (isset($User['Authenticated'])) {
    $rbacJson = file_get_contents(__DIR__.'/../'.getConfig("System","rbacjson"));
    $rbac = json_decode($rbacJson, true);
    if (isset($User['Groups'])) {
      if (isset($_SERVER['HTTP_X_AUTHENTIK_UID'])) {
        $usergroups = explode('|',$User['Groups']);
      } else {
        $usergroups = explode(',',$User['Groups']);
      }
      foreach ($usergroups as $usergroup) {
        if (isset($rbac[$usergroup])) {
          if (in_array($Service,$rbac[$usergroup]['PermittedResources'])) {
            return true;
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

function CheckAccess($User,$Service = null,$Menu = null) {
  // return true; // Remove
  if ($User == null) {
    $User = GetAuth();
  }
  if (isset($User['Authenticated'])) {
    $rbacJson = file_get_contents(__DIR__.'/../'.getConfig("System","rbacjson"));
    $rbac = json_decode($rbacJson, true);
    if (isset($User['Groups'])) {
      if (isset($_SERVER['HTTP_X_AUTHENTIK_UID'])) {
        $usergroups = explode('|',$User['Groups']);
      } else {
        $usergroups = explode(',',$User['Groups']);
      }
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
