<?php

use App\Enums\UserRole;
use App\Helpers\ShiftHelper;
use App\Models\User;
use Carbon\Carbon;

test('authenticated users can render layout and view shift info', function () {
    $user = User::factory()->create([
        'role' => UserRole::PETUGAS,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('PT PDS');
    $response->assertSee('Dashboard');
    $response->assertSee('Buat Laporan');
    $response->assertSee('Lihat Laporan');
    $response->assertSee('Pengaturan');
});

test('supervisor sees supervisor-only menu items in sidebar', function () {
    $supervisor = User::factory()->supervisor()->create([
        'name' => 'Pak Supervisor',
        'role' => UserRole::SUPERVISOR,
    ]);

    $response = $this->actingAs($supervisor)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Data Petugas');
    $response->assertSee('Update Database');
    $response->assertSee('Supervisor');
});

test('petugas does not see supervisor-only menu items in sidebar', function () {
    $petugas = User::factory()->petugas()->create([
        'name' => 'Budi Petugas',
        'role' => UserRole::PETUGAS,
    ]);

    $response = $this->actingAs($petugas)->get(route('reports.index'));

    $response->assertOk();
    $response->assertDontSee('Data Petugas');
    $response->assertDontSee('Update Database');
    $response->assertSee('Petugas');
});

test('shift helper accurately calculates shift 1, 2, and 3 based on Jakarta time', function () {
    // Shift 1: 07:00 - 15:00 WIB
    $timeShift1 = Carbon::parse('2026-08-26 09:30:00', 'Asia/Jakarta');
    $shift1 = ShiftHelper::getCurrentShift($timeShift1);
    expect($shift1['number'])->toBe(1)
        ->and($shift1['name'])->toBe('Shift 1 (Pagi)');

    // Shift 2: 15:00 - 23:00 WIB
    $timeShift2 = Carbon::parse('2026-08-26 16:45:00', 'Asia/Jakarta');
    $shift2 = ShiftHelper::getCurrentShift($timeShift2);
    expect($shift2['number'])->toBe(2)
        ->and($shift2['name'])->toBe('Shift 2 (Siang)');

    // Shift 3: 23:00 - 07:00 WIB
    $timeShift3Night = Carbon::parse('2026-08-26 23:30:00', 'Asia/Jakarta');
    $shift3Night = ShiftHelper::getCurrentShift($timeShift3Night);
    expect($shift3Night['number'])->toBe(3)
        ->and($shift3Night['name'])->toBe('Shift 3 (Malam)');

    $timeShift3EarlyMorning = Carbon::parse('2026-08-26 04:15:00', 'Asia/Jakarta');
    $shift3EarlyMorning = ShiftHelper::getCurrentShift($timeShift3EarlyMorning);
    expect($shift3EarlyMorning['number'])->toBe(3)
        ->and($shift3EarlyMorning['name'])->toBe('Shift 3 (Malam)');
});

test('shift helper formats datetime in Indonesian', function () {
    $testTime = Carbon::parse('2026-08-26 10:15:00', 'Asia/Jakarta');
    $formatted = ShiftHelper::getFormattedDateTime($testTime);

    expect($formatted)->toContain('Rabu')
        ->and($formatted)->toContain('26 Agustus 2026')
        ->and($formatted)->toContain('10:15 WIB');
});
