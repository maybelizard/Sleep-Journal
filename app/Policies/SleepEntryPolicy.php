<?php

namespace App\Policies;

use App\Models\SleepEntry;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SleepEntryPolicy
{
    public function update(User $user, SleepEntry $sleepEntry): bool
    {
        return $user->id === $sleepEntry->user_id;
    }

    public function delete(User $user, SleepEntry $sleepEntry): bool
    {
        return $user->id === $sleepEntry->user_id;
    }
}
