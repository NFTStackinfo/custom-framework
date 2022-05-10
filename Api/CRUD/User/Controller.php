<?php

namespace Api\CRUD\User;

use Core\Controller\ApiCRUDController;

class UserCRUD extends ApiCRUDController {
    public static $model = 'User';

    public static $serializer = 'CRUD\User\UserSerializer';
}
