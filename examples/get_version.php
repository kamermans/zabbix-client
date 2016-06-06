<?php

// Include authentication.  You can create a zabbix_auth_local.php
// with your credentials if you don't want to edit the files in git
$local_config = __DIR__.'/zabbix_auth_local.php';
$default_config = __DIR__.'/zabbix_auth.php';
require file_exists($local_config)? $local_config: $default_config;

// If the Zabbix API returns a string, you can use the response as a string
// The parameters array has to be sent. Guzzle eats up an empty array.
$version = $client->request('apiinfo.version', ['debug' => 'true']);
echo "Server is running Zabbix $version\n";
