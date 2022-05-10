<?php

namespace Serializers;

class ProfileSerializer {
    public static function detail($user) {
        return [
            'name' => $user->first_name . ' ' .$user->last_name,
        ];
    }
}