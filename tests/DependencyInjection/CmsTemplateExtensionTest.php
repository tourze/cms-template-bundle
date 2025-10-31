<?php

namespace Tourze\CmsTemplateBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\CmsTemplateBundle\DependencyInjection\CmsTemplateExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(CmsTemplateExtension::class)]
final class CmsTemplateExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private CmsTemplateExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new CmsTemplateExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
    }

    public function testGetConfigDir(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $method = $reflection->getMethod('getConfigDir');
        $method->setAccessible(true);

        $configDir = $method->invoke($this->extension);
        $this->assertStringContainsString('Resources/config', $configDir);
        $this->assertDirectoryExists($configDir);
    }

    public function testExtensionAlias(): void
    {
        $expectedAlias = 'cms_template';
        $this->assertEquals($expectedAlias, $this->extension->getAlias());
    }

    public function testContainerBuilderInstance(): void
    {
        $this->assertInstanceOf(ContainerBuilder::class, $this->container);
    }
}
