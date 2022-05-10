<?php 

namespace Migrations\SyncOld;

use Db\Db;
use Db\Migration\MigrationInterface;
use Db\Model\Field\CreatedAtField;
use Db\Model\Field\DeletedAtField;
use Db\Model\Field\UpdatedAtField;
use Db\Transaction;
use Db\Where;

class Migration_0000000001_UserTable implements MigrationInterface {
	public static function up() {
	    Transaction::wrap(function () {
            Db::addColumn('users', 'created_at_timestamp', CreatedAtField::init());
            Db::addColumn('users', 'updated_at_timestamp', UpdatedAtField::init());
            Db::addColumn('users', 'deleted_at', DeletedAtField::init());

            Db::update('users', ['deleted_at' => time()], Where::equal('_delete', 1));

        });
    }

	public static function down() {}
}
