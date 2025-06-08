<?php

namespace Tourze\CmsTemplateBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\CmsTemplateBundle\CmsTemplateBundle;

class CmsTemplateBundleTest extends TestCase
{
    public function test_bundle_extends_base_bundle(): void
    {
        $bundle = new CmsTemplateBundle();

        $this->assertInstanceOf(Bundle::class, $bundle);
    }

    public function test_bundle_has_correct_name(): void
    {
        $bundle = new CmsTemplateBundle();

        $this->assertEquals('CmsTemplateBundle', $bundle->getName());
    }

    public function test_bundle_has_correct_namespace(): void
    {
        $bundle = new CmsTemplateBundle();

        $this->assertEquals('Tourze\CmsTemplateBundle', $bundle->getNamespace());
    }

    public function test_bundle_instantiation(): void
    {
        $bundle = new CmsTemplateBundle();

        $this->assertNotNull($bundle);
        $this->assertInstanceOf(CmsTemplateBundle::class, $bundle);
    }
}
