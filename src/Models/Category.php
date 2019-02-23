<?php

namespace App\Models;

use Vcn\Lib\Enum;

/**
 * @method static Category DRINKS();
 * @method static Category SANDWICH();
 * @method static Category OTHER();
 */
final class Category extends Enum
{
    private const DRINKS = 0;
    private const SANDWICH = 0;
    private const OTHER = 0;
}