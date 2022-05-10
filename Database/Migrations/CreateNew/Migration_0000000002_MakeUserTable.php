<?php

namespace Migrations\CreateNew;

use Db\Db;
use Db\Model\Field;
use Db\Migration\MigrationInterface;
use Db\Transaction;

class Migration_0000000002_MakeUserTable implements MigrationInterface {
    public static function up() {
        $fields = [
            'first_name' => Field\CharField::init()->setLength(512),
            'last_name' => Field\CharField::init()->setLength(512),
            'login_hash' => Field\RandomHashField::init()->setLength(64),
        ];

        Transaction::wrap(function () use ($fields) {
            Db::createTable('users', $fields, true, true);
        });
    }

    public static function down() {
        Transaction::wrap(function () {
            Db::dropTable('users');
        });
    }
}
