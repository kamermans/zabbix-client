<?php

namespace kamermans\ZabbixClient;

use GuzzleHttp\Message\ResponseInterface as HttpResponseInterface;

class ApiResponse implements \ArrayAccess {

    private $raw_response;
    private $jsonrpc;
    private $result;
    private $id;
    private $is_error = false;

    public function __construct(HttpResponseInterface $response) {
        $this->parseResponse($response);

        if ($this->isError()) {
            throw new ApiException($this);
        }
    }

    public function __toString() {
        if (is_array($this->result)) {
            return var_export($this->result, true);
        }

        return $this->result;
    }

    public function getResult() {
        return $this->result;
    }

    public function isError() {
        return $this->is_error;
    }

    public function dump() {
        var_export($this);
        echo "\n";
    }

    public function dd() {
        $this->dump();
        exit(2);
    }

    /* Start ArrayAccess */
    public function offsetSet($offset, $value) {
        throw new Exception("API Responses are read-only");
    }

    public function offsetExists($offset) {
        return is_array($this->result) && array_key_exists($offset, $this->result);
    }

    public function offsetUnset($offset) {
        throw new Exception("API Responses are read-only");
    }

    public function offsetGet($offset) {
        return $this->offsetExists($offset)? $this->result[$offset]: null;
    }
    /* End ArrayAccess */

    private function parseResponse(HttpResponseInterface $response) {
        try {
            $this->raw_response = $resp = $response->json();
            $this->jsonrpc = $resp['jsonrpc'];
            $this->id = $resp['id'];
            if (array_key_exists('error', $resp)) {
                $this->result = $resp['error'];
                $this->is_error = true;
            } else {
                $this->result = $resp['result'];
            }

        } catch (\Exception $e) {
            throw new ApiException("Unable to parse response from Zabbix API", null, $e);
        }
    }

}
