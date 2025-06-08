<?php

namespace Tourze\CmsTemplateBundle\Tests\Controller;

use CmsBundle\Repository\EntityRepository;
use CmsBundle\Repository\ModelRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Tourze\CmsTemplateBundle\Controller\RenderController;
use Tourze\CmsTemplateBundle\Repository\RenderTemplateRepository;
use Twig\Environment;

class RenderControllerTest extends TestCase
{
    private RenderController $controller;
    private Environment $twig;
    private ModelRepository $modelRepository;
    private EntityRepository $entityRepository;
    private RenderTemplateRepository $templateRepository;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->modelRepository = $this->createMock(ModelRepository::class);
        $this->entityRepository = $this->createMock(EntityRepository::class);
        $this->templateRepository = $this->createMock(RenderTemplateRepository::class);

        $this->controller = new RenderController(
            $this->twig,
            $this->modelRepository,
            $this->entityRepository,
            $this->templateRepository
        );
    }

    public function test_extends_abstract_controller(): void
    {
        $this->assertInstanceOf(AbstractController::class, $this->controller);
    }

    public function test_constructor(): void
    {
        $controller = new RenderController(
            $this->twig,
            $this->modelRepository,
            $this->entityRepository,
            $this->templateRepository
        );

        $this->assertInstanceOf(RenderController::class, $controller);
    }

    public function test_main_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'main'));
    }

    public function test_controller_dependencies(): void
    {
        $this->assertInstanceOf(
            Environment::class,
            $this->getPrivateProperty($this->controller, 'twig')
        );
        $this->assertInstanceOf(
            ModelRepository::class,
            $this->getPrivateProperty($this->controller, 'modelRepository')
        );
        $this->assertInstanceOf(
            EntityRepository::class,
            $this->getPrivateProperty($this->controller, 'entityRepository')
        );
        $this->assertInstanceOf(
            RenderTemplateRepository::class,
            $this->getPrivateProperty($this->controller, 'templateRepository')
        );
    }

    private function getPrivateProperty(object $object, string $propertyName): mixed
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
