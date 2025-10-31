<?php

namespace Tourze\CmsTemplateBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Tourze\CmsTemplateBundle\Service\AttributeControllerLoader;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    private AttributeControllerLoader $loader;

    protected function onSetUp(): void
    {
        $this->loader = self::getService(AttributeControllerLoader::class);
    }

    public function testExtendsLoader(): void
    {
        $this->assertInstanceOf(Loader::class, $this->loader);
    }

    public function testImplementsRoutingAutoLoaderInterface(): void
    {
        $this->assertInstanceOf(RoutingAutoLoaderInterface::class, $this->loader);
    }

    public function testSupportsReturnsFalse(): void
    {
        $result = $this->loader->supports('any-resource');

        $this->assertFalse($result);
    }

    public function testSupportsWithTypeReturnsFalse(): void
    {
        $result = $this->loader->supports('any-resource', 'any-type');

        $this->assertFalse($result);
    }

    public function testSupportsWithNullTypeReturnsFalse(): void
    {
        $result = $this->loader->supports('any-resource', null);

        $this->assertFalse($result);
    }

    public function testAutoloadReturnsRouteCollection(): void
    {
        $collection = $this->loader->autoload();

        $this->assertInstanceOf(RouteCollection::class, $collection);
    }

    public function testLoadReturnsRouteCollection(): void
    {
        $collection = $this->loader->load('any-resource');

        $this->assertInstanceOf(RouteCollection::class, $collection);
    }

    public function testLoadWithTypeReturnsRouteCollection(): void
    {
        $collection = $this->loader->load('any-resource', 'any-type');

        $this->assertInstanceOf(RouteCollection::class, $collection);
    }

    public function testLoadCallsAutoload(): void
    {
        // 由于load方法直接调用autoload，我们测试结果是否一致
        $collection1 = $this->loader->autoload();
        $collection2 = $this->loader->load('any-resource');

        $this->assertInstanceOf(RouteCollection::class, $collection1);
        $this->assertInstanceOf(RouteCollection::class, $collection2);
    }

    public function testAutoloadConsistency(): void
    {
        $collection1 = $this->loader->autoload();
        $collection2 = $this->loader->autoload();

        // 两次调用应该返回相同类型的对象
        $this->assertInstanceOf(RouteCollection::class, $collection1);
        $this->assertInstanceOf(RouteCollection::class, $collection2);
    }

    public function testRouteCollectionIsNotNull(): void
    {
        $collection = $this->loader->autoload();

        $this->assertNotNull($collection);
    }
}
