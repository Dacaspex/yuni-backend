<?php

namespace App\Storage;

use Vcn\Symfony\AutoFactory\AutoFactory;

class Factory implements AutoFactory
{
    public static function getStorage(): Storage
    {
        // TODO: Extract this information from a config file or environment file
        return new Storage(
            'mysql:host=localhost;dbname=yuni',
            'yuni_dev',
            'test123'
        );
    }
}