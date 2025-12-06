<?php

namespace App\Domains\User\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function updateProfile(int $id, array $data): User
    {
        $user = User::findOrFail($id);

        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return $user->fresh();
    }
}
