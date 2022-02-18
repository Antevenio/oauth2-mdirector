<?php

namespace MDOAuth\OAuth2\Wrapper;

use MDOAuth\OAuth2\Services;
use MDOAuth\OAuth2\UnknownServiceException;
use MDOAuth\OAuth2\Wrapper;

class Factory
{
    const SERVICE_WRAPPER_FACTORIES = [
        Services::MDIRECTOR => Wrapper\MDirector\Factory::class,
        Services::TRANSACTIONAL => Wrapper\Transactional\Factory::class,
        Services::PAYMENTS => Wrapper\Payments\Factory::class
    ];

    /**
     * @param $service
     * @param $consumerKey
     * @param $secret
     * @param null $baseUrl
     * @return Wrapper
     * @throws UnknownServiceException
     */
    public function create($service, $consumerKey, $secret, $baseUrl = null)
    {
        if (!in_array($service, array_keys(self::SERVICE_WRAPPER_FACTORIES))) {
            throw new UnknownServiceException();
        }

        $wrapperFactoryClass = self::SERVICE_WRAPPER_FACTORIES[$service];

        return (new $wrapperFactoryClass())->create($consumerKey, $secret, $baseUrl);
    }
}
