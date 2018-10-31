<?php
namespace MDOAuth\OAuth2Client\MDirector;

use MDOAuth\OAuth2Client\MDirector;

class Factory
{

    public function create(ProviderFactory $providerFactory, $accessTokenUrl, $key, $secret)
    {
        //$providerFactory = new ProviderFactory();
        $options = [
            'clientId' => 'webopp',
            'urlAccessToken' => $accessTokenUrl,
            'urlAuthorize' => '',
            'urlResourceOwnerDetails' => ''
        ];
        return new MDirector($providerFactory->create($options), $key, $secret);
//        $options = [
//            'clientId' => 'webapp',
//            'urlAccessToken' => $accessTokenUrl,
//            'urlAuthorize' => '',
//            'urlResourceOwnerDetails' => ''
//        ];
//        $provider = new \League\OAuth2\Client\Provider\GenericProvider($options);
//        return new MDirector($provider, $key, $secret);
    }
}
