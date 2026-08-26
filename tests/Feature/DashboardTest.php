<?php

use App\Enums\UserRole;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard and see main sections', function () {
    $user = User::factory()->create(['name' => 'Budi Santoso', 'role' => UserRole::PETUGAS]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('Budi Santoso')
        ->assertSee('Kemas Baik Hari Ini')
        ->assertSee('Grafik Tren Produksi')
        ->assertSee('Laporan Produksi Terkini');
});
