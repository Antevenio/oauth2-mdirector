<?php
namespace MDOAuth\Test\OAuth2;

use GuzzleHttp\ClientInterface;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use MDOAuth\OAuth2\MDirector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class MDirectorTest extends TestCase
{
    /**
     * @var MDirector
     */
    protected $sut;

    protected $accessTokenUrl;
    protected $key;
    protected $secret;

    protected $uri;
    /**
     * @var GenericProvider | MockObject
     */
    protected $provider;
    /**
     * @var AccessToken | MockObject
     */
    protected $accessToken;
    /**
     * @var ClientInterface | MockObject
     */
    protected $httpClient;
    /**
     * @var RequestInterface | MockObject
     */
    protected $request;

    public function setUp()
    {
        $this->accessTokenUrl = 'http://some.url/l';
        $this->key = 'someKey';
        $this->secret = 'someSecret';
        $this->uri = 'https://some.url/k';
        $this->provider = $this->createMock(GenericProvider::class);
        $this->accessToken = $this->createMock(AccessToken::class);
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->request = $this->createMock(RequestInterface::class);
        $this->sut = new MDirector($this->provider, $this->key, $this->secret);
    }

    public function testShouldBeCreated()
    {
        $this->assertInstanceOf(MDirector::class, $this->sut);
    }

    public function testRequestShouldGetANewAccessToken()
    {
        $this->provider->expects($this->once())
            ->method('getAccessToken')
            ->with(
                $this->equalTo('password'),
                $this->equalTo(
                    [
                        'username' => $this->key,
                        'password' => $this->secret
                    ]
                )
            )
            ->will($this->returnValue($this->accessToken));

        $this->provider->expects($this->any())
            ->method('getHttpClient')
            ->will($this->returnValue($this->httpClient));

        $this->provider->expects($this->any())
            ->method('getAuthenticatedRequest')
            ->will($this->returnValue($this->request));

        $this->sut->setMethod('get')->setUri($this->uri)->request();
    }
}
