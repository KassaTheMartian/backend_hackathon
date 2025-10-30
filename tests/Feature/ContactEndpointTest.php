<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_store_requires_fields(): void
    {
        $this->postJson('/api/v1/contact', [], ['Accept' => 'application/json'])
            ->assertStatus(400);
    }

    public function test_contact_store_succeeds(): void
    {
        $payload = [
            'name' => 'Nguyen Van A',
            'email' => 'contact@example.com',
            'subject' => 'Tư vấn dịch vụ',
            'message' => 'Tôi cần tư vấn dịch vụ.',
        ];

        $this->postJson('/api/v1/contact', $payload, ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJson(['success' => true]);
    }
}


