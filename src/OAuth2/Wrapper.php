<?php

namespace MDOAuth\OAuth2;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Wrapper
{
    const DEFAULT_USER_AGENT = 'oauth2-mdirector client';

    protected $method;
    protected $uri;
    protected $parameters;
    protected $consumerKey;
    protected $consumerSecret;
    protected $userAgent;
    /**
     * @var \MDOAuth\OAuth2\Client\Provider\MDirector
     */
    protected $provider;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var ResponseInterface
     */
    protected $response;
    /**
     * @var AccessToken
     */
    protected $accessToken;

    public function __construct(
        AbstractProvider $provider,
        $consumerKey,
        $consumerSecret
    ) {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->provider = $provider;
        $this->parameters = [];
        $this->userAgent = self::DEFAULT_USER_AGENT;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request()
    {
        $this->prepareAccessToken();
        $this->prepareRequest();

        $this->response = $this->provider->getHttpClient()->send($this->request);
        return $this->response;
    }

    public function prepareAccessToken()
    {
        if (!$this->accessToken) {
            $this->accessToken = $this->provider->getAccessToken(
                'password',
                [
                    'username' => $this->consumerKey,
                    'password' => $this->consumerSecret
                ]
            );
            return $this->accessToken;
        }

        if ($this->accessToken->hasExpired()) {
            $this->accessToken = $this->provider->getAccessToken(
                'refresh_token',
                [
                    'refresh_token' => $this->accessToken->getRefreshToken()
                ]
            );
        }

        return $this->accessToken;
    }

    protected function prepareRequest()
    {
        $uri = $this->uri;

        $requestOptions = [
            'headers' => [
                'User-Agent' => $this->userAgent,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ];

        if (strtolower($this->method) == 'get') {
            $uri = $uri . '?' . http_build_query($this->parameters);
        } else {
            $requestOptions = array_merge_recursive(
                $requestOptions,
                [
                    'body' => json_encode($this->parameters)
                ]
            );
        }

        $this->request = $this->provider->getAuthenticatedRequest(
            $this->method,
            $uri,
            $this->accessToken,
            $requestOptions
        );
    }

    public function getLastResponse()
    {
        return $this->response;
    }

    public function getLastRequest()
    {
        return $this->request;
    }

    public function getProvider()
    {
        return $this->provider;
    }
}
