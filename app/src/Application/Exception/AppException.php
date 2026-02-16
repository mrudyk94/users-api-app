<?php

declare(strict_types=1);

namespace App\Application\Exception;

use RuntimeException;

/**
 * Base exception for all domain exceptions in application.
 * All more specific exception have to extend this one
 */
class AppException extends RuntimeException
{

}

