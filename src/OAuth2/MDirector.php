<?php
namespace MDOAuth\OAuth2;

use League\OAuth2\Client\Provider\AbstractProvider;
use MDOAuth\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MDirector implements Client
{
    protected $method;
    protected $uri;
    protected $parameters;
    protected $consumerKey;
    protected $consumerSecret;
    /**
     * @var AbstractProvider
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
    protected $accessToken;

    public function __construct(
        $accessTokenUrl,
        $consumerKey,
        $consumerSecret
    ) {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->provider = $this->getProvider($accessTokenUrl);
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

    protected function getProvider($accessTokenUrl)
    {
        $options = [
            'clientId' => 'webapp',
            'urlAccessToken' => $accessTokenUrl,
            'urlAuthorize' => '',
            'urlResourceOwnerDetails' => ''
        ];
        return new \League\OAuth2\Client\Provider\GenericProvider($options);
    }

    protected function initAccessToken()
    {
        if (!$this->accessToken) {
            $this->accessToken = $this->provider->getAccessToken(
                'password',
                [
                    'username' => $this->consumerKey,
                    'password' => $this->consumerSecret
                ]
            );
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

    public function request()
    {
        $this->initAccessToken();
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
