<?php

namespace Core\Command;

class Migration implements CommandInterface {
    private $name;

    private $prefix;

    function __construct(string $name, string $prefix = null) {
        $this->name = $name;
        $this->prefix = $prefix ?: '';
    }

    public function exec() {
        $migrations_dir_path = KERNEL_CONFIG['root'];
        $migrations_dir_path .= '/' . KERNEL_CONFIG['autoloader']['prefixes']['Migrations'];
        if ($this->prefix) {
            $migrations_dir_path .= "/{$this->prefix}";
        }

        if (!file_exists($migrations_dir_path)) {
            mkdir($migrations_dir_path);
        }

        $migration_file_name = 'Migration_' . time() . '_' . $this->name . '.php';
        $migration_file_path = "{$migrations_dir_path}/{$migration_file_name}";

        $migration_file = fopen($migration_file_path, "w+");
        fwrite($migration_file, $this->getMigrationTpl());
        fclose($migration_file);

        echo "Migration reference file {$migration_file_name} created and stored into Database/Migrations/{$this->prefix} \n";
    }

    /**
     * @return string
     */
    private function getMigrationTpl(): string {
        $prefix_tpl = $this->prefix ? '\\' . $this->prefix : '';
        $name_tpl = 'Migration_' . time() . '_' . $this->name;

        return (
            "<?php \n" .
            "\n" .
            "namespace Migrations{$prefix_tpl};\n" .
            "\n" .
            "use Db\Migration\MigrationInterface;\n" .
            "\n" .
            "class {$name_tpl} implements MigrationInterface {\n" .
            "\tpublic static function up() {}\n" .
            "\n" .
            "\tpublic static function down() {}\n" .
            "}".
            "\n");
    }
}