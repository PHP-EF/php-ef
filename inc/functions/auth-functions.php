<?php
// ********* //
// Functions //

function getRBAC($Group = null,$Action = null) {
  writeLog("RBAC","Queried RBAC List","debug",$_REQUEST);
  $rbacJson = file_get_contents(__DIR__.'/../'.getConfig("System","rbacjson"));
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
      $rbacJson = file_get_contents(__DIR__.'/../'.getConfig("System","rbacinfo"));
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

function setRBAC($GroupID,$GroupName,$Description = null,$Key = null,$Value = null) {
  $rbac = getRBAC();
  $roles = getRBAC(null,"listroles");
  if (array_key_exists($GroupID,$rbac)) {
    if ($Description != null) {
      $rbac[$GroupID]['Description'] = $Description;
      file_put_contents(__DIR__.'/../'.getConfig("System","rbacjson"), json_encode($rbac, JSON_PRETTY_PRINT));
      writeLog("RBAC","Updated description for ".$rbac[$GroupID]['Name'],$rbac[$GroupID]);
    }
    if ($Key != null) {
      if (array_key_exists($Key,$roles['Resources'])) {
        if ($Value == "true") {
          ## Add Key to Array
          if (in_array($Key,$rbac[$GroupID]['PermittedResources'])) {
            writeLog("RBAC","$Key is already assigned to ".$rbac[$GroupID]['Name'],"debug",$Key,$rbac[$GroupID]);
          } else {
            array_push($rbac[$GroupID]['PermittedResources'],$Key);
            file_put_contents(__DIR__.'/../'.getConfig("System","rbacjson"), json_encode($rbac, JSON_PRETTY_PRINT));
            writeLog("RBAC","Added $Key to ".$rbac[$GroupID]['Name'],"warning",$rbac[$GroupID]);
          }

          ## Add Menus to Array
          foreach ($roles['Resources'][$Key]['PermittedMenus'] as $PermittedMenu) {
            if (in_array($PermittedMenu,$rbac[$GroupID]['PermittedMenus'])) {
              writeLog("RBAC","$PermittedMenu is already assigned to: ".$rbac[$GroupID]['Name'],"debug",$rbac[$GroupID]);
            } else {
              array_push($rbac[$GroupID]['PermittedMenus'],$PermittedMenu);
              file_put_contents(__DIR__.'/../'.getConfig("System","rbacjson"), json_encode($rbac, JSON_PRETTY_PRINT));
              writeLog("RBAC","Added Menu: $PermittedMenu to ".$rbac[$GroupID]['Name'],"info",$rbac[$GroupID]);
            }
          }
        } else if ($Value == "false") {
          ## Remove Key from Array
          if (in_array($Key,$rbac[$GroupID]['PermittedResources'])) {
            if (($keytoremove = array_search($Key, $rbac[$GroupID]['PermittedResources'])) !== false) {
              unset($rbac[$GroupID]['PermittedResources'][$keytoremove]);
              $rbac[$GroupID]['PermittedResources'] = array_values($rbac[$GroupID]['PermittedResources']);
              file_put_contents(__DIR__.'/../'.getConfig("System","rbacjson"), json_encode($rbac, JSON_PRETTY_PRINT));
              writeLog("RBAC","Removed $Key from ".$rbac[$GroupID]['Name'],"warning",$rbac[$GroupID]);
            }
          } else {
            writeLog("RBAC","$Key is not assigned to ".$rbac[$GroupID]['Name'],"error",$Key,$rbac[$GroupID]);
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
                file_put_contents(__DIR__.'/../'.getConfig("System","rbacjson"), json_encode($rbac, JSON_PRETTY_PRINT));
                writeLog("RBAC","No permitted resources left, removing permitted menus from ".$rbac[$GroupID]['Name'],"debug",$rbac[$GroupID]);
              }
            } else {
              writeLog("RBAC","$PermittedMenu is not assigned to ".$rbac[$GroupID]['Name'],"error",$Key,$rbac[$GroupID]);
            }
            if (!$Needed) {
              if (($menutoremove = array_search($PermittedMenu, $rbac[$GroupID]['PermittedMenus'])) !== false) {
                unset($rbac[$GroupID]['PermittedMenus'][$menutoremove]);
                $rbac[$GroupID]['PermittedMenus'] = array_values($rbac[$GroupID]['PermittedMenus']);
                file_put_contents(__DIR__.'/../'.getConfig("System","rbacjson"), json_encode($rbac, JSON_PRETTY_PRINT));
                writeLog("RBAC","Removed Menu: $PermittedMenu from ".$rbac[$GroupID]['Name'],"info",$rbac[$GroupID]);
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