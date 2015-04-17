<?php

namespace kamermans\ZabbixClient;

use Graze\GuzzleHttp\JsonRpc\Client as RpcClient;

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
        $params['auth'] = $this->auth_token;
        $request = $this->client->request(++$this->request_id, $method, $params);
        return new ApiResponse($this->client->send($request));
    }

    public function ping() {
        $start = microtime(true);
        $version = $this->request('apiinfo.version');
        return round((microtime(true) - $start) * 100, 2);
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
