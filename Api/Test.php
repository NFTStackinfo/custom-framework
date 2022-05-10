<?php

namespace Api\Test;

use Core\Response\HttpResponse;
use Db\QueryBuilder;

use Models\UserModel;

function ping() {
    HttpResponse::ok('pong');
}

function test() {
    // Call directly builder
    $users = QueryBuilder::query('users')
         ->columns(['SUM(id)' => 'sum_id'])
         ->groupBy(['id'])
         ->limit(10)
         ->offset(10)
         ->select();

    var_dump($users);

    // Call from model
    $users = UserModel::queryBuilder()
                      ->columns(['SUM(id)' => 'sum_id'])
                      ->groupBy(['id'])
                      ->limit(10)
                      ->offset(10)
                      ->select();

    var_dump($users);
}