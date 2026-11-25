<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Las pruebas validan comportamiento del backend: no dependen de los assets compilados
        // por Vite (public/build), que no existen en el pipeline de CI.
        $this->withoutVite();
    }
}
