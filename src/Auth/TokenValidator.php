<?php

namespace App\Auth;

use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @param Request $request
     * @return bool
     */
    public function check(Request $request): bool
    {
        $token = $request->headers->get('X-api-key');

        return in_array($token, $this->tokens);
    }
}