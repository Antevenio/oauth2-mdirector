<?php

namespace MDOAuth\Test\OAuth2\Client\MDirector;

use MDOAuth\OAuth2\Wrapper\MDirector;
use MDOAuth\OAuth2\Wrapper\Transactional;
use MDOAuth\OAuth2\Wrapper\Factory;
use MDOAuth\OAuth2\Services;
use MDOAuth\OAuth2\UnknownServiceException;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testCreateShouldReturnProperInstanceForTheMDirectorService()
    {
        $client = (new Factory())->create(Services::MDIRECTOR, 'key', 'secret');
        $this->assertInstanceOf(MDirector::class, $client);
    }

    public function testCreateShouldReturnProperInstanceForTheTransactionalService()
    {
        $client = (new Factory())->create(Services::TRANSACTIONAL, 'key', 'secret');
        $this->assertInstanceOf(Transactional::class, $client);
    }

    public function testCreateShouldThrowExceptionForUnknownServices()
    {
        $this->expectException(UnknownServiceException::class);
        (new Factory())->create('NoSuchService', 'key', 'secret');
    }
}
