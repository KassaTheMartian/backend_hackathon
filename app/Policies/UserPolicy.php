<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     * Only authenticated users can view user list (for admin purposes).
     */
    public function viewAny(User $user): bool
    {
        return true; // Allow authenticated users to view user list
    }

    /**
     * Determine whether the user can view the model.
     * Users can view their own profile or admin can view any profile.
     */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can create models.
     * Only admins can create new users (registration is handled separately).
     */
    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     * Users can update their own profile or admin can update any profile.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     * Only admins can delete users, and they cannot delete themselves.
     */
    public function delete(User $user, User $model): bool
    {
        return $this->isAdmin($user) && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     * Only admins can restore soft-deleted users.
     */
    public function restore(User $user, User $model): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only admins can permanently delete users.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $this->isAdmin($user) && $user->id !== $model->id;
    }

    /**
     * Check if user is admin (you can customize this based on your admin logic).
     * For now, we'll use a simple email-based check or add an admin field later.
     */
    private function isAdmin(User $user): bool
    {
        // Use is_admin field from users table
        return $user->is_admin ?? false;
    }
}
