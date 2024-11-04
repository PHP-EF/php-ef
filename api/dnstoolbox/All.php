<?php
include_once('./DNSToolboxInterface.php');
use RemotelyLiving\PHPDNS\Resolvers;
use RemotelyLiving\PHPDNS\Entities;
class All implements DNSToolboxInterface{
  public function getOutput($hostname,$sourceserver){
    $nameserver = new Entities\Hostname($sourceserver);
    $resolver = new Resolvers\Dig(null,null,$nameserver);
    $records = $resolver->getRecords($hostname);
    echo json_encode($records,JSON_PRETTY_PRINT);
  }
}
?>
