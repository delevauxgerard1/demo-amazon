<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AddressOptionsControllerTest extends TestCase
{
    use RefreshDatabase;

    //dd($this->app['db']->table('addresses')->get());

    public function test_redirect_when_nonauthenticated_user_enters_into_address_options()
    {
        $response = $this->post("/address_options", []);

        $response->assertRedirect('/login');
    }

    public function test_display_address_form()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get("/address_options");

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Address/Add'));
    }

    public function test_display_address_fields_form_without_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get("/address_options");

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Address/Add')
                ->has('auth.address', null)
        );
    }

    public function test_display_address_fields_form_with_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $addressData = [
            'user_id' => $user->id,
            'addr1' => 'New Address 1',
            'addr2' => 'New Address 2',
            'city' => 'New City',
            'postcode' => 'XR1234',
            'country' => 'Country',
        ];

        $this->post("/address_options", $addressData);

        $response = $this->get("/address_options");

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Address/Add')
                ->where('auth.address.id', $addressData['user_id'])
                ->where('auth.address.addr1', $addressData['addr1'])
                ->where('auth.address.addr2', $addressData['addr2'])
                ->where('auth.address.city', $addressData['city'])
                ->where('auth.address.postcode', $addressData['postcode'])
                ->where('auth.address.country', $addressData['country'])
        );
    }

    public function test_it_stores_new_address_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $addressData = [
            'user_id' => $user->id,
            'addr1' => 'New Address 1',
            'addr2' => 'New Address 2',
            'city' => 'New City',
            'postcode' => 'XR1234',
            'country' => 'Country',
        ];

        $response = $this->post("/address_options", $addressData);

        $response->assertRedirect(route('address.index'));
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'addr1' => $addressData['addr1'],
            'addr2' => $addressData['addr2'],
            'city' => $addressData['city'],
            'postcode' => $addressData['postcode'],
            'country' => $addressData['country'],
        ]);
    }

    public function test_it_not_stores_new_address_for_nonauthenticated_user()
    {
        $user = User::factory()->create();

        $addressData = [
            'user_id' => $user->id,
            'addr1' => 'New Address 1',
            'addr2' => 'New Address 2',
            'city' => 'New City',
            'postcode' => 'XR1234',
            'country' => 'Country',
        ];

        $response = $this->post("/address_options", $addressData);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('addresses', [
            'user_id' => $user->id,
            'addr1' => $addressData['addr1'],
            'addr2' => $addressData['addr2'],
            'city' => $addressData['city'],
            'postcode' => $addressData['postcode'],
            'country' => $addressData['country'],
        ]);
    }

    public function test_no_store_address_with_missing_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $addressData = [
            'user_id' => $user->id,
            // Missing 'addr1' field intentionally
            'addr2' => 'New Address 2',
            'city' => 'New City',
            'postcode' => 'XR1234',
            'country' => 'Country',
        ];

        $response = $this->post("/address_options", $addressData);

        $response->assertSessionHasErrors(['addr1']);
    }

    public function test_it_deletes_address_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $address = Address::create([
            'user_id' => $user->id,
            'addr1' => 'Address 1',
            'addr2' => 'Address 1',
            'city' => 'City example',
            'postcode' => 'XR9999',
            'country' => 'Country',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->delete("/address_options/{$address->id}");

        $response->assertRedirect(route('address.index'));
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_it_not_deletes_address_for_nonauthenticated_user()
    {
        $user = User::factory()->create();

        $address = Address::create([
            'user_id' => $user->id,
            'addr1' => 'Address 1',
            'addr2' => 'Address 1',
            'city' => 'City example',
            'postcode' => 'XR9999',
            'country' => 'Country',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->delete("/address_options/{$address->id}");

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    public function test_404_when_delete_non_existing_address()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $nonExistingAddressId = 9999;

        $response = $this->delete("/address_options/{$nonExistingAddressId}");

        $response->assertStatus(404);
    }
}
