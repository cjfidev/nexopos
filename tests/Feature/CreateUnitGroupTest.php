<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\WithAuthentication;
use Tests\Traits\WithUnitTest;

class CreateUnitGroupTest extends TestCase
{
    use WithAuthentication, WithUnitTest;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_create_unit_group()
    {
        $this->attemptAuthenticate();
        $this->attemptCreateUnitGroup();
    }
}
