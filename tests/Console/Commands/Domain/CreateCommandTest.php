<?php

namespace AbuseIO\Console\Commands\Domain;

use Illuminate\Support\Facades\Artisan;
use TestCase;

/**
 * Class CreateCommandTest.
 */
class CreateCommandTest extends TestCase
{
    public function testWithoutArguments()
    {
        Artisan::call('domain:create');
        $output = Artisan::output();

        $this->assertContains('The name field is required.', $output);
        $this->assertContains('The contact id field is required.', $output);
        $this->assertContains('Failed to create the domain due to validation warnings', $output);
    }
}
