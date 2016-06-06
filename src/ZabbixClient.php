<?php

namespace kamermans\ZabbixClient;

use Graze\GuzzleHttp\JsonRpc;
use Graze\GuzzleHttp\JsonRpc\Client as RpcClient;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request as HttpRequest;

class ZabbixClient {

    private $client;
    private $auth_token;
    private $request_id;

    public function __construct($url, $user, $pass) {
        $this->request_id = time() * 10;
        $this->client = RpcClient::factory($url);
        $this->login($user, $pass);
    }

    public function request($method, $params=[]) {
        $request = $this->client->request(++$this->request_id, $method, $params);
        // https://www.zabbix.com/documentation/3.0/manual/api/reference/apiinfo/version
        if ($this->auth_token !== null && $method != 'apiinfo.version') {
            $request = $this->authenticateRequest($request);
        }
        return new ApiResponse($this->client->send($request));
    }

    public function ping() {
        $start = microtime(true);
        // The parameters array has to be sent. Guzzle eats up an empty array.
        $this->request('apiinfo.version', ['debug' => 'true']);
        return round((microtime(true) - $start) * 100, 2);
    }

    private function authenticateRequest(HttpRequest $request) {
        $body = JsonRpc\json_decode((string)$request->getBody()->getContents(), true);
        $body['auth'] = $this->auth_token;
        $json_body = JsonRpc\json_encode($body);
        $request = $request->withBody(Psr7\stream_for($json_body));
        return $request;
    }

    private function login($user, $pass) {
        try {
            $this->auth_token = $this->request('user.login', [
                'user' => $user,
                'password' => $pass,
            ])->getResult();
        } catch (ApiException $e) {
            throw new AuthException($e->getResponse());
        }
    }


}
