<?php
namespace MDOAuth\Test\OAuth2;

use MDOAuth\OAuth2\Client\MDirector;
use PHPUnit\Framework\TestCase;

class MDirectorTest extends TestCase
{
    /**
     * @var MDirector
     */
    protected $sut;

    protected $key;
    protected $secret;

    protected $uri;
    /**
     * @var \MDOAuth\OAuth2\Client\Provider\MDirector | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $provider;

    public function setUp()
    {
        $this->key = 'someKey';
        $this->secret = 'someSecret';
        $this->sut = new MDirector($this->key, $this->secret);
    }

    public function testShouldBeCreated()
    {
        $this->assertInstanceOf(MDirector::class, $this->sut);
    }
}
