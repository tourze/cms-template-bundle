<?php

namespace Tourze\CmsTemplateBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CmsTemplateBundle\Service\AdminMenu;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // 使用真实的 LinkGenerator 服务，避免 Mock
        // UserManagerInterface 服务一定存在，可以直接使用
    }

    public function testServiceIsRegistered(): void
    {
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }

    public function testInvokeIsCallable(): void
    {
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertIsCallable($adminMenu);
    }
}
