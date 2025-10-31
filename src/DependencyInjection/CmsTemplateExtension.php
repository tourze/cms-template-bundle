<?php

namespace Tourze\CmsTemplateBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class CmsTemplateExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
