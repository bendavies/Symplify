<?php declare(strict_types=1);

namespace Symplify\CodingStandard\Tests\Fixer\Property\ArrayPropertyDefaultValueFixer;

use Symplify\CodingStandard\Fixer\Property\ArrayPropertyDefaultValueFixer;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;

final class ArrayPropertyDefaultValueFixerTest extends AbstractCheckerTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/correct.php.inc',
            __DIR__ . '/Fixture/wrong.php.inc',
            __DIR__ . '/Fixture/wrong2.php.inc',
            __DIR__ . '/Fixture/wrong3.php.inc',
            __DIR__ . '/Fixture/wrong4.php.inc',
        ]);
    }

    protected function getCheckerClass(): string
    {
        return ArrayPropertyDefaultValueFixer::class;
    }
}
