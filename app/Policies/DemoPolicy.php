<?php

namespace App\Policies;

use App\Models\Demo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DemoPolicy
{
    /**
     * Determine whether the user can view any models.
     * - Admin: Can view all demos
     * - User: Can view their own demos
     * - Guest: Can view only active demos (is_active = 1)
     */
    public function viewAny(?User $user = null): bool
    {
        // Admin can view all demos
        if ($user && $this->isAdmin($user)) {
            return true;
        }
        
        // User can view their own demos
        if ($user) {
            return true;
        }
        
        // Guest can view active demos (this will be handled in controller/service)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * - Admin: Can view any demo
     * - User: Can view their own demos
     * - Guest: Can view only active demos (is_active = 1)
     */
    public function view(?User $user = null, Demo $demo = null): bool
    {
        if (!$demo) {
            return false;
        }
        
        // Admin can view any demo
        if ($user && $this->isAdmin($user)) {
            return true;
        }
        
        // User can view their own demos
        if ($user && isset($demo->user_id) && $user->id === $demo->user_id) {
            return true;
        }
        
        // Guest can view only active demos
        if (!$user && $demo->is_active) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     * Only authenticated users can create demos.
     */
    public function create(?User $user = null): bool
    {
        // Get user from auth context if not provided
        $authUser = $user ?? auth()->user();
        
        \Log::info('DemoPolicy::create called', [
            'user_param' => $user ? $user->id : null,
            'auth_user' => $authUser ? $authUser->id : null,
            'is_admin' => $authUser ? $authUser->is_admin : null
        ]);
        
        return $authUser !== null; // Only authenticated users can create demos
    }

    /**
     * Determine whether the user can update the model.
     * Users can update their own demos or admins can update any demo.
     */
    public function update(?User $user = null, Demo $demo = null): bool
    {
        if (!$user) {
            return false; // Must be authenticated to update
        }
        
        // Check if demo has a user_id field (owner)
        if (isset($demo->user_id)) {
            return $user->id === $demo->user_id || $this->isAdmin($user);
        }
        
        // If no user_id field, only admins can update
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     * Users can delete their own demos or admins can delete any demo.
     */
    public function delete(?User $user = null, Demo $demo = null): bool
    {
        if (!$user) {
            return false; // Must be authenticated to delete
        }
        
        // Check if demo has a user_id field (owner)
        if (isset($demo->user_id)) {
            return $user->id === $demo->user_id || $this->isAdmin($user);
        }
        
        // If no user_id field, only admins can delete
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can restore the model.
     * Only admins can restore soft-deleted demos.
     */
    public function restore(?User $user = null, Demo $demo = null): bool
    {
        if (!$user) {
            return false; // Must be authenticated to restore
        }
        
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only admins can permanently delete demos.
     */
    public function forceDelete(?User $user = null, Demo $demo = null): bool
    {
        if (!$user) {
            return false; // Must be authenticated to force delete
        }
        
        return $this->isAdmin($user);
    }

    /**
     * Check if user is admin using the is_admin field.
     */
    private function isAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        
        return $user->is_admin ?? false;
    }
}
