<?php

namespace MDOAuth\OAuth2;

class Services
{
    const MDIRECTOR = 'mdirector';
    const TRANSACTIONAL = 'transactional';

    const SERVICE_PROVIDERS = [
        self::MDIRECTOR => Client\Provider\MDirector::class,
        self::TRANSACTIONAL => Client\Provider\Transactional::class
    ];

    const SERVICE_WRAPPERS = [
        self::MDIRECTOR => Wrapper\MDirector::class,
        self::TRANSACTIONAL => Wrapper\Transactional::class
    ];

    public static function getProviderClassForService($service)
    {
        if (!in_array($service, array_keys(self::SERVICE_PROVIDERS))) {
            throw new UnknownServiceException();
        }

        return self::SERVICE_PROVIDERS[$service];
    }

    public static function getWrapperClassForService($service)
    {
        if (!in_array($service, array_keys(self::SERVICE_WRAPPERS))) {
            throw new UnknownServiceException();
        }

        return self::SERVICE_WRAPPERS[$service];
    }

    public static function getServiceNames()
    {
        return array_keys(self::SERVICE_PROVIDERS);
    }
}
