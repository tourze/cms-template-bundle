<?php

namespace Tourze\CmsTemplateBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CmsTemplateBundle\Entity\RenderTemplate;

/**
 * @method RenderTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method RenderTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method RenderTemplate[]    findAll()
 * @method RenderTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RenderTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RenderTemplate::class);
    }
}
