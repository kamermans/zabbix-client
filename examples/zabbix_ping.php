<?php

require __DIR__.'/../vendor/autoload.php';

use kamermans\ZabbixClient\ZabbixClient;
use Symfony\Component\Yaml\Yaml;

$zabbix_config = (object)Yaml::parse(file_get_contents(__DIR__.'/../config/zabbix.yml'));

$client = new ZabbixClient(
    $zabbix_config->api_url,
    $zabbix_config->user,
    $zabbix_config->pass
);

$time = $client->ping();
echo "Pinged Zabbix Server in {$time}ms\n";
