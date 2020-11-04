<?php

namespace DanutAvadanei\Scim2\Tests\Integration;

use PHPUnit\Framework\TestCase;

class IntegrationTestCase extends TestCase
{
    protected array $config;

    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('NAME') === false) {
            $this->markTestSkipped('Testing directory is not configured.');
        }

        $this->config = [
            'name' => getenv('NAME'),
            'driver' => [
                'url' => getenv('DRIVER_URL'),
                'method' => getenv('DRIVER_METHOD'),
            ],
            'auth' => [getenv('DRIVER_USER'), getenv('DRIVER_PASSWORD')],
        ];
    }
}
