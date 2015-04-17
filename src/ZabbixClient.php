<?php

namespace kamermans\ZabbixClient;

use Graze\GuzzleHttp\JsonRpc\Client as RpcClient;
use Graze\GuzzleHttp\JsonRpc\Utils as RpcUtils;
use GuzzleHttp\Utils as GuzzleUtils;
use GuzzleHttp\Stream\Stream as GuzzleStream;
use GuzzleHttp\Message\RequestInterface as HttpRequestInterface;

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
        if ($this->auth_token !== null) {
            $request = $this->authenticateRequest($request);
        }
        return new ApiResponse($this->client->send($request));
    }

    public function ping() {
        $start = microtime(true);
        $version = $this->request('apiinfo.version');
        return round((microtime(true) - $start) * 100, 2);
    }

    private function authenticateRequest(HttpRequestInterface $request) {
        $body = GuzzleUtils::jsonDecode((string)$request->getBody(), true);
        $body['auth'] = $this->auth_token;
        $json_body = RpcUtils::jsonEncode($body);
        $request->setBody(GuzzleStream::factory($json_body));
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
