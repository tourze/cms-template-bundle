<?php

namespace Tourze\CmsTemplateBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CmsTemplateBundle\Entity\RenderTemplate;
use Tourze\EnumExtra\Itemable;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(RenderTemplate::class)]
final class RenderTemplateTest extends AbstractEntityTestCase
{
    protected function createEntity(): RenderTemplate
    {
        return new RenderTemplate();
    }

    public function testImplementsStringable(): void
    {
        $this->assertInstanceOf(\Stringable::class, $this->createEntity());
    }

    public function testImplementsItemable(): void
    {
        $this->assertInstanceOf(Itemable::class, $this->createEntity());
    }

    public function testInitialState(): void
    {
        $template = $this->createEntity();
        $this->assertNull($template->getId());
        $this->assertNull($template->getPath());
        $this->assertNull($template->getTitle());
        $this->assertNull($template->getContent());
        $this->assertNull($template->getParent());
        $this->assertInstanceOf(ArrayCollection::class, $template->getChildren());
        $this->assertCount(0, $template->getChildren());
        $this->assertFalse($template->isValid());
    }

    public function testPathProperty(): void
    {
        $path = '/test/path';
        $template = $this->createEntity();
        $template->setPath($path);

        $this->assertEquals($path, $template->getPath());
    }

    public function testPathCanBeNull(): void
    {
        $template = $this->createEntity();
        $template->setPath(null);

        $this->assertNull($template->getPath());
    }

    public function testTitleProperty(): void
    {
        $title = 'Test Title';
        $template = $this->createEntity();
        $template->setTitle($title);

        $this->assertEquals($title, $template->getTitle());
    }

    public function testContentProperty(): void
    {
        $content = '<h1>Test Content</h1>';
        $template = $this->createEntity();
        $template->setContent($content);

        $this->assertEquals($content, $template->getContent());
    }

    public function testValidProperty(): void
    {
        $template = $this->createEntity();
        $template->setValid(true);
        $this->assertTrue($template->isValid());

        $template->setValid(false);
        $this->assertFalse($template->isValid());

        $template->setValid(null);
        $this->assertNull($template->isValid());
    }

    public function testParentChildRelationship(): void
    {
        $parent = $this->createEntity();
        $child = $this->createEntity();

        $child->setParent($parent);

        $this->assertEquals($parent, $child->getParent());
    }

    public function testAddChild(): void
    {
        $parent = $this->createEntity();
        $child = $this->createEntity();

        $parent->addChild($child);

        $this->assertCount(1, $parent->getChildren());
        $this->assertTrue($parent->getChildren()->contains($child));
        $this->assertEquals($parent, $child->getParent());
    }

    public function testAddSameChildTwice(): void
    {
        $parent = $this->createEntity();
        $child = $this->createEntity();

        $parent->addChild($child);
        $parent->addChild($child);

        $this->assertCount(1, $parent->getChildren());
    }

    public function testRemoveChild(): void
    {
        $parent = $this->createEntity();
        $child = $this->createEntity();

        $parent->addChild($child);
        $this->assertCount(1, $parent->getChildren());

        $parent->removeChild($child);

        $this->assertCount(0, $parent->getChildren());
        $this->assertNull($child->getParent());
    }

    public function testRemoveNonExistingChild(): void
    {
        $parent = $this->createEntity();
        $child = $this->createEntity();

        $parent->removeChild($child);

        $this->assertCount(0, $parent->getChildren());
    }

    public function testCreatedByProperty(): void
    {
        $createdBy = 'user123';
        $template = $this->createEntity();
        $template->setCreatedBy($createdBy);

        $this->assertEquals($createdBy, $template->getCreatedBy());
    }

    public function testUpdatedByProperty(): void
    {
        $updatedBy = 'user456';
        $template = $this->createEntity();
        $template->setUpdatedBy($updatedBy);

        $this->assertEquals($updatedBy, $template->getUpdatedBy());
    }

    public function testCreatedFromIpProperty(): void
    {
        $ip = '192.168.1.1';
        $template = $this->createEntity();
        $template->setCreatedFromIp($ip);

        $this->assertEquals($ip, $template->getCreatedFromIp());
    }

    public function testUpdatedFromIpProperty(): void
    {
        $ip = '10.0.0.1';
        $template = $this->createEntity();
        $template->setUpdatedFromIp($ip);

        $this->assertEquals($ip, $template->getUpdatedFromIp());
    }

    public function testCreateTimeProperty(): void
    {
        $now = new \DateTimeImmutable();
        $template = $this->createEntity();
        $template->setCreateTime($now);

        $this->assertEquals($now, $template->getCreateTime());
    }

    public function testUpdateTimeProperty(): void
    {
        $now = new \DateTimeImmutable();
        $template = $this->createEntity();
        $template->setUpdateTime($now);

        $this->assertEquals($now, $template->getUpdateTime());
    }

    public function testToStringWithoutId(): void
    {
        $template = $this->createEntity();
        $result = (string) $template;

        $this->assertEquals('', $result);
    }

    public function testToStringWithIdAndTitle(): void
    {
        // 由于ID是通过Snowflake生成的，我们模拟一个有标题的场景
        $title = 'Test Template';
        $template = $this->createEntity();
        $template->setTitle($title);

        // 我们需要通过反射设置ID，因为它通常由Doctrine生成
        $reflection = new \ReflectionClass($template);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($template, '123456789');

        $result = (string) $template;

        $this->assertEquals($title, $result);
    }

    public function testToSelectItemWithNullValues(): void
    {
        // 设置ID
        $template = $this->createEntity();
        $reflection = new \ReflectionClass($template);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($template, '123456789');

        $result = $template->toSelectItem();

        $expectedText = '()';
        $this->assertEquals([
            'id' => '123456789',
            'text' => $expectedText,
        ], $result);
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'path' => ['path', '/test/path'];
        yield 'title' => ['title', 'Test Template'];
        yield 'content' => ['content', '<h1>Test Content</h1>'];
        yield 'valid' => ['valid', true];
        yield 'createdBy' => ['createdBy', 'user123'];
        yield 'updatedBy' => ['updatedBy', 'user456'];
        yield 'createdFromIp' => ['createdFromIp', '192.168.1.1'];
        yield 'updatedFromIp' => ['updatedFromIp', '10.0.0.1'];
        yield 'createTime' => ['createTime', new \DateTimeImmutable()];
        yield 'updateTime' => ['updateTime', new \DateTimeImmutable()];
    }
}
