<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CartItemPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // return $user->id;
        
        parse_str($_SERVER['QUERY_STRING'], $request);
        $userId = null;
        if(!empty($request)) {
            if(!empty($request['where'])) {
                if(is_array($request['where'])) {
                    foreach($request['where'] as $item) {
                        $v = explode(',', $item);
                        if($v[0] == 'created_by') {
                            $userId = $v[1];
                        }
                    }
                } else {
                    $v = explode(',', $request['where']);
                    if($v[0] == 'created_by') {
                        $userId = $v[1];
                    }
                }
            }
        }
        
        return $user->id == $userId || $user->is_admin;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CartItem  $cartItem
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, CartItem $cartItem)
    {
        return $user->id == $cartItem->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->id;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CartItem  $cartItem
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, CartItem $cartItem)
    {
        return $user->id == $cartItem->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CartItem  $cartItem
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, CartItem $cartItem)
    {
        return $user->id == $cartItem->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CartItem  $cartItem
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, CartItem $cartItem)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CartItem  $cartItem
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, CartItem $cartItem)
    {
        //
    }
}
