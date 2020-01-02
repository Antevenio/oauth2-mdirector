<?php

namespace MDOAuth\OAuth2;

use Psr\Http\Message\ResponseInterface;

interface Wrapper
{
    public function setUri($uri);

    public function setMethod($method);

    public function setParameters($parameters);

    public function setUserAgent($userAgent);

    /**
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request();

    public function getLastResponse();

    public function getLastRequest();

    public function getProvider();
}
