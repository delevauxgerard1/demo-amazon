<?php

namespace Tests\Feature;

use App\Mail\OrderShipped;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_checkout_page_with_no_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('checkout.index'));

        $response->assertStatus(200)
            ->assertInertia(
                fn ($page) => $page->component('Checkout')
                    ->where('order', null)
            );
    }

    public function test_authenticated_user_can_access_checkout_page_with_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::create([
            'user_id' => $user->id,
            'total' => 10000,
            'total_decimal' => 100,
            'items' => 'items',
            'payment_intent' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('checkout.index'));

        $response->assertStatus(200)
            ->assertInertia(
                fn ($page) => $page->component('Checkout')
                    ->where('order', $order->toArray())
            );
    }

    public function test_unauthenticated_user_cannot_access_checkout_page()
    {
        $response = $this->get(route('checkout.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_storing_order_on_checkout_submit()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $orderData = [
            'total' => 100,
            'total_decimal' => 100.00,
            'items' => ['item1', 'item2'],
        ];

        $response = $this->post(route('checkout.store'), $orderData);

        $response->assertRedirect(route('checkout.index'));
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => $orderData['total'],
            'total_decimal' => $orderData['total_decimal'],
            'items' => json_encode($orderData['items']),
        ]);
    }

    public function test_unauthenticated_user_cannot_update_order()
    {
        $response = $this->post(route('checkout.update'), []);
        $response->assertRedirect(route('login'));
    }

    public function test_update_payment_intent_of_order_when_paying()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $order = Order::create([
            'user_id' => $user->id,
            'total' => 10000,
            'total_decimal' => 100,
            'items' => 'items',
            'payment_intent' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->put(route('checkout.update'), [
            'payment_intent' => 'pi_3Ocy5BINGzVfUHrB1o64WjA',
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => $order->total,
            'total_decimal' => $order->total_decimal,
            'items' => $order->items,
            'payment_intent' => 'pi_3Ocy5BINGzVfUHrB1o64WjA',
        ]);
    }

    public function test_update_send_email_after_paying()
    {
        Mail::fake();

        $user = User::factory()->create();
        $this->actingAs($user);

        $order = Order::create([
            'user_id' => $user->id,
            'total' => 10000,
            'total_decimal' => 100,
            'items' => 'items',
            'payment_intent' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->put(route('checkout.update'), [
            'payment_intent' => 'pi_3Ocy5BINGzVfUHrB1o64WjA',
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => $order->total,
            'total_decimal' => $order->total_decimal,
            'items' => $order->items,
            'payment_intent' => 'pi_3Ocy5BINGzVfUHrB1o64WjA',
        ]);

        Mail::assertSent(OrderShipped::class, function ($mail) use ($user, $order) {
            return $mail->hasTo($user->email) && $mail->order->id === $order->id;
        });
    }
}
