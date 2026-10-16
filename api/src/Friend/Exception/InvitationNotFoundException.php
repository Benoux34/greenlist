<?php

declare(strict_types=1);

namespace App\Friend\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_NOT_FOUND)]
final class InvitationNotFoundException extends \DomainException
{
}
