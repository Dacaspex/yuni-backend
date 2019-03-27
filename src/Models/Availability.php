<?php

namespace App\Models;

use Vcn\Lib\Enum;

/**
 * @method static Availability IN_STOCK()
 * @method static Availability LOW_STOCK()
 * @method static Availability OUT_OF_STOCK()
 */
final class Availability extends Enum
{
    private const IN_STOCK = 0;
    private const LOW_STOCK = 0;
    private const OUT_OF_STOCK = 0;
}