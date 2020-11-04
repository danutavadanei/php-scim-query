<?php

namespace DanutAvadanei\Scim2\Tests;

use DanutAvadanei\Scim2\Connection;
use DanutAvadanei\Scim2\Query\Grammar;
use DanutAvadanei\Scim2\Query\Processor;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testSettingDefaultCallsGetDefaultGrammar()
    {
        $connection = $this->getMockConnection();
        $mock = Mockery::mock(Grammar::class);
        $connection->expects($this->once())->method('getDefaultQueryGrammar')->willReturn($mock);
        $connection->useDefaultQueryGrammar();
        $this->assertEquals($mock, $connection->getQueryGrammar());
    }

    public function testSettingDefaultCallsGetDefaultPostProcessor()
    {
        $connection = $this->getMockConnection();
        $mock = Mockery::mock(Processor::class);
        $connection->expects($this->once())->method('getDefaultPostProcessor')->willReturn($mock);
        $connection->useDefaultPostProcessor();
        $this->assertEquals($mock, $connection->getPostProcessor());
    }

    public function testSelectOneCallsSelectAndReturnsSingleResult()
    {
        $connection = $this->getMockConnection(['select']);
        $connection->expects($this->once())->method('select')->with(['foo'])->willReturn(['foo']);
        $this->assertSame('foo', $connection->selectOne(['foo']));
    }

    public function testSelectProperlyCallsHttpClientWithGetMethod()
    {
        $config = [
            'driver' => [
                'url' => 'https://directory.test',
                'method' => 'get',
            ],
        ];

        $client = $this->getMockBuilder(Client::class)->onlyMethods(['getAsync'])->getMock();
        $promise = $this->getMockBuilder(Promise::class)->onlyMethods(['wait'])->getMock();
        $response = $this->getMockBuilder(Response::class)->getMock();

        $client->expects($this->once())->method('getAsync')->with($config['driver']['url'], ['query' => ['foo' => 'bar']])->willReturn($promise);
        $promise->expects($this->once())->method('wait')->willReturn($response);

        $connection = $this->getMockConnection([], $client, $config);

        $results = $connection->select(['foo' => 'bar']);
        $this->assertEquals($response, $results);
        $log = $connection->getQueryLog();
        $this->assertSame(['foo' => 'bar'], $log[0]['query']);
        $this->assertIsNumeric($log[0]['time']);
    }

    public function testSelectProperlyCallsHttpClientWithPostMethod()
    {
        $config = [
            'driver' => [
                'url' => 'https://directory.test',
                'method' => 'post',
            ],
        ];

        $client = $this->getMockBuilder(Client::class)->onlyMethods(['postAsync'])->getMock();
        $promise = $this->getMockBuilder(Promise::class)->onlyMethods(['wait'])->getMock();
        $response = $this->getMockBuilder(Response::class)->getMock();

        $client->expects($this->once())->method('postAsync')->with($config['driver']['url'], ['json' => ['foo' => 'bar']])->willReturn($promise);
        $promise->expects($this->once())->method('wait')->willReturn($response);

        $connection = $this->getMockConnection([], $client, $config);

        $results = $connection->select(['foo' => 'bar']);
        $this->assertEquals($response, $results);
        $log = $connection->getQueryLog();
        $this->assertSame(['foo' => 'bar'], $log[0]['query']);
        $this->assertIsNumeric($log[0]['time']);
    }

    protected function getMockConnection($methods = [], $client = null, array $config = [])
    {
        $client = $client ?: new Client();
        $defaults = ['getDefaultQueryGrammar', 'getDefaultPostProcessor'];
        $connection = $this->getMockBuilder(Connection::class)->onlyMethods(array_merge($defaults, $methods))->setConstructorArgs([$client, $config])->getMock();
        $connection->enableQueryLog();

        return $connection;
    }
}
