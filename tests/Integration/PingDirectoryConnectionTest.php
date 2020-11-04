<?php

namespace DanutAvadanei\Scim2\Tests\Integration;

use DanutAvadanei\Scim2\PingDirectoryConnection;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class PingDirectoryConnectionTest extends IntegrationTestCase
{
    protected PingDirectoryConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $config = $this->config;
        $client = new Client(['auth' => Arr::pull($config, 'auth')]);

        $this->connection = new PingDirectoryConnection($client, $config);
        $this->connection->enableQueryLog();
    }

    public function testItCanFindAUser()
    {
        $result = $this->connection->query()
            ->find('CV2889');

        $this->assertEquals(['CV2889'], Arr::get($result, 'uid'));
    }

    public function testItCanPerformABasicQuery()
    {
        $result = $this->connection->query()
            ->where('extShortName', 'avadanei.d')
            ->first();

        $this->assertEquals(['CV2889'], Arr::get($result, 'uid'));
    }

    public function testItCanPerformAnAdvancedQuery()
    {
        $result = $this->connection->query()
            ->whereEquals('extCompany', 'Connections Consult')
            ->whereContains('owner', 'BQ5350')
            ->get(['uid']);

        $uids = $result->pluck('uid.0');

        $this->assertContains('CV2889', $uids);
        $this->assertContains('DO5377', $uids);
        $this->assertContains('DP5851', $uids);
    }
}
