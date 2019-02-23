<?php

namespace App\Auth;

class TokenValidator
{
    /**
     * @var array
     */
    private $tokens;

    /**
     * @param array $tokens
     */
    public function __construct($tokens)
    {
        $this->tokens = $tokens;
    }

    public function check(string $token): bool
    {
        return in_array($token, $this->tokens);
    }
}