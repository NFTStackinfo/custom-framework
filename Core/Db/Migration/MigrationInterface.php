<?php

namespace Db\Migration;

interface MigrationInterface {
    public static function up();

    public static function down();
}