<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AddressControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_address_page_with_address_of_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $address = Address::create([
            'user_id' => $user->id,
            'addr1' => 'Address 1',
            'addr2' => 'Address 1',
            'city' => 'City example',
            'postcode' => 'XR9999',
            'country' => 'France',
            'created_at' => now(),
            'update_at' => now(),
        ]);

        $response = $this->get("/address");

        $response->assertStatus(200);
        $response->assertInertia(
            fn (Assert $page) => $page->component('Address/Index')
                ->url("/address")
                ->has('auth.address')
                ->where('auth.address.id', $address->id)
                ->where('auth.address.user_id', $address->user_id)
                ->where('auth.address.addr1', $address->addr1)
                ->where('auth.address.addr2', $address->addr2)
                ->where('auth.address.city', $address->city)
                ->where('auth.address.postcode', $address->postcode)
                ->where('auth.address.country', $address->country)
        );
    }

    public function test_it_does_not_show_address_data_for_nonauthenticated_user()
    {
        $response = $this->get("/address");

        $response->assertStatus(200);
        $response->assertInertia(
            fn (Assert $page) => $page->component('Address/Index')
                ->url("/address")
                ->has('auth.user', null)
                ->has('auth.address', null)
        );
    }
}
