<?php

namespace MDOAuth\Test\OAuth2\Wrapper;

use MDOAuth\OAuth2\Services;
use MDOAuth\OAuth2\UnknownServiceException;
use MDOAuth\OAuth2\Wrapper;
use MDOAuth\OAuth2\Wrapper\Factory;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testCreateShouldReturnProperInstanceForTheMDirectorService()
    {
        $client = (new Factory())->create(Services::MDIRECTOR, 'key', 'secret');
        $this->assertInstanceOf(Wrapper::class, $client);
    }

    public function testCreateShouldReturnProperInstanceForTheTransactionalService()
    {
        $client = (new Factory())->create(Services::TRANSACTIONAL, 'key', 'secret');
        $this->assertInstanceOf(Wrapper::class, $client);
    }

    public function testCreateShouldReturnProperInstanceForThePaymentsService()
    {
        $client = (new Factory())->create(Services::PAYMENTS, 'key', 'secret');
        $this->assertInstanceOf(Wrapper::class, $client);
    }

    public function testCreateShouldThrowExceptionForUnknownServices()
    {
        $this->expectException(UnknownServiceException::class);
        (new Factory())->create('NoSuchService', 'key', 'secret');
    }
}
