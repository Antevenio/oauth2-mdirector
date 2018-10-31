<?php
namespace MDOAuth\OAuth2Client\MDirector;

class ProviderFactory
{
    public function create($options)
    {
        return new \League\OAuth2\Client\Provider\GenericProvider($options);
    }
}
