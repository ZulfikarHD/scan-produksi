<?php

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;
use Livewire\Livewire;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('Masuk ke Sistem');
    $response->assertSee('PT Percetakan Dokumen Sekuriti');
});

test('users can authenticate via Livewire using email', function () {
    $user = User::factory()->create([
        'email' => 'operator@pds.co.id',
        'password' => 'password',
        'is_active' => true,
    ]);

    Livewire::test(Login::class)
        ->set('login', 'operator@pds.co.id')
        ->set('password', 'password')
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->last_login_at)->not->toBeNull();
});

test('users can authenticate via Livewire using username', function () {
    $user = User::factory()->create([
        'username' => 'operator_kemas',
        'password' => 'password',
        'is_active' => true,
    ]);

    Livewire::test(Login::class)
        ->set('login', 'operator_kemas')
        ->set('password', 'password')
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('users can authenticate via Livewire using 4-digit number NP (eg 7426)', function () {
    $user = User::factory()->create([
        'nip' => '7426',
        'password' => 'password',
        'is_active' => true,
    ]);

    Livewire::test(Login::class)
        ->set('login', '7426')
        ->set('password', 'password')
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('users can authenticate via Livewire using alphanumeric NP (eg T0087)', function () {
    $user = User::factory()->create([
        'nip' => 'T0087',
        'password' => 'password',
        'is_active' => true,
    ]);

    Livewire::test(Login::class)
        ->set('login', 'T0087')
        ->set('password', 'password')
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('users can authenticate using standard HTTP login route', function () {
    $user = User::factory()->create([
        'email' => 'supervisor@pds.co.id',
        'password' => 'password',
        'is_active' => true,
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('inactive users cannot authenticate via Livewire', function () {
    $user = User::factory()->inactive()->create([
        'email' => 'cuti@pds.co.id',
        'password' => 'password',
    ]);

    Livewire::test(Login::class)
        ->set('login', 'cuti@pds.co.id')
        ->set('password', 'password')
        ->call('authenticate')
        ->assertHasErrors(['login']);

    $this->assertGuest();
});

test('inactive users cannot authenticate via standard HTTP login route', function () {
    $user = User::factory()->inactive()->create([
        'email' => 'suspended@pds.co.id',
        'password' => 'password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors();
    $this->assertGuest();
});

test('users cannot authenticate with invalid password', function () {
    $user = User::factory()->create([
        'email' => 'user@pds.co.id',
        'password' => 'password',
    ]);

    Livewire::test(Login::class)
        ->set('login', 'user@pds.co.id')
        ->set('password', 'wrong-password')
        ->call('authenticate')
        ->assertHasErrors(['login']);

    $this->assertGuest();
});

test('login requires login identifier and password', function () {
    Livewire::test(Login::class)
        ->set('login', '')
        ->set('password', '')
        ->call('authenticate')
        ->assertHasErrors(['login', 'password']);
});

test('login attempts are rate limited after 5 failures', function () {
    $user = User::factory()->create([
        'email' => 'target@pds.co.id',
        'password' => 'password',
    ]);

    RateLimiter::clear('target@pds.co.id|127.0.0.1');

    for ($i = 0; $i < 5; $i++) {
        Livewire::test(Login::class)
            ->set('login', 'target@pds.co.id')
            ->set('password', 'wrong-password')
            ->call('authenticate')
            ->assertHasErrors(['login']);
    }

    // 6th attempt should be rate limited
    Livewire::test(Login::class)
        ->set('login', 'target@pds.co.id')
        ->set('password', 'password')
        ->call('authenticate')
        ->assertHasErrors(['login']);

    $this->assertGuest();
});

test('authenticated users are redirected when visiting login screen', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('login'));

    $response->assertRedirect(route('dashboard'));
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $this->assertGuest();
});
