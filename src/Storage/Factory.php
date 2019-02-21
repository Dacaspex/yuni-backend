<?php

namespace App\Storage;

use Vcn\Symfony\AutoFactory\AutoFactory;

class Factory implements AutoFactory
{
    public static function getStorage(): Storage
    {
        return new Storage(
            getenv('DB_DSN'),
            getenv('DB_USER'),
            getenv('DB_PASS')
        );
    }
}