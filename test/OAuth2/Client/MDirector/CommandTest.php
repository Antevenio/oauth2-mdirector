<?php

namespace MDOAuth\Test\OAuth2\Client\MDirector;

use MDOAuth\OAuth2\Client\MDirector;
use MDOAuth\OAuth2\Client\MDirector\Command;
use MDOAuth\OAuth2\Client\MDirector\Factory;
use Mockery\Mock;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

class CommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Factory | Mock
     */
    protected $clientFactory;
    /**
     * @var MDirector | Mock
     */
    protected $client;
    /**
     * @var Command
     */
    protected $sut;

    public function setUp()
    {
        $this->clientFactory = \Mockery::mock(Factory::class)
            ->shouldIgnoreMissing();
        $this->client = \Mockery::mock(MDirector::class);
        $this->client->shouldIgnoreMissing($this->client);
        $this->sut = new Command($this->clientFactory);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    public function testShouldBeCreated()
    {
        $this->assertInstanceOf(Command::class, $this->sut);
    }

    public function testRun()
    {
        $arguments = [
            'companyId' => '1231',
            'secret' => '2342',
            'uri' => 'https://go.to/endpoint',
            'method' => 'get',
            '--useragent' => 'my user agent',
            'parameters' => '{}'
        ];

        $input = new ArrayInput($arguments);
        /** @var OutputInterface | Mock $output */
        $output = \Mockery::mock(OutputInterface::class)
            ->shouldIgnoreMissing();

        $responseBody = '{"yes": "yes"}';
        $response = $this->createResponse($responseBody);

        $this->clientFactory->shouldReceive('create')
            ->once()
            ->with($arguments['companyId'], $arguments['secret'])
            ->andReturn($this->client);

        $this->client->shouldReceive('setMethod')
            ->once()
            ->ordered('setup')
            ->with($arguments['method'])
            ->andReturn($this->client);

        $this->client->shouldReceive('setUri')
            ->once()
            ->ordered('setup')
            ->with($arguments['uri'])
            ->andReturn($this->client);

        $this->client->shouldReceive('setParameters')
            ->once()
            ->ordered('setup')
            ->with(json_decode($arguments['parameters'], true))
            ->andReturn($this->client);


        $this->client->shouldReceive('setUserAgent')
            ->once()
            ->ordered('setup')
            ->with($arguments['--useragent'])
            ->andReturn($this->client);

        $this->client->shouldReceive('request')
            ->once()
            ->ordered('execute')
            ->andReturn($response);


        $output->shouldReceive('writeln')
            ->once()
            ->with($responseBody);

        $this->sut->run($input, $output);
    }

    protected function createResponse($body)
    {
        /** @var ResponseInterface | \PHPUnit_Framework_MockObject_MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        $bodyStream = \Mockery::mock(StreamInterface::class)
            ->shouldIgnoreMissing();
        $bodyStream->shouldReceive('getContents')
            ->andReturn($body);
        $response->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue($bodyStream));
        $response->expects($this->any())
            ->method('getHeader')
            ->will($this->returnValue(['content-type' => 'json']));
        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(200));

        return $response;
    }
}
