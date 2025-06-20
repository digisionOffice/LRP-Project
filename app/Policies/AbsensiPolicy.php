<?php

namespace App\Policies;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbsensiPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin and supervisor can view all attendance records
        if ($user->role === 'admin' || $user->role === 'supervisor') {
            return true;
        }

        // Karyawan can only view their own attendance records
        return $user->role === 'karyawan';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Absensi $absensi): bool
    {
        // Admin and supervisor can view any attendance record
        if ($user->role === 'admin' || $user->role === 'supervisor') {
            return true;
        }

        // Karyawan can only view their own attendance records
        if ($user->role === 'karyawan') {
            $karyawan = Karyawan::where('id_user', $user->id)->first();
            return $karyawan && $absensi->karyawan_id === $karyawan->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin, supervisor, and karyawan can create attendance records
        return in_array($user->role, ['admin', 'supervisor', 'karyawan']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Absensi $absensi): bool
    {
        // Admin and supervisor can update any attendance record
        if ($user->role === 'admin' || $user->role === 'supervisor') {
            return true;
        }

        // Karyawan can only update their own attendance records that haven't been approved yet
        if ($user->role === 'karyawan') {
            $karyawan = Karyawan::where('id_user', $user->id)->first();
            return $karyawan &&
                   $absensi->karyawan_id === $karyawan->id &&
                   is_null($absensi->approved_at);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Absensi $absensi): bool
    {
        // Only admin and supervisor can delete attendance records
        return $user->role === 'admin' || $user->role === 'supervisor';
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, Absensi $absensi): bool
    {
        // Only admin and supervisor can approve attendance records
        return $user->role === 'admin' || $user->role === 'supervisor';
    }
}
