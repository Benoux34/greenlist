<?php

declare(strict_types=1);

namespace App\User\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_UNPROCESSABLE_ENTITY)]
final class InvalidResetCodeException extends \DomainException
{
}
