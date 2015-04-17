<?php

// Include authentication.  You can create a zabbix_auth_local.php
// with your credentials if you don't want to edit the files in git
$local_config = __DIR__.'/zabbix_auth_local.php';
$default_config = __DIR__.'/zabbix_auth.php';
require file_exists($local_config)? $local_config: $default_config;

$response = $client->request('host.get', [
    'output' => [
        'host_id',
        'host',
    ],
]);

// If the Zabbix API returns an array, you can just iterate the response
foreach ($response as $host) {
    echo "{$host['hostid']}: {$host['host']}\n";
}
