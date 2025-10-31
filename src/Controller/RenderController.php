<?php

namespace Tourze\CmsTemplateBundle\Controller;

use CmsBundle\Service\EntityService;
use CmsBundle\Service\ModelService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Tourze\CmsTemplateBundle\Repository\RenderTemplateRepository;
use Tourze\CmsTemplateBundle\Service\RoutingCondition;
use Twig\Environment;

final class RenderController extends AbstractController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ModelService $modelService,
        private readonly EntityService $entityService,
        private readonly RenderTemplateRepository $templateRepository,
    ) {
    }

    #[Route(
        path: '/{path}',
        name: 'cms-render-main',
        requirements: ['path' => Requirement::CATCH_ALL],
        condition: "service('cms_routing_condition').check(context, request)",
        priority: -999
    )]
    public function __invoke(string $path, Request $request): Response
    {
        $template = $this->templateRepository->findOneBy([
            'id' => $request->attributes->get(RoutingCondition::TEMPLATE_KEY),
            'valid' => true,
        ]);
        if (null === $template) {
            throw new NotFoundHttpException('查找模板失败');
        }

        $model = null;
        if ($request->attributes->has('model_code')) {
            $model = $this->modelService->findValidModelByCode(
                $request->attributes->get('model_code')
            );
        }
        if ($request->attributes->has('model_id')) {
            $model = $this->modelService->findValidModelById(
                $request->attributes->get('model_id')
            );
        }

        $entity = null;
        if ($request->attributes->has('entity_id')) {
            $entity = $this->entityService->findEntityById(
                $request->attributes->get('entity_id')
            );
        }
        if (null !== $entity && null === $model) {
            $model = $entity->getModel();
        }

        $twigTemplate = $this->twig->createTemplate($template->getContent() ?? '');
        $content = $twigTemplate->render([
            ...$request->attributes->all(),
            'path' => $path,
            'model' => $model,
            'entity' => $entity,
        ]);
        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($content);

        return $response;
    }
}
