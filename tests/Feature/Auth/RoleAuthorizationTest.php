<?php

use App\Http\Middleware\EnsureUserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('unauthenticated guests are redirected to login when accessing petugas management', function () {
    $response = $this->get(route('petugas.index'));

    $response->assertRedirect(route('login'));
});

test('petugas role cannot access supervisor-only petugas management page', function () {
    $petugas = User::factory()->petugas()->create();

    $response = $this->actingAs($petugas)->get(route('petugas.index'));

    $response->assertForbidden();
});

test('supervisor role can access petugas management page', function () {
    $supervisor = User::factory()->supervisor()->create();

    $response = $this->actingAs($supervisor)->get(route('petugas.index'));

    $response->assertOk();
    $response->assertSee('Data Petugas');
    $response->assertSee('Tambah Petugas');
});

test('middleware correctly handles multi-role permissions', function () {
    $middleware = new EnsureUserRole;

    $petugas = User::factory()->petugas()->create();
    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $petugas);

    // Allowed when petugas is in allowed roles list
    $response = $middleware->handle($request, fn () => response('OK'), 'supervisor', 'petugas');
    expect($response->getContent())->toBe('OK');

    // Forbidden when role is not in allowed list
    expect(fn () => $middleware->handle($request, fn () => response('OK'), 'supervisor'))
        ->toThrow(HttpException::class);
});

test('middleware handles case-insensitive role comparisons', function () {
    $middleware = new EnsureUserRole;

    $supervisor = User::factory()->supervisor()->create();
    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $supervisor);

    $response = $middleware->handle($request, fn () => response('OK'), 'SUPERVISOR');
    expect($response->getContent())->toBe('OK');
});
