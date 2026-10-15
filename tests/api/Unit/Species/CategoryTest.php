<?php

declare(strict_types=1);

namespace App\Tests\Unit\Species;

use App\Species\Entity\Category;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CategoryTest extends TestCase
{
    /**
     * @return iterable<string, array{string, Category}>
     */
    public static function kingdoms(): iterable
    {
        yield 'animals' => ['Animalia', Category::Fauna];
        yield 'plants' => ['Plantae', Category::Flora];
        yield 'fungi' => ['Fungi', Category::Fungi];
        yield 'anything else' => ['Chromista', Category::Other];
        yield 'unknown kingdom' => ['', Category::Other];
    }

    #[DataProvider('kingdoms')]
    public function testCategoryFollowsTheKingdom(string $kingdom, Category $expected): void
    {
        self::assertSame($expected, Category::fromKingdom($kingdom));
    }
}
