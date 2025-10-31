<?php

namespace Tourze\CmsTemplateBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CmsTemplateBundle\Service\AdminMenu;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
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
        // 设置 LinkGenerator 的 mock
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $linkGenerator->method('getCurdListPage')->willReturn('/admin/render-template');

        self::getContainer()->set(LinkGeneratorInterface::class, $linkGenerator);
    }

    public function testInvokeCreatesContentCenterMenu(): void
    {
        $rootItem = $this->createMock(ItemInterface::class);
        $contentCenterItem = $this->createMock(ItemInterface::class);

        // 模拟根菜单项没有内容中心子菜单
        $rootItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('内容中心')
            ->willReturnOnConsecutiveCalls(null, $contentCenterItem)
        ;

        // 模拟添加内容中心菜单
        $rootItem->expects($this->once())
            ->method('addChild')
            ->with('内容中心')
            ->willReturn($contentCenterItem)
        ;

        // 模拟内容中心菜单添加渲染模板子菜单
        $contentCenterItem->expects($this->once())
            ->method('addChild')
            ->with('渲染模板')
            ->willReturn($this->createMock(ItemInterface::class))
        ;

        $adminMenu = self::getService(AdminMenu::class);
        $adminMenu($rootItem);
    }

    public function testInvokeWithExistingContentCenterMenu(): void
    {
        $rootItem = $this->createMock(ItemInterface::class);
        $contentCenterItem = $this->createMock(ItemInterface::class);

        // 模拟根菜单项已经有内容中心子菜单
        $rootItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('内容中心')
            ->willReturn($contentCenterItem)
        ;

        // 不应该再次添加内容中心菜单
        $rootItem->expects($this->never())
            ->method('addChild')
        ;

        // 模拟内容中心菜单添加渲染模板子菜单
        $contentCenterItem->expects($this->once())
            ->method('addChild')
            ->with('渲染模板')
            ->willReturn($this->createMock(ItemInterface::class))
        ;

        $adminMenu = self::getService(AdminMenu::class);
        $adminMenu($rootItem);
    }
}
