<?php

namespace MDOAuth\OAuth2\Wrapper\Payments;

use MDOAuth\OAuth2\Wrapper;

class Factory
{
    public function create($consumerKey, $secret, $baseUrl = null)
    {
        $options = [];

        if ($baseUrl) {
            $options['baseUrl'] = $baseUrl;
        }

        return new Wrapper(
            new \MDOAuth\OAuth2\Client\Provider\Payments($options),
            $consumerKey,
            $secret
        );
    }
}
