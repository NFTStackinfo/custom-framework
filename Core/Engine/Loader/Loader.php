<?php

namespace Engine\Loader;

use Engine\Loader\Exception\NotFoundException;

class Loader {
    private $prefixes = [];

    /**
     * @param string $class
     *
     * @throws NotFoundException
     */
    private function loadClass(string $class) {
        $prefix = $class;

        $file_found = false;
        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($class, 0, $pos + 1);

            $relative_class = substr($class, $pos + 1);

            $mapped_file = $this->loadMappedFile($prefix, $relative_class);
            if ($mapped_file) {
                $file_found = true;
                break;
            }

            $prefix = rtrim($prefix, '\\');
        }

        if (!$file_found) {
            throw new NotFoundException();
        }
    }

    /**
     * @param string $prefix
     * @param string $relative_class
     *
     * @return bool|string
     */
    private function loadMappedFile(string $prefix, string $relative_class) {
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }

        foreach ($this->prefixes[$prefix] as $base_dir) {
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            if ($this->requireFile($file)) {
                return $file;
            }
        }

        return false;
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    private function requireFile(string $file): bool {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    }

    /**
     * @param string $prefix
     * @param string $base_dir
     *
     * @return Loader
     */
    public function addNamespace(string $prefix, string $base_dir): Loader {
        $prefix = trim($prefix, '\\') . '\\';

        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';

        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = [];
        }

        array_push($this->prefixes[$prefix], $base_dir);

        return $this;
    }

    public function register() {
        spl_autoload_register([$this, 'loadClass']);
    }
}