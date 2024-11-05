<?php
include_once('./DNSToolboxInterface.php');

class ReverseLookup implements DNSToolboxInterface{
    public function getOutput($ip,$source){
        if((bool)ip2long($ip)){
            $response = gethostbyaddr($ip);  
            return '[{"'.$ip.'": "'.$response.'"}]';
        } else {
            return '[{"error": "Please enter a valid IP"}]';
        }

    }
}
?>
