<?php

namespace Models;

use Db\Model\Field\IdField;
use Db\Model\Model;
use Db\Model\Field\CharField;
use Db\Model\Field\RandomHashField;
use Db\Where;

/**
 * @property string login
 * @property string first_name
 * @property string last_name
 * @property string login_hash
 * @property int role
 * @property string ga_hash
 */
class UserModel extends Model {
    protected static $table_name = 'users';

    protected static $fields = [];

    protected static function fields(): array {
        return [
            'login' => CharField::init()->setLength(64),
            'first_name' => CharField::init()->setLength(512),
            'last_name' => CharField::init()->setLength(512),
            'login_hash' => RandomHashField::init()->setLength(64),
            'role' => IdField::init(),
            'ga_hash' => CharField::init()->setLength(6),
        ];
    }

    public static function getPartnersCount($user_id): int {
        $row = self::queryBuilder()->columns(['COUNT(id)' => 'cnt'])->where(Where::find_in_set('refer', $user_id))->get();
        return (int) $row['cnt'];
    }
}
