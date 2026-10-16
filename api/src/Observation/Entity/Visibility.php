<?php

declare(strict_types=1);

namespace App\Observation\Entity;

enum Visibility: string
{
    case Private = 'private';
    case Friends = 'friends';
}
