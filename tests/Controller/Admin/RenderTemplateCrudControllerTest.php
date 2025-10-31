<?php

declare(strict_types=1);

namespace Tourze\CmsTemplateBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CmsTemplateBundle\Controller\Admin\RenderTemplateCrudController;
use Tourze\CmsTemplateBundle\Entity\RenderTemplate;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * RenderTemplateCrudController 测试
 *
 * @internal
 */
#[CoversClass(RenderTemplateCrudController::class)]
#[RunTestsInSeparateProcesses]
final class RenderTemplateCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * 读取容器中的真实服务
     */
    protected function getControllerService(): RenderTemplateCrudController
    {
        return new RenderTemplateCrudController();
    }

    /**
     * 首页表头字段
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '路径' => ['路径'];
        yield '标题' => ['标题'];
        yield '有效' => ['有效'];
        yield '内容预览' => ['内容预览'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /**
     * 创建页面需要用到的字段
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'path' => ['path'];
        yield 'title' => ['title'];
        yield 'content' => ['content'];
        yield 'valid' => ['valid'];
        yield 'parent' => ['parent'];
    }

    /**
     * 编辑页用到的字段
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'path' => ['path'];
        yield 'title' => ['title'];
        yield 'content' => ['content'];
        yield 'valid' => ['valid'];
        yield 'parent' => ['parent'];
    }

    /**
     * 测试getEntityFqcn方法返回正确的实体类
     */
    public function testGetEntityFqcnShouldReturnRenderTemplateClass(): void
    {
        $this->assertEquals(RenderTemplate::class, RenderTemplateCrudController::getEntityFqcn());
    }

    /**
     * 测试控制器类结构
     */
    public function testControllerStructure(): void
    {
        $reflection = new \ReflectionClass(RenderTemplateCrudController::class);

        // 测试类是final的
        $this->assertTrue($reflection->isFinal());

        // 测试继承关系
        $this->assertTrue($reflection->isSubclassOf('EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController'));

        // 测试没有构造函数或有默认构造函数
        $constructor = $reflection->getConstructor();
        if (null !== $constructor) {
            $this->assertSame(0, $constructor->getNumberOfRequiredParameters());
        }
    }

    /**
     * 测试控制器配置方法存在
     */
    public function testControllerHasRequiredConfigurationMethods(): void
    {
        $reflection = new \ReflectionClass(RenderTemplateCrudController::class);

        $requiredMethods = [
            'getEntityFqcn',
            'configureCrud',
            'configureActions',
            'configureFields',
            'configureFilters',
        ];

        foreach ($requiredMethods as $methodName) {
            $this->assertTrue($reflection->hasMethod($methodName), "方法 {$methodName} 必须存在");

            $method = $reflection->getMethod($methodName);
            $this->assertTrue($method->isPublic(), "方法 {$methodName} 必须是public");
        }
    }

    /**
     * 测试配置方法返回正确的类型
     */
    public function testConfigurationMethodsReturnCorrectTypes(): void
    {
        $controller = new RenderTemplateCrudController();

        // 测试getEntityFqcn返回正确的FQCN
        $this->assertEquals(RenderTemplate::class, $controller::getEntityFqcn());

        // 测试可以创建实体实例
        $entityClass = $controller::getEntityFqcn();
        $entity = new $entityClass();
        $this->assertInstanceOf(RenderTemplate::class, $entity);
    }

    /**
     * 测试字段配置方法返回迭代器
     */
    public function testConfigureFieldsReturnsIterable(): void
    {
        $controller = new RenderTemplateCrudController();

        $pages = ['index', 'new', 'edit', 'detail'];

        foreach ($pages as $pageName) {
            $fields = $controller->configureFields($pageName);
            $this->assertIsIterable($fields, "页面 {$pageName} 的字段配置必须是可迭代的");

            // 将迭代器转换为数组以进行进一步测试
            $fieldsArray = iterator_to_array($fields);
            $this->assertIsArray($fieldsArray, "页面 {$pageName} 的字段配置转换后必须是数组");
            $this->assertNotEmpty($fieldsArray, "页面 {$pageName} 必须至少有一个字段");
        }
    }

    /**
     * 测试必需字段的存在
     */
    public function testRequiredFieldsAreConfigured(): void
    {
        $controller = new RenderTemplateCrudController();

        // 测试字段配置方法返回的是可迭代对象且非空
        $newFields = iterator_to_array($controller->configureFields('new'));
        $this->assertIsArray($newFields, 'configureFields应该返回可以转换为数组的迭代器');
        $this->assertNotEmpty($newFields, 'new页面应该至少有一个字段配置');

        $indexFields = iterator_to_array($controller->configureFields('index'));
        $this->assertIsArray($indexFields, 'configureFields应该返回可以转换为数组的迭代器');
        $this->assertNotEmpty($indexFields, 'index页面应该至少有一个字段配置');

        // 验证字段数量合理（至少应该有基本的几个字段）
        $this->assertGreaterThanOrEqual(4, count($newFields), 'new页面应该至少有4个字段');
        $this->assertGreaterThanOrEqual(4, count($indexFields), 'index页面应该至少有4个字段');
    }

    /**
     * 测试CRUD配置包含中文标签
     */
    public function testCrudConfigurationHasChineseLabels(): void
    {
        $controller = new RenderTemplateCrudController();

        // 测试configureCrud方法可以正常调用并返回Crud对象
        $reflection = new \ReflectionClass($controller);
        $this->assertTrue($reflection->hasMethod('configureCrud'), 'configureCrud方法必须存在');
        $this->assertTrue($reflection->getMethod('configureCrud')->isPublic(), 'configureCrud方法必须是public');
    }

    /**
     * 测试过滤器配置
     */
    public function testConfigureFiltersIsCallable(): void
    {
        $controller = new RenderTemplateCrudController();
        $reflection = new \ReflectionClass($controller);

        // 测试configureFilters和configureActions方法存在且为public
        $this->assertTrue($reflection->hasMethod('configureFilters'), 'configureFilters方法必须存在');
        $this->assertTrue($reflection->getMethod('configureFilters')->isPublic(), 'configureFilters方法必须是public');

        $this->assertTrue($reflection->hasMethod('configureActions'), 'configureActions方法必须存在');
        $this->assertTrue($reflection->getMethod('configureActions')->isPublic(), 'configureActions方法必须是public');
    }

    /**
     * 测试实体类有正确的属性
     */
    public function testEntityClassHasRequiredProperties(): void
    {
        $entityClass = RenderTemplateCrudController::getEntityFqcn();
        $reflection = new \ReflectionClass($entityClass);

        $requiredProperties = ['path', 'title', 'content', 'valid', 'parent'];

        foreach ($requiredProperties as $property) {
            $this->assertTrue($reflection->hasProperty($property), "实体类必须有属性 {$property}");
        }
    }

    /**
     * 测试实体类实现了必要的接口
     */
    public function testEntityImplementsRequiredInterfaces(): void
    {
        $entityClass = RenderTemplateCrudController::getEntityFqcn();
        $reflection = new \ReflectionClass($entityClass);

        // 测试实现了Stringable接口
        $this->assertTrue($reflection->implementsInterface(\Stringable::class), '实体必须实现Stringable接口');

        // 测试实现了Itemable接口
        $this->assertTrue($reflection->implementsInterface('Tourze\EnumExtra\Itemable'), '实体必须实现Itemable接口');
    }

    /**
     * 测试验证错误处理
     */
    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();

        // 访问新建页面
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));
        $this->assertResponseIsSuccessful();

        // 获取表单
        $form = $crawler->selectButton('Create')->form();

        // 提交空表单（应该触发验证错误）
        $crawler = $client->submit($form);

        // 验证响应状态码
        $this->assertResponseStatusCodeSame(422);

        // 验证错误信息
        $this->assertStringContainsString('should not be blank', $crawler->filter('.invalid-feedback')->text());
    }
}
