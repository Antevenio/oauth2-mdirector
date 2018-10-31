<?php
namespace MDOAuth\OAuth2Client;

use League\OAuth2\Client\Provider\GenericProvider;
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
    protected $accessToken;

    public function __construct(
        $accessTokenUrl,
        $consumerKey,
        $consumerSecret
    ) {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;

        $options = [
            'clientId' => 'webopp',
            'urlAccessToken' => $accessTokenUrl,
            'urlAuthorize' => '',
            'urlResourceOwnerDetails' => ''
        ];

        $this->provider = new \League\OAuth2\Client\Provider\GenericProvider($options);

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
//
//    protected function getProvider($accessTokenUrl)
//    {
//        $options = [
//            'clientId' => 'webapp',
//            'urlAccessToken' => $accessTokenUrl,
//            'urlAuthorize' => '',
//            'urlResourceOwnerDetails' => ''
//        ];
//        return new GenericProvider($options);
//    }

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
