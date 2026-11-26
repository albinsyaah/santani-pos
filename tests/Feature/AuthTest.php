<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

test('guests are redirected to the login page', function () {
    foreach (['dashboard', 'pos.terminal', 'products.index', 'sales.tally', 'stock.opening', 'purchases.create'] as $route) {
        $this->get(route($route))->assertRedirect(route('login'));
    }
});

test('the login page renders for guests', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Sign in')
        ->assertSee('name="remember"', false);
});

test('a user can sign in and reach the dashboard', function () {
    $user = User::factory()->create(['username' => 'owner']);

    $this->post(route('login'), ['username' => 'OWNER', 'password' => 'password'])
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
    $this->get(route('dashboard'))->assertOk();
});

test('a wrong password is rejected', function () {
    User::factory()->create(['username' => 'owner']);

    $this->from(route('login'))
        ->post(route('login'), ['username' => 'owner', 'password' => 'wrong'])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

test('an inactive account cannot sign in', function () {
    User::factory()->inactive()->create(['username' => 'old']);

    $this->post(route('login'), ['username' => 'old', 'password' => 'password'])
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

test('too many failed attempts are throttled', function () {
    RateLimiter::clear('owner|127.0.0.1');
    User::factory()->create(['username' => 'owner']);

    foreach (range(1, 5) as $i) {
        $this->post(route('login'), ['username' => 'owner', 'password' => 'wrong']);
    }

    $this->post(route('login'), ['username' => 'owner', 'password' => 'password'])
        ->assertStatus(429);

    $this->assertGuest();
});

test('signed-in users are sent away from the login page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('login'))
        ->assertRedirect('/');
});

test('a user can sign out', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('the signed-in user is shown in the sidebar', function () {
    $user = User::factory()->create(['name' => 'Siti Aminah']);

    $this->actingAs($user)->get(route('dashboard'))->assertSee('Siti Aminah');
});
