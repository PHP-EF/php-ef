<?php
include_once('./DNSToolboxInterface.php');
use RemotelyLiving\PHPDNS\Resolvers;
use RemotelyLiving\PHPDNS\Entities;
$dmarc = "_dmarc.";
$dmarchostname = $dmarc . $domain;
$hostname = $dmarchostname;
$domain = $dmarchostname;
class Dmarc implements DNSToolboxInterface{
  public function getOutput($hostname,$sourceserver){
    $nameserver = new Entities\Hostname($sourceserver);
    $resolver = new Resolvers\Dig(null,null,$nameserver);
    $records = $resolver->getTXTRecords($hostname);
    echo json_encode($records,JSON_PRETTY_PRINT);
  }
}
?>
