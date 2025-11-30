<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Load .env.testing if it exists, otherwise use defaults
        if (file_exists(base_path('.env.testing'))) {
            $dotenv = \Dotenv\Dotenv::createImmutable(base_path(), '.env.testing');
            $dotenv->load();
        }
        
        // Generate APP_KEY if not set in environment
        if (!$app['config']['app.key']) {
            $app['config']['app.key'] = 'base64:' . base64_encode(random_bytes(32));
        }
    }
}