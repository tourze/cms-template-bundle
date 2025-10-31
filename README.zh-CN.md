# CMS 模板包

[English](README.md) | [中文](README.zh-CN.md)

[![PHP Version Require](https://poser.pugx.org/tourze/cms-template-bundle/require/php)](https://packagist.org/packages/tourze/cms-template-bundle)
[![Latest Version](https://img.shields.io/packagist/v/tourze/cms-template-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/cms-template-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/cms-template-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/cms-template-bundle)
[![License](https://img.shields.io/packagist/l/tourze/cms-template-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/cms-template-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/monorepo/ci.yml?branch=master)](https://github.com/tourze/monorepo/actions)
[![Coverage Status](https://img.shields.io/codecov/c/github/tourze/monorepo)](https://codecov.io/gh/tourze/monorepo)

一个用于管理和渲染动态 CMS 模板的 Symfony 包，支持路由功能。

## 目录

- [功能特性](#功能特性)
- [系统要求](#系统要求)
- [安装](#安装)
- [配置](#配置)
- [快速开始](#快速开始)
- [使用方法](#使用方法)
- [高级用法](#高级用法)
- [组件](#组件)
- [开发](#开发)
- [贡献](#贡献)
- [安全漏洞](#安全漏洞)
- [许可证](#许可证)

## 功能特性

- **动态模板管理** - 在数据库中存储和管理模板，支持完整的 CRUD 操作
- **自动路由生成** - 模板根据其路径配置自动生成路由
- **Twig 集成** - 完整的 Twig 模板引擎支持，支持动态模板编译
- **EAV 模型集成** - 与实体-属性-值模型无缝集成，支持动态内容
- **层次化模板** - 支持父子模板关系
- **高级缓存** - 路由和模板缓存，提高性能
- **灵活路由** - 支持动态路径参数和路由条件
- **管理后台** - 内置管理菜单集成，用于模板管理
- **审计跟踪** - 完整的模板变更跟踪，包括 IP、时间戳和用户信息

## 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine ORM 3.0 或更高版本

## 安装

```bash
composer require tourze/cms-template-bundle
```

## 配置

此包在安装时会自动配置，无需额外配置。

## 快速开始

```php
<?php

use Tourze\CmsTemplateBundle\Entity\RenderTemplate;
use Doctrine\ORM\EntityManagerInterface;

// 创建新模板
$template = new RenderTemplate();
$template->setPath('/welcome');
$template->setTitle('欢迎页面');
$template->setContent('<h1>欢迎来到 {{ title }}！</h1><p>当前路径：{{ path }}</p>');
$template->setValid(true);

$entityManager->persist($template);
$entityManager->flush();

// 现在在浏览器中访问 /welcome 即可看到渲染的模板
```

## 使用方法

### 带参数的模板

可以创建接受动态参数的模板：

```php
use Tourze\CmsTemplateBundle\Entity\RenderTemplate;

$template = new RenderTemplate();
$template->setPath('/product/{id}');
$template->setTitle('产品详情');
$template->setContent('
<h1>产品详情</h1>
<p>产品 ID：{{ id }}</p>
{% if model and entity %}
    <h2>{{ entity.name ?? "未知产品" }}</h2>
    <p>模型：{{ model.name }}</p>
{% endif %}
');
$template->setValid(true);

$entityManager->persist($template);
$entityManager->flush();
```

### 层次化模板

模板可以具有父子关系：

```php
// 父模板
$parentTemplate = new RenderTemplate();
$parentTemplate->setPath('/catalog');
$parentTemplate->setTitle('产品目录');
$parentTemplate->setContent('<h1>产品目录</h1>{{ content }}');
$parentTemplate->setValid(true);

// 子模板
$childTemplate = new RenderTemplate();
$childTemplate->setPath('/catalog/featured');
$childTemplate->setTitle('精选产品');
$childTemplate->setContent('<div class="featured">{{ featured_items }}</div>');
$childTemplate->setParent($parentTemplate);
$childTemplate->setValid(true);

$entityManager->persist($parentTemplate);
$entityManager->persist($childTemplate);
$entityManager->flush();
```

### 模板渲染

当访问模板配置的路径时，模板会自动渲染。该包使用路由条件服务来匹配传入请求与存储的模板路径。

### 与 EAV 模型集成

该包与 CMS EAV Bundle 集成，支持动态内容模型：

- 模型数据可通过 URL 参数传递给模板
- 实体数据会自动加载并在模板中可用

### 可用的模板变量

渲染模板时，以下变量会自动可用：

- `path` - 请求的路径（例如：`/product/123`）
- `model` - EAV 模型实例（当提供 model_code 或 model_id 时）
- `entity` - EAV 实体实例（当提供 entity_id 时）
- `title` - 模板标题
- 所有 URL 参数（例如：`/product/{id}` 中的 `id`）
- 所有请求属性

### 模板缓存

该包自动缓存模板路由以提高性能。缓存在模板修改时自动失效。缓存使用以下配置：

- **缓存持续时间**：24 小时
- **缓存标签**：自动标记为模板实体类
- **缓存键**：`cms-template-routes`

## 高级用法

### 自定义模板变量

您可以通过创建事件监听器来扩展模板的自定义变量：

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class TemplateVariableSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        // 向请求属性添加自定义变量
        $request->attributes->set('custom_data', [
            'user_id' => $this->getCurrentUserId(),
            'site_config' => $this->getSiteConfiguration(),
        ]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }
}
```

### 性能优化

对于高流量站点，请考虑实施以下优化：

1. **预加载模板**：使用 Symfony 的缓存预加载来预热模板路由
2. **CDN 集成**：在 CDN 级别缓存渲染的模板
3. **数据库索引**：为经常查询的模板字段添加索引

### 安全考虑

- 模板内容作为 Twig 模板执行 - 确保适当的输入验证
- 使用 Symfony 的安全组件来限制模板管理访问
- 考虑为用户生成的内容实施模板沙箱

## 组件

### 实体

- **`RenderTemplate`** - 主要的模板实体，具有以下功能：
  - 基于路径的路由配置
  - Twig 模板内容存储
  - 层次化父子关系
  - 验证状态管理
  - 完整的审计跟踪（时间戳、用户跟踪、IP 跟踪）

### 控制器

- **`RenderController`** - 处理模板渲染，支持动态路由匹配和 EAV 集成

### 服务

- **`RoutingCondition`** - 智能路由条件服务，功能包括：
  - 匹配传入请求与模板路径
  - 支持动态路由参数
  - 实现智能缓存
  - 处理路由优先级和回退逻辑

### 仓储

- **`RenderTemplateRepository`** - 增强的仓储，为模板管理提供专用查询

## 开发

### 运行测试

```bash
./vendor/bin/phpunit packages/cms-template-bundle/tests
```

### 代码分析

```bash
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/cms-template-bundle
```

## 贡献

感谢您考虑为此包做出贡献！请遵循以下指南：

- 遵循 PSR-12 编码标准
- 为新功能编写测试
- 根据需要更新文档
- 使用描述性的提交消息

## 安全漏洞

如果您发现安全漏洞，请发送电子邮件至 security@tourze.com。

## 许可证

MIT 许可证。请参阅 [License File](LICENSE) 了解更多信息。