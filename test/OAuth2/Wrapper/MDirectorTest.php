<?php
namespace MDOAuth\Test\OAuth2;

use League\OAuth2\Client\Token\AccessToken;
use MDOAuth\OAuth2\Wrapper\MDirector;
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
     * @var MDirector | Mock
     */
    protected $httpClient;
    protected $accessTokenId;
    protected $refreshTokenId;

    protected $userAgent;

    public function setUp()
    {
        $this->key = 'someKey';
        $this->secret = 'someSecret';
        $this->uri = 'http://some.uri/some.path';
        $this->method = 'someMethod';
        $this->accessTokenId = 'aTokenId';
        $this->refreshTokenId = 'refreshTokenId';

        $this->userAgent = 'my custom user agent';

        $this->parameters =  [
            'a' => 'b',
            'c' => 'd'
        ];

        $this->provider = \Mockery::mock(\MDOAuth\OAuth2\Client\Provider\MDirector::class)
            ->shouldIgnoreMissing();
        $this->httpClient = \Mockery::mock(MDirector::class)
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
            ->with(
                'get',
                'http://some.uri/some.path?a=b&c=d',
                $accessToken,
                ['headers' => $this->getUserAgentHeader(MDirector::DEFAULT_USER_AGENT)]
            )
            ->andReturn($request);

        $this->sut->setUri($this->uri)
            ->setMethod('get')
            ->setParameters($this->parameters)
            ->request();
    }

    protected function getUserAgentHeader($userAgent)
    {
        return ['User-Agent' => $userAgent];
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
                \Mockery::on([$this, 'assertParametersInBody'])
            )
            ->andReturn($request);

        $this->sut->setUri($this->uri)
            ->setMethod('post')
            ->setParameters($this->parameters)
            ->request();
    }

    public function assertParametersInBody($requestOptions)
    {
        $this->assertArrayHasKey('headers', $requestOptions);
        $this->assertArrayHasKey('Content-Type', $requestOptions['headers']);
        $this->assertEquals(
            'application/x-www-form-urlencoded',
            $requestOptions['headers']['Content-Type']
        );

        return true;
    }

    public function testRequestSetsDefaultUserAgentHeaderWhenUsingTheGetMethod()
    {
        $request = \Mockery::mock(RequestInterface::class)
            ->shouldIgnoreMissing();

        $this->provider->shouldReceive('getAuthenticatedRequest')
            ->once()
            ->with(
                'get',
                \Mockery::any(),
                \Mockery::any(),
                \Mockery::on([$this, 'assertDefaultUserAgentHeader'])
            )
            ->andReturn($request);

        $this->sut->setUri($this->uri)
            ->setMethod('get')
            ->setParameters($this->parameters)
            ->request();
    }

    public function testRequestSetsCustomUserAgentHeaderWhenUsingTheGetMethod()
    {
        $request = \Mockery::mock(RequestInterface::class)
            ->shouldIgnoreMissing();

        $this->provider->shouldReceive('getAuthenticatedRequest')
            ->once()
            ->with(
                'get',
                \Mockery::any(),
                \Mockery::any(),
                \Mockery::on([$this, 'assertCustomUserAgentHeader'])
            )
            ->andReturn($request);

        $this->sut->setUserAgent($this->userAgent);
        $this->sut->setUri($this->uri)
            ->setMethod('get')
            ->setParameters($this->parameters)
            ->request();
    }

    public function testRequestSetsDefaultUserAgentHeaderWhenNotUsingTheGetMethod()
    {
        $request = \Mockery::mock(RequestInterface::class)
            ->shouldIgnoreMissing();

        $method = 'some method';

        $this->provider->shouldReceive('getAuthenticatedRequest')
            ->once()
            ->with(
                $method,
                \Mockery::any(),
                \Mockery::any(),
                \Mockery::on([$this, 'assertDefaultUserAgentHeader'])
            )
            ->andReturn($request);

        $this->sut->setUri($this->uri)
            ->setMethod($method)
            ->setParameters($this->parameters)
            ->request();
    }

    public function testRequestSetsCustomUserAgentHeaderWhenNotUsingTheGetMethod()
    {
        $request = \Mockery::mock(RequestInterface::class)
            ->shouldIgnoreMissing();

        $method = 'some method';

        $this->provider->shouldReceive('getAuthenticatedRequest')
            ->once()
            ->with(
                $method,
                \Mockery::any(),
                \Mockery::any(),
                \Mockery::on([$this, 'assertCustomUserAgentHeader'])
            )
            ->andReturn($request);

        $this->sut->setUri($this->uri)
            ->setMethod($method)
            ->setParameters($this->parameters)
            ->setUserAgent($this->userAgent)
            ->request();
    }

    public function assertDefaultUserAgentHeader($requestOptions)
    {
        return $this->assertUserAgentHeader($requestOptions, MDirector::DEFAULT_USER_AGENT);
    }

    protected function assertUserAgentHeader($requestOptions, $userAgent)
    {
        $this->assertArrayHasKey('headers', $requestOptions);
        $this->assertArrayHasKey('User-Agent', $requestOptions['headers']);
        $this->assertEquals(
            $userAgent,
            $requestOptions['headers']['User-Agent']
        );

        return true;
    }

    public function assertCustomUserAgentHeader($requestOptions)
    {
        return $this->assertUserAgentHeader($requestOptions, $this->userAgent);
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
