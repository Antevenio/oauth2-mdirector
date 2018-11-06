<?php

namespace MDOAuth\OAuth2\Client\MDirector;

use MDOAuth\OAuth2\Client\MDirector;

class Factory
{
    public function create($consumerKey, $secret)
    {
        return new MDirector(
            new \MDOAuth\OAuth2\Client\Provider\MDirector(),
            $consumerKey,
            $secret
        );
    }
}
