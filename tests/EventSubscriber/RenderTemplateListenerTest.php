<?php

namespace Tourze\CmsTemplateBundle\Tests\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CmsTemplateBundle\Entity\RenderTemplate;
use Tourze\CmsTemplateBundle\EventSubscriber\RenderTemplateListener;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(RenderTemplateListener::class)]
#[RunTestsInSeparateProcesses]
final class RenderTemplateListenerTest extends AbstractIntegrationTestCase
{
    private RenderTemplateListener $listener;

    protected function onSetUp(): void
    {
        $this->listener = self::getService(RenderTemplateListener::class);
    }

    public function testPrePersistAddsLeadingSlashWhenMissing(): void
    {
        $renderTemplate = new RenderTemplate();
        $renderTemplate->setPath('test/path');

        $this->listener->prePersist($renderTemplate);

        $this->assertSame('/test/path', $renderTemplate->getPath());
    }

    public function testPrePersistKeepsLeadingSlashWhenPresent(): void
    {
        $renderTemplate = new RenderTemplate();
        $renderTemplate->setPath('/test/path');

        $this->listener->prePersist($renderTemplate);

        $this->assertSame('/test/path', $renderTemplate->getPath());
    }

    public function testPrePersistTrimsPathAndAddsLeadingSlash(): void
    {
        $renderTemplate = new RenderTemplate();
        $renderTemplate->setPath('  test/path  ');

        $this->listener->prePersist($renderTemplate);

        $this->assertSame('/test/path', $renderTemplate->getPath());
    }

    public function testPreUpdateAddsLeadingSlashWhenMissing(): void
    {
        $renderTemplate = new RenderTemplate();
        $renderTemplate->setPath('test/path');

        $this->listener->preUpdate($renderTemplate);

        $this->assertSame('/test/path', $renderTemplate->getPath());
    }

    public function testPreUpdateKeepsLeadingSlashWhenPresent(): void
    {
        $renderTemplate = new RenderTemplate();
        $renderTemplate->setPath('/test/path');

        $this->listener->preUpdate($renderTemplate);

        $this->assertSame('/test/path', $renderTemplate->getPath());
    }

    public function testPreUpdateTrimsPathAndAddsLeadingSlash(): void
    {
        $renderTemplate = new RenderTemplate();
        $renderTemplate->setPath('  test/path  ');

        $this->listener->preUpdate($renderTemplate);

        $this->assertSame('/test/path', $renderTemplate->getPath());
    }

    public function testEnsurePathHasLeftSlashWithEmptyPath(): void
    {
        $renderTemplate = new RenderTemplate();
        $renderTemplate->setPath('');

        $this->listener->ensurePathHasLeftSlash($renderTemplate);

        $this->assertSame('/', $renderTemplate->getPath());
    }

    public function testEnsurePathHasLeftSlashWithOnlySpaces(): void
    {
        $renderTemplate = new RenderTemplate();
        $renderTemplate->setPath('   ');

        $this->listener->ensurePathHasLeftSlash($renderTemplate);

        $this->assertSame('/', $renderTemplate->getPath());
    }

    public function testEnsurePathHasLeftSlashWithMultipleSlashes(): void
    {
        $renderTemplate = new RenderTemplate();
        $renderTemplate->setPath('//test/path');

        $this->listener->ensurePathHasLeftSlash($renderTemplate);

        $this->assertSame('//test/path', $renderTemplate->getPath());
    }
}
