<?php
namespace MDOAuth\Test\OAuth2;

use GuzzleHttp\Client;
use League\OAuth2\Client\Token\AccessToken;
use MDOAuth\OAuth2\Client\MDirector;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class MDirectorTest extends TestCase
{
    /**
     * @var MDirector
     */
    protected $sut;

    protected $key;
    protected $secret;

    protected $uri;
    protected $method;
    protected $parameters;

    /**
     * @var \MDOAuth\OAuth2\Client\Provider\MDirector | Mock
     */
    protected $provider;
    /**
     * @var Client | Mock
     */
    protected $httpClient;
    protected $accessTokenId;
    protected $refreshTokenId;

    public function setUp()
    {
        $this->key = 'someKey';
        $this->secret = 'someSecret';
        $this->uri = 'http://some.uri/some.path';
        $this->method = 'someMethod';
        $this->accessTokenId = 'aTokenId';
        $this->refreshTokenId = 'refreshTokenId';

        $this->parameters =  [
            'a' => 'b',
            'c' => 'd'
        ];

        $this->provider = \Mockery::mock(\MDOAuth\OAuth2\Client\Provider\MDirector::class)
            ->shouldIgnoreMissing();
        $this->httpClient = \Mockery::mock(Client::class)
            ->shouldIgnoreMissing();
        $this->sut = new MDirector($this->provider, $this->key, $this->secret);

        $this->provider->shouldReceive('getHttpClient')
            ->andReturn($this->httpClient);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    public function testShouldBeCreated()
    {
        $this->assertInstanceOf(MDirector::class, $this->sut);
    }

    public function testRequestShouldGetANewAccessToken()
    {
        $accessToken = $this->getForgedAccessToken(time() + 3600);

        $this->setupNewAccessTokenMock($accessToken);

        $request = \Mockery::mock(RequestInterface::class)
            ->shouldIgnoreMissing();

        $this->provider->shouldReceive('getAuthenticatedRequest')
            ->once()
            ->andReturn($request);

        $this->sut->setUri($this->uri)
            ->setMethod($this->method)
            ->setParameters($this->parameters)
            ->request();
    }

    protected function getForgedAccessToken($expires)
    {
        return new AccessToken([
            'access_token' => $this->accessTokenId,
            'refresh_token' => $this->refreshTokenId,
            'expires' => $expires
        ]);
    }

    protected function setupNewAccessTokenMock($returnedToken)
    {
        $this->provider->shouldReceive('getAccessToken')
            ->once()
            ->ordered()
            ->with(
                'password',
                [
                    'username' => $this->key,
                    'password' => $this->secret
                ]
            )
            ->andReturn($returnedToken);
    }

    public function testRequestShouldNotGetANewAccessTokenIfAlreadyGotOne()
    {
        $accessToken = $this->getForgedAccessToken(time() + 3600);

        $this->setupNewAccessTokenMock($accessToken);
        $this->provider->shouldNotReceive('getAccessToken')
            ->with(
                'refresh_token',
                $this->anything()
            );
        $request = \Mockery::mock(RequestInterface::class)
            ->shouldIgnoreMissing();

        $this->provider->shouldReceive('getAuthenticatedRequest')
            ->andReturn($request);

        $this->sut->setUri($this->uri)
            ->setMethod($this->method)
            ->setParameters($this->parameters)
            ->request();

        $this->sut->request();
    }

    public function testRequestShouldRefreshTokenIfTokenExpired()
    {
        $accessToken = $this->getForgedAccessToken(time() - 1);

        $this->setupNewAccessTokenMock($accessToken);
        $this->setupRefreshTokenMock($accessToken, $accessToken);

        $request = \Mockery::mock(RequestInterface::class)
            ->shouldIgnoreMissing();

        $this->provider->shouldReceive('getAuthenticatedRequest')
            ->andReturn($request);

        $this->sut->setUri($this->uri)
            ->setMethod($this->method)
            ->setParameters($this->parameters)
            ->request();

        $this->sut->request();
    }

    protected function setupRefreshTokenMock(AccessToken $accessToken, $returnedToken)
    {
        $this->provider->shouldReceive('getAccessToken')
            ->once()
            ->ordered()
            ->with(
                'refresh_token',
                [
                    'refresh_token' => $accessToken->getRefreshToken()
                ]
            )
            ->andReturn($returnedToken);
    }

    public function testRequestAddsParametersToUriWhenUsingTheGetMethod()
    {
        $accessToken = $this->getForgedAccessToken(time() + 3600);

        $this->setupNewAccessTokenMock($accessToken);

        $request = \Mockery::mock(RequestInterface::class)
            ->shouldIgnoreMissing();

        $this->provider->shouldReceive('getAuthenticatedRequest')
            ->once()
            ->with('get', 'http://some.uri/some.path?a=b&c=d', $accessToken, [])
            ->andReturn($request);

        $this->sut->setUri($this->uri)
            ->setMethod('get')
            ->setParameters($this->parameters)
            ->request();
    }

    public function testRequestAddsParametersToBodyWhenNotUsingTheGetMethod()
    {
        $accessToken = $this->getForgedAccessToken(time() + 3600);

        $this->setupNewAccessTokenMock($accessToken);

        $request = \Mockery::mock(RequestInterface::class)
            ->shouldIgnoreMissing();

        $this->provider->shouldReceive('getAuthenticatedRequest')
            ->once()
            ->with(
                'post',
                'http://some.uri/some.path',
                $accessToken,
                [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                    'body' =>  'a=b&c=d'
                ]
            )
            ->andReturn($request);

        $this->sut->setUri($this->uri)
            ->setMethod('post')
            ->setParameters($this->parameters)
            ->request();
    }

    public function testGetLastResponse()
    {
        $accessToken = $this->getForgedAccessToken(time() + 3600);

        $this->setupNewAccessTokenMock($accessToken);

        $request = \Mockery::mock(RequestInterface::class)
            ->shouldIgnoreMissing();

        $this->provider->shouldReceive('getAuthenticatedRequest')
            ->once()
            ->andReturn($request);

        $response = $this->sut->setUri($this->uri)
            ->setMethod($this->method)
            ->setParameters($this->parameters)
            ->request();

        $this->assertEquals($response, $this->sut->getLastResponse());
    }

    public function testGetLastRequest()
    {
        $accessToken = $this->getForgedAccessToken(time() + 3600);

        $this->setupNewAccessTokenMock($accessToken);

        $request = \Mockery::mock(RequestInterface::class)
            ->shouldIgnoreMissing();

        $this->provider->shouldReceive('getAuthenticatedRequest')
            ->once()
            ->andReturn($request);

        $this->sut->setUri($this->uri)
            ->setMethod($this->method)
            ->setParameters($this->parameters)
            ->request();

        $this->assertEquals($request, $this->sut->getLastRequest());
    }
}
