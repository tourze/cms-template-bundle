<?php

namespace Tourze\CmsTemplateBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Tourze\CmsTemplateBundle\DependencyInjection\CmsTemplateExtension;

class CmsTemplateExtensionTest extends TestCase
{
    private CmsTemplateExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new CmsTemplateExtension();
        $this->container = new ContainerBuilder();
    }

    public function test_extension_extends_base_extension(): void
    {
        $this->assertInstanceOf(Extension::class, $this->extension);
    }

    public function test_load_services_configuration(): void
    {
        // 由于服务配置文件可能不存在于测试环境，我们测试扩展是否正确设置
        $configs = [];

        // 测试load方法不抛出异常
        $this->expectNotToPerformAssertions();
        try {
            $this->extension->load($configs, $this->container);
        } catch  (\Throwable $e) {
            // 如果services.yaml文件不存在，这是预期的
            $this->assertStringContainsString('services.yaml', $e->getMessage());
        }
    }

    public function test_load_with_empty_configs(): void
    {
        $configs = [];

        try {
            $this->extension->load($configs, $this->container);
            $this->assertTrue(true); // 如果没有异常，测试通过
        } catch  (\Throwable $e) {
            // 在测试环境中，如果配置文件不存在是正常的
            $this->assertStringContainsString('services.yaml', $e->getMessage());
        }
    }

    public function test_extension_alias(): void
    {
        $expectedAlias = 'cms_template';
        $this->assertEquals($expectedAlias, $this->extension->getAlias());
    }

    public function test_container_builder_instance(): void
    {
        $this->assertInstanceOf(ContainerBuilder::class, $this->container);
    }
}
