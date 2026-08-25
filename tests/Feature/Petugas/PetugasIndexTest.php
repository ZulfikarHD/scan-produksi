<?php

use App\Livewire\Petugas\PetugasIndex;
use App\Models\User;
use Livewire\Livewire;

test('petugas index component can be rendered by supervisor', function () {
    $supervisor = User::factory()->supervisor()->create();

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->assertOk()
        ->assertSee('Data Petugas')
        ->assertSee('Tambah Petugas');
});

test('petugas index displays staff records with appropriate details', function () {
    $supervisor = User::factory()->supervisor()->create([
        'name' => 'Bambang Supervisor',
        'nip' => 'SPV01',
        'username' => 'bambang_spv',
        'email' => 'bambang@pds.co.id',
    ]);

    $petugas = User::factory()->petugas()->create([
        'name' => 'Agus Operator',
        'nip' => 'OPR01',
        'username' => 'agus_kemas',
        'email' => 'agus@pds.co.id',
        'is_active' => true,
    ]);

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->assertSee('Bambang Supervisor')
        ->assertSee('SPV01')
        ->assertSee('bambang_spv')
        ->assertSee('bambang@pds.co.id')
        ->assertSee('Agus Operator')
        ->assertSee('OPR01')
        ->assertSee('agus_kemas')
        ->assertSee('agus@pds.co.id')
        ->assertSee('Supervisor / Ka. Unit')
        ->assertSee('Petugas Kemas')
        ->assertSee('Aktif');
});

test('petugas index can filter users by search keyword', function () {
    $supervisor = User::factory()->supervisor()->create(['name' => 'Admin Utama']);

    $user1 = User::factory()->petugas()->create([
        'name' => 'Siti Rahma',
        'nip' => '7426',
        'username' => 'siti_kemas',
    ]);

    $user2 = User::factory()->petugas()->create([
        'name' => 'Joko Widodo',
        'nip' => '8899',
        'username' => 'joko_kemas',
    ]);

    // Search by name
    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->set('search', 'Siti')
        ->assertSee('Siti Rahma')
        ->assertDontSee('Joko Widodo');

    // Search by NP / NIP
    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->set('search', '8899')
        ->assertSee('Joko Widodo')
        ->assertDontSee('Siti Rahma');

    // Search by username
    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->set('search', 'siti_kemas')
        ->assertSee('Siti Rahma')
        ->assertDontSee('Joko Widodo');
});

test('petugas index can filter users by role', function () {
    $supervisor = User::factory()->supervisor()->create(['name' => 'Supervisor Alpha']);
    $petugas = User::factory()->petugas()->create(['name' => 'Petugas Beta']);

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->set('role', 'supervisor')
        ->assertSee('Supervisor Alpha')
        ->assertDontSee('Petugas Beta')
        ->set('role', 'petugas')
        ->assertSee('Petugas Beta')
        ->assertDontSee('Supervisor Alpha');
});

test('petugas index can filter users by active status', function () {
    $supervisor = User::factory()->supervisor()->create(['name' => 'Supervisor Aktif']);
    $activeUser = User::factory()->petugas()->create(['name' => 'Operator Aktif', 'is_active' => true]);
    $inactiveUser = User::factory()->petugas()->inactive()->create(['name' => 'Operator Nonaktif', 'is_active' => false]);

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->set('status', '1')
        ->assertSee('Operator Aktif')
        ->assertDontSee('Operator Nonaktif')
        ->set('status', '0')
        ->assertSee('Operator Nonaktif')
        ->assertDontSee('Operator Aktif');
});

test('petugas index can reset all filters', function () {
    $supervisor = User::factory()->supervisor()->create();

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->set('search', 'KataKunci')
        ->set('role', 'petugas')
        ->set('status', '1')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('role', '')
        ->assertSet('status', '');
});

test('petugas index paginates results', function () {
    $supervisor = User::factory()->supervisor()->create();
    User::factory()->count(15)->petugas()->create();

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->assertViewHas('users', fn ($users) => $users->total() >= 16 && $users->perPage() === 10);
});
