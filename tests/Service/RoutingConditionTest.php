<?php

namespace Tourze\CmsTemplateBundle\Tests\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Tourze\CmsTemplateBundle\Entity\RenderTemplate;
use Tourze\CmsTemplateBundle\Repository\RenderTemplateRepository;
use Tourze\CmsTemplateBundle\Service\RoutingCondition;

class RoutingConditionTest extends TestCase
{
    private MockObject $templateRepository;
    private MockObject $logger;
    private MockObject $cache;
    private RoutingCondition $routingCondition;

    protected function setUp(): void
    {
        $this->templateRepository = $this->createMock(RenderTemplateRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);

        $this->routingCondition = new RoutingCondition(
            $this->templateRepository,
            $this->logger,
            $this->cache
        );
    }

    public function testCheckReturnsFalseWhenNoRoutesFound(): void
    {
        $context = new RequestContext();
        $request = new Request();
        
        $this->cache->expects($this->once())
            ->method('get')
            ->with('cms-template-routes')
            ->willReturn(null);

        $result = $this->routingCondition->check($context, $request);
        
        $this->assertFalse($result);
    }

    public function testCheckReturnsTrueWhenMatchingRouteFound(): void
    {
        $context = new RequestContext();
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/test-path']);
        $request->attributes = new ParameterBag();

        $template = $this->createMock(RenderTemplate::class);
        $template->expects($this->any())
            ->method('getId')
            ->willReturn('1');
        $template->expects($this->once())
            ->method('getPath')
            ->willReturn('/test-path');
        $template->expects($this->once())
            ->method('getTitle')
            ->willReturn('Test Template');

        $this->cache->expects($this->once())
            ->method('get')
            ->with('cms-template-routes')
            ->willReturnCallback(function ($key, $callback) use ($template) {
                $item = $this->createMock(ItemInterface::class);
                $item->expects($this->once())->method('set');
                $item->expects($this->once())->method('tag');
                $item->expects($this->once())->method('expiresAfter');
                
                $this->templateRepository->expects($this->once())
                    ->method('findBy')
                    ->with(['valid' => true])
                    ->willReturn([$template]);
                
                return $callback($item);
            });

        $result = $this->routingCondition->check($context, $request);
        
        $this->assertTrue($result);
        $this->assertEquals('1', $request->attributes->get(RoutingCondition::TEMPLATE_KEY));
    }

    public function testCheckReturnsFalseWhenNoMatchingRoute(): void
    {
        $context = new RequestContext();
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/non-matching-path']);
        $request->attributes = new ParameterBag();

        $template = $this->createMock(RenderTemplate::class);
        $template->expects($this->any())
            ->method('getId')
            ->willReturn('1');
        $template->expects($this->once())
            ->method('getPath')
            ->willReturn('/test-path');
        $template->expects($this->once())
            ->method('getTitle')
            ->willReturn('Test Template');

        $this->cache->expects($this->once())
            ->method('get')
            ->with('cms-template-routes')
            ->willReturnCallback(function ($key, $callback) use ($template) {
                $item = $this->createMock(ItemInterface::class);
                $item->expects($this->once())->method('set');
                $item->expects($this->once())->method('tag');
                $item->expects($this->once())->method('expiresAfter');
                
                $this->templateRepository->expects($this->once())
                    ->method('findBy')
                    ->with(['valid' => true])
                    ->willReturn([$template]);
                
                return $callback($item);
            });

        $result = $this->routingCondition->check($context, $request);
        
        $this->assertFalse($result);
    }

    public function testCheckHandlesRepositoryException(): void
    {
        $context = new RequestContext();
        $request = new Request();

        $exception = new \Exception('Database error');
        
        $this->cache->expects($this->once())
            ->method('get')
            ->with('cms-template-routes')
            ->willReturnCallback(function ($key, $callback) use ($exception) {
                $item = $this->createMock(ItemInterface::class);
                
                $this->templateRepository->expects($this->once())
                    ->method('findBy')
                    ->with(['valid' => true])
                    ->willThrowException($exception);
                
                $this->logger->expects($this->once())
                    ->method('error')
                    ->with('检查CMS路由配置时发生异常', [
                        'exception' => $exception,
                    ]);
                
                return $callback($item);
            });

        $result = $this->routingCondition->check($context, $request);
        
        $this->assertFalse($result);
    }
}