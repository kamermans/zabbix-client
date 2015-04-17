# zabbix-client

This is a simple, yet powerful Zabbix API client for PHP.  I have made no attempt to model the Zabbix API in PHP functions, but rather, I have made the interface extremely easy to use in order to query the Zabbix JSON-RPC API.

There are several examples in `examples/` that should be quite useful.  You can even make a copy of `zabbix_auth.php` and call it `zabbix_auth_local.php` and that file will be used by the examples instead, so you don't need to taint the git repo.

## Compatibility
This library is designed and tested for Zabbix 2.2 and it's likely to have problems with other versions.  If you want to add support for 2.4 (or any other version) you are more than welcome :)

## Authentication
Zabbix uses a username and password to connect initially, then an auth token for subsequent requests.

Here's how you connect and authenticate:

```php
$client = new ZabbixClient(
    "http://myzabbixserver/zabbix/api_jsonrpc.php",
    "myusername",
    "mypassword"
);
```

## Calling Zabbix Endpoints
All the Zabbix endpoints are described in the [Zabbix Documentation](https://www.zabbix.com/documentation/2.2/manual/api).

To call and endpoint, use the `$client->request(string endpoint, array params=[])` method:

```php
$response = $client->request('apiinfo.version');
echo "Server is running Zabbix $response\n";
```

Note that `$response` is a `kamermans\ZabbixClient\ApiResponse` object that has some magical properties.  In the example above, the Zabbix endpoint `apiinfo.version` returns a single string, so you can use `$response` as a string as well.

Most endpoints return an associative array as a response:

```php
$hosts = $client->request('host.get', [
    'output' => [
        'host_id',
        'host',
    ],
]);

foreach ($hosts as $host) {
    echo "{$host['hostid']}: {$host['host']}\n";
}
```

As you can see, you can iterate over the response object (although it's still a `kamermans\ZabbixClient\ApiResponse`).

In this example we also passed an array of parameters as the second argument.

## Error Handling
When the Zabbix API returns an error, it is wrapped up into a proper exception and thrown.

There are three types of exceptions that you can catch inside the `kamermans\ZabbixClient` namespace:
 - `Exception`: base class used for generic exceptions.  All exceptions extend this one.
 - `ApiException`: all API calls that returned a parseable JSON response are wrapped by this exception which gives you a well-formed error message from Zabbix and access to raw response via `ApiException::getResponse()`.
 - `AuthException`: an extention of `ApiException` that only gets thrown for authentication errors so they can be handled differently.


## Utility Functions

### Response Dumping
For easy troubleshooting of responses, you can use the `ApiResponse::dump()` function to output the response to the screen, or `ApiResponse::dd()` to dump and die:

```php
$response = $client->request('apiinfo.version');
$response->dd(); // '2.2.8'
```

### Server Ping
There is a utility function to check to latency (in milliseconds) to the Zabbix Server API:

```php
$time = $client->ping();
echo "Pinged Zabbix Server in {$time}ms\n";
```

## Complete Example
This example lists all the system items in the Zabbix Server:

```php
<?php

require __DIR__.'/../vendor/autoload.php';

use kamermans\ZabbixClient\ZabbixClient;

$client = new ZabbixClient(
    "http://myzabbixserver/zabbix/api_jsonrpc.php",
    "myusername",
    "mypassword"
);

$host = 'Zabbix server';
$key = 'system';

echo "Showing $key items for $host\n\n";

$response = $client->request('item.get', [
    'host' => $host,
    'search' => [
        'key_' => $key,
    ],
    'sortfield' => 'name',
]);

foreach ($response as $item) {
    echo "{$item['name']}: {$item['lastvalue']}\n";
}

```

Output:

```
Showing system items for Zabbix server

Context switches per second: 715
CPU $2 time: 98.3452
CPU $2 time: 0.0000
CPU $2 time: 0.0000
CPU $2 time: 0.0000
CPU $2 time: 0.0042
CPU $2 time: 0.4585
CPU $2 time: 0.4960
CPU $2 time: 0.6961
Free swap space: 2207690752
Free swap space in %: 84.8960
Host boot time: 1427393569
Host local time: 1429251462
Host name: zabbix
Interrupts per second: 481
Number of logged in users: 1
Processor load (1 min average per core): 0.0000
Processor load (15 min average per core): 0.0125
Processor load (5 min average per core): 0.0075
System information: Linux zabbix 3.13.0-32-generic #57-Ubuntu SMP Tue Jul 15 03:51:08 UTC 2014 x86_64
System uptime: 1857561
Total swap space: 2600464384
```