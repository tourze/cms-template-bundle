<?php

declare(strict_types=1);

namespace Tourze\CmsTemplateBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\CmsTemplateBundle\Entity\RenderTemplate;

/**
 * @extends AbstractCrudController<RenderTemplate>
 */
#[AdminCrud(routePath: '/cms-template/render-template', routeName: 'cms_template_render_template')]
final class RenderTemplateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RenderTemplate::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('渲染模板')
            ->setEntityLabelInPlural('渲染模板管理')
            ->setSearchFields(['path', 'title', 'content'])
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setHelp('index', '管理页面渲染模板，用于动态页面内容渲染')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield TextField::new('path', '路径')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('页面路径，必须以 / 开头')
        ;

        yield TextField::new('title', '标题')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('模板标题，用于后台管理识别')
        ;

        yield TextareaField::new('content', '模板内容')
            ->setRequired(true)
            ->hideOnIndex()
            ->setColumns(12)
            ->setHelp('模板的HTML内容，支持Twig语法')
        ;

        yield BooleanField::new('valid', '有效')
            ->setColumns(3)
            ->setHelp('是否启用此模板')
        ;

        yield AssociationField::new('parent', '父级模板')
            ->setColumns(6)
            ->setFormTypeOptions([
                'choice_label' => function (RenderTemplate $template) {
                    return sprintf('%s (%s)', $template->getTitle(), $template->getPath());
                },
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('t')
                        ->orderBy('t.title', 'ASC')
                    ;
                },
                'required' => false,
            ])
            ->setHelp('选择父级模板（可选）')
            ->hideOnIndex()
        ;

        if (Crud::PAGE_INDEX === $pageName) {
            yield TextField::new('contentPreview', '内容预览');
        }

        yield DateTimeField::new('createTime', '创建时间')
            ->onlyOnIndex()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->onlyOnIndex()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('path', '路径'))
            ->add(TextFilter::new('title', '标题'))
            ->add(BooleanFilter::new('valid', '有效'))
            ->add(EntityFilter::new('parent', '父级模板'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
