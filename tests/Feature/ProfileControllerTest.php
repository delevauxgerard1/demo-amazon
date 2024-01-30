<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->state([
            'first_name' => 'Jhon',
            'last_name' => 'Doe',
            'email' => 'jhondoe@mail.com',
            'password' => Hash::make('password')
        ])->create();
    }

    public function redirect_when_nonauthenticated_user_enters_into_profile()
    {
        $response = $this->get(route('profile.edit'));

        $response->assertRedirect('/login');
    }

    public function display_profile_edit_form_with_user_data()
    {
        $response = $this->actingAs($this->user)->get(route('profile.edit'));

        $response->assertStatus(200)
            ->assertInertia(
                fn (Assert $page) => $page->component('Profile/Edit')
                    ->where('auth.user.first_name', $this->user->first_name)
                    ->where('auth.user.last_name', $this->user->last_name)
                    ->where('auth.user.email', $this->user->email)
            );

        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }

    public function it_updates_profile_for_authenticated_user()
    {
        $updatedData = [
            'first_name' => 'UpdatedJhon',
            'last_name' => 'UpdatedDoe',
            'email' => 'updatedjhondoe@mail.com',
        ];

        $response = $this->actingAs($this->user)->patch(route('profile.update'), $updatedData);

        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHasNoErrors();

        $updatedUser = $this->user->fresh();

        $this->assertEquals($updatedData['first_name'], $updatedUser->first_name);
        $this->assertEquals($updatedData['last_name'], $updatedUser->last_name);
        $this->assertEquals($updatedData['email'], $updatedUser->email);
    }

    public function it_updates_profile_for_nonauthenticated_user()
    {
        $updatedData = [
            'first_name' => 'UpdatedJhon',
            'last_name' => 'UpdatedDoe',
            'email' => 'updatedjhondoe@mail.com',
        ];

        $response = $this->patch(route('profile.update'), $updatedData);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['first_name' => $this->user->first_name]);
        $this->assertDatabaseHas('users', ['last_name' => $this->user->last_name]);
        $this->assertDatabaseHas('users', ['email' => $this->user->email]);
    }

    public function it_not_updates_profile_for_authenticated_user_when_firstname_is_missing()
    {
        $updatedData = [
            'first_name' => null,
            'last_name' => 'UpdatedDoe',
            'email' => 'updatedjhondoe@mail.com',
        ];

        $response = $this->actingAs($this->user)->patch(route('profile.update'), $updatedData);

        $response->assertSessionHasErrors(['first_name' => 'The first name field is required.']);
        $this->assertDatabaseHas('users', ['first_name' => $this->user->first_name]);
    }

    public function it_deletes_own_user_when_authenticated()
    {
        $password = 'password';

        $updatedData = [
            'password' => $password,
        ];

        $response = $this->actingAs($this->user)->delete(route('profile.destroy'), $updatedData);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }

    public function it_not_deletes_user_when_nonauthenticated()
    {
        $password = 'password';

        $updatedData = [
            'password' => $password,
        ];

        $response = $this->delete(route('profile.destroy'), $updatedData);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }

    public function it_not_deletes_user_when_authenticated_user_sends_wrong_password()
    {
        $password = 'wrongpassword';

        $updatedData = [
            'password' => $password,
        ];

        $response = $this->actingAs($this->user)->delete(route('profile.destroy'), $updatedData);

        $response->assertSessionHasErrors(['password' => 'The password is incorrect.']);
        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }
}
