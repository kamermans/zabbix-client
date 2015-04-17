<?php

namespace kamermans\ZabbixClient;

class ApiException extends Exception {

    private $response;

    public function __construct(ApiResponse $response, $previous=null) {
        $this->response = $response;
        $message = "{$response['message']} {$response['data']} ({$response['code']})";
        parent::__construct($message, $response['code'], $previous);
    }

    public function getResponse() {
        return $this->response;
    }
}
