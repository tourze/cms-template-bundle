<?php

namespace Tourze\CmsTemplateBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Tourze\CmsTemplateBundle\Entity\RenderTemplate;
use Tourze\EnumExtra\Itemable;

class RenderTemplateTest extends TestCase
{
    private RenderTemplate $template;

    protected function setUp(): void
    {
        $this->template = new RenderTemplate();
    }

    public function test_implements_stringable(): void
    {
        $this->assertInstanceOf(\Stringable::class, $this->template);
    }

    public function test_implements_itemable(): void
    {
        $this->assertInstanceOf(Itemable::class, $this->template);
    }

    public function test_initial_state(): void
    {
        $this->assertNull($this->template->getId());
        $this->assertNull($this->template->getPath());
        $this->assertNull($this->template->getTitle());
        $this->assertNull($this->template->getContent());
        $this->assertNull($this->template->getParent());
        $this->assertInstanceOf(ArrayCollection::class, $this->template->getChildren());
        $this->assertCount(0, $this->template->getChildren());
        $this->assertFalse($this->template->isValid());
    }

    public function test_path_property(): void
    {
        $path = '/test/path';
        $this->template->setPath($path);

        $this->assertEquals($path, $this->template->getPath());
    }

    public function test_path_can_be_null(): void
    {
        $this->template->setPath(null);

        $this->assertNull($this->template->getPath());
    }

    public function test_title_property(): void
    {
        $title = 'Test Title';
        $this->template->setTitle($title);

        $this->assertEquals($title, $this->template->getTitle());
    }

    public function test_content_property(): void
    {
        $content = '<h1>Test Content</h1>';
        $this->template->setContent($content);

        $this->assertEquals($content, $this->template->getContent());
    }

    public function test_valid_property(): void
    {
        $this->template->setValid(true);
        $this->assertTrue($this->template->isValid());

        $this->template->setValid(false);
        $this->assertFalse($this->template->isValid());

        $this->template->setValid(null);
        $this->assertNull($this->template->isValid());
    }

    public function test_parent_child_relationship(): void
    {
        $parent = new RenderTemplate();
        $child = new RenderTemplate();

        $child->setParent($parent);

        $this->assertEquals($parent, $child->getParent());
    }

    public function test_add_child(): void
    {
        $parent = new RenderTemplate();
        $child = new RenderTemplate();

        $parent->addChild($child);

        $this->assertCount(1, $parent->getChildren());
        $this->assertTrue($parent->getChildren()->contains($child));
        $this->assertEquals($parent, $child->getParent());
    }

    public function test_add_same_child_twice(): void
    {
        $parent = new RenderTemplate();
        $child = new RenderTemplate();

        $parent->addChild($child);
        $parent->addChild($child);

        $this->assertCount(1, $parent->getChildren());
    }

    public function test_remove_child(): void
    {
        $parent = new RenderTemplate();
        $child = new RenderTemplate();

        $parent->addChild($child);
        $this->assertCount(1, $parent->getChildren());

        $parent->removeChild($child);

        $this->assertCount(0, $parent->getChildren());
        $this->assertNull($child->getParent());
    }

    public function test_remove_non_existing_child(): void
    {
        $parent = new RenderTemplate();
        $child = new RenderTemplate();

        $parent->removeChild($child);

        $this->assertCount(0, $parent->getChildren());
    }

    public function test_created_by_property(): void
    {
        $createdBy = 'user123';
        $this->template->setCreatedBy($createdBy);

        $this->assertEquals($createdBy, $this->template->getCreatedBy());
    }

    public function test_updated_by_property(): void
    {
        $updatedBy = 'user456';
        $this->template->setUpdatedBy($updatedBy);

        $this->assertEquals($updatedBy, $this->template->getUpdatedBy());
    }

    public function test_created_from_ip_property(): void
    {
        $ip = '192.168.1.1';
        $this->template->setCreatedFromIp($ip);

        $this->assertEquals($ip, $this->template->getCreatedFromIp());
    }

    public function test_updated_from_ip_property(): void
    {
        $ip = '10.0.0.1';
        $this->template->setUpdatedFromIp($ip);

        $this->assertEquals($ip, $this->template->getUpdatedFromIp());
    }

    public function test_create_time_property(): void
    {
        $now = new \DateTime();
        $this->template->setCreateTime($now);

        $this->assertEquals($now, $this->template->getCreateTime());
    }

    public function test_update_time_property(): void
    {
        $now = new \DateTime();
        $this->template->setUpdateTime($now);

        $this->assertEquals($now, $this->template->getUpdateTime());
    }

    public function test_to_string_without_id(): void
    {
        $result = (string) $this->template;

        $this->assertEquals('', $result);
    }

    public function test_to_string_with_id_and_title(): void
    {
        // 由于ID是通过Snowflake生成的，我们模拟一个有标题的场景
        $title = 'Test Template';
        $this->template->setTitle($title);

        // 我们需要通过反射设置ID，因为它通常由Doctrine生成
        $reflection = new \ReflectionClass($this->template);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->template, '123456789');

        $result = (string) $this->template;

        $this->assertEquals($title, $result);
    }

    public function test_to_select_item(): void
    {
        $title = 'Test Template';
        $path = '/test/path';

        $this->template->setTitle($title);
        $this->template->setPath($path);

        // 设置ID
        $reflection = new \ReflectionClass($this->template);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->template, '123456789');

        $result = $this->template->toSelectItem();

        $expectedText = "{$title}({$path})";
        $this->assertEquals([
            'label' => $expectedText,
            'text' => $expectedText,
            'value' => '123456789',
        ], $result);
    }

    public function test_to_select_item_with_null_values(): void
    {
        // 设置ID
        $reflection = new \ReflectionClass($this->template);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->template, '123456789');

        $result = $this->template->toSelectItem();

        $expectedText = "()";
        $this->assertEquals([
            'label' => $expectedText,
            'text' => $expectedText,
            'value' => '123456789',
        ], $result);
    }
}
