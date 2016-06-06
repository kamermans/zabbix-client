<?php

// Include authentication.  You can create a zabbix_auth_local.php
// with your credentials if you don't want to edit the files in git
$local_config = __DIR__.'/zabbix_auth_local.php';
$default_config = __DIR__.'/zabbix_auth.php';
require file_exists($local_config)? $local_config: $default_config;

// Note that this is the `Host name`, not the `Visible name`
$host = 'Zabbix Server';
$key = 'system';

echo "Showing $key items for $host\n\n";

$response = $client->request('item.get', [
    'host' => $host,
    'search' => [
        'key_' => $key,
    ],
    'sortfield' => 'name',
]);

echo "Search returned ".$response->length()." result(s)\n\n";

// If the Zabbix API returns an array, you can just iterate the response
foreach ($response as $item) {
    echo "{$item['name']}: {$item['lastvalue']}\n";
}
