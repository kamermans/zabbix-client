<?php

require __DIR__.'/../vendor/autoload.php';

use kamermans\ZabbixClient\ZabbixClient;

$client = new ZabbixClient(
    "http://myzabbixserver/zabbix/api_jsonrpc.php",
    "myusername",
    "mypassword"
);
