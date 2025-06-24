<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TransaksiPenjualan;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransaksiPenjualanPolicy
{
    use HandlesAuthorization;

    // bypass Super Admin
    public function before(User $user, string $ability): ?bool
    {
        // Check if the user has the 'Super Admin' role.
        // If they do, they can do anything, so we return true immediately.
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Return null to allow the check to fall through to the specific policy method below.
        return null; 
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_transaksi::penjualan');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TransaksiPenjualan $transaksiPenjualan): bool
    {
        return $user->can('view_transaksi::penjualan');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_transaksi::penjualan');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TransaksiPenjualan $transaksiPenjualan): bool
    {
        return $user->can('update_transaksi::penjualan');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TransaksiPenjualan $transaksiPenjualan): bool
    {
        return $user->can('delete_transaksi::penjualan');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_transaksi::penjualan');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, TransaksiPenjualan $transaksiPenjualan): bool
    {
        return $user->can('force_delete_transaksi::penjualan');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_transaksi::penjualan');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, TransaksiPenjualan $transaksiPenjualan): bool
    {
        return $user->can('restore_transaksi::penjualan');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_transaksi::penjualan');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, TransaksiPenjualan $transaksiPenjualan): bool
    {
        return $user->can('replicate_transaksi::penjualan');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_transaksi::penjualan');
    }

    public function approve(User $user, TransaksiPenjualan $transaksiPenjualan): bool
    {
        // Layer 1: Check for the general permission first.
        if (!$user->can('approve_transaksi::penjualan')) {
            return false;
        }

        // Layer 2: If they have the permission, now check the business logic.
        return $transaksiPenjualan->status === 'pending_approval' &&
               $user->divisi === 'sales' &&
               $user->jabatan === 'manager';
    }
}
