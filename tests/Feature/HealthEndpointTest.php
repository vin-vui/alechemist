<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_la_sonde_repond_200_sans_authentification(): void
    {
        $this->get('/up')->assertOk();
    }
}
