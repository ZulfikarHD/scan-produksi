<?php

use App\Livewire\Petugas\PetugasIndex;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('supervisor can create a new petugas account', function () {
    $supervisor = User::factory()->supervisor()->create();

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->call('openCreateModal')
        ->set('name', 'Operator Baru')
        ->set('nip', 'NP999')
        ->set('username', 'operator_baru')
        ->set('email', 'operator.baru@pds.co.id')
        ->set('userRole', 'petugas')
        ->set('is_active', true)
        ->set('password', 'secret12345')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'name' => 'Operator Baru',
        'nip' => 'NP999',
        'username' => 'operator_baru',
        'email' => 'operator.baru@pds.co.id',
        'role' => 'petugas',
        'is_active' => 1,
    ]);

    $createdUser = User::where('nip', 'NP999')->first();
    expect(Hash::check('secret12345', $createdUser->password))->toBeTrue();
});

test('validation prevents creating petugas with invalid or duplicate data', function () {
    $supervisor = User::factory()->supervisor()->create();
    $existing = User::factory()->petugas()->create([
        'nip' => 'EXIST01',
        'username' => 'existing_user',
        'email' => 'existing@pds.co.id',
    ]);

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->call('openCreateModal')
        ->set('name', '')
        ->set('nip', 'EXIST01')
        ->set('username', 'existing_user')
        ->set('email', 'existing@pds.co.id')
        ->set('userRole', 'invalid_role')
        ->set('password', 'short')
        ->call('save')
        ->assertHasErrors([
            'name' => 'required',
            'nip' => 'unique',
            'username' => 'unique',
            'email' => 'unique',
            'userRole' => 'in',
            'password' => 'min',
        ]);
});

test('supervisor can edit existing petugas without changing password and keeping original unique attributes', function () {
    $supervisor = User::factory()->supervisor()->create();
    $petugas = User::factory()->petugas()->create([
        'name' => 'Nama Lama',
        'nip' => 'NP111',
        'username' => 'user_lama',
        'email' => 'lama@pds.co.id',
        'password' => Hash::make('original_password'),
    ]);

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->call('openEditModal', $petugas->id)
        ->assertSet('name', 'Nama Lama')
        ->assertSet('nip', 'NP111')
        ->assertSet('username', 'user_lama')
        ->assertSet('email', 'lama@pds.co.id')
        ->set('name', 'Nama Baru Terkoreksi')
        ->set('password', '') // Kosongkan jika tidak ubah password
        ->call('save')
        ->assertHasNoErrors();

    $petugas->refresh();
    expect($petugas->name)->toBe('Nama Baru Terkoreksi');
    expect(Hash::check('original_password', $petugas->password))->toBeTrue();
});

test('supervisor can edit existing petugas with new password', function () {
    $supervisor = User::factory()->supervisor()->create();
    $petugas = User::factory()->petugas()->create([
        'password' => Hash::make('old_password'),
    ]);

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->call('openEditModal', $petugas->id)
        ->set('password', 'new_secure_password')
        ->call('save')
        ->assertHasNoErrors();

    $petugas->refresh();
    expect(Hash::check('new_secure_password', $petugas->password))->toBeTrue();
});

test('supervisor can toggle active and inactive status of another petugas', function () {
    $supervisor = User::factory()->supervisor()->create();
    $petugas = User::factory()->petugas()->create(['is_active' => true]);

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->call('toggleStatus', $petugas->id);

    expect($petugas->fresh()->is_active)->toBeFalse();

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->call('toggleStatus', $petugas->id);

    expect($petugas->fresh()->is_active)->toBeTrue();
});

test('supervisor cannot deactivate their own logged in account', function () {
    $supervisor = User::factory()->supervisor()->create(['is_active' => true]);

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->call('toggleStatus', $supervisor->id);

    expect($supervisor->fresh()->is_active)->toBeTrue();
});

test('supervisor can delete another petugas account', function () {
    $supervisor = User::factory()->supervisor()->create();
    $petugas = User::factory()->petugas()->create();

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->call('confirmDelete', $petugas->id)
        ->assertSet('confirmingUserId', $petugas->id)
        ->call('deleteUser');

    $this->assertDatabaseMissing('users', ['id' => $petugas->id]);
});

test('supervisor cannot delete their own logged in account', function () {
    $supervisor = User::factory()->supervisor()->create();

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->call('confirmDelete', $supervisor->id)
        ->call('deleteUser');

    $this->assertDatabaseHas('users', ['id' => $supervisor->id]);
});

test('opening create modal resets validation errors and form state', function () {
    $supervisor = User::factory()->supervisor()->create();

    Livewire::actingAs($supervisor)
        ->test(PetugasIndex::class)
        ->set('name', 'Data Kotor')
        ->call('openCreateModal')
        ->assertSet('name', '')
        ->assertSet('editingUserId', null)
        ->assertSet('userRole', 'petugas')
        ->assertSet('is_active', true);
});
