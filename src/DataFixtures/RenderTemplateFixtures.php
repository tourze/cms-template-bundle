<?php

declare(strict_types=1);

namespace Tourze\CmsTemplateBundle\DataFixtures;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\CmsTemplateBundle\Entity\RenderTemplate;

#[When(env: 'test')]
#[When(env: 'dev')]
final class RenderTemplateFixtures extends Fixture
{
    public const RENDER_TEMPLATE_REFERENCE_PREFIX = 'render-template-';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('zh_CN');

        // 创建首页模板
        $homeTemplate = new RenderTemplate();
        $homeTemplate->setPath('/');
        $homeTemplate->setTitle('首页模板');
        $homeTemplate->setContent('<h1>欢迎访问我们的网站</h1><p>这是首页模板内容</p>');
        $homeTemplate->setValid(true);
        $homeTemplate->setCreateTime(CarbonImmutable::now()->modify('-30 days'));
        $homeTemplate->setUpdateTime(CarbonImmutable::now()->modify('-30 days'));

        $manager->persist($homeTemplate);
        $this->addReference(self::RENDER_TEMPLATE_REFERENCE_PREFIX . '0', $homeTemplate);

        // 创建关于我们页面模板
        $aboutTemplate = new RenderTemplate();
        $aboutTemplate->setPath('/about');
        $aboutTemplate->setTitle('关于我们');
        $aboutTemplate->setContent('<h1>关于我们</h1><p>我们是一家专业的技术公司</p>');
        $aboutTemplate->setValid(true);
        $aboutTemplate->setCreateTime(CarbonImmutable::now()->modify('-25 days'));
        $aboutTemplate->setUpdateTime(CarbonImmutable::now()->modify('-25 days'));

        $manager->persist($aboutTemplate);
        $this->addReference(self::RENDER_TEMPLATE_REFERENCE_PREFIX . '1', $aboutTemplate);

        // 创建联系我们页面模板
        $contactTemplate = new RenderTemplate();
        $contactTemplate->setPath('/contact');
        $contactTemplate->setTitle('联系我们');
        $contactTemplate->setContent('<h1>联系我们</h1><p>电话：400-123-4567</p><p>邮箱：contact@test.local</p>');
        $contactTemplate->setValid(true);
        $contactTemplate->setCreateTime(CarbonImmutable::now()->modify('-20 days'));
        $contactTemplate->setUpdateTime(CarbonImmutable::now()->modify('-20 days'));

        $manager->persist($contactTemplate);
        $this->addReference(self::RENDER_TEMPLATE_REFERENCE_PREFIX . '2', $contactTemplate);

        // 创建产品页面模板作为父模板
        $productParentTemplate = new RenderTemplate();
        $productParentTemplate->setPath('/products');
        $productParentTemplate->setTitle('产品中心');
        $productParentTemplate->setContent('<h1>产品中心</h1><div class="product-list">{{ products }}</div>');
        $productParentTemplate->setValid(true);
        $productParentTemplate->setCreateTime(CarbonImmutable::now()->modify('-15 days'));
        $productParentTemplate->setUpdateTime(CarbonImmutable::now()->modify('-15 days'));

        $manager->persist($productParentTemplate);
        $this->addReference(self::RENDER_TEMPLATE_REFERENCE_PREFIX . '3', $productParentTemplate);

        // 创建产品详情子模板
        $productDetailTemplate = new RenderTemplate();
        $productDetailTemplate->setPath('/products/detail');
        $productDetailTemplate->setTitle('产品详情');
        $productDetailTemplate->setContent('<h2>产品详情</h2><div class="product-detail">{{ product_detail }}</div>');
        $productDetailTemplate->setParent($productParentTemplate);
        $productDetailTemplate->setValid(true);
        $productDetailTemplate->setCreateTime(CarbonImmutable::now()->modify('-10 days'));
        $productDetailTemplate->setUpdateTime(CarbonImmutable::now()->modify('-10 days'));

        $manager->persist($productDetailTemplate);
        $this->addReference(self::RENDER_TEMPLATE_REFERENCE_PREFIX . '4', $productDetailTemplate);

        // 创建更多随机模板
        for ($i = 5; $i < 15; ++$i) {
            $template = new RenderTemplate();
            $template->setPath('/page' . $i);
            $template->setTitle($faker->sentence(3));
            $template->setContent('<h1>' . $faker->sentence(4) . '</h1><p>' . $faker->paragraph() . '</p>');
            $template->setValid($faker->boolean(80)); // 80% 概率为有效
            $template->setCreateTime(CarbonImmutable::now()->modify('-' . $faker->numberBetween(1, 30) . ' days'));
            $template->setUpdateTime(CarbonImmutable::now()->modify('-' . $faker->numberBetween(1, 30) . ' days'));

            $manager->persist($template);
            $this->addReference(self::RENDER_TEMPLATE_REFERENCE_PREFIX . $i, $template);
        }

        $manager->flush();
    }
}
