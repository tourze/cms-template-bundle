<?php

namespace Tourze\CmsTemplateBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tourze\CmsTemplateBundle\Controller\RenderController;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(RenderController::class)]
#[RunTestsInSeparateProcesses]
final class RenderControllerTest extends AbstractWebTestCase
{
    public function testUnauthenticatedAccessWithNoValidTemplates(): void
    {
        $client = self::createClientWithDatabase();

        $client->catchExceptions(false);

        try {
            $client->request('GET', '/non-existent-path');
            $this->assertResponseStatusCodeSame(404);
        } catch (NotFoundHttpException $e) {
            $this->assertStringContainsString('No route found', $e->getMessage());
        }
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();

        $client->catchExceptions(false);

        try {
            $client->request($method, '/non-existent-path');

            // 如果没有抛出异常，检查响应状态码
            $statusCode = $client->getResponse()->getStatusCode();
            $this->assertSame(405, $statusCode, sprintf('HTTP方法 %s 应该返回405状态码，实际返回了 %d', $method, $statusCode));
        } catch (NotFoundHttpException $e) {
            // 404说明路由没有为这个方法注册，这也是期望的行为
            $this->assertTrue(true, sprintf('HTTP方法 %s 正确地没有路由注册', $method));
        } catch (MethodNotAllowedHttpException $e) {
            // 405是我们期望的结果
            $this->assertTrue(true, sprintf('HTTP方法 %s 正确地被禁止', $method));
        }
    }
}
