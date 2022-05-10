<?php

namespace Core\Command;

use Exception;

use Db\Db;
use Db\Exception as DbException;
use Db\Model\Model;
use Db\Model\Exception as ModelException;
use Db\Model\Field;
use Db\Transaction;
use Db\Where;

use Migrations;

class Migrate implements CommandInterface {
    private $prefix;

    /**
     * Migrate constructor.
     *
     * @param string|null $prefix
     */
    function __construct(string $prefix = null) {
        $this->prefix = $prefix ?: '';
    }

    public function exec() {
        if (!$this->isInitialized()) {
            $this->initialize();
        }

        $migrations = $this->getPendingMigrations();

        foreach ($migrations as $migration) {
            $this->migrate($migration);
        }
    }

    /**
     * @return bool
     * @throws DbException\DbAdapterException
     * @throws ModelException\TableNameUndefinedException
     */
    private function isInitialized(): bool {
        return Db::hasTable(MigrationModel::getTableName());
    }

    /**
     * @throws ModelException\ModelUndefinedFieldsException
     */
    private function initialize() {
        $fields = MigrationModel::getFields();

        $mig = new MigrationModel();
        $mig->prefix = '';
        $mig->name = 'Initial';

        Transaction::wrap(function () use ($fields, $mig) {
            Db::createTable(MigrationModel::getTableName(), $fields, MigrationModel::hasDefaultFields());
            $mig->save();
        });

        echo "Migration `Initial` done! \n";
    }

    /**
     * @return array
     * @throws DbException\DbAdapterException
     * @throws DbException\InvalidSelectQueryException
     * @throws DbException\InvalidWhereOperatorException
     * @throws ModelException\TableNameUndefinedException
     */
    private function getPendingMigrations(): array {
        $all_migrations = [];

        $migrations_dir_path = KERNEL_CONFIG['root'];
        $migrations_dir_path .= '/' . KERNEL_CONFIG['autoloader']['prefixes']['Migrations'];
        $migrations_dir_path .= "/{$this->prefix}/";

        if (is_dir($migrations_dir_path)) {
            if ($dh = opendir($migrations_dir_path)) {
                while (($file_path = readdir($dh)) !== false) {
                    $migration_file_path = $migrations_dir_path . $file_path;
                    if (is_file($migration_file_path) && substr($file_path, 0, 10) == 'Migration_') {
                        list($migration_name, $ext) = explode('.', $file_path);

                        $all_migrations[] = $migration_name;
                    }
                }
                closedir($dh);
            }
        }

        $applied_migrations = MigrationModel::select(Where::equal('prefix', $this->prefix));
        $applied_migrations_names = $applied_migrations->column('name');

        $pending_migrations = array_diff($all_migrations, $applied_migrations_names);

        sort($pending_migrations);

        return $pending_migrations;
    }

    /**
     * @param string $name
     *
     * @throws Exception
     */
    private function migrate(string $name) {
        $migration_name = 'Migrations\\';
        if ($this->prefix) {
            $migration_name .= "{$this->prefix}\\";
        }
        $migration_name .= "{$name}";

        echo "Making migration `{$migration_name}`... \n";

        $up_callable = $migration_name . '::up';

        $mig = new MigrationModel();
        $mig->prefix = $this->prefix;
        $mig->name = $name;

        Transaction::wrap(function () use ($up_callable, $mig) {
            $up_callable();
            $mig->save();
        });

        echo "Migration `{$migration_name}` done! \n";
    }
}

class MigrationModel extends Model {
    protected static $table_name = 'core_migrations';

    protected static $has_default_fields = false;

    protected static $fields = [];

    protected static function fields(): array {
        return [
            'prefix' => Field\CharField::init()->setLength(256),
            'name' => Field\CharField::init()->setLength(256),
            'applied_at' => Field\CreatedAtField::init(),
        ];
    }
}