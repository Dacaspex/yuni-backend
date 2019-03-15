<?php

namespace App\Service;

use App\Storage\Storage;
use Vcn\Symfony\AutoFactory\AutoFactory;

class Factory implements AutoFactory
{
    /**
     * @param Storage $storage
     * @return VisitorService
     */
    public static function getCanteenService(Storage $storage): VisitorService
    {
        return new VisitorService($storage);
    }
}