<?php

namespace MDOAuth\Test\OAuth2\Client\Provider;

use MDOAuth\OAuth2\Client\Provider\MDirector;
use PHPUnit\Framework\TestCase;

class MDirectorTest extends TestCase
{
    /**
     * @var MDirector
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new MDirector();
    }

    public function testProviderShouldHaveProperClientIdSet()
    {
        $headers =  $this->sut->getHeaders();
        var_dump($headers);
        $this->assertEquals('webapp', $this->sut->getHeaders());
    }
}
