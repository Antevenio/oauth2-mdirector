<?php

namespace MDOAuth\Test\OAuth2\Client\Provider;

use GuzzleHttp\Client;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Grant\GrantFactory;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\RequestFactory;
use MDOAuth\OAuth2\Client\Provider\MDirector;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class MDirectorTest extends TestCase
{
    /**
     * @var MDirector
     */
    protected $sut;
    /**
     * @var GrantFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $grantFactory;
    protected $requestFactory;
    /**
     * @var Client | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpClient;

    protected $clientId;

    public function setUp()
    {
        // TODO: Should this be provided by the oauth provider?
        $this->clientId = 'mock_client_id';
        $this->grantFactory = $this->createMock(GrantFactory::class);
        $this->httpClient = $this->createMock(Client::class);
        // TODO: Do i have to mock this last one?
        // $this->requestFactory = $this->createMock(RequestFactory::class);

        $this->sut = new MDirector(
            [
                // TODO: Should this be provided by the oauth provider?
                'clientId' => $this->clientId,
            ],
            [
                'grantFactory' => $this->grantFactory,
                //'requestFactory' => $this->requestFactory,
                'httpClient' => $this->httpClient
            ]
        );
    }

    public function testShouldBeCreated()
    {
        $this->assertInstanceOf(MDirector::class, $this->sut);
    }

    public function testGetBaseAuthorizationUrl()
    {
        $this->assertEquals(
            'https://app.mdirector.com/oauth2-authorize',
            $this->sut->getBaseAuthorizationUrl()
        );
    }

    public function testGetBaseAccessTokenUrl()
    {
        $this->assertEquals(
            'https://app.mdirector.com/oauth2',
            $this->sut->getBaseAccessTokenUrl([])
        );
    }

    public function testGetResourceOwnerDetailsUrl()
    {
        /** @var \League\OAuth2\Client\Token\AccessToken $accessToken */
        $accessToken = $this->createMock(AccessToken::class);
        $this->assertEquals(
            'https://app.mdirector.com/oauth2-api',
            $this->sut->getResourceOwnerDetailsUrl($accessToken)
        );
    }

    public function testGetAccessToken()
    {
        $grantName = 'someGrant';
        $options = ['option1' => '1', 'option2' => '2'];

        $grant = $this->createMock(AbstractGrant::class);
        $preparedRequestParameters = [];
        $grant->expects($this->once())
            ->method('prepareRequestParameters')
            ->with(
                $this->callback(function ($params) {
                    $this->assertArrayHasKey('client_id', $params);
                    $this->assertEquals($this->clientId, $params['client_id']);
                    return true;
                }),
                $this->equalTo($options)
            )
            ->will($this->returnValue($preparedRequestParameters));

        $this->grantFactory->expects($this->once())
            ->method('getGrant')
            ->with($this->equalTo($grantName))
            ->will($this->returnValue($grant));

        $response = $this->createMock(ResponseInterface::class);

        $this->httpClient->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($haha) {
                //TODO: Should we mock the request preparation?, most probably YES
                var_dump($haha);
                return true;
            }))
            ->will($this->returnValue($response));

        $this->sut->getAccessToken($grantName, $options);
    }
}
