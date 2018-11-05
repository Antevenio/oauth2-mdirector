# mdirector-oauth-client-php

![Travis build](https://api.travis-ci.com/Antevenio/mdirector-oauth-client-php.svg?branch=master)

OAuth client libraries specific to access MDirector api services, written in PHP.

## Description
As of now, only an OAuth2 implementation for the MDirector email marketing application 
is provided. 
It is composed of an [oauth2-client](https://github.com/thephpleague/oauth2-client) 
provider, and a wrapper around it to hide the burden of the required OAuth2 negotations.

As a consumer you may chose to use just the provider or the client wrapper, as it suits you best.

## Installation

```
composer require antevenio/mdirector-oauth-client-php 
```

## Usage

As mentioned before you can choose to use just the provider, or the wrapper around it. 
Here you can find usage examples for each case: 

### 1. oauth2-client provider

You can find the [oauth2-client](https://github.com/thephpleague/oauth2-client) provider under 
src/OAuth2/Client/Provider, as of usage instructions please refer to generic usage in the
[oauth2-client github project](https://github.com/thephpleague/oauth2-client).

### 2. mdirector wrapper

```php
<?php

$companyId = 'yourCompanyId';
$secret = 'yourApiSecret';

$client = new MDOAuth\OAuth2\Client\MDirector($companyId, $secret);
$response = $client->setUri('https://api.mdirector.com/api_contact')
    ->setMethod('get')
    ->setParameters([
        'email' => 'myemail@mydomain.org'    
    ])
    ->request();

echo $response->getBody()->getContents();
```
 