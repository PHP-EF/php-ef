<?php
use RemotelyLiving\PHPDNS\Resolvers;
use RemotelyLiving\PHPDNS\Entities;

class DNSToolbox {

    public function a($hostname,$sourceserver) {
        $nameserver = new Entities\Hostname($sourceserver);
        $resolver = new Resolvers\Dig(null,null,$nameserver);
        $records = $resolver->getARecords($hostname);
        return $records;
    }

    public function aaaa($hostname,$sourceserver) {
        $nameserver = new Entities\Hostname($sourceserver);
        $resolver = new Resolvers\Dig(null,null,$nameserver);
        $records = $resolver->getAAAARecords($hostname);
        return $records;
    }

    public function cname($hostname,$sourceserver) {
        $nameserver = new Entities\Hostname($sourceserver);
        $resolver = new Resolvers\Dig(null,null,$nameserver);
        $records = $resolver->getCNAMERecords($hostname);
        return $records;
    }

    public function all($hostname,$sourceserver) {
        $nameserver = new Entities\Hostname($sourceserver);
        $resolver = new Resolvers\Dig(null,null,$nameserver);
        $records = $resolver->getRecords($hostname);
        return $records;
    }

    public function dmarc($hostname,$sourceserver) {
        $dmarc = "_dmarc.";
        $dmarchostname = $dmarc . $hostname;
        $nameserver = new Entities\Hostname($sourceserver);
        $resolver = new Resolvers\Dig(null,null,$nameserver);
        $records = $resolver->getTXTRecords($dmarchostname);
        return $records;
    }

    public function txt($hostname,$sourceserver) {
        $nameserver = new Entities\Hostname($sourceserver);
        $resolver = new Resolvers\Dig(null,null,$nameserver);
        $records = $resolver->getTxtRecords($hostname);
        return $records;
    }

    public function mx($hostname,$sourceserver) {
        $nameserver = new Entities\Hostname($sourceserver);
        $resolver = new Resolvers\Dig(null,null,$nameserver);
        $records = $resolver->getMXRecords($hostname);
        return $records;
    }

    public function ns($domain,$sourceserver) {
        $nameserver = new Entities\Hostname($sourceserver);
        $resolver = new Resolvers\Dig(null,null,$nameserver);
        $dnsType = new Entities\DNSRecordType("NS");
        $records = $resolver->getRecords($domain,$dnsType);
        return $records;
    }

    public function soa($hostname,$sourceserver) {
        $nameserver = new Entities\Hostname($sourceserver);
        $resolver = new Resolvers\Dig(null,null,$nameserver);
        $dnsType = new Entities\DNSRecordType("SOA");
        $records = $resolver->getRecords($hostname,$dnsType);
        return $records;
    }

    public function reverse($ip,$source) {
        if((bool)ip2long($ip)){
            $response = gethostbyaddr($ip);
            return [array(
                'ip' => $ip,
                'hostname' => $response
            )];
        } else {
            return array(
                'Status' => 'Error',
                'Message' => 'Invalid IP Address'
            );
        }
    }

    public function port($hostname,$port) {
        if (count($port) == 0) {
            $port = [22, 25, 53, 80 , 443, 445, 3389];
        }
        $portList = [];
        for ($i = 0; $i < count($port); $i++) {
            $fp = fsockopen($hostname, $port[$i], $errno, $errstr, 5);
            if ($fp) {
                $result = 'Open';
                fclose($fp);
            } else {
                $result = 'Closed';
            }
            $portList[] = array(
                'hostname' => $hostname,
                'port' => $port[$i],
                'result' => $result
            );
        }
        return $portList;
    }
}