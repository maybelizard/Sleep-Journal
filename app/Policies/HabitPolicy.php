<?php

namespace App\Policies;

use App\Models\Habit;
use App\Models\User;

class HabitPolicy
{
    public function delete(User $user, Habit $habit): bool
    {
        return $user->id === $habit->user_id;
    }
}