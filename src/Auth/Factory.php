<?php

namespace App\Auth;

use Vcn\Pipette\Json;
use Vcn\Symfony\AutoFactory\AutoFactory;

class Factory implements AutoFactory
{
    /**
     * @return TokenValidator
     * @throws Json\Exception\CantDecode
     * @throws Json\Exception\AssertionFailed
     */
    public static function createTokenValidator(): TokenValidator
    {
        $json   = Json::parse(file_get_contents(__DIR__ . '/../../auth.json'));
        $tokens = $json->field('tokens')->arrayMap(
            function (Json\Value $json) {
                return $json->field('token')->string();
            }
        );

        return new TokenValidator($tokens);
    }
}