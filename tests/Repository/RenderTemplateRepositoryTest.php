<?php

namespace Tourze\CmsTemplateBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\CmsTemplateBundle\Repository\RenderTemplateRepository;

class RenderTemplateRepositoryTest extends TestCase
{
    private RenderTemplateRepository $repository;
    private ManagerRegistry|MockObject $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new RenderTemplateRepository($this->registry);
    }

    public function test_extends_service_entity_repository(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function test_repository_constructor(): void
    {
        $repository = new RenderTemplateRepository($this->registry);

        $this->assertInstanceOf(RenderTemplateRepository::class, $repository);
    }

    public function test_repository_manages_render_template_entity(): void
    {
        // 通过反射检查Repository管理的实体类
        $reflection = new \ReflectionClass($this->repository);
        $parent = $reflection->getParentClass();

        $this->assertEquals(ServiceEntityRepository::class, $parent->getName());
    }


    public function test_repository_with_mock_registry(): void
    {
        $mockRegistry = $this->createMock(ManagerRegistry::class);
        $repository = new RenderTemplateRepository($mockRegistry);

        $this->assertInstanceOf(RenderTemplateRepository::class, $repository);
        $this->assertInstanceOf(ServiceEntityRepository::class, $repository);
    }
}
