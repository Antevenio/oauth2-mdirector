<?php

namespace MDOAuth\Test\OAuth2\Client\MDirector;

use MDOAuth\OAuth2\Client\MDirector;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testCreateShouldReturnProperInstances()
    {
        $this->assertInstanceOf(
            MDirector::class,
            (new MDirector\Factory())->create('key', 'secret')
        );
    }
}
