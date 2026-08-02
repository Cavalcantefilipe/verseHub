<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicStorePagesTest extends TestCase
{
    public function test_public_app_pages_are_available(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('VerseHub')
            ->assertSee('/privacy', false)
            ->assertSee('/support', false);

        $this->get('/privacy')
            ->assertOk()
            ->assertSee('Política de Privacidade');

        $this->get('/terms')
            ->assertOk()
            ->assertSee('Termos de Uso');

        $this->get('/delete-account')
            ->assertOk()
            ->assertSee('Solicitar Exclusão de Conta e Dados');

        $this->get('/support')
            ->assertOk()
            ->assertSee('Como podemos ajudar?')
            ->assertSee('contato@filipelab.com');
    }
}
