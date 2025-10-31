<?php

namespace Tourze\CmsTemplateBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CmsTemplateBundle\Service\RoutingCondition;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(RoutingCondition::class)]
#[RunTestsInSeparateProcesses]
final class RoutingConditionTest extends AbstractIntegrationTestCase
{
    private RoutingCondition $routingCondition;

    protected function onSetUp(): void
    {
        $this->routingCondition = self::getService(RoutingCondition::class);
    }

    public function testServiceIsRegistered(): void
    {
        $this->assertInstanceOf(RoutingCondition::class, $this->routingCondition);
    }

    public function testCheck(): void
    {
        // 测试check方法的返回类型
        $reflection = new \ReflectionMethod($this->routingCondition, 'check');
        $this->assertTrue($reflection->isPublic());

        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('bool', (string) $returnType);
    }
}
