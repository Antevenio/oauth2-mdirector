<?php
namespace MDOAuth\OAuth2Client\MDirector;

use League\OAuth2\Client\Provider\GenericProvider;

class Provider extends GenericProvider
{
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
    }

    public function setOptions()
    {
    }
}
