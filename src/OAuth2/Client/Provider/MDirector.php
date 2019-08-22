<?php

namespace MDOAuth\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;

class MDirector extends AbstractProvider
{
    use BearerAuthorizationTrait;

    const CLIENT_ID = 'webapp';
    const DEFAULT_BASE_URL = 'https://app.mdirector.com';

    protected $baseUrl = false;

    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
        $this->clientId = self::CLIENT_ID;
        if (!$this->baseUrl) {
            $this->baseUrl = self::DEFAULT_BASE_URL;
        }
    }

    public function getBaseAuthorizationUrl()
    {
        return $this->baseUrl . '/oauth2-authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->baseUrl . '/oauth2';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->baseUrl . '/oauth2-api';
    }

    protected function getDefaultScopes()
    {
        return null;
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $error = $data['error'];

            if (isset($data['error_description'])) {
                $error .= ': ' . $data['error_description'];
            }
            throw new IdentityProviderException($error, 0, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return null;
    }
}
