<?php declare(strict_types=1);

namespace Symplify\Autodiscovery\Tests;

use PHPUnit\Framework\TestCase;
use Symplify\Autodiscovery\DependencyInjection\AutodiscoveryKernel;

abstract class AbstractContainerAwareTestCase extends TestCase
{
    use ContainerAwareTestCaseTrait;

    protected function getKernelClass(): string
    {
        return AutodiscoveryKernel::class;
    }
}
