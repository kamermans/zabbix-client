<?php

namespace kamermans\ZabbixClient;

class ApiException extends Exception {

    private $response;

    public function __construct(ApiResponse $response, $previous=null) {
        $this->response = $response;
        $rpcError = $response->getResult();
        $message = "{$rpcError->getRpcErrorMessage()} {$rpcError->getRpcErrorData()} ({$rpcError->getRpcErrorCode()})";
        parent::__construct($message, $response['code'], $previous);
    }

    public function getResponse() {
        return $this->response;
    }
}
