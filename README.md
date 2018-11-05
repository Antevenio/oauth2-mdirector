# mdirector-oauth-client-php

[![Travis build](https://api.travis-ci.com/Antevenio/mdirector-oauth-client-php.svg?branch=master)](https://travis-ci.org/Antevenio/mdirector-oauth-client-php)

OAuth client libraries specific to access MDirector api services, written in PHP.

## Description
As of now, only an OAuth2 implementation for the MDirector email marketing application 
is provided. 
It is composed of an [oauth2-client](https://github.com/thephpleague/oauth2-client) 
provider, and a wrapper around it to hide the burden of the required OAuth2 negotiations.

As a consumer you may chose to use just the provider or the client wrapper, as it suits you best.

There is also a command line script to help you test it from the shell.

## Installation

```
composer require antevenio/mdirector-oauth-client-php 
```

## Usage

As mentioned before you can choose to use just the provider, or the wrapper around it. 
Here you can find usage examples of use for each case: 

### 1. MDirector wrapper

```php
<?php
$companyId = 'yourCompanyId';
$secret = 'yourApiSecret';

$client = new \MDOAuth\OAuth2\Client\MDirector($companyId, $secret);
$response = $client->setUri('https://api.mdirector.com/api_contact')
    ->setMethod('get')
    ->setParameters([
        'email' => 'myemail@mydomain.org'    
    ])
    ->request();

echo $response->getBody()->getContents();
```

### 2. Oauth2-client provider

You can find the [oauth2-client](https://github.com/thephpleague/oauth2-client) provider under 
src/OAuth2/Client/Provider, for generic usage instructions please refer to generic usage in the
[oauth2-client github project](https://github.com/thephpleague/oauth2-client).

MDirector as of now is just providing the **Resource Owner Password Credentials Grant** grant 
having a generic clientId named **webapp**. So, here it is an example to get a valid accessToken:

```php
<?php
$provider = new \MDOAuth\OAuth2\Client\Provider\MDirector([
    'clientId'                => 'webapp' // The client ID assigned to you by the provider
]);

try {
    // Try to get an access token using the resource owner password credentials grant.
    $accessToken = $provider->getAccessToken('password', [
        'username' => '{yourCompanyId}',
        'password' => '{yourApiSecret}'
    ]);
} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    // Failed to get the access token
    exit($e->getMessage());
}
```

### 3. Shell script

The library also provides with a symfony/console client so you can call the mdirector api from a 
shell.
To do so run:

```
$ ./bin/mdirector-oauth-client oauth2:mdirector --help    
```                                            
The command will display some self explanatory help.
