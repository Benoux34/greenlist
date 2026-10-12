<?php

declare(strict_types=1);

namespace App\Place\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_SERVICE_UNAVAILABLE)]
final class PlaceLookupFailedException extends \RuntimeException
{
}
