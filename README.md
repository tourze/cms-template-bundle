# CMS Template Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![PHP Version Require](https://poser.pugx.org/tourze/cms-template-bundle/require/php)](https://packagist.org/packages/tourze/cms-template-bundle)
[![Latest Version](https://img.shields.io/packagist/v/tourze/cms-template-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/cms-template-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/cms-template-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/cms-template-bundle)
[![License](https://img.shields.io/packagist/l/tourze/cms-template-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/cms-template-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/monorepo/ci.yml?branch=master)](https://github.com/tourze/monorepo/actions)
[![Coverage Status](https://img.shields.io/codecov/c/github/tourze/monorepo)](https://codecov.io/gh/tourze/monorepo)

A Symfony bundle for managing and rendering dynamic CMS templates with routing support.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Usage](#usage)
- [Advanced Usage](#advanced-usage)
- [Components](#components)
- [Development](#development)
- [Contributing](#contributing)
- [Security Vulnerabilities](#security-vulnerabilities)
- [License](#license)

## Features

- **Dynamic Template Management** - Store and manage templates in database with full CRUD operations
- **Automatic Route Generation** - Templates automatically generate routes based on their path configuration
- **Twig Integration** - Full Twig template engine support with dynamic template compilation
- **EAV Model Integration** - Seamless integration with Entity-Attribute-Value models for dynamic content
- **Hierarchical Templates** - Support for parent-child template relationships
- **Advanced Caching** - Route and template caching for improved performance
- **Flexible Routing** - Support for dynamic path parameters and route conditions
- **Admin Interface** - Built-in admin menu integration for template management
- **Audit Trail** - Full tracking of template changes with IP, timestamp, and user information

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine ORM 3.0 or higher

## Installation

```bash
composer require tourze/cms-template-bundle
```

## Configuration

This bundle is automatically configured when installed. No additional configuration is required.

## Quick Start

```php
<?php

use Tourze\CmsTemplateBundle\Entity\RenderTemplate;
use Doctrine\ORM\EntityManagerInterface;

// Create a new template
$template = new RenderTemplate();
$template->setPath('/welcome');
$template->setTitle('Welcome Page');
$template->setContent('<h1>Welcome to {{ title }}!</h1><p>Current path: {{ path }}</p>');
$template->setValid(true);

$entityManager->persist($template);
$entityManager->flush();

// Now visit /welcome in your browser to see the rendered template
```

## Usage

### Template with Parameters

You can create templates that accept dynamic parameters:

```php
use Tourze\CmsTemplateBundle\Entity\RenderTemplate;

$template = new RenderTemplate();
$template->setPath('/product/{id}');
$template->setTitle('Product Details');
$template->setContent('
<h1>Product Details</h1>
<p>Product ID: {{ id }}</p>
{% if model and entity %}
    <h2>{{ entity.name ?? "Unknown Product" }}</h2>
    <p>Model: {{ model.name }}</p>
{% endif %}
');
$template->setValid(true);

$entityManager->persist($template);
$entityManager->flush();
```

### Hierarchical Templates

Templates can have parent-child relationships:

```php
// Parent template
$parentTemplate = new RenderTemplate();
$parentTemplate->setPath('/catalog');
$parentTemplate->setTitle('Product Catalog');
$parentTemplate->setContent('<h1>Product Catalog</h1>{{ content }}');
$parentTemplate->setValid(true);

// Child template
$childTemplate = new RenderTemplate();
$childTemplate->setPath('/catalog/featured');
$childTemplate->setTitle('Featured Products');
$childTemplate->setContent('<div class="featured">{{ featured_items }}</div>');
$childTemplate->setParent($parentTemplate);
$childTemplate->setValid(true);

$entityManager->persist($parentTemplate);
$entityManager->persist($childTemplate);
$entityManager->flush();
```

### Template Rendering

Templates are automatically rendered when accessing their configured path. The bundle uses a routing condition 
service to match incoming requests against stored template paths.

### Integration with EAV Models

The bundle integrates with the CMS EAV Bundle to support dynamic content models:

- Model data can be passed to templates via URL parameters
- Entity data is automatically loaded and available in templates

### Available Template Variables

When rendering templates, the following variables are automatically available:

- `path` - The requested path (e.g., `/product/123`)
- `model` - The EAV model instance (when model_code or model_id is provided)
- `entity` - The EAV entity instance (when entity_id is provided)
- `title` - The template title
- All URL parameters (e.g., `id` from `/product/{id}`)
- All request attributes

### Template Caching

The bundle automatically caches template routes for improved performance. Cache is automatically invalidated when 
templates are modified. The cache uses the following configuration:

- **Cache Duration**: 24 hours
- **Cache Tags**: Automatically tagged with template entity class
- **Cache Key**: `cms-template-routes`

## Advanced Usage

### Custom Template Variables

You can extend templates with custom variables by creating event listeners:

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class TemplateVariableSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        // Add custom variables to request attributes
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

### Performance Optimization

For high-traffic sites, consider implementing these optimizations:

1. **Preload Templates**: Use Symfony's cache preloading to warm template routes
2. **CDN Integration**: Cache rendered templates at the CDN level
3. **Database Indexing**: Add indexes on frequently queried template fields

### Security Considerations

- Template content is executed as Twig templates - ensure proper input validation
- Use Symfony's security component to restrict template management access
- Consider implementing template sandboxing for user-generated content

## Components

### Entities

- **`RenderTemplate`** - Main template entity with the following features:
  - Path-based routing configuration
  - Twig template content storage
  - Hierarchical parent-child relationships
  - Validation status management
  - Full audit trail (timestamps, user tracking, IP tracking)

### Controllers

- **`RenderController`** - Handles template rendering with dynamic route matching and EAV integration

### Services

- **`RoutingCondition`** - Smart routing condition service that:
  - Matches incoming requests against template paths
  - Supports dynamic route parameters
  - Implements intelligent caching
  - Handles route priority and fallback logic

### Repositories

- **`RenderTemplateRepository`** - Enhanced repository with specialized queries for template management

## Development

### Running Tests

```bash
./vendor/bin/phpunit packages/cms-template-bundle/tests
```

### Code Analysis

```bash
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/cms-template-bundle
```

## Contributing

Thank you for considering contributing to this package! Please follow these guidelines:

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation when needed
- Use descriptive commit messages

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to security@tourze.com.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.