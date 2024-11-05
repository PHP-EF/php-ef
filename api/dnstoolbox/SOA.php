<?php
include_once('./DNSToolboxInterface.php');
use RemotelyLiving\PHPDNS\Resolvers;
use RemotelyLiving\PHPDNS\Entities;
class soa implements DNSToolboxInterface{
  public function getOutput($hostname,$sourceserver){
    $nameserver = new Entities\Hostname($sourceserver);
    $resolver = new Resolvers\Dig(null,null,$nameserver);
    $dnsType = new Entities\DNSRecordType("SOA");
    $records = $resolver->getRecords($domain,$dnsType);
    echo json_encode($records,JSON_PRETTY_PRINT);
  }
}

?>
