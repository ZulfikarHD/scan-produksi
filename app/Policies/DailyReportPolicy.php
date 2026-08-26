<?php

namespace App\Policies;

use App\Enums\ReportStatus;
use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DailyReportPolicy
{
    use HandlesAuthorization;

    /**
     * Menentukan apakah pengguna dapat melihat daftar seluruh arsip laporan.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Menentukan apakah pengguna dapat melihat rincian laporan tertentu.
     */
    public function view(User $user, DailyReport $report): bool
    {
        return $user->is_active;
    }

    /**
     * Menentukan apakah pengguna dapat membuat laporan baru.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Menentukan apakah pengguna dapat mengoreksi / memperbarui isi laporan.
     *
     * Aturan:
     * - Supervisor memiliki hak edit penuh kapan pun.
     * - Petugas hanya dapat mengedit laporannya sendiri yang belum diverifikasi dan dalam batas 24 jam sejak dibuat.
     */
    public function update(User $user, DailyReport $report): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isSupervisor()) {
            return true;
        }

        // Petugas: hanya laporan milik sendiri, belum berstatus terverifikasi, dan masih dalam batas 24 jam
        if ($report->user_id !== $user->id) {
            return false;
        }

        if ($report->status === ReportStatus::VERIFIED) {
            return false;
        }

        if ($report->created_at && $report->created_at->lt(now()->subHours(24))) {
            return false;
        }

        return true;
    }

    /**
     * Menentukan apakah pengguna berhak memverifikasi laporan (persetujuan resmi).
     */
    public function verify(User $user, DailyReport $report): bool
    {
        if (! $user->is_active) {
            return false;
        }

        return $user->isSupervisor();
    }

    /**
     * Menentukan apakah pengguna berhak menghapus berkas laporan.
     */
    public function delete(User $user, DailyReport $report): bool
    {
        if (! $user->is_active) {
            return false;
        }

        return $user->isSupervisor();
    }
}
