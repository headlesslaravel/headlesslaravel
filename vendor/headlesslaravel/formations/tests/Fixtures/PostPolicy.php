<?php

namespace HeadlessLaravel\Formations\Tests\Fixtures;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user):bool
    {
        return $this->check($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @return bool
     */
    public function view(User $user):bool
    {
        return $this->check($user, 'view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user):bool
    {
        return $this->check($user, 'create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @return bool
     */
    public function update(User $user):bool
    {
        return $this->check($user, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @return bool
     */
    public function delete(User $user):bool
    {
        return $this->check($user, 'delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User  $user
     * @return bool
     */
    public function restore(User $user):bool
    {
        return $this->check($user, 'restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  User  $user
     * @return bool
     */
    public function forceDelete(User $user):bool
    {
        return $this->check($user, 'forceDelete');
    }

    /**
     * Determine whether the user can perform ability.
     *
     * @param  User  $user
     * @param  string  $ability
     * @return bool
     */
    private function check($user, $ability):bool
    {
        if(!$user->permissions) {
            return false;
        }

        return in_array($ability, json_decode($user->permissions, true));
    }
}
