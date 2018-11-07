<?php

namespace MDOAuth\Test\OAuth2\Client\Provider;

use GuzzleHttp\Client;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Grant\GrantFactory;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use MDOAuth\OAuth2\Client\Provider\MDirector;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
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
    /**
     * @var Client | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpClient;
    protected $clientId;
    protected $baseAuthorizationUrl;
    protected $baseAccessTokenUrl;
    protected $resourceOwnerDetailsUrl;

    public function setUp()
    {
        $this->baseAuthorizationUrl = 'https://app.mdirector.com/oauth2-authorize';
        $this->baseAccessTokenUrl = 'https://app.mdirector.com/oauth2';
        $this->resourceOwnerDetailsUrl = 'https://app.mdirector.com/oauth2-api';
        $this->clientId = 'webapp';

        $this->grantFactory = $this->createMock(GrantFactory::class);
        $this->httpClient = $this->createMock(Client::class);

        $this->sut = new MDirector(
            [],
            [
                'grantFactory' => $this->grantFactory,
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
            $this->baseAuthorizationUrl,
            $this->sut->getBaseAuthorizationUrl()
        );
    }

    public function testGetBaseAccessTokenUrl()
    {
        $this->assertEquals(
            $this->baseAccessTokenUrl,
            $this->sut->getBaseAccessTokenUrl([])
        );
    }

    public function testGetResourceOwnerDetailsUrl()
    {
        /** @var \League\OAuth2\Client\Token\AccessToken $accessToken */
        $accessToken = $this->createMock(AccessToken::class);
        $this->assertEquals(
            $this->resourceOwnerDetailsUrl,
            $this->sut->getResourceOwnerDetailsUrl($accessToken)
        );
    }

    public function testGetAccessToken()
    {
        $grantName = 'someGrant';
        $options = ['option1' => '1', 'option2' => '2'];
        $this->setupGrantMock($grantName, $options);

        $accessTokenId = 'aTokenId';
        $refreshTokenId = 'refreshTokenId';
        $expiration = 3600;

        $response = $this->createResponse(
            json_encode(
                [
                    'access_token' => $accessTokenId,
                    'token_type' => 'Bearer',
                    'refresh_token' => $refreshTokenId,
                    'expires_in' => $expiration,
                    'scope' => null
                ]
            )
        );

        $this->setupAccessTokenHttpClientMock($response);

        $token = $this->sut->getAccessToken($grantName, $options);
        $this->assertEquals($token->getToken(), $accessTokenId);
        $this->assertEquals($token->getExpires(), time() + $expiration);
        $this->assertEquals($token->getRefreshToken(), $refreshTokenId);
        $this->assertNull($token->getResourceOwnerId());
        $this->assertEquals('Bearer', $token->getValues()['token_type']);
        $this->assertNull($token->getValues()['scope']);
    }

    protected function setupGrantMock($grantName, $options)
    {
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
    }

    protected function createResponse($body)
    {
        /** @var ResponseInterface | \PHPUnit_Framework_MockObject_MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue($body));
        $response->expects($this->any())
            ->method('getHeader')
            ->will($this->returnValue(['content-type' => 'json']));
        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(200));

        return $response;
    }

    protected function setupAccessTokenHttpClientMock(ResponseInterface $response)
    {
        $this->httpClient->expects($this->once())
            ->method('send')
            ->with($this->callback(function (RequestInterface $request) {
                $this->assertEquals('POST', $request->getMethod());
                $this->assertEquals(
                    $this->baseAccessTokenUrl,
                    $request->getUri()->__toString()
                );

                return true;
            }))
            ->will($this->returnValue($response));
    }

    public function testGetAccessTokenShouldThrowIdentityExceptionOnError()
    {
        $grantName = 'someGrant';
        $options = ['option1' => '1', 'option2' => '2'];
        $this->setupGrantMock($grantName, $options);

        $error = 'someError';
        $errorDescription = 'someErrorDescription';
        $code = 123;

        $response = $this->createResponse(
            json_encode(
                [
                    'error' => $error,
                    'error_description' => $errorDescription,
                    'code' => $code
                ]
            )
        );

        $this->setupAccessTokenHttpClientMock($response);
        $this->expectException(IdentityProviderException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage($error . ': ' . $errorDescription);
        $this->sut->getAccessToken($grantName, $options);
    }

    public function testGetAuthorizationUrl()
    {
        $authorizationUrl = $this->sut->getAuthorizationUrl([]);
        $this->assertRegExp(
            '/^' . preg_quote($this->baseAuthorizationUrl, '/') . '/',
            $authorizationUrl
        );
    }

    public function testGetResourceOwner()
    {
        $accessTokenId = 'aTokenId';
        $resourceOwnerId = 'resourceOwnerId';

        $accessToken = new AccessToken([
            'access_token' => $accessTokenId,
            'resource_owner_id' => $resourceOwnerId
        ]);

        $response = $this->createResponse(
            json_encode([])
        );

        $this->setupResourceOwnerHttpClientMock($response);
        $this->assertNull($this->sut->getResourceOwner($accessToken));
    }

    protected function setupResourceOwnerHttpClientMock(ResponseInterface $response)
    {
        $this->httpClient->expects($this->once())
            ->method('send')
            ->with($this->callback(function (RequestInterface $request) {
                $this->assertEquals('GET', $request->getMethod());
                $this->assertEquals(
                    $this->resourceOwnerDetailsUrl,
                    $request->getUri()->__toString()
                );

                return true;
            }))
            ->will($this->returnValue($response));
    }
}
