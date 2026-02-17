<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class ApiRequestMatcher implements RequestMatcherInterface
{
    public function matches(Request $request): bool
    {
        if ($request->getPathInfo() === '/v1/api/login') {
            return false;
        }

        return str_starts_with($request->getPathInfo(), '/v1/api');
    }
}
