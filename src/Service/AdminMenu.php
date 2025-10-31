<?php

namespace Tourze\CmsTemplateBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\CmsTemplateBundle\Entity\RenderTemplate;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('内容中心')) {
            $item->addChild('内容中心')->setExtra('permission', 'CmsBundle');
        }

        $contentCenter = $item->getChild('内容中心');
        if (null !== $contentCenter) {
            $contentCenter->addChild('渲染模板')->setUri($this->linkGenerator->getCurdListPage(RenderTemplate::class));
        }
    }
}
