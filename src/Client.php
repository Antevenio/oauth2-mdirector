<?php
namespace MDOAuth;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface Client
{
    public function setUri($uri);
    public function setMethod($method);
    public function setParameters($parameters);
    public function request();
    /**
     * @return ResponseInterface
     */
    public function getLastResponse();

    /**
     * @return RequestInterface
     */
    public function getLastRequest();
}
