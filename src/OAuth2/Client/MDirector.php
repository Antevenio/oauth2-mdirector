<?php
namespace MDOAuth\OAuth2\Client;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use MDOAuth\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MDirector implements Client
{
    const CLIENT_ID = 'webapp';

    protected $method;
    protected $uri;
    protected $parameters;
    protected $consumerKey;
    protected $consumerSecret;
    /**
     * @var GenericProvider
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
        \MDOAuth\OAuth2\Client\Provider\MDirector $provider,
        $consumerKey,
        $consumerSecret
    ) {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->provider = $provider;
        $this->parameters = [];
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

    protected function prepareAccessToken()
    {
        if (!$this->accessToken) {
            $this->accessToken = $this->provider->getAccessToken(
                'password',
                [
                    'username' => $this->consumerKey,
                    'password' => $this->consumerSecret
                ]
            );
            return;
        }

        if ($this->accessToken->hasExpired()) {
            $this->accessToken = $this->provider->getAccessToken(
                'refresh_token',
                [
                    'refresh_token' => $this->accessToken->getRefreshToken()
                ]
            );
        }
    }

    protected function prepareRequest()
    {
        $requestOptions = [];
        $uri = $this->uri;

        if (strtolower($this->method) == 'get') {
            $uri = $uri . '?' . http_build_query($this->parameters);
        } else {
            $requestOptions = array_merge(
                $requestOptions,
                [
                    'body' => http_build_query($this->parameters)
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

    public function getLastResponse()
    {
        return $this->response;
    }

    public function getLastRequest()
    {
        return $this->request;
    }
}
