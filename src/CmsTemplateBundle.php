<?php

namespace Tourze\CmsTemplateBundle;

use Tourze\CmsBundle\CmsBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle;
use Tourze\RoutingAutoLoaderBundle\RoutingAutoLoaderBundle;

class CmsTemplateBundle extends Bundle implements BundleDependencyInterface
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    public static function getBundleDependencies(): array
    {
        return [
            DoctrineSnowflakeBundle::class => ['all' => true],
            CmsBundle::class => ['all' => true],
            DoctrineBundle::class => ['all' => true],
            RoutingAutoLoaderBundle::class => ['all' => true],
            EasyAdminMenuBundle::class => ['all' => true],
        ];
    }
}
