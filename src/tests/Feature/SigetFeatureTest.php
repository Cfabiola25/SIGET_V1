<?php

namespace Tests\Feature;

use Tests\TestCase;

class SigetFeatureTest extends TestCase
{
    public function test_home_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
