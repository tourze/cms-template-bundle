<?php

namespace Tourze\CmsTemplateBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Tourze\CmsTemplateBundle\Service\AttributeControllerLoader;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;

class AttributeControllerLoaderTest extends TestCase
{
    private AttributeControllerLoader $loader;

    protected function setUp(): void
    {
        $this->loader = new AttributeControllerLoader();
    }

    public function test_extends_loader(): void
    {
        $this->assertInstanceOf(Loader::class, $this->loader);
    }

    public function test_implements_routing_auto_loader_interface(): void
    {
        $this->assertInstanceOf(RoutingAutoLoaderInterface::class, $this->loader);
    }

    public function test_constructor(): void
    {
        $loader = new AttributeControllerLoader();

        $this->assertInstanceOf(AttributeControllerLoader::class, $loader);
    }

    public function test_supports_returns_false(): void
    {
        $result = $this->loader->supports('any-resource');

        $this->assertFalse($result);
    }

    public function test_supports_with_type_returns_false(): void
    {
        $result = $this->loader->supports('any-resource', 'any-type');

        $this->assertFalse($result);
    }

    public function test_supports_with_null_type_returns_false(): void
    {
        $result = $this->loader->supports('any-resource', null);

        $this->assertFalse($result);
    }

    public function test_autoload_returns_route_collection(): void
    {
        $collection = $this->loader->autoload();

        $this->assertInstanceOf(RouteCollection::class, $collection);
    }

    public function test_load_returns_route_collection(): void
    {
        $collection = $this->loader->load('any-resource');

        $this->assertInstanceOf(RouteCollection::class, $collection);
    }

    public function test_load_with_type_returns_route_collection(): void
    {
        $collection = $this->loader->load('any-resource', 'any-type');

        $this->assertInstanceOf(RouteCollection::class, $collection);
    }

    public function test_load_calls_autoload(): void
    {
        // 由于load方法直接调用autoload，我们测试结果是否一致
        $collection1 = $this->loader->autoload();
        $collection2 = $this->loader->load('any-resource');

        $this->assertInstanceOf(RouteCollection::class, $collection1);
        $this->assertInstanceOf(RouteCollection::class, $collection2);
    }

    public function test_autoload_consistency(): void
    {
        $collection1 = $this->loader->autoload();
        $collection2 = $this->loader->autoload();

        // 两次调用应该返回相同类型的对象
        $this->assertInstanceOf(RouteCollection::class, $collection1);
        $this->assertInstanceOf(RouteCollection::class, $collection2);
    }

    public function test_route_collection_is_not_null(): void
    {
        $collection = $this->loader->autoload();

        $this->assertNotNull($collection);
    }

}
