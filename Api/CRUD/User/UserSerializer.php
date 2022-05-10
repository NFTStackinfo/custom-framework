<?php

namespace Api\CRUD\User;

class UserSerializer {
    public static function detail($user) {
        return [
            'id' => $user->id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'created_at' => $user->created_at,
        ];
    }
}
