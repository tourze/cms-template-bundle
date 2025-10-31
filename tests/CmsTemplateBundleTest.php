<?php

declare(strict_types=1);

namespace CmsTemplateBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CmsTemplateBundle\CmsTemplateBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(CmsTemplateBundle::class)]
#[RunTestsInSeparateProcesses]
final class CmsTemplateBundleTest extends AbstractBundleTestCase
{
}
